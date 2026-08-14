<?php
require_once __DIR__ . '/../../includes/auth.php';
exigerRole(['administrateur']); // seul l'administrateur peut supprimer

$pdo = getPDO();
$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT raison_sociale FROM societes WHERE id_societe = :id');
    $stmt->execute([':id' => $id]);
    $societe = $stmt->fetch();

    if ($societe) {
        // Les tables liées (associes, dirigeants, contrats, formalites, mouvements_capital, AG)
        // sont supprimées automatiquement grâce à ON DELETE CASCADE défini dans schema.sql
        $del = $pdo->prepare('DELETE FROM societes WHERE id_societe = :id');
        $del->execute([':id' => $id]);

        logAudit('DELETE', 'societes', $id, 'Suppression de la société ' . $societe['raison_sociale']);
        $_SESSION['flash_succes'] = 'Société supprimée avec succès.';
    }
}

redirect('liste.php');
