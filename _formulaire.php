<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" class="row g-3 js-validate" novalidate id="formFormalite">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <?php if ($mode === 'modif'): ?><input type="hidden" name="id" value="<?= (int)$formalite['id_formalite'] ?>"><?php endif; ?>

      <div class="col-md-6">
        <label class="form-label">Société *</label>
        <select name="id_societe" class="form-select" required>
          <option value="">-- Choisir --</option>
          <?php foreach ($societes as $s): ?>
            <option value="<?= (int)$s['id_societe'] ?>" <?= (string)($formalite['id_societe'] ?? '') === (string)$s['id_societe'] ? 'selected' : '' ?>><?= e($s['raison_sociale']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Type de formalité *</label>
        <select name="type_formalite" class="form-select" required>
          <?php foreach (['Immatriculation_RCCM','Modification_statuts','Depot_comptes_annuels','Renouvellement_mandat',
                          'Dissolution','Radiation','Publication_BOOC','Declaration_beneficiaires_effectifs','Autre'] as $t): ?>
            <option value="<?= $t ?>" <?= ($formalite['type_formalite'] ?? '') === $t ? 'selected' : '' ?>><?= str_replace('_',' ', $t) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-12">
        <label class="form-label">Description *</label>
        <input type="text" name="description" class="form-control" required value="<?= e($formalite['description'] ?? '') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Date d'échéance *</label>
        <input type="date" name="date_echeance" class="form-control" required value="<?= e($formalite['date_echeance'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Date de réalisation</label>
        <input type="date" name="date_realisation" class="form-control" value="<?= e($formalite['date_realisation'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Statut</label>
        <select name="statut" class="form-select">
          <?php foreach (['a_faire'=>'À faire','en_cours'=>'En cours','realisee'=>'Réalisée','en_retard'=>'En retard'] as $val=>$lbl): ?>
            <option value="<?= $val ?>" <?= ($formalite['statut'] ?? 'a_faire') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
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
