<?php
/**
 * Fonctions utilitaires globales
 */

/** Échappe une chaîne pour affichage HTML (protection XSS) */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Redirige vers une autre page et arrête l'exécution */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** Formate un montant en FCFA */
function formatMontant($montant): string
{
    if ($montant === null) return '-';
    return number_format((float)$montant, 0, ',', ' ') . ' FCFA';
}

/** Formate une date SQL (Y-m-d) en format sénégalais (d/m/Y) */
function formatDate(?string $date): string
{
    if (empty($date)) return '-';
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d ? $d->format('d/m/Y') : '-';
}

/**
 * Enregistre une action dans le journal d'audit (traçabilité obligatoire).
 */
function logAudit(string $action, string $table, ?int $idEnregistrement = null, string $details = ''): void
{
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare(
            'INSERT INTO journal_audit (id_utilisateur, action, table_concernee, id_enregistrement, details, adresse_ip)
             VALUES (:id_utilisateur, :action, :table_concernee, :id_enregistrement, :details, :ip)'
        );
        $stmt->execute([
            ':id_utilisateur'   => $_SESSION['id_utilisateur'] ?? null,
            ':action'           => $action,
            ':table_concernee'  => $table,
            ':id_enregistrement'=> $idEnregistrement,
            ':details'          => $details,
            ':ip'               => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Throwable $e) {
        error_log('Erreur journal_audit : ' . $e->getMessage());
    }
}

/** Calcule les paramètres de pagination */
function paginer(int $totalLignes, int $pageActuelle, int $parPage = 20): array
{
    $totalPages = max(1, (int)ceil($totalLignes / $parPage));
    $pageActuelle = max(1, min($pageActuelle, $totalPages));
    $offset = ($pageActuelle - 1) * $parPage;

    return [
        'total_pages'   => $totalPages,
        'page_actuelle' => $pageActuelle,
        'par_page'      => $parPage,
        'offset'        => $offset,
    ];
}

/** Génère un jeton CSRF et le stocke en session */
function genererTokenCSRF(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Vérifie le jeton CSRF soumis dans un formulaire */
function verifierTokenCSRF(?string $token): bool
{
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
