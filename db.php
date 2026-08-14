<?php
require_once __DIR__ . '/config.php';

/**
 * Connexion PDO centralisée.
 * Retourne un objet PDO si la base est disponible, sinon null.
 * Les modules doivent gérer le cas null et retomber sur des données de démonstration
 * (voir functions.php) tant que la base n'est pas encore créée (database/schema.sql).
 */
function db(): ?PDO
{
    static $pdo = null;
    static $tried = false;

    if ($pdo !== null || $tried) {
        return $pdo;
    }
    $tried = true;

    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        // Base non configurée pour l'instant : la plateforme reste utilisable en mode démo.
        $pdo = null;
    }
    return $pdo;
}
