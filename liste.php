<?php
require_once __DIR__ . '/../../includes/auth.php';
exigerConnexion();

$pdo = getPDO();
$recherche = trim($_GET['q'] ?? '');
$societeFiltre = (int)($_GET['societe'] ?? 0);

$conditions = [];
$params = [];
if ($recherche !== '') { $conditions[] = "d.nom_complet LIKE :q"; $params[':q'] = '%' . $recherche . '%'; }
if ($societeFiltre > 0) { $conditions[] = "d.id_societe = :societe"; $params[':societe'] = $societeFiltre; }
$whereSql = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM dirigeants d $whereSql");
$stmtCount->execute($params);
$totalLignes = (int)$stmtCount->fetchColumn();

$page = max(1, (int)($_GET['page'] ?? 1));
$pag = paginer($totalLignes, $page, 20);

$sql = "SELECT d.*, s.raison_sociale FROM dirigeants d
        JOIN societes s ON s.id_societe = d.id_societe
        $whereSql ORDER BY d.date_fin_mandat ASC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $pag['par_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pag['offset'], PDO::PARAM_INT);
$stmt->execute();
$dirigeants = $stmt->fetchAll();

$societes = $pdo->query("SELECT id_societe, raison_sociale FROM societes ORDER BY raison_sociale")->fetchAll();
$messageSucces = $_SESSION['flash_succes'] ?? null;
unset($_SESSION['flash_succes']);

$titrePage = 'Dirigeants';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="fw-bold mb-0"><i class="fa-solid fa-user-tie text-primary me-2"></i>Dirigeants</h3>
  <?php if (!estConsultant()): ?><a href="ajouter.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Nouveau dirigeant</a><?php endif; ?>
</div>

<?php if ($messageSucces): ?><div class="alert alert-success"><i class="fa-solid fa-circle-check me-1"></i><?= e($messageSucces) ?></div><?php endif; ?>

<div class="card shadow-sm mb-3">
  <div class="card-body">
    <form method="get" class="row g-2">
      <div class="col-md-6"><input type="text" name="q" class="form-control" placeholder="Rechercher un dirigeant..." value="<?= e($recherche) ?>"></div>
      <div class="col-md-5">
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
      <thead class="table-light"><tr><th>Nom</th><th>Société</th><th>Fonction</th><th>Début mandat</th><th>Fin mandat</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
        <?php if (empty($dirigeants)): ?><tr><td colspan="7" class="text-center text-muted py-4">Aucun dirigeant trouvé</td></tr><?php endif; ?>
        <?php foreach ($dirigeants as $d):
          $finProche = $d['date_fin_mandat'] && strtotime($d['date_fin_mandat']) <= strtotime('+90 days') && $d['statut'] === 'en_cours';
        ?>
          <tr class="<?= $finProche ? 'table-warning' : '' ?>">
            <td class="fw-semibold"><?= e($d['nom_complet']) ?></td>
            <td><?= e($d['raison_sociale']) ?></td>
            <td><?= e(str_replace('_', ' ', $d['fonction'])) ?></td>
            <td><?= formatDate($d['date_debut_mandat']) ?></td>
            <td><?= formatDate($d['date_fin_mandat']) ?> <?= $finProche ? '<i class="fa-solid fa-triangle-exclamation text-warning ms-1" title="Fin de mandat proche"></i>' : '' ?></td>
            <td><span class="badge bg-<?= $d['statut'] === 'en_cours' ? 'success' : ($d['statut'] === 'revoque' ? 'danger' : 'secondary') ?>"><?= e($d['statut']) ?></span></td>
            <td class="text-end">
              <?php if (!estConsultant()): ?><a href="modifier.php?id=<?= (int)$d['id_dirigeant'] ?>" class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-pen"></i></a><?php endif; ?>
              <?php if (estAdministrateur()): ?><a href="supprimer.php?id=<?= (int)$d['id_dirigeant'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Confirmer la suppression ?');"><i class="fa-solid fa-trash"></i></a><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($pag['total_pages'] > 1): ?>
<nav class="mt-3"><ul class="pagination justify-content-center">
  <?php for ($p = 1; $p <= $pag['total_pages']; $p++): ?>
    <li class="page-item <?= $p === $pag['page_actuelle'] ? 'active' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a></li>
  <?php endfor; ?>
</ul></nav>
<?php endif; ?>
<p class="text-muted small text-center"><?= $totalLignes ?> dirigeant(s) au total</p>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
