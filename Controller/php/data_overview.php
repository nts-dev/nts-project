<?php

ini_set('display_errors', '0');
require 'config_mysqli.php';
include("GeneralFunctions.php");
date_default_timezone_set('UTC');
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_NUMBER_INT);

switch ($action) {

    default:

        break;
    case 1:

        $start = "SELECT
                            ID a1,
                            CONCAT(
                                    COALESCE(FirstName, ''),
                                    ' ',
                                    COALESCE(SecondName, ''),
                                    ' ',
                                    COALESCE(LastName, '')
                            )a2
                    FROM
                            trainees
                    WHERE
                            status_id = 1
                    AND(
                            IntranetId <> 0 || IntranetId IS NOT NULL
                    )
                    AND ID <> 33";
        $result = mysqli_query($dbc,$start) ;
        $values[] = array('type' => 'button', 'id' => '0', "text" => 'Show All');
        while ($row = mysqli_fetch_array($result)) {
            $values[] = array('type' => 'button', 'id' => $row["a1"], "text" => $row["a2"]);
        }
        echo json_encode(array('options' => $values));


        break;

    case 2:

        $insert = "INSERT INTO `projects_monitoring` (`date_added`) VALUES ('" . date('Y-m-d') . "')";

        $insertEventResult = mysqli_query($dbc,$insert) or die(mysqli_error($dbc) . $insert);
        if ($insertEventResult) {
            $newId = mysqli_insert_id($dbc);
            $data['data'] = array('response' => $insertEventResult, 'text' => 'Successfully Added', 'row_id' => $newId);
        } else {
            $data['data'] = array('response' => $insertEventResult, 'text' => 'An Error Occured');
        }
        echo json_encode($data);

        break;

    case 3:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $employee_id = filter_input(INPUT_GET, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
        $open = filter_input(INPUT_GET, 'open', FILTER_SANITIZE_NUMBER_INT);
        $problems = filter_input(INPUT_GET, 'problems', FILTER_SANITIZE_NUMBER_INT);
        $date = filter_input(INPUT_GET, 'date');

        $qry = "SELECT
                        projects_monitoring.*,
                        a.doc_topic procedure_name,
                        (SELECT
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
                                IntranetId = a.doc_author_id) procedure_by
                FROM
                        projects_monitoring
                LEFT JOIN tbdocuments a ON a.doc_id = projects_monitoring.procedure_id ";
        if ($employee_id) {
            $qry .= "WHERE projects_monitoring.employee_id = " . $employee_id;
        }
        if ($open) {
            $qry .= "WHERE projects_monitoring.completed IS NOT NULL";
        }

        if ($date) {
            $qry .= "WHERE (projects_monitoring.procedure_date = '" . $date . "' OR projects_monitoring.bom = '" . $date . "' OR projects_monitoring.bought = '" . $date . "' OR projects_monitoring.delivered = '" . $date . "')";
        }

        if ($problems) {
            $qry .= "WHERE `completed` IS NULL AND `problems` <> ''";
        }
        $res = mysqli_query($dbc,$qry) or die(mysqli_error($dbc) . $qry);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        while ($row = mysqli_fetch_array($res)) {

            echo "<row id = '" . $row["id"] . "'>";
            echo "<cell>" . $row["id"] . "</cell>";
            echo "<cell><![CDATA[" . $row["date_added"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["employee_id"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["toc_id"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["location"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["procedure_by"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["procedure_name"] . "]]></cell>";
            echo "<cell>" . $row["procedure_id"] . "</cell>";
            echo "<cell>" . $row["doc_id"] . "</cell>";
            echo "<cell><![CDATA[" . $row["chapter"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["bom"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["bought"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["delivered"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["completed"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["verified"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["duration"] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 4:
        $id = $_GET['id'];

        $deleteEvents = "DELETE FROM `projects_monitoring` WHERE id IN ( " . $id . ")";
        $deleteEventsResult = mysqli_query($dbc,$deleteEvents) ;

        if ($deleteEventsResult) {
            $data['data'] = array('response' => $deleteEventsResult, 'text' => 'Successfully Deleted');
        } else {
            $data['data'] = array('response' => $deleteEventsResult, 'text' => 'An Error Occured While Deleting');
        }

        echo json_encode($data);
        break;

    case 5:

        $fieldvalue = filter_input(INPUT_GET, 'fieldvalue');
        $id = $_GET["id"];
        $field = $_GET["colId"];
        $colType = $_GET["colType"];
        $fieldvalue = mysqli_real_escape_string($dbc,$fieldvalue);

        $updateResult = updateSQL("projects_monitoring", $field, $fieldvalue, $id, "id", $colType);
        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);
        break;

    case 6:
        $fieldvalue = filter_input(INPUT_POST, "nValue");
        $id = filter_input(INPUT_POST, "id");
        $field = filter_input(INPUT_POST, "colId");

        $updateResult = updateSQL("projects_monitoring", $field, $fieldvalue, $id, "id", $colType);
        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);
        break;

    case 7:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $query = "
            SELECT
                    projects_monitoring.*,
                    a.doc_topic procedure_name,
                    (SELECT
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
                            IntranetId = a.doc_author_id) procedure_by
            FROM
                    projects_monitoring
            LEFT JOIN tbdocuments a ON a.doc_id = projects_monitoring.procedure_id
            WHERE projects_monitoring.id=" . $id;
        $result = mysqli_query($dbc,$query) ;
        $row = mysqli_fetch_assoc($result);

        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<data>';
        echo "<id><![CDATA[" . $row['id'] . "]]></id>";
        echo "<date_added><![CDATA[" . $row['date_added'] . "]]></date_added>";
        echo "<location><![CDATA[" . $row['location'] . "]]></location>";
        echo "<employee_id><![CDATA[" . $row['employee_id'] . "]]></employee_id>";
        echo "<toc_id><![CDATA[" . $row['toc_id'] . "]]></toc_id>";
        echo "<procedure_id><![CDATA[" . $row['procedure_id'] . "]]></procedure_id>";
        echo "<procedure_name><![CDATA[" . $row['procedure_name'] . "]]></procedure_name>";
        echo "<procedure_by><![CDATA[" . $row['procedure_by'] . "]]></procedure_by>";
        echo "<procedure_date><![CDATA[" . $row['procedure_date'] . "]]></procedure_date>";
        echo "<doc_id><![CDATA[" . $row['doc_id'] . "]]></doc_id>";
        echo "<chapter><![CDATA[" . $row['chapter'] . "]]></chapter>";
        echo "<observation><![CDATA[" . $row['observation'] . "]]></observation>";
        echo "<solution><![CDATA[" . $row['solution'] . "]]></solution>";
        echo "<problems><![CDATA[" . $row['problems'] . "]]></problems>";
        echo "<bom><![CDATA[" . $row['bom'] . "]]></bom>";
        echo "<bought><![CDATA[" . $row['bought'] . "]]></bought>";
        echo "<delivered><![CDATA[" . $row['delivered'] . "]]></delivered>";
        echo "<completed><![CDATA[" . $row['completed'] . "]]></completed>";
        echo "<verified><![CDATA[" . $row['verified'] . "]]></verified>";
        echo "<duration><![CDATA[" . $row['duration'] . "]]></duration>";
        echo '</data>';
        break;

    case 8:

        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $location = filter_input(INPUT_POST, 'location');
        $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
        $toc_id = filter_input(INPUT_POST, 'toc_id');
        $procedure_id = filter_input(INPUT_POST, 'procedure_id', FILTER_SANITIZE_NUMBER_INT);
        $procedure_date = filter_input(INPUT_POST, 'procedure_date');
        $doc_id = filter_input(INPUT_POST, 'doc_id', FILTER_SANITIZE_NUMBER_INT);
        $chapter = filter_input(INPUT_POST, 'chapter');
        $observation = filter_input(INPUT_POST, 'observation');
        $solution = filter_input(INPUT_POST, 'solution');
        $problems = filter_input(INPUT_POST, 'problems');
        $bom = filter_input(INPUT_POST, 'bom');
        $bought = filter_input(INPUT_POST, 'bought');
        $delivered = filter_input(INPUT_POST, 'delivered');
        $completed = filter_input(INPUT_POST, 'completed');
        $verified = filter_input(INPUT_POST, 'verified');
        $duration = filter_input(INPUT_POST, 'duration');


        $update = "UPDATE projects_monitoring SET `location` = '" . $location . "',`chapter`='" . $chapter . "',`observation`='" . $observation . "',`solution`='" . $solution . "',`problems`='" . $problems . "',`verified`='" . $verified . "',`duration`='" . $duration . "'";

        if (empty($doc_id)) {
            $update .= ",`doc_id`=NULL";
        } else {
            $update .= ",`doc_id`=" . $doc_id . "";
        }
        if (empty($procedure_id)) {
            $update .= ",`procedure_id`=NULL";
        } else {
            $update .= ",`procedure_id`=" . $procedure_id . "";
        }
        if (empty($toc_id)) {
            $update .= ",`toc_id`=NULL";
        } else {
            $update .= ",`toc_id`=" . $toc_id . "";
        }
        if (empty($employee_id)) {
            $update .= ",`employee_id`=NULL";
        } else {
            $update .= ",`employee_id`=" . $employee_id . "";
        }
        if (empty($procedure_date)) {
            $update .= ",`procedure_date`=NULL";
        } else {
            $update .= ",`procedure_date`='" . date('Y-m-d', strtotime($procedure_date)) . "'";
        }
        if (empty($bom)) {
            $update .= ",`bom`=NULL";
        } else {
            $update .= ",`bom`='" . date('Y-m-d', strtotime($bom)) . "'";
        }
        if (empty($bought)) {
            $update .= ",`bought`=NULL";
        } else {
            $update .= ",`bought`='" . date('Y-m-d', strtotime($bought)) . "'";
        }
        if (empty($delivered)) {
            $update .= ",`delivered`=NULL";
        } else {
            $update .= ",`delivered`='" . date('Y-m-d', strtotime($delivered)) . "'";
        }
        if (empty($completed)) {
            $update .= ",`completed`=NULL";
        } else {
            $update .= ",`completed`='" . date('Y-m-d', strtotime($completed)) . "'";
        }

        $update .= " WHERE id = " . $id;

        $updateResult = mysqli_query($dbc,$update) ;

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);

        break;

    case 9:
        $docId = filter_input(INPUT_POST, 'search_doc_input', FILTER_SANITIZE_NUMBER_INT);

        $result = mysqli_query($dbc,"SELECT PrId from tradestar_reports WHERE Report_ID =" . $docId);
        $row = mysqli_fetch_array($result);
        $projectId = $row[0];

        if ($projectId) {
            $data['data'] = array('response' => TRUE, 'text' => 'Successfully Added', 'item_id' => $projectId);
        } else {
            $data['data'] = array('response' => FALSE, 'text' => 'Document does not exist');
        }
        echo json_encode($data);
        break;

    case 10:
        $docId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);


        $result = mysqli_query($dbc,"SELECT PrId from tradestar_reports WHERE Report_ID =" . $docId);
        $row = mysqli_fetch_array($result);
        $projectId = $row[0];

        $start_date = (isset($_GET['start_date'])) ? $_GET['start_date'] : date("Y-m-d");
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $qry = "SELECT
                        e.event_id,
                        e.details,
                        e.`event_name`,
                        e.`employee_id`,
                        e.`start_date`,
                        e.`end_date`,
                        e.`entered_by`,
                        e.`cat_id`,
                        e.`event_pid`,
                        e.`visible`,
                        e.`assigned_eid`,
                        e.`completed`,
                        e.`main_task`,
                        e.`comment`
                FROM
                        projects_planning p
                JOIN `events` e ON e.event_id = p.event_id AND e.multiuser = 0 AND YEAR(start_date)= YEAR(CURDATE())
                WHERE
                        p.parent = " . $projectId ." ORDER BY start_date DESC";

        $res = mysqli_query($dbc,$qry) or die(mysqli_error($dbc) . $qry);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        while ($row = mysqli_fetch_array($res)) {
            echo "<row id = '" . $row["event_id"] . "'>";
            echo "<cell>" . $row["event_id"] . "</cell>";
            echo "<cell><![CDATA[" . $row["details"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["employee_id"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["start_date"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["end_date"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["event_name"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["visible"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["main_task"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["completed"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["comment"] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;
}

