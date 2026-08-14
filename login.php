<?php
require_once __DIR__ . '/../includes/auth.php';

if (!empty($_SESSION['id_utilisateur'])) {
    redirect('dashboard.php');
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierTokenCSRF($_POST['csrf_token'] ?? null)) {
        $erreur = 'Session expirée, veuillez réessayer.';
    } else {
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '');
        $motDePasse = $_POST['mot_de_passe'] ?? '';

        if ($email === '' || $motDePasse === '') {
            $erreur = 'Veuillez renseigner l\'email et le mot de passe.';
        } elseif (tenterConnexion($email, $motDePasse)) {
            redirect('dashboard.php');
        } else {
            $erreur = 'Identifiants incorrects ou compte inactif.';
        }
    }
}

$csrf = genererTokenCSRF();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion - OHADA Juridique</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-body">
<div class="login-wrapper">
  <div class="card login-card shadow-lg">
    <div class="card-body p-5">
      <div class="text-center mb-4">
        <div class="login-brand-icon"><i class="fa-solid fa-scale-balanced"></i></div>
        <h4 class="mb-0">Gestion Juridique OHADA</h4>
        <p class="text-muted small mb-0">AUSCGIE &middot; Formalités RCCM</p>
      </div>

      <?php if (isset($_GET['expire'])): ?>
        <div class="alert alert-warning py-2 small"><i class="fa-solid fa-clock me-1"></i>Votre session a expiré, veuillez vous reconnecter.</div>
      <?php endif; ?>
      <?php if ($erreur): ?>
        <div class="alert alert-danger py-2 small"><i class="fa-solid fa-circle-exclamation me-1"></i><?= e($erreur) ?></div>
      <?php endif; ?>

      <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <div class="mb-3">
          <label class="form-label">Adresse email</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="fa-solid fa-envelope text-muted"></i></span>
            <input type="email" name="email" class="form-control" required autofocus placeholder="nom@structure.sn">
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label">Mot de passe</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="fa-solid fa-lock text-muted"></i></span>
            <input type="password" name="mot_de_passe" class="form-control" required>
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2">
          <i class="fa-solid fa-right-to-bracket me-1"></i> Se connecter
        </button>
      </form>

      <hr class="my-4">

      <div class="demo-accounts">
        <strong><i class="fa-solid fa-flask me-1"></i>Comptes de démonstration</strong>
        <div class="mt-2">Administrateur &middot; admin@cabinet-ohada.sn &middot; Admin@2026</div>
        <div>Juriste &middot; juriste@cabinet-ohada.sn &middot; Juriste@2026</div>
        <div>Consultant &middot; consultant@cabinet-ohada.sn &middot; Consult@2026</div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
