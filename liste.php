<?php
require_once __DIR__ . '/../../includes/auth.php';
exigerConnexion();

$pdo = getPDO();
$societeFiltre = (int)($_GET['societe'] ?? 0);
$where = $societeFiltre > 0 ? 'WHERE ag.id_societe = :societe' : '';
$params = $societeFiltre > 0 ? [':societe' => $societeFiltre] : [];

$stmt = $pdo->prepare("
    SELECT ag.*, s.raison_sociale FROM assemblees_generales ag
    JOIN societes s ON s.id_societe = ag.id_societe
    $where ORDER BY ag.date_ag DESC
");
$stmt->execute($params);
$assemblees = $stmt->fetchAll();

$societes = $pdo->query("SELECT id_societe, raison_sociale FROM societes ORDER BY raison_sociale")->fetchAll();
$messageSucces = $_SESSION['flash_succes'] ?? null;
unset($_SESSION['flash_succes']);

$titrePage = 'Assemblées générales';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="fw-bold mb-0"><i class="fa-solid fa-people-roof text-primary me-2"></i>Assemblées générales</h3>
  <?php if (!estConsultant()): ?><a href="ajouter.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Nouvelle AG</a><?php endif; ?>
</div>

<?php if ($messageSucces): ?><div class="alert alert-success"><i class="fa-solid fa-circle-check me-1"></i><?= e($messageSucces) ?></div><?php endif; ?>

<div class="card shadow-sm mb-3">
  <div class="card-body">
    <form method="get" class="row g-2">
      <div class="col-md-11">
        <select name="societe" class="form-select">
          <option value="0">Toutes les sociétés</option>
          <?php foreach ($societes as $s): ?>
            <option value="<?= (int)$s['id_societe'] ?>" <?= $societeFiltre === (int)$s['id_societe'] ? 'selected' : '' ?>><?= e($s['raison_sociale']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-1"><button class="btn btn-primary w-100"><i class="fa-solid fa-search"></i></button></div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light"><tr><th>Société</th><th>Type</th><th>Date</th><th>Quorum</th><th>Ordre du jour</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
        <?php if (empty($assemblees)): ?><tr><td colspan="6" class="text-center text-muted py-4">Aucune assemblée enregistrée</td></tr><?php endif; ?>
        <?php foreach ($assemblees as $ag): ?>
          <tr>
            <td><?= e($ag['raison_sociale']) ?></td>
            <td><span class="badge bg-secondary"><?= e($ag['type_ag']) ?></span></td>
            <td><?= formatDate($ag['date_ag']) ?></td>
            <td><span class="badge bg-<?= $ag['quorum_atteint'] ? 'success' : 'danger' ?>"><?= $ag['quorum_atteint'] ? 'Atteint' : 'Non atteint' ?></span></td>
            <td class="small text-truncate" style="max-width:280px;"><?= e($ag['ordre_du_jour']) ?></td>
            <td class="text-end">
              <?php if (!estConsultant()): ?><a href="modifier.php?id=<?= (int)$ag['id_ag'] ?>" class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-pen"></i></a><?php endif; ?>
              <?php if (estAdministrateur()): ?><a href="supprimer.php?id=<?= (int)$ag['id_ag'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Confirmer la suppression ?');"><i class="fa-solid fa-trash"></i></a><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
