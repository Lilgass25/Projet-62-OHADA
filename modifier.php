<?php
require_once __DIR__ . '/../../includes/auth.php';
exigerRole(['administrateur', 'juriste']);

$pdo = getPDO();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) redirect('liste.php');

$stmt = $pdo->prepare('SELECT * FROM assemblees_generales WHERE id_ag = :id');
$stmt->execute([':id' => $id]);
$ag = $stmt->fetch();
if (!$ag) redirect('liste.php');

$societes = $pdo->query("SELECT id_societe, raison_sociale FROM societes ORDER BY raison_sociale")->fetchAll();
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierTokenCSRF($_POST['csrf_token'] ?? null)) {
        $erreurs[] = 'Session expirée, veuillez réessayer.';
    } else {
        foreach (['id_societe','type_ag','date_ag','lieu','ordre_du_jour','parts_representees','parts_totales'] as $champ) {
            $ag[$champ] = trim($_POST[$champ] ?? '');
        }
        if (empty($ag['id_societe'])) $erreurs[] = 'La société est obligatoire.';
        if (empty($ag['date_ag'])) $erreurs[] = "La date de l'AG est obligatoire.";
        if ($ag['ordre_du_jour'] === '') $erreurs[] = "L'ordre du jour est obligatoire.";

        if (empty($erreurs)) {
            $partsRepresentees = (int)($ag['parts_representees'] ?: 0);
            $partsTotales = (int)($ag['parts_totales'] ?: 0);
            $quorumAtteint = ($partsTotales > 0 && $partsRepresentees >= ($partsTotales / 2)) ? 1 : 0;

            $maj = $pdo->prepare("
                UPDATE assemblees_generales SET id_societe=:id_societe, type_ag=:type_ag, date_ag=:date_ag,
                    lieu=:lieu, ordre_du_jour=:ordre_du_jour, parts_representees=:parts_representees,
                    parts_totales=:parts_totales, quorum_atteint=:quorum_atteint
                WHERE id_ag=:id
            ");
            $maj->execute([
                ':id_societe' => $ag['id_societe'], ':type_ag' => $ag['type_ag'], ':date_ag' => $ag['date_ag'],
                ':lieu' => $ag['lieu'] ?: null, ':ordre_du_jour' => $ag['ordre_du_jour'],
                ':parts_representees' => $partsRepresentees ?: null, ':parts_totales' => $partsTotales ?: null,
                ':quorum_atteint' => $quorumAtteint, ':id' => $id,
            ]);
            logAudit('UPDATE', 'assemblees_generales', $id, 'Modification AG société #' . $ag['id_societe']);
            $_SESSION['flash_succes'] = 'Assemblée générale modifiée. Quorum ' . ($quorumAtteint ? 'atteint' : 'non atteint') . '.';
            redirect('liste.php');
        }
    }
}

$csrf = genererTokenCSRF();
$titrePage = "Modifier l'assemblée générale";
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="mb-3"><a href="liste.php" class="text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Retour</a></div>
<h3 class="fw-bold mb-4"><i class="fa-solid fa-people-roof text-warning me-2"></i>Modifier l'assemblée générale</h3>
<?php if ($erreurs): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($erreurs as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" class="row g-3 js-validate">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="id" value="<?= (int)$id ?>">
      <div class="col-md-6">
        <label class="form-label">Société *</label>
        <select name="id_societe" class="form-select" required>
          <?php foreach ($societes as $s): ?>
            <option value="<?= (int)$s['id_societe'] ?>" <?= (string)$ag['id_societe'] === (string)$s['id_societe'] ? 'selected' : '' ?>><?= e($s['raison_sociale']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Type d'AG *</label>
        <select name="type_ag" class="form-select" required>
          <?php foreach (['Ordinaire','Extraordinaire','Mixte'] as $t): ?>
            <option value="<?= $t ?>" <?= $ag['type_ag'] === $t ? 'selected' : '' ?>><?= $t ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Date de l'AG *</label>
        <input type="date" name="date_ag" class="form-control" required value="<?= e($ag['date_ag']) ?>">
      </div>
      <div class="col-md-8">
        <label class="form-label">Lieu</label>
        <input type="text" name="lieu" class="form-control" value="<?= e($ag['lieu']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Parts représentées</label>
        <input type="number" name="parts_representees" class="form-control" min="0" value="<?= e((string)$ag['parts_representees']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Parts totales de la société</label>
        <input type="number" name="parts_totales" class="form-control" min="0" value="<?= e((string)$ag['parts_totales']) ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Ordre du jour *</label>
        <textarea name="ordre_du_jour" class="form-control" rows="3" required><?= e($ag['ordre_du_jour']) ?></textarea>
      </div>
      <div class="col-12 text-end mt-4">
        <a href="liste.php" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-warning"><i class="fa-solid fa-floppy-disk me-1"></i>Mettre à jour</button>
      </div>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
