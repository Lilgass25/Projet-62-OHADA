<?php
require_once __DIR__ . '/../../includes/auth.php';
exigerRole(['administrateur', 'juriste']);

$pdo = getPDO();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) redirect('liste.php');

$stmt = $pdo->prepare('SELECT * FROM formalites WHERE id_formalite = :id');
$stmt->execute([':id' => $id]);
$formalite = $stmt->fetch();
if (!$formalite) redirect('liste.php');

$societes = $pdo->query("SELECT id_societe, raison_sociale FROM societes ORDER BY raison_sociale")->fetchAll();
$erreurs = [];
$mode = 'modif';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierTokenCSRF($_POST['csrf_token'] ?? null)) {
        $erreurs[] = 'Session expirée, veuillez réessayer.';
    } else {
        foreach (['id_societe','type_formalite','description','date_echeance','date_realisation','statut'] as $champ) {
            $formalite[$champ] = trim($_POST[$champ] ?? '');
        }
        if (empty($formalite['id_societe'])) $erreurs[] = 'La société est obligatoire.';
        if ($formalite['description'] === '') $erreurs[] = 'La description est obligatoire.';

        // Si passage à "réalisée" sans date de réalisation, on la fixe à aujourd'hui
        if ($formalite['statut'] === 'realisee' && empty($formalite['date_realisation'])) {
            $formalite['date_realisation'] = date('Y-m-d');
        }

        if (empty($erreurs)) {
            $maj = $pdo->prepare("
                UPDATE formalites SET id_societe=:id_societe, type_formalite=:type_formalite, description=:description,
                    date_echeance=:date_echeance, date_realisation=:date_realisation, statut=:statut
                WHERE id_formalite=:id
            ");
            $maj->execute([
                ':id_societe' => $formalite['id_societe'], ':type_formalite' => $formalite['type_formalite'],
                ':description' => $formalite['description'], ':date_echeance' => $formalite['date_echeance'],
                ':date_realisation' => $formalite['date_realisation'] ?: null, ':statut' => $formalite['statut'], ':id' => $id,
            ]);
            logAudit('UPDATE', 'formalites', $id, 'Modification de la formalité ' . $formalite['description']);
            $_SESSION['flash_succes'] = 'Formalité modifiée avec succès.';
            redirect('liste.php');
        }
    }
}

$csrf = genererTokenCSRF();
$titrePage = 'Modifier une formalité';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="mb-3"><a href="liste.php" class="text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Retour</a></div>
<h3 class="fw-bold mb-4"><i class="fa-solid fa-file-pen text-warning me-2"></i>Modifier : <?= e($formalite['description']) ?></h3>
<?php if ($erreurs): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($erreurs as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php require __DIR__ . '/_formulaire.php'; ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
