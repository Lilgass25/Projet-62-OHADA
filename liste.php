<?php
require_once __DIR__ . '/../../includes/auth.php';
exigerConnexion();

$pdo = getPDO();

// Mise à jour automatique du statut "en_retard" pour les formalités dépassées non réalisées
$pdo->exec("UPDATE formalites SET statut = 'en_retard' WHERE date_echeance < CURDATE() AND statut IN ('a_faire','en_cours')");

$recherche = trim($_GET['q'] ?? '');
$societeFiltre = (int)($_GET['societe'] ?? 0);
$statutFiltre = $_GET['statut'] ?? '';

$conditions = [];
$params = [];
if ($recherche !== '') { $conditions[] = "f.description LIKE :q"; $params[':q'] = '%' . $recherche . '%'; }
if ($societeFiltre > 0) { $conditions[] = "f.id_societe = :societe"; $params[':societe'] = $societeFiltre; }
if ($statutFiltre !== '') { $conditions[] = "f.statut = :statut"; $params[':statut'] = $statutFiltre; }
$whereSql = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM formalites f $whereSql");
$stmtCount->execute($params);
$totalLignes = (int)$stmtCount->fetchColumn();

$page = max(1, (int)($_GET['page'] ?? 1));
$pag = paginer($totalLignes, $page, 20);

$sql = "SELECT f.*, s.raison_sociale FROM formalites f
        JOIN societes s ON s.id_societe = f.id_societe
        $whereSql ORDER BY f.date_echeance ASC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $pag['par_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pag['offset'], PDO::PARAM_INT);
$stmt->execute();
$formalites = $stmt->fetchAll();

$societes = $pdo->query("SELECT id_societe, raison_sociale FROM societes ORDER BY raison_sociale")->fetchAll();
$messageSucces = $_SESSION['flash_succes'] ?? null;
unset($_SESSION['flash_succes']);
$messageErreur = $_SESSION['flash_erreur'] ?? null;
unset($_SESSION['flash_erreur']);

$titrePage = 'Formalités';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="fw-bold mb-0"><i class="fa-solid fa-file-signature text-primary me-2"></i>Formalités OHADA / RCCM</h3>
  <?php if (!estConsultant()): ?><a href="ajouter.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Nouvelle formalité</a><?php endif; ?>
</div>

<?php if ($messageSucces): ?><div class="alert alert-success"><i class="fa-solid fa-circle-check me-1"></i><?= e($messageSucces) ?></div><?php endif; ?>
<?php if ($messageErreur): ?><div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i><?= e($messageErreur) ?></div><?php endif; ?>

<div class="card shadow-sm mb-3">
  <div class="card-body">
    <form method="get" class="row g-2">
      <div class="col-md-4"><input type="text" name="q" class="form-control" placeholder="Rechercher..." value="<?= e($recherche) ?>"></div>
      <div class="col-md-4">
        <select name="societe" class="form-select">
          <option value="0">Toutes les sociétés</option>
          <?php foreach ($societes as $s): ?>
            <option value="<?= (int)$s['id_societe'] ?>" <?= $societeFiltre === (int)$s['id_societe'] ? 'selected' : '' ?>><?= e($s['raison_sociale']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <select name="statut" class="form-select">
          <option value="">Tous les statuts</option>
          <?php foreach (['a_faire'=>'À faire','en_cours'=>'En cours','realisee'=>'Réalisée','en_retard'=>'En retard'] as $val=>$lbl): ?>
            <option value="<?= $val ?>" <?= $statutFiltre === $val ? 'selected' : '' ?>><?= $lbl ?></option>
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
      <thead class="table-light"><tr><th>Société</th><th>Type</th><th>Description</th><th>Échéance</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
        <?php if (empty($formalites)): ?><tr><td colspan="6" class="text-center text-muted py-4">Aucune formalité trouvée</td></tr><?php endif; ?>
        <?php foreach ($formalites as $f):
          $couleurs = ['a_faire'=>'secondary','en_cours'=>'warning','realisee'=>'success','en_retard'=>'danger'];
          $c = $couleurs[$f['statut']] ?? 'secondary';
          $ligneAlerte = $f['statut'] === 'en_retard' ? 'table-danger' : '';
        ?>
          <tr class="<?= $ligneAlerte ?>">
            <td><?= e($f['raison_sociale']) ?></td>
            <td><?= e(str_replace('_', ' ', $f['type_formalite'])) ?></td>
            <td><?= e($f['description']) ?></td>
            <td><?= formatDate($f['date_echeance']) ?></td>
            <td><span class="badge bg-<?= $c ?>"><?= e(str_replace('_',' ',$f['statut'])) ?></span></td>
            <td class="text-end">
              <?php if (!estConsultant() && $f['statut'] !== 'realisee'): ?>
              <a href="alerter.php?id=<?= (int)$f['id_formalite'] ?>" class="btn btn-sm btn-outline-info" title="Envoyer une alerte email">
                <i class="fa-solid fa-envelope"></i>
              </a>
              <?php endif; ?>
              <?php if (!estConsultant()): ?><a href="modifier.php?id=<?= (int)$f['id_formalite'] ?>" class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-pen"></i></a><?php endif; ?>
              <?php if (estAdministrateur()): ?><a href="supprimer.php?id=<?= (int)$f['id_formalite'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Confirmer la suppression ?');"><i class="fa-solid fa-trash"></i></a><?php endif; ?>
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
<p class="text-muted small text-center"><?= $totalLignes ?> formalité(s) au total</p>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
