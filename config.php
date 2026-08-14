<?php
/**
 * Configuration générale — projet62_ohada
 * Adaptez ces constantes à votre environnement (WAMP/XAMPP/LAMP).
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'ohada_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'Registre OHADA');
define('APP_SIGLE', 'projet62_ohada');

// Chemin relatif de base utilisé par header.php pour les liens (à adapter si besoin)
define('BASE_URL', '/projet62_ohada');

date_default_timezone_set('Africa/Dakar');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
