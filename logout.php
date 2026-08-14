<?php
require_once __DIR__ . '/../includes/auth.php';

if (!empty($_SESSION['id_utilisateur'])) {
    logAudit('LOGOUT', 'utilisateurs', $_SESSION['id_utilisateur'], 'Déconnexion');
}

$_SESSION = [];
session_unset();
session_destroy();

redirect('login.php');
