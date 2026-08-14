<?php
/**
 * Envoi d'emails automatiques (alertes formalités, notifications)
 * Utilise PHPMailer. En développement local (XAMPP sans SMTP configuré),
 * les erreurs d'envoi sont journalisées sans bloquer l'application.
 */

require_once __DIR__ . '/../libs/phpmailer/Exception.php';
require_once __DIR__ . '/../libs/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../libs/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

// --- Paramètres SMTP (à adapter : Gmail, Outlook, ou serveur de l'établissement) ---
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'votre.email@gmail.com');       // à remplacer
define('SMTP_PASS', 'mot_de_passe_application');     // à remplacer (mot de passe d'application Gmail)
define('SMTP_FROM_NAME', 'Plateforme Juridique OHADA');

/**
 * Envoie un email. Retourne true en cas de succès, false sinon (jamais d'exception bloquante).
 * $erreur (passé par référence) reçoit un message explicite destiné à l'utilisateur.
 */
function envoyerEmail(string $destinataire, string $sujet, string $corpsHtml, ?string &$erreur = null): bool
{
    // Identifiants encore laissés à leur valeur de test : on échoue immédiatement
    // plutôt que de tenter (et d'attendre) une vraie connexion à Gmail.
    if (SMTP_USER === 'votre.email@gmail.com' || SMTP_PASS === 'mot_de_passe_application') {
        $erreur = "Serveur SMTP non configuré (identifiants de test). Renseignez SMTP_USER et SMTP_PASS dans includes/mailer.php.";
        error_log('Erreur envoi email : SMTP non configuré (identifiants de test)');
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        // Évite un blocage long si le serveur SMTP est injoignable.
        $mail->Timeout       = 10;
        $mail->SMTPKeepAlive = false;

        $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
        $mail->addAddress($destinataire);

        $mail->isHTML(true);
        $mail->Subject = $sujet;
        $mail->Body    = $corpsHtml;
        $mail->AltBody  = strip_tags($corpsHtml);

        $mail->send();
        return true;
    } catch (PHPMailerException $e) {
        // En environnement XAMPP local sans SMTP réel configuré, l'envoi échoue :
        // on journalise sans interrompre le fonctionnement de l'application.
        error_log('Erreur envoi email : ' . $mail->ErrorInfo);
        $erreur = "Échec de l'envoi de l'email : " . $mail->ErrorInfo;
        return false;
    }
}

/**
 * Envoie une alerte automatique pour une formalité proche de son échéance.
 */
function envoyerAlerteFormalite(array $formalite, string $raisonSocialeSociete, string $emailResponsable, ?string &$erreur = null): bool
{
    $sujet = 'Alerte échéance formalité : ' . $formalite['description'];
    $corps = "
        <div style='font-family:Arial,sans-serif;'>
            <h3 style='color:#0d3b66;'>Alerte échéance de formalité juridique</h3>
            <p>Bonjour,</p>
            <p>La formalité suivante approche de son échéance :</p>
            <table style='border-collapse:collapse;width:100%;'>
                <tr><td style='padding:6px;border:1px solid #ddd;'><strong>Société</strong></td>
                    <td style='padding:6px;border:1px solid #ddd;'>" . htmlspecialchars($raisonSocialeSociete) . "</td></tr>
                <tr><td style='padding:6px;border:1px solid #ddd;'><strong>Formalité</strong></td>
                    <td style='padding:6px;border:1px solid #ddd;'>" . htmlspecialchars($formalite['description']) . "</td></tr>
                <tr><td style='padding:6px;border:1px solid #ddd;'><strong>Échéance</strong></td>
                    <td style='padding:6px;border:1px solid #ddd;'>" . formatDate($formalite['date_echeance']) . "</td></tr>
            </table>
            <p style='margin-top:16px;'>Merci de traiter cette formalité avant l'échéance afin d'éviter tout risque de non-conformité OHADA/RCCM.</p>
            <p style='color:#888;font-size:12px;'>Message automatique — Plateforme de gestion juridique OHADA</p>
        </div>
    ";
    return envoyerEmail($emailResponsable, $sujet, $corps, $erreur);
}
