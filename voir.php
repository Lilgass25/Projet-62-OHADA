<?php
require_once __DIR__ . '/../../includes/auth.php';
exigerConnexion();

$pdo = getPDO();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM societes WHERE id_societe = :id');
$stmt->execute([':id' => $id]);
$societe = $stmt->fetch();
if (!$societe) redirect('liste.php');

$stmtAssocies = $pdo->prepare('SELECT * FROM associes WHERE id_societe = :id ORDER BY nombre_parts DESC');
$stmtAssocies->execute([':id' => $id]);
$associes = $stmtAssocies->fetchAll();

$stmtDirigeants = $pdo->prepare('SELECT * FROM dirigeants WHERE id_societe = :id ORDER BY date_debut_mandat DESC');
$stmtDirigeants->execute([':id' => $id]);
$dirigeants = $stmtDirigeants->fetchAll();

$stmtFormalites = $pdo->prepare('SELECT * FROM formalites WHERE id_societe = :id ORDER BY date_echeance ASC');
$stmtFormalites->execute([':id' => $id]);
$formalites = $stmtFormalites->fetchAll();

$titrePage = $societe['raison_sociale'];
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="mb-3">
  <a href="liste.php" class="text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Retour à la liste</a>
</div>

<div class="d-flex justify-content-between align-items-start mb-4">
  <div>
    <h3 class="fw-bold mb-1"><?= e($societe['raison_sociale']) ?> <?= $societe['sigle'] ? '(' . e($societe['sigle']) . ')' : '' ?></h3>
    <span class="badge bg-secondary"><?= e($societe['forme_juridique']) ?></span>
    <span class="badge bg-success"><?= e($societe['statut']) ?></span>
  </div>
  <?php if (!estConsultant()): ?>
  <a href="modifier.php?id=<?= (int)$id ?>" class="btn btn-warning btn-sm"><i class="fa-solid fa-pen me-1"></i>Modifier</a>
  <?php endif; ?>
</div>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card shadow-sm mb-3">
      <div class="card-header bg-white fw-semibold">Informations générales</div>
      <div class="card-body small">
        <p><strong>Capital social :</strong> <?= formatMontant($societe['capital_social']) ?></p>
        <p><strong>Siège social :</strong> <?= e($societe['siege_social']) ?></p>
        <p><strong>NINEA :</strong> <?= e($societe['ninea'] ?: '-') ?></p>
        <p><strong>RCCM :</strong> <?= e($societe['numero_rccm'] ?: '-') ?></p>
        <p><strong>Date d'immatriculation :</strong> <?= formatDate($societe['date_immatriculation']) ?></p>
        <p><strong>Durée :</strong> <?= (int)$societe['duree_annees'] ?> ans</p>
        <p class="mb-0"><strong>Objet social :</strong><br><?= nl2br(e($societe['objet_social'] ?: '-')) ?></p>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header bg-white fw-semibold">Dirigeants</div>
      <ul class="list-group list-group-flush">
        <?php if (empty($dirigeants)): ?><li class="list-group-item text-muted small">Aucun dirigeant enregistré</li><?php endif; ?>
        <?php foreach ($dirigeants as $d): ?>
          <li class="list-group-item small">
            <strong><?= e($d['nom_complet']) ?></strong> — <?= e($d['fonction']) ?><br>
            <span class="text-muted">Mandat : <?= formatDate($d['date_debut_mandat']) ?> → <?= formatDate($d['date_fin_mandat']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card shadow-sm mb-3">
      <div class="card-header bg-white fw-semibold">Registre des associés / actionnaires</div>
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="table-light"><tr><th>Nom / Dénomination</th><th>Type</th><th>Parts</th><th>Valeur nominale</th><th>Entrée</th></tr></thead>
          <tbody>
            <?php if (empty($associes)): ?><tr><td colspan="5" class="text-center text-muted py-3">Aucun associé enregistré</td></tr><?php endif; ?>
            <?php foreach ($associes as $a): ?>
              <tr>
                <td><?= e($a['nom_denomination']) ?></td>
                <td><?= e($a['type_personne']) ?></td>
                <td><?= (int)$a['nombre_parts'] ?></td>
                <td><?= formatMontant($a['valeur_nominale']) ?></td>
                <td><?= formatDate($a['date_entree']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header bg-white fw-semibold">Formalités OHADA / RCCM</div>
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="table-light"><tr><th>Type</th><th>Description</th><th>Échéance</th><th>Statut</th></tr></thead>
          <tbody>
            <?php if (empty($formalites)): ?><tr><td colspan="4" class="text-center text-muted py-3">Aucune formalité enregistrée</td></tr><?php endif; ?>
            <?php foreach ($formalites as $f):
              $couleurs = ['a_faire'=>'secondary','en_cours'=>'warning','realisee'=>'success','en_retard'=>'danger'];
              $c = $couleurs[$f['statut']] ?? 'secondary';
            ?>
              <tr>
                <td><?= e(str_replace('_',' ', $f['type_formalite'])) ?></td>
                <td><?= e($f['description']) ?></td>
                <td><?= formatDate($f['date_echeance']) ?></td>
                <td><span class="badge bg-<?= $c ?>"><?= e($f['statut']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
