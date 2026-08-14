<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../libs/fpdf/fpdf.php';
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

$stmt = $pdo->prepare("SELECT raison_sociale, forme_juridique, capital_social, numero_rccm, statut FROM societes $whereSql ORDER BY raison_sociale");
$stmt->execute($params);
$societes = $stmt->fetchAll();

logAudit('EXPORT', 'societes', null, 'Export PDF de la liste des sociétés');

/** Nettoie les caractères accentués pour l'encodage latin1 attendu par FPDF standard */
function propre(string $texte): string
{
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texte);
}

class PDFSocietes extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, propre('Liste des societes - Registre juridique OHADA'), 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 6, 'Genere le ' . date('d/m/Y a H:i'), 0, 1, 'C');
        $this->Ln(4);
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(13, 59, 102);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(65, 8, propre('Raison sociale'), 1, 0, 'L', true);
        $this->Cell(25, 8, 'Forme', 1, 0, 'C', true);
        $this->Cell(40, 8, propre('Capital (FCFA)'), 1, 0, 'R', true);
        $this->Cell(35, 8, 'RCCM', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Statut', 1, 1, 'C', true);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', '', 8);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDFSocietes();
$pdf->AliasNbPages();
$pdf->AddPage();

foreach ($societes as $s) {
    $pdf->Cell(65, 7, propre($s['raison_sociale']), 1);
    $pdf->Cell(25, 7, $s['forme_juridique'], 1, 0, 'C');
    $pdf->Cell(40, 7, number_format((float)$s['capital_social'], 0, ',', ' '), 1, 0, 'R');
    $pdf->Cell(35, 7, $s['numero_rccm'] ?: '-', 1, 0, 'C');
    $pdf->Cell(25, 7, propre($s['statut']), 1, 1, 'C');
}

$pdf->Output('I', 'societes_' . date('Y-m-d') . '.pdf');
exit;
