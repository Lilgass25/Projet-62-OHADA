<?php
require_once __DIR__ . '/../includes/auth.php';
exigerConnexion();

$pdo = getPDO();

// --- KPI ---
$nbSocietes   = $pdo->query("SELECT COUNT(*) FROM societes")->fetchColumn();
$nbActives    = $pdo->query("SELECT COUNT(*) FROM societes WHERE statut = 'active'")->fetchColumn();
$nbFormalites = $pdo->query("SELECT COUNT(*) FROM formalites WHERE statut IN ('a_faire','en_cours')")->fetchColumn();
$nbEnRetard   = $pdo->query("SELECT COUNT(*) FROM formalites WHERE date_echeance < CURDATE() AND statut NOT IN ('realisee')")->fetchColumn();
$nbContrats   = $pdo->query("SELECT COUNT(*) FROM contrats WHERE statut = 'actif'")->fetchColumn();
$nbAssocies   = $pdo->query("SELECT COUNT(*) FROM associes WHERE statut = 'actif'")->fetchColumn();
$nbDirigeants = $pdo->query("SELECT COUNT(*) FROM dirigeants WHERE statut = 'en_cours'")->fetchColumn();

// --- Répartition des sociétés par forme juridique (pour le graphique) ---
$stmt = $pdo->query("SELECT forme_juridique, COUNT(*) AS total FROM societes GROUP BY forme_juridique");
$repartitionForme = $stmt->fetchAll();

// --- Formalités à venir (30 prochains jours) ---
$stmt = $pdo->query("
    SELECT f.description, f.date_echeance, f.statut, s.raison_sociale
    FROM formalites f
    JOIN societes s ON s.id_societe = f.id_societe
    WHERE f.date_echeance <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
      AND f.statut != 'realisee'
    ORDER BY f.date_echeance ASC
    LIMIT 10
");
$formalitesProches = $stmt->fetchAll();

$titrePage = 'Tableau de bord';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h3 class="fw-bold mb-1"><i class="fa-solid fa-gauge-high text-primary me-2"></i>Tableau de bord</h3>
    <p class="text-muted mb-0">Vue d'ensemble de votre portefeuille juridique</p>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="card kpi-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="kpi-icon" style="background:#eaf3ff;color:#0b5fa5;"><i class="fa-solid fa-building"></i></div>
        <div>
          <div class="text-muted small">Sociétés gérées</div>
          <div class="fs-3 fw-bold"><?= (int)$nbSocietes ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card kpi-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="kpi-icon" style="background:#e9f7ef;color:#1e7e42;"><i class="fa-solid fa-circle-check"></i></div>
        <div>
          <div class="text-muted small">Sociétés actives</div>
          <div class="fs-3 fw-bold"><?= (int)$nbActives ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card kpi-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="kpi-icon" style="background:#fef6e6;color:#a06a00;"><i class="fa-solid fa-file-signature"></i></div>
        <div>
          <div class="text-muted small">Formalités en attente</div>
          <div class="fs-3 fw-bold"><?= (int)$nbFormalites ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card kpi-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="kpi-icon" style="background:#fdecec;color:#b3261e;"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div>
          <div class="text-muted small">Formalités en retard</div>
          <div class="fs-3 fw-bold text-danger"><?= (int)$nbEnRetard ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="card kpi-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="kpi-icon" style="background:#eef2f8;color:var(--navy);"><i class="fa-solid fa-users"></i></div>
        <div>
          <div class="text-muted small">Associés actifs</div>
          <div class="fs-3 fw-bold"><?= (int)$nbAssocies ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card kpi-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="kpi-icon" style="background:#f3eefc;color:#6f42c1;"><i class="fa-solid fa-user-tie"></i></div>
        <div>
          <div class="text-muted small">Dirigeants en mandat</div>
          <div class="fs-3 fw-bold"><?= (int)$nbDirigeants ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card kpi-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="kpi-icon" style="background:#eaf3ff;color:#0b5fa5;"><i class="fa-solid fa-file-contract"></i></div>
        <div>
          <div class="text-muted small">Contrats actifs</div>
          <div class="fs-3 fw-bold"><?= (int)$nbContrats ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card kpi-card h-100">
      <div class="card-body d-flex flex-column justify-content-center gap-2 py-3">
        <a href="../modules/formalites/liste.php" class="btn btn-sm btn-warning"><i class="fa-solid fa-file-signature me-1"></i>Formalités</a>
        <a href="../modules/contrats/liste.php" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-file-contract me-1"></i>Contrats</a>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white fw-semibold">Répartition par forme juridique</div>
      <div class="card-body">
        <canvas id="chartForme" height="220"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white fw-semibold">
        <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>
        Formalités à échéance dans les 30 prochains jours
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr><th>Société</th><th>Formalité</th><th>Échéance</th><th>Statut</th></tr>
            </thead>
            <tbody>
              <?php if (empty($formalitesProches)): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">Aucune formalité urgente</td></tr>
              <?php endif; ?>
              <?php foreach ($formalitesProches as $f): ?>
                <tr>
                  <td><?= e($f['raison_sociale']) ?></td>
                  <td><?= e($f['description']) ?></td>
                  <td><?= formatDate($f['date_echeance']) ?></td>
                  <td>
                    <?php $badge = $f['statut'] === 'en_cours' ? 'warning' : 'secondary'; ?>
                    <span class="badge bg-<?= $badge ?>"><?= e($f['statut']) ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('chartForme');
new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: <?= json_encode(array_column($repartitionForme, 'forme_juridique')) ?>,
    datasets: [{
      data: <?= json_encode(array_map('intval', array_column($repartitionForme, 'total'))) ?>,
      backgroundColor: ['#0b2d4f', '#1d6fa5', '#d9a441', '#2e8b57', '#6f42c1', '#b3261e']
    }]
  },
  options: { plugins: { legend: { position: 'bottom' } } }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
