<?php
require_once __DIR__ . '/../includes/auth.php';
redirect(!empty($_SESSION['id_utilisateur']) ? 'dashboard.php' : 'login.php');
