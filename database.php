<?php
/**
 * Connexion PDO à la base de données
 * Projet 62 - Gestion juridique des sociétés OHADA (AUSCGIE) / RCCM
 * Adapter les identifiants ci-dessous à votre environnement XAMPP.
 */

// --- Paramètres de connexion (à adapter si besoin) ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'projet62_ohada');
define('DB_USER', 'root');
define('DB_PASS', '');          // Sous XAMPP, root n'a pas de mot de passe par défaut
define('DB_CHARSET', 'utf8mb4');

// --- Durée maximale d'inactivité avant déconnexion automatique (en secondes) ---
define('SESSION_TIMEOUT', 1800); // 30 minutes

/**
 * Retourne une instance PDO connectée (singleton simple).
 * Utilise des requêtes préparées partout ailleurs dans l'application
 * pour se prémunir des injections SQL (exigence obligatoire du cahier des charges).
 */
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // force les vraies requêtes préparées côté MySQL
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // On ne remonte jamais le message d'erreur brut à l'écran (sécurité)
            error_log('Erreur de connexion BDD : ' . $e->getMessage());
            die('Erreur de connexion à la base de données. Contactez l\'administrateur.');
        }
    }

    return $pdo;
}
