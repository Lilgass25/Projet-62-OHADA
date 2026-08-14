<?php
// Détection automatique du niveau de profondeur (public/ = 1 niveau, modules/xxx/ = 2 niveaux)
$estModule  = strpos($_SERVER['SCRIPT_NAME'], '/modules/') !== false;
$rootUrl    = $estModule ? '../../public/' : '';
$assetsUrl  = $estModule ? '../../' : '../';
$scriptName = $_SERVER['SCRIPT_NAME'];

/** Retourne 'active' si le segment d'URL correspond à la page courante */
function navActive(string $segment): string
{
    global $scriptName;
    return (strpos($scriptName, $segment) !== false) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($titrePage) ? e($titrePage) . ' - ' : '' ?>OHADA Juridique | Projet 62</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="<?= $assetsUrl ?>assets/css/style.css">
</head>
<body>
<nav class="app-navbar navbar navbar-expand-xl navbar-dark">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="<?= $rootUrl ?>dashboard.php">
      <span class="brand-icon"><i class="fa-solid fa-scale-balanced"></i></span>
      <span class="brand-text">OHADA <strong>Juridique</strong></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-label="Menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link <?= navActive('dashboard.php') ?>" href="<?= $rootUrl ?>dashboard.php"><i class="fa-solid fa-gauge-high"></i>Tableau de bord</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('/societes/') ?>" href="<?= $assetsUrl ?>modules/societes/liste.php"><i class="fa-solid fa-building"></i>Sociétés</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('/associes/') ?>" href="<?= $assetsUrl ?>modules/associes/liste.php"><i class="fa-solid fa-users"></i>Associés</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('/dirigeants/') ?>" href="<?= $assetsUrl ?>modules/dirigeants/liste.php"><i class="fa-solid fa-user-tie"></i>Dirigeants</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('/assemblees/') ?>" href="<?= $assetsUrl ?>modules/assemblees/liste.php"><i class="fa-solid fa-people-roof"></i>Assemblées</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('/formalites/') ?>" href="<?= $assetsUrl ?>modules/formalites/liste.php"><i class="fa-solid fa-file-signature"></i>Formalités</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('/contrats/') ?>" href="<?= $assetsUrl ?>modules/contrats/liste.php"><i class="fa-solid fa-file-contract"></i>Contrats</a></li>
      </ul>
      <?php if (!empty($_SESSION['nom_complet'])): ?>
      <div class="navbar-user dropdown">
        <button class="user-toggle dropdown-toggle" type="button" id="userMenuBtn" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="user-avatar"><?php preg_match('/^./u', $_SESSION['nom_complet'], $m); echo e($m[0] ?? '?'); ?></span>
          <span class="user-name d-none d-md-inline"><?= e($_SESSION['nom_complet']) ?></span>
        </button>
        <div class="dropdown-menu dropdown-menu-end user-dropdown-menu" aria-labelledby="userMenuBtn">
          <div class="user-dropdown-header">
            <span class="user-dropdown-fullname"><?= e($_SESSION['nom_complet']) ?></span>
            <span class="user-role role-<?= e($_SESSION['role']) ?>"><?= e(ucfirst($_SESSION['role'])) ?></span>
          </div>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="<?= $rootUrl ?>logout.php"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</nav>
<main class="container-fluid px-4 py-4 content-area">
