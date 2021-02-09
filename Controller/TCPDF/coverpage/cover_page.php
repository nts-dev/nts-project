<?php

date_default_timezone_set('Europe/Amsterdam');

require_once('config/tcpdf_config_alt.php');

require_once('../tcpdf.php');


$docId = filter_input(INPUT_GET, 'doc_id', FILTER_SANITIZE_NUMBER_INT);
$projectId = filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT);

$query = "SELECT Report_Subject,Report_Employee_ID FROM tradestar_reports WHERE Report_ID =" . $docId;
$result = mysqli_query($dbc,$query) ;
$row = mysqli_fetch_assoc($result);
$rptSubject = $row['Report_Subject'];
$Report_Employee_ID = $row['Report_Employee_ID'];

if ($Report_Employee_ID > 0) {

    $result = mysqli_query($dbc,"SELECT CONCAT(COALESCE(FirstName,''),' ',COALESCE(SecondName,''),' ',COALESCE(LastName,'')) employee FROM nts_site.trainees WHERE ID = " . $Report_Employee_ID);
    $row = mysqli_fetch_array($result);
    $assigned_eid = $row[0];
}

$no = generateProjectId($projectId);
$prct_details = $no . '/' . $docId . ' ' . $rptSubject;


// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($assigned_eid);
$pdf->SetTitle($prct_details);
$pdf->SetSubject($rptSubject);
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(0, 67, 0);
//$pdf->SetRightMargin(87);
// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// set font
$pdf->SetFont('helvetica', '', 16);

// ---------------------------------------------------------
// set page format (read source code documentation for further information)
$page_format = array(
    'MediaBox' => array('llx' => 0, 'lly' => 0, 'urx' => 210, 'ury' => 297),
    'CropBox' => array('llx' => 0, 'lly' => 0, 'urx' => 210, 'ury' => 297),
    'BleedBox' => array('llx' => 5, 'lly' => 5, 'urx' => 205, 'ury' => 292),
    'TrimBox' => array('llx' => 10, 'lly' => 10, 'urx' => 200, 'ury' => 287),
    'ArtBox' => array('llx' => 15, 'lly' => 15, 'urx' => 195, 'ury' => 282),
    'Dur' => 3,
    'trans' => array(
        'D' => 1.5,
        'S' => 'Split',
        'Dm' => 'V',
        'M' => 'O'
    ),
    'Rotate' => 90,
    'PZ' => 1,
);

// Check the example n. 29 for viewer preferences
// add first page ---
$pdf->AddPage('P', $page_format, false, false);
$pdf->Ln();

$pdf->Cell(0, 12, $prct_details, 'T', 1, 'C');
//Cell( $w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '', $stretch = 0, $ignore_min_height = false, $calign = 'T', $valign = 'M' )
// ---------------------------------------------------------
//Close and output PDF document
$pdf->Output($rptSubject.'.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
function generateProjectId($itemId) {
    if (strlen($itemId) == 1) {
        $projectId = "P00000" . $itemId . "";
    } else if (strlen($itemId) == 2) {
        $projectId = "P0000" . $itemId . "";
    } else if (strlen($itemId) == 3) {
        $projectId = "P000" . $itemId . "";
    } else if (strlen($itemId) == 4) {
        $projectId = "P00" . $itemId . "";
    } else if (strlen($itemId) == 5) {
        $projectId = "P0" . $itemId . "";
    } else {
        $projectId = $itemId;
    }

    return $projectId;
}
