<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" class="row g-3 js-validate" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <?php if ($mode === 'modif'): ?><input type="hidden" name="id" value="<?= (int)$dirigeant['id_dirigeant'] ?>"><?php endif; ?>

      <div class="col-md-6">
        <label class="form-label">Société *</label>
        <select name="id_societe" class="form-select" required>
          <option value="">-- Choisir --</option>
          <?php foreach ($societes as $s): ?>
            <option value="<?= (int)$s['id_societe'] ?>" <?= (string)($dirigeant['id_societe'] ?? '') === (string)$s['id_societe'] ? 'selected' : '' ?>><?= e($s['raison_sociale']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Fonction *</label>
        <select name="fonction" class="form-select" required>
          <?php foreach (['Gerant','PDG','DG','Administrateur','President_CA','CAC_Titulaire','CAC_Suppleant'] as $f): ?>
            <option value="<?= $f ?>" <?= ($dirigeant['fonction'] ?? '') === $f ? 'selected' : '' ?>><?= str_replace('_',' ', $f) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Nom complet *</label>
        <input type="text" name="nom_complet" class="form-control" required value="<?= e($dirigeant['nom_complet'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Durée du mandat (mois)</label>
        <input type="number" name="duree_mandat_mois" class="form-control" min="1" value="<?= e((string)($dirigeant['duree_mandat_mois'] ?? 36)) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Statut</label>
        <select name="statut" class="form-select">
          <?php foreach (['en_cours'=>'En cours','termine'=>'Terminé','revoque'=>'Révoqué'] as $val=>$lbl): ?>
            <option value="<?= $val ?>" <?= ($dirigeant['statut'] ?? 'en_cours') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Date de début du mandat *</label>
        <input type="date" name="date_debut_mandat" class="form-control" required value="<?= e($dirigeant['date_debut_mandat'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Date de fin du mandat</label>
        <input type="date" name="date_fin_mandat" class="form-control" value="<?= e($dirigeant['date_fin_mandat'] ?? '') ?>">
      </div>

      <div class="col-12 text-end mt-4">
        <a href="liste.php" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Enregistrer</button>
      </div>
    </form>
  </div>
</div>
