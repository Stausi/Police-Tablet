<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$journalId = $_GET['id'] ?? null;
function getUserJournalDetails($link, $journalId) {
    $stmt = $link->prepare("SELECT * FROM population_psykjournals WHERE id = ?");
    $stmt->bind_param('i', $journalId);
    $stmt->execute();
    $result = $stmt->get_result();

    $details = [];
    if ($result->num_rows > 0) {
        $details = $result->fetch_assoc();
    }
    return $details;
}

function getUserDetails($link, $playerId) {
    $stmt = $link->prepare("SELECT * FROM population_ems WHERE id = ?");
    $stmt->bind_param('i', $playerId);
    $stmt->execute();
    $user_result = $stmt->get_result();

    $target_user = "";
    if ($user_result->num_rows > 0) {
        while ($row = $user_result->fetch_assoc()) {
            if (isset($row["name"])) {
                $target_user = $row["name"];
            }
        }
    }
    return $target_user;
}

if ($journalId !== null) {
    $journalDetails = getUserJournalDetails($link, $journalId);
    $playerName = getUserDetails($link, $journalDetails['pid']);

    $pdf = new TCPDF();
    $pdf->AddPage();
    
    $pdf->SetFont('helvetica', '', 16);
    
    $imageFile = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/ems-logo-background.jpg';
    $imageX = 50;
    $imageY = 20;
    $textX = $imageX + 65;
    $textY = $imageY + 5;
    
    $pdf->Image($imageFile, $imageX, $imageY, 30, 30);
    
    $text = "Patient: " . $playerName . "\Psykolog: " . $journalDetails["username"] . "\nDato: " . $journalDetails["dato"];
    $pdf->SetXY($textX, $textY);
    $pdf->MultiCell(0, 10, $text, 0, 'L');
    $titleY = $imageY + 40;
    
    $pdf->SetFont('helvetica', 'B', 26);
    $pdf->SetXY(0, $titleY);

    $title = "Psykolog Report";
    $pdf->Cell(0, 0, $title, 0, 1, 'C', 0, '', 0);

    $labelValuePairs = [
        'Årsag' => $journalDetails["reason"],
        'Epikrise' => $journalDetails["epikrise"],
        'Samtale' => $journalDetails["conversation"],
        'Medicinsk behandling' => $journalDetails["medicin_treatment"],
        'Vurdering' => $journalDetails["psykolog_assessment"],
    ];
    
    $baseY = $pdf->GetY() + 10;
    function renderColumn($pdf, $pairs, $startX, &$currentY, $spacing = 2) {
        foreach ($pairs as $label => $value) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetXY($startX, $currentY);
            $pdf->MultiCell(150, 2, "$label:", 0, 'L');
            
            $currentY = $pdf->GetY() + $spacing;

            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetXY($startX, $currentY);
            $pdf->MultiCell(150, 2, $value, 0, 'L');
            
            $currentY = $pdf->GetY() + 10;
        }
    }
    
    $startXColumn1 = 30;
    $startXColumn2 = 125;
    
    renderColumn($pdf, $labelValuePairs, $startXColumn1, $baseY);

    $fontPath = $_SERVER['DOCUMENT_ROOT'] . '/assets/fonts/Creattion-Demo.otf';
    $fontName = TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 96);

    $pdf->SetAutoPageBreak(false);
    $pdf->SetFont($fontName, '', 50);
    $signatureText = $journalDetails["username"];

    $signatureWidth = $pdf->GetStringWidth($signatureText);
    $pdf->SetY(-30);

    $pageWidth = $pdf->getPageWidth();
    $margin = $pdf->getMargins();
    $centerX = ($pageWidth - $margin['left'] - $margin['right']) / 2 - ($signatureWidth / 2);

    $pdf->SetX($centerX);
    $pdf->Cell($signatureWidth, 10, $signatureText, 0, 0, 'L', 0);
    
    $pdf->Output('medical_report.pdf', 'I');
}
?>