<?php
require_once __DIR__ . '/../../includes/auth.php';
exigerRole(['administrateur', 'juriste']);

$pdo = getPDO();
$societes = $pdo->query("SELECT id_societe, raison_sociale FROM societes ORDER BY raison_sociale")->fetchAll();
$erreurs = [];
$donnees = ['id_societe'=>'','type_ag'=>'Ordinaire','date_ag'=>'','lieu'=>'','ordre_du_jour'=>'','parts_representees'=>'','parts_totales'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierTokenCSRF($_POST['csrf_token'] ?? null)) {
        $erreurs[] = 'Session expirée, veuillez réessayer.';
    } else {
        foreach ($donnees as $champ => $v) $donnees[$champ] = trim($_POST[$champ] ?? '');

        if (empty($donnees['id_societe'])) $erreurs[] = 'La société est obligatoire.';
        if (empty($donnees['date_ag'])) $erreurs[] = "La date de l'AG est obligatoire.";
        if ($donnees['ordre_du_jour'] === '') $erreurs[] = "L'ordre du jour est obligatoire.";

        if (empty($erreurs)) {
            // Quorum : majorité simple des parts représentées sur le total (règle OHADA de base, simplifiée)
            $partsRepresentees = (int)($donnees['parts_representees'] ?: 0);
            $partsTotales = (int)($donnees['parts_totales'] ?: 0);
            $quorumAtteint = ($partsTotales > 0 && $partsRepresentees >= ($partsTotales / 2)) ? 1 : 0;

            $stmt = $pdo->prepare("
                INSERT INTO assemblees_generales (id_societe, type_ag, date_ag, lieu, ordre_du_jour,
                    parts_representees, parts_totales, quorum_atteint, id_utilisateur)
                VALUES (:id_societe, :type_ag, :date_ag, :lieu, :ordre_du_jour, :parts_representees, :parts_totales, :quorum_atteint, :id_utilisateur)
            ");
            $stmt->execute([
                ':id_societe' => $donnees['id_societe'], ':type_ag' => $donnees['type_ag'], ':date_ag' => $donnees['date_ag'],
                ':lieu' => $donnees['lieu'] ?: null, ':ordre_du_jour' => $donnees['ordre_du_jour'],
                ':parts_representees' => $partsRepresentees ?: null, ':parts_totales' => $partsTotales ?: null,
                ':quorum_atteint' => $quorumAtteint, ':id_utilisateur' => $_SESSION['id_utilisateur'],
            ]);
            $idNouveau = (int)$pdo->lastInsertId();
            logAudit('CREATE', 'assemblees_generales', $idNouveau, 'Ajout AG pour société #' . $donnees['id_societe']);
            $_SESSION['flash_succes'] = 'Assemblée générale enregistrée. Quorum ' . ($quorumAtteint ? 'atteint' : 'non atteint') . '.';
            redirect('liste.php');
        }
    }
}

$csrf = genererTokenCSRF();
$titrePage = 'Nouvelle assemblée générale';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="mb-3"><a href="liste.php" class="text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Retour</a></div>
<h3 class="fw-bold mb-4"><i class="fa-solid fa-people-roof text-primary me-2"></i>Nouvelle assemblée générale</h3>
<?php if ($erreurs): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($erreurs as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" class="row g-3 js-validate">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <div class="col-md-6">
        <label class="form-label">Société *</label>
        <select name="id_societe" class="form-select" required>
          <option value="">-- Choisir --</option>
          <?php foreach ($societes as $s): ?>
            <option value="<?= (int)$s['id_societe'] ?>" <?= $donnees['id_societe'] === (string)$s['id_societe'] ? 'selected' : '' ?>><?= e($s['raison_sociale']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Type d'AG *</label>
        <select name="type_ag" class="form-select" required>
          <?php foreach (['Ordinaire','Extraordinaire','Mixte'] as $t): ?>
            <option value="<?= $t ?>" <?= $donnees['type_ag'] === $t ? 'selected' : '' ?>><?= $t ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Date de l'AG *</label>
        <input type="date" name="date_ag" class="form-control" required value="<?= e($donnees['date_ag']) ?>">
      </div>
      <div class="col-md-8">
        <label class="form-label">Lieu</label>
        <input type="text" name="lieu" class="form-control" value="<?= e($donnees['lieu']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Parts représentées</label>
        <input type="number" name="parts_representees" class="form-control" min="0" value="<?= e($donnees['parts_representees']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Parts totales de la société</label>
        <input type="number" name="parts_totales" class="form-control" min="0" value="<?= e($donnees['parts_totales']) ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Ordre du jour *</label>
        <textarea name="ordre_du_jour" class="form-control" rows="3" required><?= e($donnees['ordre_du_jour']) ?></textarea>
      </div>
      <div class="col-12 text-end mt-4">
        <a href="liste.php" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Enregistrer</button>
      </div>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
