<?php
require_once __DIR__ . '/../../includes/auth.php';
exigerRole(['administrateur']);

$pdo = getPDO();
$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $pdo->prepare('DELETE FROM assemblees_generales WHERE id_ag = :id')->execute([':id' => $id]);
    logAudit('DELETE', 'assemblees_generales', $id, 'Suppression AG');
    $_SESSION['flash_succes'] = 'Assemblée générale supprimée avec succès.';
}
redirect('liste.php');
