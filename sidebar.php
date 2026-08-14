<?php $activeNav = $activeNav ?? ''; ?>
<aside class="sidebar">
  <div class="brand">
    <div class="brand-mark">OH</div>
    <div class="brand-text"><?= APP_NAME ?><span><?= APP_SIGLE ?> · Dakar</span></div>
  </div>
  <nav class="side-nav">
    <a class="nav-item <?= $activeNav==='dashboard'?'active':'' ?>" href="<?= BASE_URL ?>/public/dashboard.php"><span class="nav-dot"></span> Tableau de bord</a>

    <div class="nav-label">Portefeuille</div>
    <a class="nav-item <?= $activeNav==='societes'?'active':'' ?>" href="<?= BASE_URL ?>/modules/societes/liste.php"><span class="nav-dot"></span> Sociétés</a>
    <a class="nav-item <?= $activeNav==='capital'?'active':'' ?>" href="<?= BASE_URL ?>/modules/capital/registre.php"><span class="nav-dot"></span> Actions &amp; parts sociales</a>
    <a class="nav-item <?= $activeNav==='dirigeants'?'active':'' ?>" href="<?= BASE_URL ?>/modules/dirigeants/liste.php"><span class="nav-dot"></span> Dirigeants &amp; mandats</a>

    <div class="nav-label">Vie juridique</div>
    <a class="nav-item <?= $activeNav==='ag'?'active':'' ?>" href="<?= BASE_URL ?>/modules/ag/liste.php"><span class="nav-dot"></span> Assemblées générales</a>
    <a class="nav-item <?= $activeNav==='contrats'?'active':'' ?>" href="<?= BASE_URL ?>/modules/contrats/liste.php"><span class="nav-dot"></span> Contrats &amp; engagements</a>
    <a class="nav-item <?= $activeNav==='conformite'?'active':'' ?>" href="<?= BASE_URL ?>/modules/conformite/tableau.php"><span class="nav-dot"></span> Conformité &amp; dépôts</a>

    <div class="nav-label">Administration</div>
    <a class="nav-item" href="#"><span class="nav-dot"></span> Utilisateurs</a>
    <a class="nav-item" href="#"><span class="nav-dot"></span> Paramètres</a>
  </nav>
  <div class="sidebar-foot">
    <div class="user-chip">
      <div class="avatar">MG</div>
      <div class="user-meta">
        <div class="user-name">Moussa GASSAMA</div>
        <div class="user-role">Administrateur</div>
      </div>
    </div>
  </div>
</aside>
