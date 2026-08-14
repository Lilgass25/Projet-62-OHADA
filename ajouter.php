<?php
require_once __DIR__ . '/../../includes/auth.php';
exigerRole(['administrateur', 'juriste']);

$pdo = getPDO();
$societes = $pdo->query("SELECT id_societe, raison_sociale FROM societes ORDER BY raison_sociale")->fetchAll();
$erreurs = [];
$dirigeant = [];
$mode = 'ajout';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierTokenCSRF($_POST['csrf_token'] ?? null)) {
        $erreurs[] = 'Session expirée, veuillez réessayer.';
    } else {
        foreach (['id_societe','fonction','nom_complet','duree_mandat_mois','statut','date_debut_mandat','date_fin_mandat'] as $champ) {
            $dirigeant[$champ] = trim($_POST[$champ] ?? '');
        }
        if (empty($dirigeant['id_societe'])) $erreurs[] = 'La société est obligatoire.';
        if ($dirigeant['nom_complet'] === '') $erreurs[] = 'Le nom complet est obligatoire.';
        if (empty($dirigeant['date_debut_mandat'])) $erreurs[] = 'La date de début de mandat est obligatoire.';

        if (empty($erreurs)) {
            $stmt = $pdo->prepare("
                INSERT INTO dirigeants (id_societe, nom_complet, fonction, date_debut_mandat, date_fin_mandat, duree_mandat_mois, statut)
                VALUES (:id_societe, :nom_complet, :fonction, :date_debut_mandat, :date_fin_mandat, :duree_mandat_mois, :statut)
            ");
            $stmt->execute([
                ':id_societe' => $dirigeant['id_societe'], ':nom_complet' => $dirigeant['nom_complet'],
                ':fonction' => $dirigeant['fonction'], ':date_debut_mandat' => $dirigeant['date_debut_mandat'],
                ':date_fin_mandat' => $dirigeant['date_fin_mandat'] ?: null,
                ':duree_mandat_mois' => $dirigeant['duree_mandat_mois'] ?: 36, ':statut' => $dirigeant['statut'] ?: 'en_cours',
            ]);
            $idNouveau = (int)$pdo->lastInsertId();
            logAudit('CREATE', 'dirigeants', $idNouveau, 'Ajout du dirigeant ' . $dirigeant['nom_complet']);
            $_SESSION['flash_succes'] = 'Dirigeant ajouté avec succès.';
            redirect('liste.php');
        }
    }
}

$csrf = genererTokenCSRF();
$titrePage = 'Ajouter un dirigeant';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="mb-3"><a href="liste.php" class="text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Retour</a></div>
<h3 class="fw-bold mb-4"><i class="fa-solid fa-user-tie text-primary me-2"></i>Nouveau dirigeant</h3>
<?php if ($erreurs): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($erreurs as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php require __DIR__ . '/_formulaire.php'; ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
