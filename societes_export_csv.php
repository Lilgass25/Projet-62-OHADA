<?php
require_once __DIR__ . '/../includes/auth.php';
exigerConnexion();

$pdo = getPDO();

$recherche = trim($_GET['q'] ?? '');
$formeFiltre = $_GET['forme'] ?? '';
$statutFiltre = $_GET['statut'] ?? '';

$conditions = [];
$params = [];
if ($recherche !== '') {
    $conditions[] = "(raison_sociale LIKE :q1 OR numero_rccm LIKE :q2)";
    $params[':q1'] = '%' . $recherche . '%';
    $params[':q2'] = '%' . $recherche . '%';
}
if ($formeFiltre !== '') { $conditions[] = "forme_juridique = :forme"; $params[':forme'] = $formeFiltre; }
if ($statutFiltre !== '') { $conditions[] = "statut = :statut"; $params[':statut'] = $statutFiltre; }
$whereSql = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

$stmt = $pdo->prepare("SELECT raison_sociale, sigle, forme_juridique, capital_social, siege_social, ninea, numero_rccm, date_immatriculation, statut FROM societes $whereSql ORDER BY raison_sociale");
$stmt->execute($params);
$societes = $stmt->fetchAll();

logAudit('EXPORT', 'societes', null, 'Export CSV de la liste des sociétés');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=societes_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fputs($output, "\xEF\xBB\xBF"); // BOM UTF-8 pour compatibilité Excel
fputcsv($output, ['Raison sociale', 'Sigle', 'Forme juridique', 'Capital social (FCFA)', 'Siège social', 'NINEA', 'RCCM', 'Date immatriculation', 'Statut'], ';');

foreach ($societes as $s) {
    fputcsv($output, [
        $s['raison_sociale'], $s['sigle'], $s['forme_juridique'], $s['capital_social'],
        $s['siege_social'], $s['ninea'], $s['numero_rccm'], $s['date_immatriculation'], $s['statut']
    ], ';');
}
fclose($output);
exit;
