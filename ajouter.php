<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/mailer.php';
exigerRole(['administrateur', 'juriste']);

$pdo = getPDO();
$societes = $pdo->query("SELECT id_societe, raison_sociale FROM societes ORDER BY raison_sociale")->fetchAll();
$erreurs = [];
$formalite = [];
$mode = 'ajout';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierTokenCSRF($_POST['csrf_token'] ?? null)) {
        $erreurs[] = 'Session expirée, veuillez réessayer.';
    } else {
        foreach (['id_societe','type_formalite','description','date_echeance','date_realisation','statut'] as $champ) {
            $formalite[$champ] = trim($_POST[$champ] ?? '');
        }
        if (empty($formalite['id_societe'])) $erreurs[] = 'La société est obligatoire.';
        if ($formalite['description'] === '') $erreurs[] = 'La description est obligatoire.';
        if (empty($formalite['date_echeance'])) $erreurs[] = "La date d'échéance est obligatoire.";

        if (empty($erreurs)) {
            $stmt = $pdo->prepare("
                INSERT INTO formalites (id_societe, type_formalite, description, date_echeance, date_realisation, statut, id_utilisateur_responsable)
                VALUES (:id_societe, :type_formalite, :description, :date_echeance, :date_realisation, :statut, :id_utilisateur)
            ");
            $stmt->execute([
                ':id_societe' => $formalite['id_societe'], ':type_formalite' => $formalite['type_formalite'],
                ':description' => $formalite['description'], ':date_echeance' => $formalite['date_echeance'],
                ':date_realisation' => $formalite['date_realisation'] ?: null, ':statut' => $formalite['statut'] ?: 'a_faire',
                ':id_utilisateur' => $_SESSION['id_utilisateur'],
            ]);
            $idNouveau = (int)$pdo->lastInsertId();
            logAudit('CREATE', 'formalites', $idNouveau, 'Ajout de la formalité ' . $formalite['description']);

            // --- Envoi d'un email automatique de confirmation au responsable (exigence obligatoire) ---
            $stmtUser = $pdo->prepare('SELECT email FROM utilisateurs WHERE id_utilisateur = :id');
            $stmtUser->execute([':id' => $_SESSION['id_utilisateur']]);
            $emailUtilisateur = $stmtUser->fetchColumn();
            $stmtSociete = $pdo->prepare('SELECT raison_sociale FROM societes WHERE id_societe = :id');
            $stmtSociete->execute([':id' => $formalite['id_societe']]);
            $raisonSociale = $stmtSociete->fetchColumn();

            $emailEnvoye = false;
            $erreurEnvoi = null;
            if ($emailUtilisateur) {
                $formaliteComplete = $formalite;
                $formaliteComplete['id_formalite'] = $idNouveau;
                $emailEnvoye = envoyerAlerteFormalite($formaliteComplete, $raisonSociale, $emailUtilisateur, $erreurEnvoi);
            }

            $_SESSION['flash_succes'] = 'Formalité créée avec succès.' .
                ($emailEnvoye ? ' Un email de confirmation a été envoyé.' : '');
            if (!$emailEnvoye) {
                $_SESSION['flash_erreur'] = $erreurEnvoi ?: "La formalité a été créée, mais l'email de confirmation n'a pas pu être envoyé.";
            }
            redirect('liste.php');
        }
    }
}

$csrf = genererTokenCSRF();
$titrePage = 'Ajouter une formalité';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="mb-3"><a href="liste.php" class="text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Retour</a></div>
<h3 class="fw-bold mb-4"><i class="fa-solid fa-file-circle-plus text-primary me-2"></i>Nouvelle formalité</h3>
<?php if ($erreurs): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($erreurs as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="alert alert-info small"><i class="fa-solid fa-circle-info me-1"></i>Un email de confirmation sera automatiquement envoyé au responsable à la création.</div>
<?php require __DIR__ . '/_formulaire.php'; ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
