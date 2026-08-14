<?php
require_once __DIR__ . '/../../includes/auth.php';
exigerRole(['administrateur']);

$pdo = getPDO();
$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT description FROM formalites WHERE id_formalite = :id');
    $stmt->execute([':id' => $id]);
    $formalite = $stmt->fetch();
    if ($formalite) {
        $pdo->prepare('DELETE FROM formalites WHERE id_formalite = :id')->execute([':id' => $id]);
        logAudit('DELETE', 'formalites', $id, 'Suppression de la formalité ' . $formalite['description']);
        $_SESSION['flash_succes'] = 'Formalité supprimée avec succès.';
    }
}
redirect('liste.php');
