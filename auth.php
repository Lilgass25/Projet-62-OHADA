<?php
/**
 * Authentification, session sécurisée et gestion des rôles
 * Rôles : administrateur (avancé+), juriste (utilisateur avancé), consultant (utilisateur standard)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// --- Configuration de session sécurisée ---
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    // Décommenter en production HTTPS :
    // ini_set('session.cookie_secure', 1);
    session_start();
}

/** Vérifie l'expiration par inactivité et déconnecte si nécessaire */
function verifierExpirationSession(): void
{
    if (isset($_SESSION['derniere_activite']) &&
        (time() - $_SESSION['derniere_activite']) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        redirect('login.php?expire=1');
    }
    $_SESSION['derniere_activite'] = time();
}

/** Tente une connexion. Retourne true si succès. */
function tenterConnexion(string $email, string $motDePasse): bool
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE email = :email AND statut = "actif" LIMIT 1');
    $stmt->execute([':email' => $email]);
    $utilisateur = $stmt->fetch();

    if ($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
        session_regenerate_id(true); // évite la fixation de session
        $_SESSION['id_utilisateur']   = $utilisateur['id_utilisateur'];
        $_SESSION['nom_complet']      = $utilisateur['prenom'] . ' ' . $utilisateur['nom'];
        $_SESSION['role']             = $utilisateur['role'];
        $_SESSION['derniere_activite'] = time();

        $maj = $pdo->prepare('UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id_utilisateur = :id');
        $maj->execute([':id' => $utilisateur['id_utilisateur']]);

        logAudit('LOGIN', 'utilisateurs', $utilisateur['id_utilisateur'], 'Connexion réussie');
        return true;
    }

    logAudit('LOGIN_ECHEC', 'utilisateurs', null, 'Tentative échouée pour ' . $email);
    return false;
}

/** Exige que l'utilisateur soit connecté, sinon redirige vers login */
function exigerConnexion(): void
{
    if (empty($_SESSION['id_utilisateur'])) {
        redirect('login.php');
    }
    verifierExpirationSession();
}

/**
 * Exige un ou plusieurs rôles précis.
 * Exemple : exigerRole(['administrateur','juriste']);
 */
function exigerRole(array $rolesAutorises): void
{
    exigerConnexion();
    if (!in_array($_SESSION['role'], $rolesAutorises, true)) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;padding:40px;text-align:center;">
                <h2>Accès refusé</h2>
                <p>Votre rôle (' . e($_SESSION['role']) . ') ne permet pas d\'accéder à cette page.</p>
                <a href="dashboard.php">Retour au tableau de bord</a>
             </div>');
    }
}

function estAdministrateur(): bool { return ($_SESSION['role'] ?? '') === 'administrateur'; }
function estJuriste(): bool        { return ($_SESSION['role'] ?? '') === 'juriste'; }
function estConsultant(): bool     { return ($_SESSION['role'] ?? '') === 'consultant'; }
