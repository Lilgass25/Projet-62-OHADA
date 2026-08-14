<?php
require_once __DIR__ . '/../../includes/auth.php';
exigerRole(['administrateur', 'juriste']); // seuls admin et juriste peuvent créer

$pdo = getPDO();
$erreurs = [];
$donnees = [
    'raison_sociale' => '', 'sigle' => '', 'forme_juridique' => 'SARL', 'capital_social' => '',
    'siege_social' => '', 'ninea' => '', 'numero_rccm' => '', 'date_immatriculation' => '',
    'objet_social' => '', 'duree_annees' => 99, 'statut' => 'en_formation'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierTokenCSRF($_POST['csrf_token'] ?? null)) {
        $erreurs[] = 'Session expirée, veuillez réessayer.';
    } else {
        foreach ($donnees as $champ => $valDefaut) {
            $donnees[$champ] = trim($_POST[$champ] ?? '');
        }

        // --- Validation côté serveur (obligatoire même si JS valide déjà côté client) ---
        if ($donnees['raison_sociale'] === '') $erreurs[] = 'La raison sociale est obligatoire.';
        if ($donnees['siege_social'] === '') $erreurs[] = 'Le siège social est obligatoire.';
        if (!is_numeric($donnees['capital_social']) || (float)$donnees['capital_social'] < 0) {
            $erreurs[] = 'Le capital social doit être un nombre positif.';
        }
        if (!in_array($donnees['forme_juridique'], ['SARL','SARLU','SA','SAS','SASU','GIE','SNC','SCS'], true)) {
            $erreurs[] = 'Forme juridique invalide.';
        }

        if (empty($erreurs)) {
            $stmt = $pdo->prepare("
                INSERT INTO societes
                    (raison_sociale, sigle, forme_juridique, capital_social, siege_social, ninea,
                     numero_rccm, date_immatriculation, objet_social, duree_annees, statut, id_utilisateur_creation)
                VALUES
                    (:raison_sociale, :sigle, :forme_juridique, :capital_social, :siege_social, :ninea,
                     :numero_rccm, :date_immatriculation, :objet_social, :duree_annees, :statut, :id_utilisateur)
            ");
            $stmt->execute([
                ':raison_sociale' => $donnees['raison_sociale'],
                ':sigle' => $donnees['sigle'] ?: null,
                ':forme_juridique' => $donnees['forme_juridique'],
                ':capital_social' => $donnees['capital_social'],
                ':siege_social' => $donnees['siege_social'],
                ':ninea' => $donnees['ninea'] ?: null,
                ':numero_rccm' => $donnees['numero_rccm'] ?: null,
                ':date_immatriculation' => $donnees['date_immatriculation'] ?: null,
                ':objet_social' => $donnees['objet_social'] ?: null,
                ':duree_annees' => $donnees['duree_annees'] ?: 99,
                ':statut' => $donnees['statut'],
                ':id_utilisateur' => $_SESSION['id_utilisateur'],
            ]);

            $idNouveau = (int)$pdo->lastInsertId();
            logAudit('CREATE', 'societes', $idNouveau, 'Création de la société ' . $donnees['raison_sociale']);

            $_SESSION['flash_succes'] = 'Société créée avec succès.';
            redirect('liste.php');
        }
    }
}

$csrf = genererTokenCSRF();
$titrePage = 'Ajouter une société';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="mb-3">
  <a href="liste.php" class="text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Retour à la liste</a>
</div>
<h3 class="fw-bold mb-4"><i class="fa-solid fa-building-circle-check text-primary me-2"></i>Nouvelle société</h3>

<?php if ($erreurs): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($erreurs as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" class="row g-3 js-validate" novalidate id="formSociete">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

      <div class="col-md-6">
        <label class="form-label">Raison sociale *</label>
        <input type="text" name="raison_sociale" class="form-control" required minlength="2"
               value="<?= e($donnees['raison_sociale']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Sigle</label>
        <input type="text" name="sigle" class="form-control" value="<?= e($donnees['sigle']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Forme juridique *</label>
        <select name="forme_juridique" class="form-select" required>
          <?php foreach (['SARL','SARLU','SA','SAS','SASU','GIE','SNC','SCS'] as $f): ?>
            <option value="<?= $f ?>" <?= $donnees['forme_juridique'] === $f ? 'selected' : '' ?>><?= $f ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Capital social (FCFA) *</label>
        <input type="number" step="0.01" min="0" name="capital_social" class="form-control" required
               value="<?= e($donnees['capital_social']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">NINEA</label>
        <input type="text" name="ninea" class="form-control" value="<?= e($donnees['ninea']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Numéro RCCM</label>
        <input type="text" name="numero_rccm" class="form-control" value="<?= e($donnees['numero_rccm']) ?>">
      </div>

      <div class="col-md-8">
        <label class="form-label">Siège social *</label>
        <input type="text" name="siege_social" class="form-control" required
               value="<?= e($donnees['siege_social']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Date d'immatriculation</label>
        <input type="date" name="date_immatriculation" class="form-control"
               value="<?= e($donnees['date_immatriculation']) ?>">
      </div>

      <div class="col-md-8">
        <label class="form-label">Objet social</label>
        <textarea name="objet_social" class="form-control" rows="3"><?= e($donnees['objet_social']) ?></textarea>
      </div>
      <div class="col-md-2">
        <label class="form-label">Durée (années)</label>
        <input type="number" name="duree_annees" class="form-control" min="1" max="99"
               value="<?= e((string)$donnees['duree_annees']) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Statut</label>
        <select name="statut" class="form-select">
          <?php foreach (['en_formation'=>'En formation','active'=>'Active','dissoute'=>'Dissoute','radiee'=>'Radiée'] as $val=>$lbl): ?>
            <option value="<?= $val ?>" <?= $donnees['statut'] === $val ? 'selected' : '' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 text-end mt-4">
        <a href="liste.php" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<script>
// Validation JavaScript côté client (complément de la validation serveur)
document.getElementById('formSociete').addEventListener('submit', function (e) {
  const capital = parseFloat(this.capital_social.value);
  if (isNaN(capital) || capital < 0) {
    alert('Le capital social doit être un nombre positif.');
    e.preventDefault();
  }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
