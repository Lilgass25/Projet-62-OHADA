<?php
require_once __DIR__ . '/../../includes/auth.php';
exigerRole(['administrateur', 'juriste']);

$pdo = getPDO();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) redirect('liste.php');

$stmt = $pdo->prepare('SELECT * FROM dirigeants WHERE id_dirigeant = :id');
$stmt->execute([':id' => $id]);
$dirigeant = $stmt->fetch();
if (!$dirigeant) redirect('liste.php');

$societes = $pdo->query("SELECT id_societe, raison_sociale FROM societes ORDER BY raison_sociale")->fetchAll();
$erreurs = [];
$mode = 'modif';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierTokenCSRF($_POST['csrf_token'] ?? null)) {
        $erreurs[] = 'Session expirée, veuillez réessayer.';
    } else {
        foreach (['id_societe','fonction','nom_complet','duree_mandat_mois','statut','date_debut_mandat','date_fin_mandat'] as $champ) {
            $dirigeant[$champ] = trim($_POST[$champ] ?? '');
        }
        if (empty($dirigeant['id_societe'])) $erreurs[] = 'La société est obligatoire.';
        if ($dirigeant['nom_complet'] === '') $erreurs[] = 'Le nom complet est obligatoire.';

        if (empty($erreurs)) {
            $maj = $pdo->prepare("
                UPDATE dirigeants SET id_societe=:id_societe, nom_complet=:nom_complet, fonction=:fonction,
                    date_debut_mandat=:date_debut_mandat, date_fin_mandat=:date_fin_mandat,
                    duree_mandat_mois=:duree_mandat_mois, statut=:statut
                WHERE id_dirigeant=:id
            ");
            $maj->execute([
                ':id_societe' => $dirigeant['id_societe'], ':nom_complet' => $dirigeant['nom_complet'],
                ':fonction' => $dirigeant['fonction'], ':date_debut_mandat' => $dirigeant['date_debut_mandat'],
                ':date_fin_mandat' => $dirigeant['date_fin_mandat'] ?: null,
                ':duree_mandat_mois' => $dirigeant['duree_mandat_mois'] ?: 36, ':statut' => $dirigeant['statut'], ':id' => $id,
            ]);
            logAudit('UPDATE', 'dirigeants', $id, 'Modification du dirigeant ' . $dirigeant['nom_complet']);
            $_SESSION['flash_succes'] = 'Dirigeant modifié avec succès.';
            redirect('liste.php');
        }
    }
}

$csrf = genererTokenCSRF();
$titrePage = 'Modifier un dirigeant';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="mb-3"><a href="liste.php" class="text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Retour</a></div>
<h3 class="fw-bold mb-4"><i class="fa-solid fa-user-pen text-warning me-2"></i>Modifier : <?= e($dirigeant['nom_complet']) ?></h3>
<?php if ($erreurs): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($erreurs as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php require __DIR__ . '/_formulaire.php'; ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
