<?php

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

set_time_limit(0);

ini_set('memory_limit', '-1');

//ini_set('display_errors', '1');

require_once("config.php");


$query = "
    SELECT
	tr.Report_Subject title,
	tr.explorer_id,
	tr.goal,
	tr.scope,
	(
		SELECT
			CONCAT(
				COALESCE(FirstName, ''),
				' ',
				COALESCE(SecondName, ''),
				' ',
				COALESCE(LastName, '')
			)
		FROM
			trainees
		WHERE
			ID = tr.supervisor
	)`supervisor`,
	tr.doc_input,
	tr.doc_output,
	tr.doc_frequency,
	(
		SELECT
			GROUP_CONCAT(
				CONCAT(COALESCE(FirstName, ''))
			)
		FROM
			trainees
		WHERE
			ID IN(
				SELECT
					employee_id
				FROM
					`tradestar_reports_to_employees`
				WHERE
					`report_id` = $id
			)
	)employees
FROM
	tradestar_reports tr
WHERE
	tr.Report_ID = " . $id;
$result = mysqli_query($dbc,$query) ;
$row = mysqli_fetch_assoc($result);

$content .= "<table style='width:100%;font-style: Trebuchet MS;font-size:13px;'>";

$content .= "<tr><td style='width:100%;font-weight:bold;'>Document Details</td></tr>";
$content .= "<tr><td>";
$content .= "<table cellspacing='0' cellpadding='0' style='width:80%;font-style: Trebuchet MS;font-size:9px;'>";
$content .= "<tr><td style='width:20%;border: 1px solid #ddd; padding: 5px;'>Goal</td><td style='width:60%;border: 1px solid #ddd;padding: 5px;border-left:none;white-space: pre-line;word-wrap: break-word;'>" . $row['goal'] . "</td></tr>";
$content .= "<tr><td style='width:20%;border: 1px solid #ddd; padding: 5px;border-top: none;'>Scope</td><td style='width:60%;border: 1px solid #ddd;padding: 5px;border-left:none; border-top: none;'>" . $row['scope'] . "</td></tr>";
$content .= "<tr><td style='width:20%;border: 1px solid #ddd; padding: 5px;border-top: none;'>Supervisor</td><td style='width:60%;border: 1px solid #ddd;padding: 5px;border-left:none; border-top: none;'>" . $row['supervisor'] . "</td></tr>";
$content .= "<tr><td style='width:20%;border: 1px solid #ddd; padding: 5px;border-top: none;'>Employee</td><td style='width:60%;border: 1px solid #ddd;padding: 5px;border-left:none; border-top: none;'>" . $row['employees'] . "</td></tr>";
$content .= "<tr><td style='width:20%;border: 1px solid #ddd; padding: 5px;border-top: none;'>Frequency</td><td style='width:60%;border: 1px solid #ddd;padding: 5px;border-left:none; border-top: none;'>" . $row['doc_frequency'] . "</td></tr>";
$content .= "<tr><td style='width:20%;border: 1px solid #ddd; padding: 5px;border-top: none;'>Input</td><td style='width:60%;border: 1px solid #ddd;padding: 5px;border-left:none; border-top: none;'>" . $row['doc_input'] . "</td></tr>";
$content .= "<tr><td style='width:20%;border: 1px solid #ddd; padding: 5px;border-top: none;'>Output</td><td style='width:60%;border: 1px solid #ddd;padding: 5px;border-left:none; border-top: none;'>" . $row['doc_output'] . "</td></tr>";
$content .= "<tr><td style='width:20%;border: 1px solid #ddd; padding: 5px;border-top: none;'>Procedures</td><td style='width:60%;border: 1px solid #ddd;padding: 5px;border-left:none; border-top: none;'>" . $row['explorer_id'] . "</td></tr>";
$content .= "</table>";
$content .= "</td></tr>";

$content .= "<tr><td>&nbsp;</td></tr>";
$content .= "<tr><td style='width:100%;font-weight:bold;'>Table of Content</td></tr>";

$qry_content = "SELECT
	id,
	parent_id,
	title,
	sort,
	content
FROM
	document_toc
WHERE
	doc_id = $id
ORDER BY
	parent_id = 0 DESC,
	sort ASC";

$array_parent = array();
$array_child = array();

$res_content = mysqli_query($dbc,$qry_content) ;
$array_content = array();
while ($obj = mysql_fetch_object($res_content)) {
    $array_content[] = $obj;
}

foreach ($array_content as $obj) {
    if ($obj->parent_id == 0) {
        $array_parent[$obj->id] = $obj;
    }
    if ($obj->parent_id != 0) {
        $array_child[$obj->parent_id][$obj->id] = $obj;
    }
}

$content .= "<tr><td>";
$content .= "<table cellspacing='0' cellpadding='0' style='width:80%;font-style: Trebuchet MS;font-size:9px;'>";

foreach ($array_parent as $value) {
    $content .= "<tr><td style='padding:5px;'> " . $value->sort . ". " . $value->title . "</td></tr>";

    if (count($array_child[$value->id]) > 0) {
        foreach ($array_child[$value->id] as $child) {
            $content .= "<tr><td style='padding:5px;'> " . $value->sort . "." . $child->sort . ". " . $child->title . "</td></tr>";
        }
    }
}

$content .= "</table>";
$content .= "</td></tr>";

$content .= "</table>";

$content .= "<table cellspacing='0' cellpadding='0' style='width:80%;font-style: Trebuchet MS;font-size:9px;'>";

foreach ($array_parent as $value) {
    $content .= "<tr><td style='padding:5px;font-size:11px;font-weight:bold;'> " . $value->sort . ". " . $value->title . "</td></tr>";
    $content .= "<tr><td style='padding:5px;'> " . $value->content . "</td></tr>";
    
    if (count($array_child[$value->id]) > 0) {
        foreach ($array_child[$value->id] as $child) {
            $content .= "<tr><td style='padding:5px;font-size:11px;font-weight:bold;'> " . $value->sort . "." . $child->sort . ". " . $child->title . "</td></tr>";
            $content .= "<tr><td style='padding:5px;'> " . $child->content . "</td></tr>";
        }
    }
}

$content .= "</table>";

require_once(dirname(__FILE__) . '/../html2pdf/html2pdf.class.php');
try {
    $html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', 10);
    $html2pdf->pdf->SetDisplayMode('real');
    $html2pdf->writeHTML($content, isset($_GET['vuehtml']));
    $html2pdf->Output($company_id . "_" . $year_text . "_profit_loss.pdf");
} catch (HTML2PDF_exception $e) {
    echo $e;
    exit;
}