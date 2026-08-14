<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" class="row g-3 js-validate" novalidate id="formContrat">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <?php if ($mode === 'modif'): ?><input type="hidden" name="id" value="<?= (int)$contrat['id_contrat'] ?>"><?php endif; ?>

      <div class="col-md-6">
        <label class="form-label">Société *</label>
        <select name="id_societe" class="form-select" required>
          <option value="">-- Choisir --</option>
          <?php foreach ($societes as $s): ?>
            <option value="<?= (int)$s['id_societe'] ?>" <?= (string)($contrat['id_societe'] ?? '') === (string)$s['id_societe'] ? 'selected' : '' ?>><?= e($s['raison_sociale']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Type de contrat *</label>
        <select name="type_contrat" class="form-select" required>
          <?php foreach (['Bail','Prestation','Fourniture','Travail','Partenariat','Autre'] as $t): ?>
            <option value="<?= $t ?>" <?= ($contrat['type_contrat'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-8">
        <label class="form-label">Intitulé *</label>
        <input type="text" name="intitule" class="form-control" required value="<?= e($contrat['intitule'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Montant (FCFA)</label>
        <input type="number" step="0.01" name="montant" class="form-control" min="0" value="<?= e((string)($contrat['montant'] ?? '')) ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Partie cocontractante *</label>
        <input type="text" name="partie_cocontractante" class="form-control" required value="<?= e($contrat['partie_cocontractante'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Date de signature *</label>
        <input type="date" name="date_signature" class="form-control" required value="<?= e($contrat['date_signature'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Date d'échéance</label>
        <input type="date" name="date_echeance" class="form-control" value="<?= e($contrat['date_echeance'] ?? '') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Préavis (jours)</label>
        <input type="number" name="preavis_jours" class="form-control" min="0" value="<?= e((string)($contrat['preavis_jours'] ?? '')) ?>">
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <div class="form-check">
          <input type="checkbox" name="tacite_reconduction" value="1" class="form-check-input" id="tr"
            <?= !empty($contrat['tacite_reconduction']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="tr">Tacite reconduction</label>
        </div>
      </div>
      <div class="col-md-4">
        <label class="form-label">Statut</label>
        <select name="statut" class="form-select">
          <?php foreach (['actif'=>'Actif','expire'=>'Expiré','resilie'=>'Résilié'] as $val=>$lbl): ?>
            <option value="<?= $val ?>" <?= ($contrat['statut'] ?? 'actif') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
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
