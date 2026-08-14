<?php
require_once __DIR__ . '/../../includes/auth.php';
exigerRole(['administrateur', 'juriste']);

$pdo = getPDO();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) redirect('liste.php');

$stmt = $pdo->prepare('SELECT * FROM societes WHERE id_societe = :id');
$stmt->execute([':id' => $id]);
$societe = $stmt->fetch();
if (!$societe) {
    $_SESSION['flash_succes'] = null;
    redirect('liste.php');
}

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierTokenCSRF($_POST['csrf_token'] ?? null)) {
        $erreurs[] = 'Session expirée, veuillez réessayer.';
    } else {
        foreach (['raison_sociale','sigle','forme_juridique','capital_social','siege_social','ninea',
                  'numero_rccm','date_immatriculation','objet_social','duree_annees','statut'] as $champ) {
            $societe[$champ] = trim($_POST[$champ] ?? '');
        }

        if ($societe['raison_sociale'] === '') $erreurs[] = 'La raison sociale est obligatoire.';
        if (!is_numeric($societe['capital_social']) || (float)$societe['capital_social'] < 0) {
            $erreurs[] = 'Le capital social doit être un nombre positif.';
        }

        if (empty($erreurs)) {
            $maj = $pdo->prepare("
                UPDATE societes SET
                    raison_sociale = :raison_sociale, sigle = :sigle, forme_juridique = :forme_juridique,
                    capital_social = :capital_social, siege_social = :siege_social, ninea = :ninea,
                    numero_rccm = :numero_rccm, date_immatriculation = :date_immatriculation,
                    objet_social = :objet_social, duree_annees = :duree_annees, statut = :statut
                WHERE id_societe = :id
            ");
            $maj->execute([
                ':raison_sociale' => $societe['raison_sociale'],
                ':sigle' => $societe['sigle'] ?: null,
                ':forme_juridique' => $societe['forme_juridique'],
                ':capital_social' => $societe['capital_social'],
                ':siege_social' => $societe['siege_social'],
                ':ninea' => $societe['ninea'] ?: null,
                ':numero_rccm' => $societe['numero_rccm'] ?: null,
                ':date_immatriculation' => $societe['date_immatriculation'] ?: null,
                ':objet_social' => $societe['objet_social'] ?: null,
                ':duree_annees' => $societe['duree_annees'] ?: 99,
                ':statut' => $societe['statut'],
                ':id' => $id,
            ]);

            logAudit('UPDATE', 'societes', $id, 'Modification de la société ' . $societe['raison_sociale']);
            $_SESSION['flash_succes'] = 'Société modifiée avec succès.';
            redirect('liste.php');
        }
    }
}

$csrf = genererTokenCSRF();
$titrePage = 'Modifier la société';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="mb-3">
  <a href="liste.php" class="text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Retour à la liste</a>
</div>
<h3 class="fw-bold mb-4"><i class="fa-solid fa-pen-to-square text-warning me-2"></i>Modifier : <?= e($societe['raison_sociale']) ?></h3>

<?php if ($erreurs): ?>
  <div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($erreurs as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" class="row g-3 js-validate" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="id" value="<?= (int)$id ?>">

      <div class="col-md-6">
        <label class="form-label">Raison sociale *</label>
        <input type="text" name="raison_sociale" class="form-control" required value="<?= e($societe['raison_sociale']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Sigle</label>
        <input type="text" name="sigle" class="form-control" value="<?= e($societe['sigle']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Forme juridique *</label>
        <select name="forme_juridique" class="form-select" required>
          <?php foreach (['SARL','SARLU','SA','SAS','SASU','GIE','SNC','SCS'] as $f): ?>
            <option value="<?= $f ?>" <?= $societe['forme_juridique'] === $f ? 'selected' : '' ?>><?= $f ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Capital social (FCFA) *</label>
        <input type="number" step="0.01" min="0" name="capital_social" class="form-control" required value="<?= e($societe['capital_social']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">NINEA</label>
        <input type="text" name="ninea" class="form-control" value="<?= e($societe['ninea']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Numéro RCCM</label>
        <input type="text" name="numero_rccm" class="form-control" value="<?= e($societe['numero_rccm']) ?>">
      </div>

      <div class="col-md-8">
        <label class="form-label">Siège social *</label>
        <input type="text" name="siege_social" class="form-control" required value="<?= e($societe['siege_social']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Date d'immatriculation</label>
        <input type="date" name="date_immatriculation" class="form-control" value="<?= e($societe['date_immatriculation']) ?>">
      </div>

      <div class="col-md-8">
        <label class="form-label">Objet social</label>
        <textarea name="objet_social" class="form-control" rows="3"><?= e($societe['objet_social']) ?></textarea>
      </div>
      <div class="col-md-2">
        <label class="form-label">Durée (années)</label>
        <input type="number" name="duree_annees" class="form-control" min="1" max="99" value="<?= e((string)$societe['duree_annees']) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Statut</label>
        <select name="statut" class="form-select">
          <?php foreach (['en_formation'=>'En formation','active'=>'Active','dissoute'=>'Dissoute','radiee'=>'Radiée'] as $val=>$lbl): ?>
            <option value="<?= $val ?>" <?= $societe['statut'] === $val ? 'selected' : '' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 text-end mt-4">
        <a href="liste.php" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-warning"><i class="fa-solid fa-floppy-disk me-1"></i>Mettre à jour</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
