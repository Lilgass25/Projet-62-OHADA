<?php
require_once __DIR__ . '/../../includes/auth.php';
exigerConnexion();

$pdo = getPDO();

// --- Recherche et filtres multi-critères ---
$recherche = trim($_GET['q'] ?? '');
$formeFiltre = $_GET['forme'] ?? '';
$statutFiltre = $_GET['statut'] ?? '';

$conditions = [];
$params = [];

if ($recherche !== '') {
    $conditions[] = "(raison_sociale LIKE :q1 OR numero_rccm LIKE :q2 OR ninea LIKE :q3)";
    $params[':q1'] = '%' . $recherche . '%';
    $params[':q2'] = '%' . $recherche . '%';
    $params[':q3'] = '%' . $recherche . '%';
}
if ($formeFiltre !== '') {
    $conditions[] = "forme_juridique = :forme";
    $params[':forme'] = $formeFiltre;
}
if ($statutFiltre !== '') {
    $conditions[] = "statut = :statut";
    $params[':statut'] = $statutFiltre;
}

$whereSql = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

// --- Comptage total pour pagination ---
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM societes $whereSql");
$stmtCount->execute($params);
$totalLignes = (int)$stmtCount->fetchColumn();

$page = max(1, (int)($_GET['page'] ?? 1));
$pag = paginer($totalLignes, $page, 20);

// --- Requête paginée (requête préparée PDO) ---
$sql = "SELECT * FROM societes $whereSql ORDER BY date_creation DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $pag['par_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pag['offset'], PDO::PARAM_INT);
$stmt->execute();
$societes = $stmt->fetchAll();

$messageSucces = $_SESSION['flash_succes'] ?? null;
unset($_SESSION['flash_succes']);

$titrePage = 'Sociétés';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="fw-bold mb-0"><i class="fa-solid fa-building text-primary me-2"></i>Sociétés</h3>
  <div>
    <a href="../../exports/societes_export_pdf.php?<?= http_build_query($_GET) ?>" class="btn btn-outline-danger btn-sm" target="_blank">
      <i class="fa-solid fa-file-pdf me-1"></i>Export PDF
    </a>
    <a href="../../exports/societes_export_csv.php?<?= http_build_query($_GET) ?>" class="btn btn-outline-success btn-sm">
      <i class="fa-solid fa-file-csv me-1"></i>Export CSV
    </a>
    <?php if (!estConsultant()): ?>
    <a href="ajouter.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Nouvelle société</a>
    <?php endif; ?>
  </div>
</div>

<?php if ($messageSucces): ?>
  <div class="alert alert-success"><i class="fa-solid fa-circle-check me-1"></i><?= e($messageSucces) ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
  <div class="card-body">
    <form method="get" class="row g-2">
      <div class="col-md-5">
        <input type="text" name="q" class="form-control" placeholder="Rechercher (raison sociale, RCCM, NINEA)..." value="<?= e($recherche) ?>">
      </div>
      <div class="col-md-3">
        <select name="forme" class="form-select">
          <option value="">Toutes les formes juridiques</option>
          <?php foreach (['SARL','SARLU','SA','SAS','SASU','GIE','SNC','SCS'] as $f): ?>
            <option value="<?= $f ?>" <?= $formeFiltre === $f ? 'selected' : '' ?>><?= $f ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <select name="statut" class="form-select">
          <option value="">Tous les statuts</option>
          <?php foreach (['active'=>'Active','en_formation'=>'En formation','dissoute'=>'Dissoute','radiee'=>'Radiée'] as $val=>$lbl): ?>
            <option value="<?= $val ?>" <?= $statutFiltre === $val ? 'selected' : '' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-1">
        <button class="btn btn-primary w-100"><i class="fa-solid fa-search"></i></button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Raison sociale</th>
          <th>Forme</th>
          <th>Capital</th>
          <th>RCCM</th>
          <th>Statut</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($societes)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Aucune société trouvée</td></tr>
        <?php endif; ?>
        <?php foreach ($societes as $s): ?>
          <tr>
            <td class="fw-semibold"><?= e($s['raison_sociale']) ?><?= $s['sigle'] ? ' <span class="text-muted">(' . e($s['sigle']) . ')</span>' : '' ?></td>
            <td><span class="badge bg-secondary"><?= e($s['forme_juridique']) ?></span></td>
            <td><?= formatMontant($s['capital_social']) ?></td>
            <td><?= e($s['numero_rccm'] ?: '-') ?></td>
            <td>
              <?php
                $couleurs = ['active'=>'success','en_formation'=>'warning','dissoute'=>'secondary','radiee'=>'danger'];
                $c = $couleurs[$s['statut']] ?? 'secondary';
              ?>
              <span class="badge bg-<?= $c ?>"><?= e($s['statut']) ?></span>
            </td>
            <td class="text-end">
              <a href="voir.php?id=<?= (int)$s['id_societe'] ?>" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fa-solid fa-eye"></i></a>
              <?php if (!estConsultant()): ?>
              <a href="modifier.php?id=<?= (int)$s['id_societe'] ?>" class="btn btn-sm btn-outline-warning" title="Modifier"><i class="fa-solid fa-pen"></i></a>
              <?php endif; ?>
              <?php if (estAdministrateur()): ?>
              <a href="supprimer.php?id=<?= (int)$s['id_societe'] ?>" class="btn btn-sm btn-outline-danger" title="Supprimer"
                 onclick="return confirm('Confirmer la suppression de cette société et de toutes ses données liées ?');">
                <i class="fa-solid fa-trash"></i>
              </a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($pag['total_pages'] > 1): ?>
<nav class="mt-3">
  <ul class="pagination justify-content-center">
    <?php for ($p = 1; $p <= $pag['total_pages']; $p++): ?>
      <li class="page-item <?= $p === $pag['page_actuelle'] ? 'active' : '' ?>">
        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>

<p class="text-muted small text-center"><?= $totalLignes ?> société(s) au total</p>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
