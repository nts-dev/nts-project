<?php

include_once '../../../config.php';

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_NUMBER_INT);

switch ($action) {

    case 1:

        $report_id = filter_input(INPUT_POST, 'report_id', FILTER_SANITIZE_NUMBER_INT);
        $userlggd = filter_input(INPUT_POST, 'eid', FILTER_SANITIZE_NUMBER_INT);

        setArchive($report_id, $userlggd);

        break;

    case 3:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $query = "SELECT Report_Body FROM tradestar_reports WHERE Report_ID =" . $id;
        $result = mysqli_query($dbc, $query);
        $row = mysqli_fetch_assoc($result);

        $content = $row['Report_Body'];
        $image_path = "http://bo.nts.nl";

        //format article text
//        $content = str_replace('"../../Controller/files', '"' . $image_path . '/projects_new/Controller/files', $content);
//        $content = str_replace('"../userfiles', '"' . $image_path . '/userfiles', $content);
//        $content = str_replace("../video", $image_path . "/video", $content);
//        $content = str_replace("../nts_admin", $image_path . "/nts_admin", $content);
//        $content = str_replace("tinymce/jscripts", $image_path . "/script/tinymce/jscripts", $content);

        echo json_encode(array("content" => $content));
        break;

    case 4:

        //update report document
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $content = $_POST['notes'];
//        $content = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);
        $content = mysqli_real_escape_string($dbc, $content);
        $userlggd = filter_input(INPUT_POST, 'eid', FILTER_SANITIZE_NUMBER_INT);
//        echo $content; exit;

        $sql = "UPDATE tradestar_reports SET Report_Body = '" . $content . "' WHERE Report_ID =" . $id;
        if (mysqli_query($dbc, $sql)) {
            $msg = "Successfully saved!";
        } else {
            $msg = "Error : " . mysqli_error($dbc);
        }
        echo json_encode(array("message" => $msg, 'report_id' => $id));
        break;

    default:
        break;
}

function setArchive($tradestar_report_id, $userlggd) {

    global $dbc;

    $date = new DateTime();
    $today = $date->format('Y-m-d H:i:s');

    //get author of the already logged
    $author = $userlggd;
    if ($author == null) {
        $author = 'NULL';
    }

    $Report_Body = getTableDetailField("tradestar_reports", $tradestar_report_id, "Report_ID", "Report_Body");

    $category = getTableDetailField("tradestar_reports", $tradestar_report_id, "Report_ID", "Report_Category");
    $subject = getTableDetailField("tradestar_reports", $tradestar_report_id, "Report_ID", "Report_Subject");
    $rptCategory = getTableDetailField("tradestar_reports", $tradestar_report_id, "Report_ID", "PrId");

    $report_editor = mysqli_real_escape_string($dbc, $Report_Body);

    $sql = "INSERT INTO tradestar_reports_archive(History_Date,History_Report_ID,History_Body,History_Category,History_Subject,History_Author,Report_Category)
                        VALUES ('{$today}','{$tradestar_report_id}','{$report_editor}','{$category}','{$subject}','{$author}','{$rptCategory}')";

    $res = mysqli_query($dbc, $sql) or die(mysqli_error($dbc) . " INSERT TRADESTAR HISTORY ERROR");

    //update time of report on changes
    $sql = "Update tradestar_reports SET Report_Date = '$today' where Report_ID = '$tradestar_report_id'";
    $res = mysqli_query($dbc, $sql) or die(mysqli_error($dbc) . " INSERT TRADESTAR HISTORY ERROR");


    $result = mysqli_query($dbc, "SELECT History_ID FROM tradestar_reports_archive WHERE History_Report_ID = " . $tradestar_report_id . " ORDER BY History_ID DESC LIMIT 49,1");

    if (mysqli_num_rows($result) > 0) {

        $fetch = mysqli_fetch_assoc($result);
        mysqli_query($dbc, "DELETE FROM tradestar_reports_archive WHERE History_ID <" . $fetch['History_ID'] . " AND History_Report_ID = " . $tradestar_report_id);
    }
}

function getTableDetailField($table, $id, $idcol, $field) {

    global $dbc;

    $sql = "SELECT {$field} FROM {$table} WHERE {$idcol} = {$id}";

    $res = mysqli_query($dbc, $sql) or die(mysqli_error($dbc));
    $row = mysqli_fetch_array($res);

    return $row["{$field}"];
}

function xml_entities($string) {
    return strtr(
            $string, array(
        "<" => "&lt;",
        ">" => "&gt;",
        '"' => "&quot;",
        "'" => "&apos;",
        "&" => "&amp;",
            )
    );
}
