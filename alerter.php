<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/mailer.php';
exigerRole(['administrateur', 'juriste']);

$pdo = getPDO();
$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("
        SELECT f.*, s.raison_sociale, u.email AS email_responsable
        FROM formalites f
        JOIN societes s ON s.id_societe = f.id_societe
        JOIN utilisateurs u ON u.id_utilisateur = f.id_utilisateur_responsable
        WHERE f.id_formalite = :id
    ");
    $stmt->execute([':id' => $id]);
    $formalite = $stmt->fetch();

    if ($formalite) {
        $erreurEnvoi = null;
        $succes = envoyerAlerteFormalite($formalite, $formalite['raison_sociale'], $formalite['email_responsable'], $erreurEnvoi);
        logAudit('EMAIL_ALERTE', 'formalites', $id, 'Alerte envoyée pour ' . $formalite['description']);
        if ($succes) {
            $_SESSION['flash_succes'] = 'Email d\'alerte envoyé avec succès à ' . $formalite['email_responsable'] . '.';
        } else {
            $_SESSION['flash_erreur'] = $erreurEnvoi ?: 'Échec de l\'envoi de l\'email.';
        }
    }
}
redirect('liste.php');
