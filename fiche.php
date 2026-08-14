<?php
require_once __DIR__ . '/../../includes/functions.php';
$activeNav = 'societes';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$societes = demoSocietes();
$societe = null;
foreach ($societes as $s) { if ($s['id'] === $id) { $societe = $s; break; } }
if (!$societe) { $societe = $societes[0]; }

$pageTitle = $societe['nom'];
$pageSub   = 'Fiche référentiel — ' . $societe['forme'] . ' · ' . $societe['rccm'];

$associes   = demoAssocies($id);
$dirigeants = array_filter(demoDirigeants(), fn($d) => $d['societe'] === $societe['nom']);
$tab = $_GET['tab'] ?? 'referentiel';

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="tabs-page">
  <a href="?id=<?= $id ?>&tab=referentiel" class="<?= $tab==='referentiel'?'active':'' ?>">Référentiel</a>
  <a href="?id=<?= $id ?>&tab=capital" class="<?= $tab==='capital'?'active':'' ?>">Composition du capital</a>
  <a href="?id=<?= $id ?>&tab=dirigeants" class="<?= $tab==='dirigeants'?'active':'' ?>">Dirigeants</a>
</div>

<?php if ($tab === 'referentiel'): ?>
  <div class="panel">
    <div class="panel-head"><h2>Informations générales</h2><?= badgeStatutSociete($societe['statut']) ?></div>
    <div class="kv-grid">
      <div class="kv-item"><div class="k">Forme juridique</div><div class="v"><?= htmlspecialchars($societe['forme']) ?></div></div>
      <div class="kv-item"><div class="k">Capital social</div><div class="v"><?= fmtMoney($societe['capital']) ?></div></div>
      <div class="kv-item"><div class="k">Siège social</div><div class="v"><?= htmlspecialchars($societe['siege']) ?></div></div>
      <div class="kv-item"><div class="k">NINEA</div><div class="v mono"><?= htmlspecialchars($societe['ninea']) ?></div></div>
      <div class="kv-item"><div class="k">N° RCCM</div><div class="v"><span class="rccm"><?= htmlspecialchars($societe['rccm']) ?></span></div></div>
      <div class="kv-item"><div class="k">Date d'immatriculation</div><div class="v"><?= fmtDate($societe['date_immat']) ?></div></div>
    </div>
  </div>

<?php elseif ($tab === 'capital'): ?>
  <div class="panel">
    <div class="panel-head"><div><h2>Composition du capital — temps réel</h2><div class="count"><?= count($associes) ?> associés</div></div></div>
    <table>
      <thead><tr><th>Associé</th><th>Type</th><th>Titres</th><th>% du capital</th><th>Valeur</th></tr></thead>
      <tbody>
      <?php foreach ($associes as $a): ?>
        <tr>
          <td class="soc-name"><?= htmlspecialchars($a['associe']) ?></td>
          <td><?= htmlspecialchars($a['type']) ?></td>
          <td><?= $a['titres'] ?></td>
          <td style="min-width:130px;">
            <div class="progress-track"><div class="progress-fill" style="width:<?= $a['pourcentage'] ?>%;"></div></div>
            <div class="soc-sub" style="margin-top:4px;"><?= $a['pourcentage'] ?>%</div>
          </td>
          <td><?= fmtMoney($a['valeur']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Registre des mouvements de titres</h2></div>
    <table>
      <thead><tr><th>Date</th><th>Opération</th><th>De</th><th>Vers</th><th>Titres</th></tr></thead>
      <tbody>
      <?php foreach (demoMouvementsTitres() as $m): ?>
        <tr>
          <td><?= fmtDate($m['date']) ?></td>
          <td><?= htmlspecialchars($m['type']) ?></td>
          <td><?= htmlspecialchars($m['de']) ?></td>
          <td><?= htmlspecialchars($m['vers']) ?></td>
          <td><?= $m['titres'] ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php else: ?>
  <div class="panel">
    <div class="panel-head"><h2>Dirigeants &amp; mandataires</h2></div>
    <table>
      <thead><tr><th>Nom</th><th>Fonction</th><th>Début du mandat</th><th>Fin du mandat</th><th>Alerte</th></tr></thead>
      <tbody>
      <?php foreach ($dirigeants as $d): ?>
        <tr>
          <td class="soc-name"><?= htmlspecialchars($d['nom']) ?></td>
          <td><?= htmlspecialchars($d['fonction']) ?></td>
          <td><?= fmtDate($d['debut']) ?></td>
          <td><?= fmtDate($d['fin']) ?></td>
          <td><?= badgeEcheance(joursRestants($d['fin'])) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($dirigeants)): ?><tr><td colspan="5" class="soc-sub">Aucun dirigeant enregistré pour cette société.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
