<?php
require_once __DIR__ . '/../../includes/auth.php';
exigerRole(['administrateur']);

$pdo = getPDO();
$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT nom_complet FROM dirigeants WHERE id_dirigeant = :id');
    $stmt->execute([':id' => $id]);
    $dirigeant = $stmt->fetch();
    if ($dirigeant) {
        $pdo->prepare('DELETE FROM dirigeants WHERE id_dirigeant = :id')->execute([':id' => $id]);
        logAudit('DELETE', 'dirigeants', $id, 'Suppression du dirigeant ' . $dirigeant['nom_complet']);
        $_SESSION['flash_succes'] = 'Dirigeant supprimé avec succès.';
    }
}
redirect('liste.php');
