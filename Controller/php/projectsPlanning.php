<?php

require 'config_mysqli.php';
include("GeneralFunctions.php");

$action = $_GET['action'];
switch ($action) {

    default:
        break;

    case 1:

        $start_date = (isset($_GET['start_date'])) ? $_GET['start_date'] : date("Y-m-d");
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $qry = "
            SELECT DISTINCT
                e.event_id,
                e.details,
                e.`event_name`,
                e.`start_date`,
                e.`end_date`,
                e.`entered_by`,
                e.`cat_id`,
                e.`event_pid`,
                e.`visible`,
                e.`is_procedure`,
                CONCAT(
                        COALESCE(trainees.FirstName, ''),
                        ' ',
                        COALESCE(trainees.SecondName, ''),
                        ' ',
                        COALESCE(trainees.LastName, '')
                )assigned_eid,
                e.`completed`,
                e.`main_task`,
                e.employee_id
            FROM
                projects_planning p
            JOIN `events` e ON(
                e.event_id = p.event_id
                AND YEAR(e.start_date) >= YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR))
            )
--            OR(
--                e.event_pid = p.event_id
--                AND DATE(e.start_date) >= CURDATE()
--            )
            LEFT JOIN trainees ON trainees.ID = e.employee_id 
            WHERE
                p.parent = " . $id . "
            AND e.is_active = 1";

//        echo $qry;exit;

        $res = mysqli_query($dbc, $qry) or die(mysqli_error($dbc) . $qry);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        while ($row = mysqli_fetch_array($res)) {
            echo "<row id = '" . $row["event_id"] . "'>";
            echo "<cell>" . $row["event_id"] . "</cell>";
            echo "<cell><![CDATA[" . $row["details"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["assigned_eid"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["start_date"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["end_date"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["event_name"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["visible"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["main_task"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["completed"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["is_procedure"] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";

        break;

    case 2:

        $query = "
            SELECT 
              ID,
              CONCAT(
                COALESCE(FirstName, ''),
                ' ',
                COALESCE(SecondName, ''),
                ' ',
                COALESCE(LastName, '')
              ) employee 
            FROM
              nts_site.trainees 
            WHERE status_id = 1 
              AND (
                IntranetId <> 0 || IntranetId IS NOT NULL
              ) 
              AND ID NOT IN (33, 501399, 501420, 501417, 501427) 
              AND branch_id IS NOT NULL 
            ORDER BY ID";

        $result = mysqli_query($dbc, $query);
        header("Content-type:text/xml");
        print('<?xml version="1.0" encoding="utf-8"?>');
        echo "<complete>";
        echo "<option value='0'></option>";
        while ($row = mysqli_fetch_array($result)) {
            echo "<option value='" . $row['ID'] . "'><![CDATA[" . $row['employee'] . "]]></option>";
        }
        echo "</complete>";
        break;

    case 3:

        $docId = filter_input(INPUT_GET, 'doc_id', FILTER_SANITIZE_NUMBER_INT);
        $projectId = filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT);
        $uID = filter_input(INPUT_GET, 'eid', FILTER_SANITIZE_NUMBER_INT);

        $query = "SELECT Report_Subject,Report_Employee_ID FROM tradestar_reports WHERE Report_ID =" . $docId;
        $result = mysqli_query($dbc, $query);
        $row = mysqli_fetch_assoc($result);
        $rptSubject = $row['Report_Subject'];
        $Report_Employee_ID = $row['Report_Employee_ID'];

        if ($Report_Employee_ID > 0) {

            $result = mysqli_query($dbc, "SELECT CONCAT(COALESCE(FirstName,''),' ',COALESCE(SecondName,''),' ',COALESCE(LastName,'')) employee FROM nts_site.trainees WHERE ID = " . $Report_Employee_ID);
            $row = mysqli_fetch_array($result);
            $assigned_eid = $row[0];
        }

        $startDate = date("Y-m-d H:i:s", mktime(9, 00, 0, date('n'), date('j') + 1, date('Y')));
        $endDate = date("Y-m-d H:i:s", mktime(17, 30, 0, date('n'), date('j') + 1, date('Y')));

        $no = generateProjectId($projectId);
        $prct_details = '[' . $no . '/' . $docId . ']' . ' ' . $rptSubject;
        $insert = "INSERT INTO events(`event_name`,`details`,`start_date`,`end_date`,`entered_by`,`cat_id`,`event_pid`,`visible`,`completed`,`masterrecord`,`employee_id`,`assigned_eid`)
                            VALUES
                            ('$prct_details','" . $no . "| " . $rptSubject . "','" . $startDate . "','" . $endDate . "',$uID,1,null,1,0,'" . $projectId . "','" . (($Report_Employee_ID > 0) ? $Report_Employee_ID : "NULL") . "','" . (($Report_Employee_ID > 0) ? mysqli_real_escape_string($dbc, $assigned_eid) : "NULL") . "')";

        $insertTOEvents = mysqli_query($dbc, $insert) or die(mysqli_error($dbc) . $insert);
        if ($insertTOEvents) {
            $eventId = mysqli_insert_id($dbc);
            mysqli_query($dbc, "INSERT INTO projects_planning(`parent`,`event_id`) VALUES('" . $projectId . "','" . $eventId . "')");
            $data['data'] = array('response' => $insertTOEvents, 'newId' => $eventId, 'text' => 'Successfully Added');
        } else {
            $data['data'] = array('response' => $insertTOEvents, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);

        break;

    case 4:

        $eventId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $projectId = filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT);
        $deleteFromEvents = mysqli_query($dbc, "DELETE FROM nts_site.`events` WHERE `event_id`=" . $eventId);
        if ($deleteFromEvents) {
            mysqli_query($dbc, "DELETE FROM projects_planning WHERE event_id = " . $eventId . " AND parent=" . $projectId);
            $data['data'] = array('response' => $deleteFromEvents, 'text' => 'Deleted');
        } else {
            $data['data'] = array('response' => $deleteFromEvents, 'text' => 'An Error Occured While Deleting');
        }
        echo json_encode($data);
        break;

    case 5:
        $id = $_GET['id'];
        $days = $_GET['days'];

        $SQL = mysqli_query($dbc, "select * from events where event_id ='$id'");
        $ROW = mysqli_fetch_array($SQL);
        $s1 = $ROW['start_date'];
        $s2 = $ROW['end_date'];
        $start_date = date('Y-m-d', strtotime($s1));
        $start_time = date('H:i:s', strtotime($s1));
        $end_time = date('H:i:s', strtotime($s2));
        $date = date('Y-m-d', strtotime($s1));

        if ($days > 1) {

            $start_date = add_date($start_date, $day = 1, $mth = 0, $yr = 0);

            for ($i = 0; $i < $days; $i++) {

                if (isWeekend($start_date) == 1) {
                    $start_date = add_date($start_date, $day = 1, $mth = 0, $yr = 0);

                    if (isWeekend($start_date) == 1) {
                        $start_date = add_date($start_date, $day = 1, $mth = 0, $yr = 0);
                    }
                }
                $start_date = add_date($start_date, $day = 1, $mth = 0, $yr = 0);
            }

            $new_date = date('Y-m-d', strtotime($start_date));
            $new_start_date = $new_date . " " . $start_time;
            $new_end_date = $new_date . " " . $end_time;
            $update = "UPDATE events SET start_date='$new_start_date', end_date='$new_end_date' WHERE event_id='$id'";
            mysqli_query($dbc, $update);
        } else {

            $date = add_date($start_date, $day = 1, $mth = 0, $yr = 0);

            if (isWeekend($date) == 1) {

                $date = add_date($date, $day = 1, $mth = 0, $yr = 0);

                if (isWeekend($date) == 1) {

                    $date = add_date($date, $day = 1, $mth = 0, $yr = 0);
                    $date = $date . " " . $end_time;
                    $end_date = date('Y-m-d', strtotime($date));
                    $end_date = $end_date . " " . $end_time;
                    $update = "UPDATE events SET start_date='$date',end_date='$end_date' WHERE event_id='$id'";
                    mysqli_query($dbc, $update);
                    // $date = $date;  
                } else {

                    $date = $date . " " . $end_time;
                    $end_date = date('Y-m-d', strtotime($date));
                    $end_date = $end_date . " " . $end_time;
                    $update = "UPDATE events SET start_date='$date',end_date='$end_date' WHERE event_id='$id'";
                    mysqli_query($dbc, $update);
                    //$date = $date;  
                }
            } else {

                $date = $date . " " . $end_time;
                $end_date = date('Y-m-d', strtotime($date));
                $end_date = $end_date . " " . $end_time;
                $update = "UPDATE events SET start_date='$date',end_date='$end_date' WHERE event_id='$id'";
                mysqli_query($dbc, $update);
                //$date=$date;
            }
            //echo $date; 
        }
        echo json_encode(array("message" => "update successfull"));
        break;

    case 6:

        $eventId = filter_input(INPUT_POST, 'event_id', FILTER_SANITIZE_NUMBER_INT);
        $projectId = filter_input(INPUT_POST, 'project_id', FILTER_SANITIZE_NUMBER_INT);
        $start_date = filter_input(INPUT_POST, 'start_date');
        $end_date = filter_input(INPUT_POST, 'end_date');
        $begin_time = filter_input(INPUT_POST, 'begn');
        $end_time = filter_input(INPUT_POST, 'end');

        $start_date = new DateTime($start_date);
        $start_date = $start_date->format('Y-m-d');
        $actual_start_date = $start_date . " " . $begin_time;

        $end_date = new DateTime($end_date);
        $end_date = $end_date->format('Y-m-d');
        $actual_end_date = $end_date . " " . $end_time;

        $insert = "INSERT INTO events(`event_name`,`details`,`start_date`,`end_date`,`entered_by`,`cat_id`,`event_pid`,`visible`,`completed`,`masterrecord`,`employee_id`,`assigned_eid`) SELECT `event_name`,`details`,'" . $actual_start_date . "','" . $actual_end_date . "',`entered_by`,`cat_id`,`event_pid`,`visible`,`completed`,`masterrecord`,`employee_id`,`assigned_eid` FROM events WHERE event_id =" . $eventId;

        $insertResult = mysqli_query($dbc, $insert);

        if ($insertResult) {
            $newId = mysqli_insert_id($dbc);
            $linkWithProject = "INSERT INTO projects_planning(`parent`,`event_id`) VALUES('" . $projectId . "','" . $newId . "')";
            mysqli_query($dbc, $linkWithProject);

            $data['data'] = array('response' => $insertResult, 'newId' => $newId, 'text' => 'Successfully Copied');
        } else {
            $data['data'] = array('response' => $insertResult, 'text' => 'An Error Occured While Copying');
        }

        echo json_encode($data);

        break;

    case 7:
        //new blank recur
        $eventId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $uID = filter_input(INPUT_GET, 'eid', FILTER_SANITIZE_NUMBER_INT);

        $insert = "INSERT INTO events(`event_name`,`details`,`employee_id`,`start_date`,`end_date`,`entered_by`,`cat_id`,`event_pid`,`assigned_eid`,`visible`) SELECT `event_name`,`details`,`employee_id`,now(),now()," . $uID . ",`cat_id`," . $eventId . ",`assigned_eid`,1 FROM events WHERE event_id =" . $eventId;

        $insertTOEvents = mysqli_query($dbc, $insert) or die(mysqli_error($dbc) . $insert);
        if ($insertTOEvents) {
            $eventId = mysqli_insert_id($dbc);
            $data['data'] = array('response' => $insertTOEvents, 'newId' => $eventId, 'text' => 'Successfully Added');
        } else {
            $data['data'] = array('response' => $insertTOEvents, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);
        break;

    case 8:
        $eventId = $_POST['id'];

        $deleteEvents = "DELETE FROM `events` WHERE event_id IN ( " . $eventId . ")";
        $deleteEventsResult = mysqli_query($dbc, $deleteEvents);

        if ($deleteEventsResult) {
            $data['data'] = array('response' => $deleteEventsResult, 'text' => 'Successfully Deleted');
        } else {
            $data['data'] = array('response' => $deleteEventsResult, 'text' => 'An Error Occured While Deleting');
        }
        echo json_encode($data);
        break;

    case 9:

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
                        e.`main_task`
                FROM
                        projects_planning p
                JOIN `events` e ON e.event_id = p.event_id
                AND e.visible = 1
                AND YEAR(start_date)= YEAR(CURDATE())
                WHERE
                        p.parent = " . $id . " 
                ORDER BY
                        e.start_date";

        $res = mysqli_query($dbc, $qry) or die(mysqli_error($dbc) . $qry);
        $num_rows = mysqli_num_rows($res);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0" encoding="UTF-8"?>' . PHP_EOL;
        echo '<data>';
        while ($row = mysqli_fetch_array($res)) {
            echo "<task id = '" . $row["event_id"] . "'>";
            echo "<start_date><![CDATA[" . $row["start_date"] . "]]></start_date>";
            echo "<end_date><![CDATA[" . $row["end_date"] . "]]></end_date>";
//            echo "<duration><![CDATA[5]]></duration>";
            echo "<text><![CDATA[" . $row["details"] . "]]></text>";
            echo "<details><![CDATA[" . $row["event_name"] . "]]></details>";
            echo "<progress><![CDATA[0.8]]></progress>";
            echo "<employee><![CDATA[" . $row["assigned_eid"] . "]]></employee>";
            echo "<parent><![CDATA[0]]></parent>";
            echo "<owner><![CDATA[" . $row["employee_id"] . "]]></owner>";
            echo "<done>" . $row["completed"] . "</done>";
            echo "</task>";
        }

        $query = "SELECT ID,CONCAT(COALESCE(FirstName,''),' ',COALESCE(SecondName,''),' ',COALESCE(LastName,'')) employee FROM nts_site.trainees WHERE status_id = 1
                    AND(
                            IntranetId <> 0 || IntranetId IS NOT NULL
                    )
                    AND ID <> 33 AND branch_id IS NOT NULL ORDER BY ID";
        $result = mysqli_query($dbc, $query);
        echo '<coll_options  for="nameList">';
        while ($row = mysqli_fetch_array($result)) {
            echo '<item value="' . $row['ID'] . '" label="' . xml_entities($row['employee']) . '"/>';
        }
        echo '</coll_options>';
        echo "</data>";

        break;

    case 10:

        $fieldvalue = $_POST["nValue"];
        $id = $_POST["id"];
        $field = $_POST["colId"];

        $updateResult = updateSQL("nts_site.`events`", $field, $fieldvalue, $id, "event_id", $colType);

        if ($updateResult) {
            if ($field === 'is_procedure') {
                updateSQL("nts_site.`events`", $field, $fieldvalue, $id, "event_pid", $colType);
            }
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);

        break;

    case 11:
        $index = $_GET["index"];
        $fieldvalue = $_GET["fieldvalue"];
        $id = $_GET["id"];
        $field = $_GET["colId"];
        $colType = $_GET["colType"];
        $fieldvalue = mysqli_real_escape_string($dbc, $fieldvalue);

        $updateResult = updateSQL("nts_site.`events`", $field, $fieldvalue, $id, "event_id", $colType);
        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);

        break;

    case 12:
        $eventId = filter_input(INPUT_POST, 'event_id', FILTER_SANITIZE_NUMBER_INT);
        $docId = filter_input(INPUT_POST, 'doc_id', FILTER_SANITIZE_NUMBER_INT);
        $projectId = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $is_new = $_POST['is_new'];
        $details = $_POST['details'];
        $employee = $_POST['employee'];
        $end_date = $_POST['end_date'];
        $start_date = $_POST['start_date'];
        $title = $_POST['title'];
        $uID = filter_input(INPUT_POST, 'eid', FILTER_SANITIZE_NUMBER_INT);

        $result = mysqli_query($dbc, "SELECT Report_Subject FROM nts_site.tradestar_reports WHERE Report_ID =" . $docId);
        $row = mysqli_fetch_array($result);
        $rptSubject = $row[0];

        $no = generateProjectId($projectId);

        $prct_details = '[' . $no . '/' . $docId . ']' . ' ' . $details;
        $prct_name = '[' . $no . '/' . $docId . ']' . ' ' . $title;

        if ($is_new) {
            $insert = "INSERT INTO events(`event_name`,`details`,`start_date`,`end_date`,`entered_by`,`cat_id`,`event_pid`,`visible`,`completed`,`masterrecord`,employee_id,assigned_eid) SELECT '$prct_details','" . $prct_name . "','" . $start_date . "','" . $end_date . "',$uID,1,null,1,0,'" . $projectId . "'," . $employee . ",CONCAT(COALESCE(FirstName,''),' ',COALESCE(SecondName,''),' ',COALESCE(LastName,'')) employee FROM nts_site.trainees WHERE ID = " . $employee; //echo $insert; exit;

            $insertTOEvents = mysqli_query($dbc, $insert) or die(mysqli_error($dbc) . $insert);
            if ($insertTOEvents) {
                $eventId = mysqli_insert_id($dbc);
                mysqli_query($dbc, "INSERT INTO projects_planning(`parent`,`event_id`) VALUES('" . $projectId . "','" . $eventId . "')");
                $data['data'] = array('response' => $insertTOEvents, 'newId' => $eventId, 'text' => 'Successfully Added');
            } else {
                $data['data'] = array('response' => $insertTOEvents, 'text' => 'An Error Occured While Saving');
            }
        } else {

            $update = "UPDATE events SET `event_name` = '$prct_details',`details`= '" . $prct_name . "',`start_date` = '" . $start_date . "',`end_date` = '" . $end_date . "',`entered_by` = $uID,`visible` = 1,`completed` = 0,employee_id = " . $employee . ",assigned_eid = (SELECT CONCAT(COALESCE(FirstName,''),' ',COALESCE(SecondName,''),' ',COALESCE(LastName,'')) employee FROM nts_site.trainees WHERE ID = " . $employee . ") WHERE event_id =" . $eventId;

            $updateResult = mysqli_query($dbc, $update) or die(mysqli_error($dbc) . $update);
            if ($updateResult) {
                $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
            } else {
                $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
            }
        }

        echo json_encode($data);

        break;

    case 13:
        error_reporting(E_ALL ^ E_DEPRECATED);
        ini_set('display_errors', TRUE); //set to false in release mode
        ini_set('display_startup_errors', TRUE); // set to false in release mode
        $temploc = $_FILES["file"]["tmp_name"];
        $filename = $_FILES["file"]["name"];
        $target_path = "../excel/";
        $file_type = $_FILES["file"]['type'];
        $file_size = $_FILES["file"]["size"];
        $filedesc = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if (!file_exists($target_path)) {
            mkdir($target_path);
        }
        $target_path .= $filename;


        if (move_uploaded_file($temploc, $target_path)) {

            $docId = filter_input(INPUT_GET, 'doc_id', FILTER_SANITIZE_NUMBER_INT);
            $projectId = filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT);
            $uID = filter_input(INPUT_GET, 'eid', FILTER_SANITIZE_NUMBER_INT);

            $no = generateProjectId($projectId);

            require_once('../../Model/PHPExcel/Classes/PHPExcel/IOFactory.php');
            $html = "<table border='1'>";
            $objPHPExcel = PHPExcel_IOFactory::load($target_path);
            foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                $highestRow = $worksheet->getHighestRow();
                for ($row = 2; $row <= $highestRow; $row++) {


                    $task_name = mysqli_real_escape_string($dbc, $worksheet->getCellByColumnAndRow(0, $row)->getValue());
                    $details = mysqli_real_escape_string($dbc, $worksheet->getCellByColumnAndRow(1, $row)->getValue());
                    $begin_date = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                    $PHPTimeStamp = PHPExcel_Shared_Date::ExcelToPHP($begin_date);
                    $begin_date = date('Y-m-d H:i:s', $PHPTimeStamp);
                    $end_date = mysqli_real_escape_string($dbc, $worksheet->getCellByColumnAndRow(3, $row)->getValue());
                    $PHPTimeStamp = PHPExcel_Shared_Date::ExcelToPHP($end_date);
                    $end_date = date('Y-m-d H:i:s', $PHPTimeStamp);
                    $duration = mysqli_real_escape_string($dbc, $worksheet->getCellByColumnAndRow(4, $row)->getValue());
                    $employee_id = mysqli_real_escape_string($dbc, $worksheet->getCellByColumnAndRow(5, $row)->getValue());
                    $employee_name = mysqli_real_escape_string($dbc, $worksheet->getCellByColumnAndRow(6, $row)->getValue());

                    $prct_details = '[' . $no . '/' . $docId . ']' . ' ' . $task_name;
                    $emp_list = explode(',', $employee_id);

                    foreach ($emp_list as $emp_id) {

                        $insert = "INSERT INTO events(`event_name`,`details`,`start_date`,`end_date`,`entered_by`,`cat_id`,`event_pid`,`visible`,`completed`,`masterrecord`,employee_id,assigned_eid) SELECT '" . $details . "','" . $prct_details . "','" . $begin_date . "','" . $end_date . "',$uID,1,null,1,0,'" . $projectId . "',ID,'" . $employee_name . "' FROM nts_site.`trainees` WHERE `IntranetID` =" . $emp_id;

                        $insertTOEvents = mysqli_query($dbc, $insert) or die(mysqli_error($dbc) . $insert);
                        if ($insertTOEvents) {
                            $eventId = mysqli_insert_id($dbc);
                            mysqli_query($dbc, "INSERT INTO projects_planning(`parent`,`event_id`) VALUES('" . $projectId . "','" . $eventId . "')");
                            $data['data'] = array('response' => $insertTOEvents, 'newId' => $eventId, 'text' => 'Successfully Added');
                        } else {
                            $data['data'] = array('response' => $insertTOEvents, 'text' => 'An Error Occured While Saving');
                        }
//                        echo json_encode($data);
                    }
                }
            }
            print_r("{state: true, name:'" . str_replace("'", "\\'", $eventId) . "', size:" . $file_size . "}");
        } else {
            print_r("{state:'cancelled'}");
        }

        break;


    case 14:

        $eventId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $qry = "SELECT
                        event_id,
                        cat_id,
                        event_name AS details,
                        event_name AS event_name_child,
                        details AS event_name,
                        info AS information,
                        employee_id,
                        event_pid,
                        DATE_FORMAT(start_date, '%Y-%m-%d')AS start_date,
                        DATE_FORMAT(end_date, '%Y-%m-%d')AS end_date,
                        DATE_FORMAT(start_date, '%H:%i')AS begn,
                        DATE_FORMAT(end_date, '%H:%i')AS `end`,
                        event_length AS freq,
                        rec_type,
                        is_variable AS variable,
                        map,
                        masterrecord,
                        duration,
                        reoccur_map
                FROM
                        events
                WHERE
                        event_id =" . $eventId;

        $res = mysqli_query($dbc, $qry) or die(mysqli_error($dbc) . $qry);
        $row = mysqli_fetch_array($res);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0" encoding="UTF-8"?>' . PHP_EOL;
        echo '<data>';
        echo '<event_id><![CDATA[' . $row["event_id"] . ']]></event_id>';
        echo '<cat_id><![CDATA[' . $row["cat_id"] . ']]></cat_id>';
        echo '<details><![CDATA[' . $row["details"] . ']]></details>';
        echo '<event_name_child><![CDATA[' . $row["event_name_child"] . ']]></event_name_child>';
        echo '<event_name><![CDATA[' . $row["event_name"] . ']]></event_name>';
        echo '<information><![CDATA[' . $row["information"] . ']]></information>';
        echo '<employee_id><![CDATA[' . $row["employee_id"] . ']]></employee_id>';
        echo '<event_pid><![CDATA[' . $row["event_pid"] . ']]></event_pid>';
        echo '<start_date><![CDATA[' . $row["start_date"] . ']]></start_date>';
        echo '<end_date><![CDATA[' . $row["end_date"] . ']]></end_date>';
        echo '<begn><![CDATA[' . $row["begn"] . ']]></begn>';
        echo '<end><![CDATA[' . $row["end"] . ']]></end>';
        echo '<freq><![CDATA[' . $row["freq"] . ']]></freq>';
        echo '<rec_type><![CDATA[' . $row["rec_type"] . ']]></rec_type>';
        echo '<variable><![CDATA[' . $row["variable"] . ']]></variable>';
        echo '<map><![CDATA[' . $row["map"] . ']]></map>';
        echo '<masterrecord><![CDATA[' . $row["masterrecord"] . ']]></masterrecord>';
        echo '<duration><![CDATA[' . $row["duration"] . ']]></duration>';
        echo '<reoccur_map><![CDATA[' . $row["reoccur_map"] . ']]></reoccur_map>';
        echo '</data>';

        break;

    case 15:

        $eventId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $emplist = array();
        $query = "SELECT event_id,employee_id FROM `events` WHERE `event_pid` =" . $eventId;
        $result = mysqli_query($dbc, $query);
        $count = mysqli_num_rows($result);
        if ($count > 0) {
            while ($row = mysqli_fetch_array($result)) {
                $emplist[] = $row['employee_id'];
            }
        } else {

            $result = mysqli_query($dbc, "SELECT employee_id FROM `events` WHERE `event_id` =" . $eventId);
            $row = mysqli_fetch_array($result);
            $empId = $row[0];

        }

        $query = "SELECT ID,CONCAT(COALESCE(FirstName,''),' ',COALESCE(SecondName,''),' ',COALESCE(LastName,'')) employee FROM nts_site.trainees WHERE status_id = 1 || id = 51 ORDER BY branch_id,SortId";
        $result = mysqli_query($dbc, $query);

        //load all employees with default:checkbox case 1:select emp string saved in assigned value                        
        header("Content-type:text/xml");
        ini_set('max_execution_time', 600);
        print("<?xml version=\"1.0\"?>");
        echo "<complete>";
        while ($row = mysqli_fetch_array($result)) {
            if ($row['ID'] == $empId || in_array($row['ID'], $emplist)) {
                echo "<option value='" . $row['ID'] . "' checked='1' selected='1'>" . $row['employee'] . "</option>";
            } else {
                echo "<option value='" . $row['ID'] . "'>" . $row['employee'] . "</option>";
            }
        }
        echo "</complete>";
        break;


    case 16:

        $eventId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $qry = "SELECT
                        event_id,
                        details,
                        assigned_eid employee_id,
                        start_date,
                        end_date,
                        event_name,
                        protection,
                        personal,
                        visible,
                        completed
                FROM
                        `events`
                WHERE
                        event_pid = " . $eventId . "
                AND(tag_id IS NULL OR event_pid = 0)
                AND(
                        event_pjd IS NULL
                        OR event_pjd = 0
                )
                ORDER BY
                        start_date ASC";

        $res = mysqli_query($dbc, $qry) or die(mysqli_error($dbc) . $qry);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        while ($row = mysqli_fetch_array($res)) {
            echo "<row id = '" . $row["event_id"] . "'>";
            echo "<cell><![CDATA[" . $row["details"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["employee_id"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["start_date"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["end_date"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["event_name"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["protection"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["personal"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["visible"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["completed"] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";

        break;

    case 17:

        $eventId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $qry = "SELECT
                        event_id,
                        cat_id,
                        event_name details,
                        event_name event_name_child,
                        details event_name,
                        info,
                        employee_id emp,
                        event_pid,
                        DATE_FORMAT(start_date, '%Y-%m-%d') start_date,
                        DATE_FORMAT(end_date, '%Y-%m-%d') end_date,
                        DATE_FORMAT(start_date, '%H:%i') begn,
                        DATE_FORMAT(end_date, '%H:%i')`end`,
                        event_length freq,
                        rec_type,
                        is_variable variable,
                        map,
                        masterrecord,
                        duration period,
                        reoccur_map
                FROM
                        `events`
                WHERE
                        event_id = " . $eventId;

        $res = mysqli_query($dbc, $qry) or die(mysqli_error($dbc) . $qry);
        $row = mysqli_fetch_array($res);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0" encoding="UTF-8"?>' . PHP_EOL;
        echo '<data>';
        echo '<event_id><![CDATA[' . $row["event_id"] . ']]></event_id>';
        echo '<cat_id><![CDATA[' . $row["cat_id"] . ']]></cat_id>';
        echo '<details><![CDATA[' . $row["details"] . ']]></details>';
        echo '<event_name_child><![CDATA[' . $row["event_name_child"] . ']]></event_name_child>';
        echo '<event_name><![CDATA[' . $row["event_name"] . ']]></event_name>';
        echo '<information><![CDATA[' . $row["info"] . ']]></information>';
        echo '<employee_id><![CDATA[' . $row["emp"] . ']]></employee_id>';
        echo '<event_pid><![CDATA[' . $row["event_pid"] . ']]></event_pid>';
        echo '<start_date><![CDATA[' . $row["start_date"] . ']]></start_date>';
        echo '<end_date><![CDATA[' . $row["end_date"] . ']]></end_date>';
        echo '<begn><![CDATA[' . $row["begn"] . ']]></begn>';
        echo '<end><![CDATA[' . $row["end"] . ']]></end>';
        echo '<freq><![CDATA[' . $row["freq"] . ']]></freq>';
        echo '<rec_type><![CDATA[' . $row["rec_type"] . ']]></rec_type>';
        echo '<variable><![CDATA[' . $row["variable"] . ']]></variable>';
        echo '<map><![CDATA[' . $row["map"] . ']]></map>';
        echo '<masterrecord><![CDATA[' . $row["masterrecord"] . ']]></masterrecord>';
        echo '<period><![CDATA[' . $row["period"] . ']]></period>';
        echo '<reoccur_map><![CDATA[' . $row["reoccur_map"] . ']]></reoccur_map>';
        echo '</data>';
        break;

    case 18:
        $query = "SELECT
                            id AS a1,
                            FirstName AS a2,
                            LastName AS a3,
                            IntranetID AS a4
                    FROM
                            trainees
                    WHERE
                            status_id = 1 || id = 51
                    ORDER BY
                            id ASC";

        $result = mysqli_query($dbc, $query);

        header("Content-type:text/xml");
        ini_set('max_execution_time', 600);
        print("<?xml version=\"1.0\"?>");
        echo "<complete>";
        while ($rowEmp = mysqli_fetch_array($result)) {
            $checked = "";
            echo "<option value='{$rowEmp["a1"]}' {$checked}>" . $rowEmp["a2"] . " " . $rowEmp["a3"] . "</option>";
        }
        echo "</complete>";
        break;


    case 19:
        $date1 = new DateTime($_POST['start_date']);
        $s1 = $date1->format('Y-m-d H:i:s');
        $date2 = new DateTime($_POST['end_date']);
        $s2 = $date2->format('Y-m-d H:i:s');
        $cat = $_POST['cat_id'];
        if ($cat == null || $cat == '')
            $cat = 45;

        //user logged in enters tasks
        $uID = $_POST['eid'];
        if ($uID == "") {
            $uID = 0;
        }
        //if the time is set:overite the time set on calendar popup.
        $bgntime = $_POST['begn'];
        $endtime = $_POST['end'];
        if ($bgntime != '') {
            $cdtstart = new DateTime($s1);
            $s1 = $cdtstart->format('Y-m-d');
            $s1 = $s1 . " " . $bgntime;
            $cdtend = new DateTime($s2);
            $s2 = $cdtend->format('Y-m-d');
            $s2 = $s2 . " " . $endtime;
        }
        $chkvalues = $_POST['days_select'];
        $dbString = array();
        foreach ($chkvalues as $key => $value) {//check for optin and opt out
            if ($value == 1) {
                $dbString[] = $key;
            }
        }
        $dbString = implode(',', $dbString);

        $emp_str = $_GET['eid_assigned'];
        $apprv_str = $_GET['approved'];
        $event_id = $_POST['event_id'];
        //$emp = $_GET['emplist'];

        $emp = $_GET['eid_assigned'];

        $emplist = explode(",", $emp);
        if (count($emplist) > 1) {
            //get the employees

            $result = mysqli_query($dbc, "SELECT GROUP_CONCAT(COALESCE(FirstName, ''),' ',COALESCE(SecondName, ''),' ',COALESCE(LastName, ''))AS FirstName FROM trainees WHERE id IN($emp)");
            $row = mysqli_fetch_array($result);
            $emp_strdb = mysqli_real_escape_string($dbc, $row[0]);

            mysqli_query($dbc, "UPDATE nts_site.`events`
                            SET details = '{$_POST['event_name']}',
                             event_name = '{$_POST['details']}',
                             start_date = '{$s1}',
                             employee_id = NULL,
                             end_date = '{$s2}',
                             event_length = '{$_POST['freq']}',
                             rec_type = '{$dbString}',
                             is_variable = '{$_POST['variable']}',
                             assigned_eid = '{$emp_strdb}',
                             info = '{$_POST['info']}',
                             approved_by = '{$emp_apprv}',
                             map = '{$_POST['map']}',
                             duration = '{$_POST['period']}',
                             reoccur_map = '{$_POST['reoccur_map']}',
                             visible = 0
                         WHERE
                             event_id = '{$event_id}'");


            $sql = "DELETE FROM nts_site.`events` WHERE event_pid = '{$_POST['event_id']}'";
            $res = mysqli_query($dbc, $sql);

            $result = mysqli_query($dbc, "SELECT document_id FROM nts_site.`events` WHERE event_id = $event_id");
            $row = mysqli_fetch_array($result);
            $document_id = $row[0];

            foreach ($emplist as $key => $value) {
                $qry_insert = "INSERT INTO nts_site.`events`(
                                    `event_pid`,
                                    `event_name`,
                                    `details`,
                                    `start_date`,
                                    `end_date`,
                                    `employee_id`,
                                    `event_length`,
                                    `cat_id`,
                                    `entered_by`,
                                    `assigned_eid`,
                                    `visible`,
                                    `document_id`,
                                    `duration`,
                                    `masterrecord`
                            )SELECT
                                    '{$_POST['event_id']}',
                                    '{$_POST['details']}',
                                    '{$_POST['event_name']}',
                                    '{$s1}',
                                    '{$s2}',
                                    '$value',
                                    `event_length`,
                                    `cat_id`,
                                    `entered_by`,
                                    '{$emp_strdb}',
                                     1,
                                    `document_id`,
                                    `duration`,
                                    `masterrecord`
                             FROM
                                    nts_site.`events`
                             WHERE event_id = {$_POST['event_id']}";
                mysqli_query($dbc, $qry_insert);
            }
        } else {

            $result = mysqli_query($dbc, "SELECT GROUP_CONCAT(COALESCE(FirstName, ''),' ',COALESCE(SecondName, ''),' ',COALESCE(LastName, ''))AS FirstName FROM trainees WHERE id IN($emp)");
            $row = mysqli_fetch_array($result);
            $emp_strdb = mysqli_real_escape_string($dbc, $row[0]);
            //get the approved items
            if ($apprv_str == null) {
                $apprv_str = 0;
            }

            $result = mysqli_query($dbc, "Select CONCAT(COALESCE(FirstName, ''),' ',COALESCE(SecondName, ''),' ',COALESCE(LastName, ''))AS FirstName from trainees where id in($apprv_str)");
            $row = mysqli_fetch_array($result);
            $emp_apprv = mysqli_real_escape_string($dbc, $row[0]);


            $update = "UPDATE nts_site.`events`
                            SET details = '{$_POST['event_name']}',
                             event_name = '{$_POST['details']}',
                             start_date = '{$s1}',
                             end_date = '{$s2}',
                             event_length = '{$_POST['freq']}',
                             rec_type = '{$dbString}',
                             is_variable = '{$_POST['variable']}',
                             assigned_eid = '{$emp_strdb}',
                             info = '{$_POST['info']}',
                             approved_by = '{$emp_apprv}',
                             map = '{$_POST['map']}',
                             reoccur_map = '{$_POST['reoccur_map']}',
                             duration = '{$_POST['period']}',
                             employee_id = '{$emp}'
                            WHERE
                             event_id = '{$_POST['event_id']}'"; //echo $update;
            mysqli_query($dbc, $update);
        }
        $info = "saved";
        echo json_encode(array("info" => $info));
        break;

    case 20:
        //get start and end date
        $startDate = $_POST['start_date'];
        $dateSpan = new DateTime($_POST['start_date']);
        $sPan = $dateSpan->format('Y-m-d');
        $endDate = $_POST['end_date'];
        $cat = $_POST['cat_id'];
        if ($cat == null || $cat == '')
            $cat = 45;

        $result = mysqli_query($dbc, "Select document_id from events where event_id =" . $_POST['event_id']);
        $row = mysqli_fetch_array($result);
        $document_id = $row[0];

        //update for form 2                              
        $chkvalues = $_POST['days_select'];
        $dbString = array();
        foreach ($chkvalues as $key => $value) {//check for optin and opt out
            if ($value == 1) {
                $dbString[] = $key;
            }
        }
        $typ = "day";
        $date1 = new DateTime($_POST['start_date']);
        $s1 = $date1->format('Y-m-d H:i:s');
        $date2 = new DateTime($_POST['end_date']);
        $s2 = $date2->format('Y-m-d H:i:s');
        //if the time is set:overite the time set on calendar popup.
        $bgntime = $_POST['begn'];
        $endtime = $_POST['end'];
        if ($bgntime != '') {
            $cdtstart = new DateTime($s1);
            $s1 = $cdtstart->format('Y-m-d');
            $s1 = $s1 . " " . $bgntime;
            $cdtend = new DateTime($s2);
            $s2 = $cdtend->format('Y-m-d');
            $s2 = $s2 . " " . $endtime;
        }
        $empResBranch = mysqli_query($dbc, "Select branch_id from nts_site.trainees where ID in ($_GET[ass_emp]) order by ID asc");
        $branchOfEmp = array();
        while ($rowBranch = mysqli_fetch_array($empResBranch)) {
            $branchOfEmp[] = $rowBranch['branch_id'];
        }

        if (in_array('6', $branchOfEmp)) {
            $evtCountry = 1;
        } else {
            $evtCountry = 2;
        }
        $resHols = mysqli_query($dbc, "select * from hrm_events where country = '" . $evtCountry . "'");
        while ($rowsH = mysqli_fetch_array($resHols)) {
            $holdays[] = $rowsH['start_date'];
        }

        switch ($_POST['freq']) {
            //switch days days
            case 1:
                $interval = 7;
                break;
            case 2:
                $interval = 14;
                break;
            case 3:
                $interval = 1;
                $typ = "month";
                createInt($max_pl, $s1, $s2, $interval, $typ, $endtime, $dbString, $document_id, $holdays);
                exit();
                break;
            case 4:
                $interval = 84;
                break;
            case 5:
                $interval = 6;
                $typ = "month";
                createInt($max_pl, $s1, $s2, $interval, $typ, $endtime, $dbString, $document_id, $holdays);
                exit();
                break;
            case 6:
                $interval = 366;
                /* $interval = 1;
                  $typ = "year";
                  createInt($max_pl, $s1, $s2, $interval, $typ, $endtime, $dbString, $document_id, $holdays);
                  exit(); */
                break;
            case 7:
                $interval = 28;
                break;
            case 8:
                $interval = 2;
                $typ = "month";
                createInt($max_pl, $s1, $s2, $interval, $typ, $endtime, $dbString, $document_id, $holdays);
                exit();
                break;
            case 9:
                $interval = 56;
                break;
            case 10:
                $interval = 21;
                break;
            default :
                $interval = 365;
                break; //handle date infinite 							 
        }
        //clears and work with the variable day set 
        if ($variable == 1) {
            unset($dbString);
            $dbString[] = date('N', strtotime($startDate));
        }
        $seldays = $dbString;
        $s = $s1;
        //get the day number                             
        $day_number = date('N', strtotime($s));
        foreach ($seldays as $key => $value) {
            $emp = explode(",", $_GET['ass_emp']);
            $new_string = "" . implode("','", $emp) . "";
            $endDate = $_POST['end_date'];
            $startDate = $s;
            if ($day_number != $value) {
                $z = $day_number - $value;
                $w = 7;
                $y = $w - $z;
                $y = str_replace("-", "", $y);

                if ($z < 0) {
                    $z = str_replace("-", "", $z);
                    $startDate = date('Y-m-d H:i:s', strtotime($startDate . $z . " day"));
                } else {
                    $startDate = date('Y-m-d H:i:s', strtotime($startDate . $y . " day"));
                }
            }


            while (strtotime($startDate) < strtotime($endDate)) {
                //select employee_id sent by category table 
                $empQuery = "Select ID as a1,FirstName as a2 from nts_site.trainees where ID in ($_GET[ass_emp]) order by ID asc";
                $empRes = mysqli_query($dbc, $empQuery);
                while ($row_pl = mysqli_fetch_array($empRes)) {
                    $max_pl++;
                    $empID = $row_pl['a1'];
                    //task period withing time set
                    $tskend = new DateTime($startDate);
                    $tskend = $tskend->format('Y-m-d');
                    $tskend = $tskend . " " . $endtime;
                    //user logged in enters tasks
                    $uID = $_POST['eid'];
                    if ($uID == "") {
                        $uID = 0;
                    }
                    //filter for saturday and sunday events
                    if ($day_weeknd == 6 && $_POST['variable'] == 1) {
                        $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -1 day"));
                    }
                    if ($day_weeknd == 7 && $_POST['variable'] == 1) {
                        $startDate = date('Y-m-d H:i:s', strtotime($startDate . " +1 day"));
                    }
                    $emp_strdb = $row_pl['FirstName'];

                    $checkHolidayDate = new DateTime($startDate);
                    $checkHolidayDate = $checkHolidayDate->format('Y-m-d');
                    if (in_array($checkHolidayDate, $holdays)) {
                        $evtDateH = date('Y-m-d H:i:s', strtotime($startDate . " -1 week"));
                    } else {
                        $evtDateH = $startDate;
                    }

                    $qry_insert = "Insert into events(`event_pid`,`event_name`,`details`,`start_date`,`end_date`,`employee_id`,`event_length`,
												 `cat_id`,`entered_by`,`assigned_eid`,`visible`,`document_id`)values('$_POST[event_id]','$_POST[details]','$_POST[event_name]','$evtDateH','$tskend','$empID',null,'$cat','$uID','$row_pl[a1]',0,'$document_id')";
                    mysqli_query($dbc, $qry_insert);
                }
                $startDate = date('Y-m-d H:i:s', strtotime($startDate . " + " . $interval . $typ));
            }
            $sql_event_update = "update events set target = 1 where event_id = '$_POST[event_id]'";
            mysqli_query($dbc, $sql_event_update);
        }

        break;

    case 21:
        //do visible/invisible selection
        $parentRecord = $_GET['grdRow'];
        //get total children/checked records
        $res_child = mysqli_query($dbc, "Select * from events where event_pid = '$parentRecord' and tag_id is null");
        $res_child_checked = mysqli_query($dbc, "Select * from events where event_pid = '$parentRecord' and visible = 1 and tag_id is null");
        $bool = true;
        if (mysqli_num_rows($res_child) == mysqli_num_rows($res_child_checked)) {
            $info = "Task have been made invisible to the schedule!";
            $qry = "Update events set visible = 0 where event_pid = '$parentRecord' and tag_id is null";
            $bool = false;
        } else {
            $info = "Tasks are now visible in the schedule.";
            $qry = "Update events set visible = 1 where event_pid = '$parentRecord' and tag_id is null";
        }
        mysqli_query($dbc, $qry);
        echo json_encode(array("info" => $info, "bool" => $bool));
        break;

    case 22:

        $date1 = new DateTime($_POST['start_date']);
        $s1 = $date1->format('Y-m-d H:i:s');
        $date2 = new DateTime($_POST['end_date']);
        $s2 = $date2->format('Y-m-d H:i:s');
        //if the time is not set:overite the time set on calendar popup.
        $bgntime = $_POST['begn'];
        $endtime = $_POST['end'];
        if ($bgntime != '') {
            $cdtstart = new DateTime($s1);
            $s1 = $cdtstart->format('Y-m-d');
            $s1 = $s1 . " " . $bgntime;
            $cdtend = new DateTime($s2);
            $s2 = $cdtend->format('Y-m-d');
            $s2 = $s2 . " " . $endtime;
        }
        //user logged in enters tasks
        $uID = $_POST['eid'];
        if ($uID == "") {
            $uID = 0;
        }
        $str = "Update events set event_name = '" . $_POST['event_name_child'] . "',info = '" . $_POST['information'] . "',employee_id = " . $_POST['employee_id'] . ",start_date = '$s1',end_date = '$s2',entered_by = " . $uID . " where event_id = " . $_POST['event_id'];
        mysqli_query($dbc, $str);
        $info = "saved" . $str;
        echo json_encode(array("info" => $info, "event_id" => $_POST['event_id']));
        break;

    case 23:

        $employeeId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $start_date = (isset($_GET['start_date'])) ? date('Y-m-d', strtotime(filter_input(INPUT_GET, 'start_date'))) : date('Y-m-d');

        $query = "SELECT
                    e.event_id,
                    event_name AS details,
                    (SELECT parent FROM projects_planning WHERE event_id = e.event_pid)project_id,
                    e.document_id doc_id,
                    (
                            CASE
                            WHEN e.event_length = '1' THEN
                                    'Every Week'
                            WHEN e.event_length = '2' THEN
                                    'Every (2) Weeks'
                            WHEN e.event_length = '10' THEN
                                    'Every (3) Weeks'
                            WHEN e.event_length = '7' THEN
                                    'Every (4) Weeks'
                            WHEN e.event_length = '9' THEN
                                    'Every (8) Weeks'
                            WHEN e.event_length = '3' THEN
                                    'Every Month'
                            WHEN e.event_length = '8' THEN
                                    'Every (2) Month'
                            WHEN e.event_length = '4' THEN
                                    'Every (12) Weeks'
                            WHEN e.event_length = '5' THEN
                                    'Every half year'
                            WHEN e.event_length = '6' THEN
                                    'Every year'
                            ELSE
                                    e.event_length
                            END
                    )frequency,
                    DATE_FORMAT(start_date, '%H:%i')AS begin_time,
                    DATE_FORMAT(end_date, '%H:%i')AS `end_time`,
                    (SELECT rec_type FROM `events` WHERE event_id = e.event_pid) days,
                    duration,
                    e.event_pid
                FROM
                        `events` e
                JOIN trainees t ON t.ID = e.employee_id 
                WHERE
                t.IntranetID = " . $employeeId . "
                -- AND e.main_task = 1
                AND e.event_pid > 0
                AND DATE(e.start_date) = '" . $start_date . "'";
        $result = mysqli_query($dbc, $query);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        $counter = 0;
        while ($row = mysqli_fetch_assoc($result)) {

            $projectId = $row['project_id'];
            $no = generateProjectId($projectId);

            if ($row['doc_id']) {
                $doc_id = $row['doc_id'];
            } else {
                $doc_id = get_string_between($row['details'], '/', ']');
            }

            $days = explode(',', $row['days']);
            echo '<row id="' . $row['event_id'] . '">';
            echo "<cell><![CDATA[" . $row['details'] . "]]></cell>";
            echo "<cell><![CDATA[" . $no . "]]></cell>";
            echo "<cell><![CDATA[" . $row['event_pid'] . "]]></cell>";
            echo "<cell><![CDATA[" . $doc_id . "]]></cell>";
            echo "<cell><![CDATA[" . $row['frequency'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['begin_time'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['end_time'] . "]]></cell>";
            echo "<cell><![CDATA[" . round($row['duration'] / 60, 1) . 'hr(s)' . "]]></cell>";
            echo "<cell><![CDATA[" . (in_array('1', $days) ? 1 : 0) . "]]></cell>";
            echo "<cell><![CDATA[" . (in_array('2', $days) ? 1 : 0) . "]]></cell>";
            echo "<cell><![CDATA[" . (in_array('3', $days) ? 1 : 0) . "]]></cell>";
            echo "<cell><![CDATA[" . (in_array('4', $days) ? 1 : 0) . "]]></cell>";
            echo "<cell><![CDATA[" . (in_array('5', $days) ? 1 : 0) . "]]></cell>";
            echo "<cell><![CDATA[" . (in_array('6', $days) ? 1 : 0) . "]]></cell>";
            echo '</row>';
            unset($days);
        }
        echo '</rows>';
        break;

    case 24:

        $query = "SELECT
                    trainees.ID,
                    trainees.IntranetID,
                    CONCAT(
                        COALESCE(trainees.FirstName, ''),
                        ' ',
                        COALESCE(trainees.SecondName, ''),
                        ' ',
                        COALESCE(trainees.LastName, '')
                    )employee,
                    branch.Branch_Name,
                    branch.Branch_ID
                FROM
                    trainees
                LEFT JOIN branch ON trainees.branch_id = branch.Branch_ID
                WHERE
                    trainees.status_id = 1
                AND trainees.IntranetID IS NOT NULL
                AND branch.Branch_ID > 0
                ORDER BY
                    branch.Branch_ID,
                    trainees.ID";

        $result = mysqli_query($dbc, $query);
        $branches = array();
        $employees = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $branches[$row['Branch_ID']] = $row['Branch_Name'];
            $employees[$row['Branch_ID']][$row['IntranetID']] = $row['employee'];
        }
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        print ('<menu>');
        print('<item id="web_admin"  img="web_admin.png" imgdis="web_admin.png" text="Select Employee">');
        $sepId = 1;
        foreach ($branches as $Branch_ID => $Branch_Name) {
            print('<item  id="' . $Branch_ID . '" text="  ' . $Branch_Name . '" img="branch.png" width="200" >');
            foreach ($employees[$Branch_ID] as $IntranetID => $employeeName) {
                print('<item  id="user_' . $IntranetID . '" text="  ' . $employeeName . '" img="users.png" width="200" />');
            }
            print('</item>');
            print('<item id="sep' . $sepId . '" type="separator"/>');
            ++$sepId;
        }
        print ('</item>');
        print('<item id="seper" type="separator"/>');
        print('<item id="seper_1" type="separator"/>');
        print('<item id="export"  img="excel.png" imgdis="excel.png" text="Export to Excel"/>');
        print('<item id="seper_2" type="separator"/>');
        print('<item id="show" text="Show Reoccuring/Day/All Tasks">');
        print('<item id="reccuring" text="Show Reoccuring" type="radio" group="visible" checked="true"/>');
        print('<item id="day" text="Show Day Tasks" type="radio" group="visible"/>');
        print('<item id="all" text="Show All" type="radio" group="visible"/>');
        print('</item>');
        print('<item id="seper_3" type="separator"/>');
        print('<item id="show_procedure"  img="file.png" imgdis="file.png" text="Show Procedure"/>');
        print('<item id="seper_4" type="separator"/>');
        print('<item id="delete"  img="deleteall.png" imgdis="deleteall.png" text="Delete"/>');
        print('<item id="seper_5" type="separator"/>');
        print('<item id="edit"  img="file.png" imgdis="file.png" text="Edit"/>');
        print ('</menu>');
        break;

    case 25:

        $employeeId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $start_date = (isset($_GET['start_date'])) ? date('Y-m-d', strtotime(filter_input(INPUT_GET, 'start_date'))) : date('Y-m-d');


        $week = date('W', strtotime($start_date));
        $year = date("Y", strtotime($start_date));

        $from = getWeekDates($year, $week);
        $to = getWeekDates($year, $week, false);

        $query = "
            SELECT
                    e.event_id,
                    e.event_name AS details,
                    p.parent project_id,
                    e.document_id doc_id,
                    (
                            CASE
                            WHEN parent_events.event_length = '1' THEN
                                    'Every Week'
                            WHEN parent_events.event_length = '2' THEN
                                    'Every (2) Weeks'
                            WHEN parent_events.event_length = '10' THEN
                                    'Every (3) Weeks'
                            WHEN parent_events.event_length = '7' THEN
                                    'Every (4) Weeks'
                            WHEN parent_events.event_length = '9' THEN
                                    'Every (8) Weeks'
                            WHEN parent_events.event_length = '3' THEN
                                    'Every Month'
                            WHEN parent_events.event_length = '8' THEN
                                    'Every (2) Month'
                            WHEN parent_events.event_length = '4' THEN
                                    'Every (12) Weeks'
                            WHEN parent_events.event_length = '5' THEN
                                    'Every half year'
                            WHEN parent_events.event_length = '6' THEN
                                    'Every year'
                            ELSE
                                    parent_events.event_length
                            END
                    )frequency,
                    DATE_FORMAT(e.start_date, '%H:%i')AS begin_time,
                    DATE_FORMAT(e.end_date, '%H:%i')AS `end_time`,
                    parent_events.rec_type days,
                    parent_events.duration,
                    e.event_pid,
                    e.completed,
                    e.start_date,
                    DAYOFWEEK(e.start_date) day_number
            FROM
                    `events` e
            JOIN trainees t ON t.ID = e.employee_id
            JOIN `events` parent_events ON e.event_pid = parent_events.event_id
            JOIN projects_planning p ON parent_events.event_id = p.event_id
            WHERE
                    IntranetID = " . $employeeId . "
            AND e.event_pid > 0 AND e.`main_task` = 1
            AND e.is_active = 1
            AND DATE(e.start_date) > '" . $from . "'";
//        echo $query;
//        exit;


        $result = mysqli_query($dbc, $query);

        $events = array();
        while ($row = mysqli_fetch_assoc($result)) {

            $events[$row['event_pid']]['event_id'] = $row['event_id'];
            $events[$row['event_pid']]['event_pid'] = $row['event_pid'];
            $events[$row['event_pid']]['project_id'] = $row['project_id'];
            $events[$row['event_pid']]['doc_id'] = $row['doc_id'];
            $events[$row['event_pid']]['details'] = $row['details'];
            $events[$row['event_pid']]['days'] = $row['days'];
            $events[$row['event_pid']]['frequency'] = $row['frequency'];
            $events[$row['event_pid']]['begin_time'] = $row['begin_time'];
            $events[$row['event_pid']]['end_time'] = $row['end_time'];
            $events[$row['event_pid']]['duration'] = $row['duration'];
            $events[$row['event_pid']]['event_pid'] = $row['event_pid'];

            $day_number = $row['day_number'] - 1;
            $events[$row['event_pid']]['event_status'][$day_number] = $row['completed'];
        }


//        echo $queryWeekEv;
//        exit;
//        print '<pre>';
//        print_r($events);
//        exit;

        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        $counter = 0;
        foreach ($events as $row) {

            $projectId = $row['project_id'];
            $no = generateProjectId($projectId);

            if ($row['doc_id']) {
                $doc_id = $row['doc_id'];
            } else {
                $doc_id = get_string_between($row['details'], '/', ']');
            }

            $namelist = explode(']', $row['details']);
            $days = explode(',', $row['days']);

            echo '<row id="' . $row['event_pid'] . '">';
            echo "<cell><![CDATA[" . $row['event_pid'] . "]]></cell>";
            echo "<cell><![CDATA[" . $namelist[1] . "]]></cell>";
            echo "<cell><![CDATA[" . $no . "]]></cell>";
            echo "<cell><![CDATA[" . $row['event_pid'] . "]]></cell>";
            echo "<cell><![CDATA[" . $doc_id . "]]></cell>";
            echo "<cell><![CDATA[" . $row['frequency'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['begin_time'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['end_time'] . "]]></cell>";
            echo "<cell><![CDATA[" . round($row['duration'] / 60, 1) . 'hr(s)' . "]]></cell>";

            $queryWeekEv = "
            SELECT
                DATE(e.start_date) start_date,
                e.completed
            FROM
                `events` e
            JOIN trainees t ON t.ID = e.employee_id
            WHERE
                e.event_pid = " . $row['event_pid'] . "
            AND IntranetID = " . $employeeId . "
            AND(tag_id IS NULL OR event_pid = 0)
            AND(event_pjd IS NULL OR event_pjd = 0)
            AND DATE(e.start_date) BETWEEN CAST('" . $from . "' AS DATE) AND CAST('" . $to . "' AS DATE)
                
            ORDER BY
                start_date ASC";
            $resultWeekEv = mysqli_query($dbc, $queryWeekEv);
            $weekEv = array();
            if (mysqli_num_rows($resultWeekEv) > 0) {
                while ($rowWeekEv = mysqli_fetch_assoc($resultWeekEv)) {
                    $weekEv[$rowWeekEv['start_date']] = $rowWeekEv['completed'];
                }
            }

            for ($i = 0; $i <= 5; $i++) {

                $j = $i + 1;
                $eDate = date('Y-m-d', strtotime($from . " + $i days"));
                echo "<cell style='background-color:" . (in_array($j, $days) ? 'orange' : 'white') . "'>" . $weekEv[$eDate] . "</cell>";
            }
            echo '</row>';
            unset($days);
            unset($weekEv);
        }
        echo '</rows>';
        break;

    case 26:

        $employeeId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $start_date = (isset($_GET['start_date'])) ? date('Y-m-d', strtotime(filter_input(INPUT_GET, 'start_date'))) : date('Y-m-d');

        $query = "SELECT
                    e.event_id,
                    event_name AS details,
                    p.parent project_id,
                    e.document_id doc_id,
                    'Once' frequency,
                    DATE_FORMAT(start_date, '%H:%i')AS begin_time,
                    DATE_FORMAT(end_date, '%H:%i')AS `end_time`,
                    rec_type days,
                    duration
                FROM
                        `events` e
                LEFT JOIN projects_planning p ON e.event_id = p.event_id
                JOIN trainees t ON t.ID = e.employee_id 
                WHERE
                t.IntranetID = " . $employeeId . "
                AND e.main_task = 0
                AND e.event_pid IS NULL
                AND DATE(e.start_date) = '" . $start_date . "'";

        $result = mysqli_query($dbc, $query);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        $counter = 0;
        while ($row = mysqli_fetch_assoc($result)) {

            $projectId = $row['project_id'];
            $no = generateProjectId($projectId);

            if ($row['doc_id']) {
                $doc_id = $row['doc_id'];
            } else {
                $doc_id = get_string_between($row['details'], '/', ']');
            }

            $days = explode(',', $row['days']);
            echo '<row id="' . $row['event_id'] . '">';
            echo "<cell><![CDATA[" . $row['details'] . "]]></cell>";
            echo "<cell><![CDATA[" . $no . "]]></cell>";
            echo "<cell><![CDATA[" . $row['event_id'] . "]]></cell>";
            echo "<cell><![CDATA[" . $doc_id . "]]></cell>";
            echo "<cell><![CDATA[" . $row['frequency'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['begin_time'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['end_time'] . "]]></cell>";
            echo "<cell><![CDATA[" . round($row['duration'] / 60, 1) . 'hr(s)' . "]]></cell>";
            echo "<cell><![CDATA[" . (in_array('1', $days) ? 1 : 0) . "]]></cell>";
            echo "<cell><![CDATA[" . (in_array('2', $days) ? 1 : 0) . "]]></cell>";
            echo "<cell><![CDATA[" . (in_array('3', $days) ? 1 : 0) . "]]></cell>";
            echo "<cell><![CDATA[" . (in_array('4', $days) ? 1 : 0) . "]]></cell>";
            echo "<cell><![CDATA[" . (in_array('5', $days) ? 1 : 0) . "]]></cell>";
            echo "<cell><![CDATA[" . (in_array('6', $days) ? 1 : 0) . "]]></cell>";
            echo '</row>';
            unset($days);
        }
        echo '</rows>';
        break;

    case 27:

        $employeeId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $start_date = (isset($_GET['start_date'])) ? date('Y-m-d', strtotime(filter_input(INPUT_GET, 'start_date'))) : date('Y-m-d');

        $query = "
            SELECT
                    e.event_id,
                    e.event_name AS details,
                    e.event_pid,
            IF(
                    e.event_pid > 0,
                    (
                            SELECT
                                    parent
                            FROM
                                    projects_planning
                            WHERE
                                    event_id = e.event_pid
                    ),
                    p.parent
            )project_id,
             e.document_id doc_id,

            IF(
                    e.event_pid > 0,
                    (
                            CASE
                            WHEN parent_events.event_length = '1' THEN
                                    'Every Week'
                            WHEN parent_events.event_length = '2' THEN
                                    'Every (2) Weeks'
                            WHEN parent_events.event_length = '10' THEN
                                    'Every (3) Weeks'
                            WHEN parent_events.event_length = '7' THEN
                                    'Every (4) Weeks'
                            WHEN parent_events.event_length = '9' THEN
                                    'Every (8) Weeks'
                            WHEN parent_events.event_length = '3' THEN
                                    'Every Month'
                            WHEN parent_events.event_length = '8' THEN
                                    'Every (2) Month'
                            WHEN parent_events.event_length = '4' THEN
                                    'Every (12) Weeks'
                            WHEN parent_events.event_length = '5' THEN
                                    'Every half year'
                            WHEN parent_events.event_length = '6' THEN
                                    'Every year'
                            ELSE
                                    ''
                            END
                    ),
                    'Once'
            )frequency,
             parent_events.event_length,
             DATE_FORMAT(e.start_date, '%H:%i')AS begin_time,
             DATE_FORMAT(e.end_date, '%H:%i')AS `end_time`,

            IF(
                    e.event_pid > 0,
                    parent_events.rec_type,
                    e.rec_type
            )days,
             e.duration
            FROM
                    `events` e
            JOIN trainees t ON t.ID = e.employee_id
            LEFT JOIN projects_planning p ON e.event_id = p.event_id
            LEFT JOIN `events` parent_events ON e.event_pid = parent_events.event_id
            WHERE
                    t.IntranetID = " . $employeeId . "
            AND e.main_task = 0
            AND DATE(e.start_date)= '" . $start_date . "'";
//        echo $query; exit;

        $result = mysqli_query($dbc, $query);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        $counter = 0;
        while ($row = mysqli_fetch_assoc($result)) {

            $projectId = $row['project_id'];
            $no = generateProjectId($projectId);

            if ($row['doc_id']) {
                $doc_id = $row['doc_id'];
            } else {
                $doc_id = get_string_between($row['details'], '/', ']');
            }

            $days = explode(',', $row['days']);
            echo '<row id="' . $row['event_id'] . '">';
            echo "<cell><![CDATA[" . $row['details'] . "]]></cell>";
            echo "<cell><![CDATA[" . $no . "]]></cell>";
            echo "<cell><![CDATA[" . ($row['event_pid'] > 0 ? $row['event_pid'] : $row['event_id']) . "]]></cell>";
            echo "<cell><![CDATA[" . $doc_id . "]]></cell>";
            echo "<cell><![CDATA[" . $row['frequency'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['begin_time'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['end_time'] . "]]></cell>";
            echo "<cell><![CDATA[" . round($row['duration'] / 60, 1) . 'hr(s)' . "]]></cell>";
            echo "<cell><![CDATA[" . (in_array('1', $days) ? 1 : 0) . "]]></cell>";
            echo "<cell><![CDATA[" . (in_array('2', $days) ? 1 : 0) . "]]></cell>";
            echo "<cell><![CDATA[" . (in_array('3', $days) ? 1 : 0) . "]]></cell>";
            echo "<cell><![CDATA[" . (in_array('4', $days) ? 1 : 0) . "]]></cell>";
            echo "<cell><![CDATA[" . (in_array('5', $days) ? 1 : 0) . "]]></cell>";
            echo "<cell><![CDATA[" . (in_array('6', $days) ? 1 : 0) . "]]></cell>";
            echo '</row>';
            unset($days);
        }
        echo '</rows>';
        break;

    case 28:

        $eventId = filter_input(INPUT_POST, 'eventId', FILTER_SANITIZE_NUMBER_INT);
        $employeeId = filter_input(INPUT_POST, 'employeeId', FILTER_SANITIZE_NUMBER_INT);
        $fieldvalue = filter_input(INPUT_POST, 'nValue');
        $uID = filter_input(INPUT_POST, 'eid', FILTER_SANITIZE_NUMBER_INT);

        $insert = "INSERT INTO events(`event_id`,`employee_id`,`event_name`,`details`,`start_date`,`end_date`,entered_by,`cat_id`,`event_pid`,`visible`,`completed`,`masterrecord`,`assigned_eid`)SELECT " . $eventId . "," . $employeeId . ",`event_name`,`details`,`start_date`,`end_date`," . $uID . ",`cat_id`,`event_pid`,IF($fieldvalue>0,1,0),completed,`masterrecord`,`assigned_eid` FROM `events` WHERE `event_id`=" . $eventId . " LIMIT 1 ON DUPLICATE KEY UPDATE is_active='" . $fieldvalue . "'";

        $insertResult = mysqli_query($dbc, $insert);
        if ($insertResult) {

            $result = mysqli_query($dbc, "SELECT GROUP_CONCAT(CONCAT(COALESCE(FirstName,''),' ',COALESCE(SecondName,''),' ',COALESCE(LastName,'')))employee FROM nts_site.trainees WHERE ID IN (SELECT employee_id FROM `events` WHERE `event_id` = " . $eventId . " AND is_active = 1)");
            $row = mysqli_fetch_array($result);
            $assigned_eid = $row[0];

            $update = "UPDATE `events` SET assigned_eid ='" . $assigned_eid . "' WHERE event_id = " . $eventId;
            $updateResult = mysqli_query($dbc, $update);
            if ($updateResult) {
                $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
            } else {
                $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
            }
        } else {
            $data['data'] = array('response' => $insertResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);

        break;

    case 29:

        $emplist = array();

        $query = "SELECT event_id,employee_id FROM `events` WHERE `event_id` =" . $_GET['evt_id'] . " AND is_active = 1";
        $result = mysqli_query($dbc, $query);
        while ($row = mysqli_fetch_array($result)) {
            $emplist[] = $row['employee_id'];
        }

//        $query = "SELECT ID,CONCAT(COALESCE(FirstName,''),' ',COALESCE(SecondName,''),' ',COALESCE(LastName,'')) employee FROM nts_site.trainees WHERE status_id = 1 || id = 51 ORDER BY branch_id,ID";

        $query = "
            SELECT 
              ID,
              CONCAT(
                COALESCE(FirstName, ''),
                ' ',
                COALESCE(SecondName, ''),
                ' ',
                COALESCE(LastName, '')
              ) employee 
            FROM
              nts_site.trainees 
            WHERE status_id = 1 
              AND (
                IntranetId <> 0 || IntranetId IS NOT NULL
              ) 
              AND ID NOT IN (33, 501399, 501420, 501417, 501427) 
              AND branch_id IS NOT NULL 
            ORDER BY ID";

        $result = mysqli_query($dbc, $query);

        header("Content-type:text/xml");
        ini_set('max_execution_time', 600);
        print("<?xml version=\"1.0\"?>");
        echo "<complete>";
        while ($row = mysqli_fetch_array($result)) {
            if (in_array($row['ID'], $emplist)) {
                echo "<option value='" . $row['ID'] . "' checked='1' selected='1'>" . $row['employee'] . "</option>";
            } else {
                echo "<option value='" . $row['ID'] . "'>" . $row['employee'] . "</option>";
            }
        }
        echo "</complete>";
        break;

    case 30:
        $date1 = new DateTime($_POST['start_date']);
        $s1 = $date1->format('Y-m-d H:i:s');
        $date2 = new DateTime($_POST['end_date']);
        $s2 = $date2->format('Y-m-d H:i:s');
        $cat = $_POST['cat_id'];
        if ($cat == null || $cat == '')
            $cat = 45;

        //user logged in enters tasks
        $uID = $_GET['eid'];
        if ($uID == "") {
            $uID = 0;
        }
        //if the time is set:overite the time set on calendar popup.
        $bgntime = $_POST['begn'];
        $endtime = $_POST['end'];
        if ($bgntime != '') {
            $cdtstart = new DateTime($s1);
            $s1 = $cdtstart->format('Y-m-d');
            $s1 = $s1 . " " . $bgntime;
            $cdtend = new DateTime($s2);
            $s2 = $cdtend->format('Y-m-d');
            $s2 = $s2 . " " . $endtime;
        }
        $chkvalues = $_POST['days_select'];
        $dbString = array();
        foreach ($chkvalues as $key => $value) {//check for optin and opt out
            if ($value == 1) {
                $dbString[] = $key;
            }
        }
        $dbString = implode(',', $dbString);

        $apprv_str = $_GET['approved'];
        $event_id = $_POST['event_id'];

        //get the approved items
        if ($apprv_str == null) {
            $apprv_str = 0;
        }

        $update = "UPDATE nts_site.`events`
                            SET details = '{$_POST['event_name']}',
                             event_name = '{$_POST['details']}',
                             start_date = '{$s1}',
                             end_date = '{$s2}',
                             event_length = '{$_POST['freq']}',
                             rec_type = '{$dbString}',
                             is_variable = '{$_POST['variable']}',
                             info = '{$_POST['info']}',
                             approved_by = '{$apprv_str}',
                             map = '{$_POST['map']}',
                             reoccur_map = '{$_POST['reoccur_map']}',
                             duration = '{$_POST['period']}'
                            WHERE
                             event_id = '{$_POST['event_id']}'";
//echo $update;exit;

        $updateResult = mysqli_query($dbc, $update) or die(mysqli_error($dbc) . $update);
        if ($updateResult)
            $data['data'] = array('response' => $updateResult, 'text' => 'Data Successfully Saved');
        else
            $data['data'] = array('response' => $updateResult, 'text' => 'Error: Data Not Saved');

        echo json_encode($data);
        break;

    case 31:

        $projectId = filter_input(INPUT_POST, 'tId', FILTER_SANITIZE_NUMBER_INT);
        $eventId = filter_input(INPUT_POST, 'sId', FILTER_SANITIZE_NUMBER_INT);
        $update = "UPDATE projects_planning SET parent = '" . $projectId . "' WHERE event_id = " . $eventId;
        $updateResult = mysqli_query($dbc, $update);
        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);

        break;

    case 32:
        $date1 = new DateTime($_POST['start_date']);
        $s1 = $date1->format('Y-m-d H:i:s');
        $date2 = new DateTime($_POST['end_date']);
        $s2 = $date2->format('Y-m-d H:i:s');
        $cat = $_POST['cat_id'];
        if ($cat == null || $cat == '')
            $cat = 45;

        //user logged in enters tasks
        $uID = $_GET['eid'];
        if ($uID == "") {
            $uID = 0;
        }
        //if the time is set:overite the time set on calendar popup.
        $bgntime = $_POST['begn'];
        $endtime = $_POST['end'];
        if ($bgntime != '') {
            $cdtstart = new DateTime($s1);
            $s1 = $cdtstart->format('Y-m-d');
            $s1 = $s1 . " " . $bgntime;
            $cdtend = new DateTime($s2);
            $s2 = $cdtend->format('Y-m-d');
            $s2 = $s2 . " " . $endtime;
        }
        $chkvalues = $_POST['days_select'];
        $dbString = array();
        foreach ($chkvalues as $key => $value) {//check for optin and opt out
            if ($value == 1) {
                $dbString[] = $key;
            }
        }
        $dbString = implode(',', $dbString);

        $apprv_str = $_GET['approved'];
        $event_id = $_POST['event_id'];

        //get the approved items
        if ($apprv_str == null) {
            $apprv_str = 0;
        }

        $update = "UPDATE nts_site.`events`
                            SET details = '{$_POST['event_name']}',
                             event_name = '{$_POST['asset_event_details']}',
                             start_date = '{$s1}',
                             end_date = '{$s2}',
                             event_length = '{$_POST['freq']}',
                             rec_type = '{$dbString}',
                             is_variable = '{$_POST['variable']}',
                             info = '{$_POST['asset_event_info']}',
                             approved_by = '{$apprv_str}',
                             map = '{$_POST['map']}',
                             reoccur_map = '{$_POST['reoccur_map']}',
                             duration = '{$_POST['period']}'
                            WHERE
                             event_id = '{$_POST['event_id']}'"; //echo $update;

        $updateResult = mysqli_query($dbc, $update) or die(mysqli_error($dbc) . $update);
        if ($updateResult)
            $data['data'] = array('response' => $updateResult, 'text' => 'Data Successfully Saved');
        else
            $data['data'] = array('response' => $updateResult, 'text' => 'Error: Data Not Saved');

        echo json_encode($data);
        break;

    case 33:

        $date1 = new DateTime($_POST['start_date']);
        $s1 = $date1->format('Y-m-d H:i:s');
        $date2 = new DateTime($_POST['end_date']);
        $s2 = $date2->format('Y-m-d H:i:s');
        //if the time is not set:overite the time set on calendar popup.
        $bgntime = $_POST['begn'];
        $endtime = $_POST['end'];
        if ($bgntime != '') {
            $cdtstart = new DateTime($s1);
            $s1 = $cdtstart->format('Y-m-d');
            $s1 = $s1 . " " . $bgntime;
            $cdtend = new DateTime($s2);
            $s2 = $cdtend->format('Y-m-d');
            $s2 = $s2 . " " . $endtime;
        }
        //user logged in enters tasks
        $uID = $_POST['eid'];
        if ($uID == "") {
            $uID = 0;
        }
        $str = "Update events set event_name = '" . $_POST['asset_event_name_child'] . "',info = '" . $_POST['asset_event_information'] . "',employee_id = " . $_POST['employee_id'] . ",start_date = '$s1',end_date = '$s2',entered_by = " . $uID . " where event_id = " . $_POST['event_id'];
        mysqli_query($dbc, $str);
        $info = "saved" . $str;
        echo json_encode(array("info" => $info, "event_id" => $_POST['event_id']));
        break;

    case 34:
        //get start and end date
        $startDate = $_POST['start_date'];
        $dateSpan = new DateTime($_POST['start_date']);
        $sPan = $dateSpan->format('Y-m-d');
        $endDate = $_POST['end_date'];
        $cat = $_POST['cat_id'];
        if ($cat == null || $cat == '')
            $cat = 45;

        $qry_par = "Select document_id from events where event_id =" . $_POST['event_id'];
        $res_par = mysqli_query($dbc, $qry_par);
        $row_par = mysqli_fetch_array($res_par);
        $document_id = $row_par['document_id']; //
        $empl = $_GET['emp'];
        if ($empl == "undefined" || $empl == "") {
            $empl = 1;
        }

        //set main task field = true
        mysqli_query($dbc, "UPDATE `events` SET main_task = 1 WHERE event_id=" . $_POST['event_id']);

        //update for form 2                              
        $chkvalues = $_POST['days_select'];
        $dbString = array();
        foreach ($chkvalues as $key => $value) {//check for optin and opt out
            if ($value == 1) {
                $dbString[] = $key;
            }
        }
        $typ = "day";
        $date1 = new DateTime($_POST['start_date']);
        $s1 = $date1->format('Y-m-d H:i:s');
        $date2 = new DateTime($_POST['end_date']);
        $s2 = $date2->format('Y-m-d H:i:s');
        //if the time is set:overite the time set on calendar popup.
        $bgntime = $_POST['begn'];
        $endtime = $_POST['end'];
        if ($bgntime != '') {
            $cdtstart = new DateTime($s1);
            $s1 = $cdtstart->format('Y-m-d');
            $s1 = $s1 . " " . $bgntime;
            $cdtend = new DateTime($s2);
            $s2 = $cdtend->format('Y-m-d');
            $s2 = $s2 . " " . $endtime;
        }


        $empResBranch = mysqli_query($dbc, "SELECT branch_id FROM nts_site.trainees WHERE ID IN (" . $_GET['ass_emp'] . ") GROUP BY ID ORDER BY ID ASC");
        $branchOfEmp = array();
        while ($rowBranch = mysqli_fetch_assoc($empResBranch)) {
            $branchOfEmp[] = $rowBranch['branch_id'];
        }

        if (in_array('6', $branchOfEmp)) {
            $evtCountry = 1;
        } else {
            $evtCountry = 2;
        }
        $resHols = mysqli_query($dbc, "SELECT * FROM `hrm_events` WHERE `country` = '" . $evtCountry . "'");
        while ($rowsH = mysqli_fetch_assoc($resHols)) {
            $holdays[] = $rowsH['start_date'];
        }

        switch ($_POST['freq']) {
            //switch days days
            case 1:
                $interval = 7;
                break;
            case 2:
                $interval = 14;
                break;
            case 3:
                $interval = 1;
                $typ = "month";
                createInt($max_pl, $s1, $s2, $interval, $typ, $endtime, $dbString, $document_id, $holdays);
                exit();
                break;
            case 4:
                $interval = 84;
                break;
            case 5:
                $interval = 6;
                $typ = "month";
                createInt($max_pl, $s1, $s2, $interval, $typ, $endtime, $dbString, $document_id, $holdays);
                exit();
                break;
            case 6:
                $interval = 366;
                /* $interval = 1;
                  $typ = "year";
                  createInt($max_pl, $s1, $s2, $interval, $typ, $endtime, $dbString, $document_id, $holdays);
                  exit(); */
                break;
            case 7:
                $interval = 28;
                break;
            case 8:
                $interval = 2;
                $typ = "month";
                createInt($max_pl, $s1, $s2, $interval, $typ, $endtime, $dbString, $document_id, $holdays);
                exit();
                break;
            case 9:
                $interval = 56;
                break;
            case 10:
                $interval = 21;
                break;
            default :
                $interval = 365;
                break; //handle date infinite 							 
        }
        //clears and work with the variable day set 
        if ($variable == 1) {
            unset($dbString);
            $dbString[] = date('N', strtotime($startDate));
        }
        $seldays = $dbString;
        $s = $s1;
        //get the day number                             
        $day_number = date('N', strtotime($s));
        foreach ($seldays as $key => $value) {
            $emp = explode(",", $_GET['ass_emp']);
            $new_string = "" . implode("','", $emp) . "";
            $endDate = $_POST['end_date'];
            $startDate = $s;
            if ($day_number != $value) {
                $z = $day_number - $value;
                $w = 7;
                $y = $w - $z;
                $y = str_replace("-", "", $y);

                if ($z < 0) {
                    $z = str_replace("-", "", $z);
                    $startDate = date('Y-m-d H:i:s', strtotime($startDate . $z . " day"));
                } else {
                    $startDate = date('Y-m-d H:i:s', strtotime($startDate . $y . " day"));
                }
            }


            while (strtotime($startDate) < strtotime($endDate)) {
                //select employee_id sent by category table 
                $empQuery = "Select ID as a1,FirstName as a2 from nts_site.trainees where ID IN(" . $_GET['ass_emp'] . ") GROUP BY ID order by ID asc";
                $empRes = mysqli_query($dbc, $empQuery);
                while ($row_pl = mysqli_fetch_array($empRes)) {
                    $max_pl++;
                    $empID = $row_pl['a1'];
                    //task period withing time set
                    $tskend = new DateTime($startDate);
                    $tskend = $tskend->format('Y-m-d');
                    $tskend = $tskend . " " . $endtime;
                    //user logged in enters tasks
                    $uID = $_COOKIE['userlggd'];
                    if ($uID == "") {
                        $uID = 0;
                    }
                    //filter for saturday and sunday events
                    if ($day_weeknd == 6 && $_POST['variable'] == 1) {
                        $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -1 day"));
                    }
                    if ($day_weeknd == 7 && $_POST['variable'] == 1) {
                        $startDate = date('Y-m-d H:i:s', strtotime($startDate . " +1 day"));
                    }
                    $emp_strdb = $row_pl['FirstName'];

                    $checkHolidayDate = new DateTime($startDate);
                    $checkHolidayDate = $checkHolidayDate->format('Y-m-d');
                    if (in_array($checkHolidayDate, $holdays)) {
                        $evtDateH = date('Y-m-d H:i:s', strtotime($startDate . " -1 week"));
                    } else {
                        $evtDateH = $startDate;
                    }

                    $qry_insert = "INSERT INTO `events` (`event_pid`,`event_name`,`details`,`start_date`,`end_date`,`employee_id`,`event_length`,`cat_id`,`entered_by`,`assigned_eid`,`visible`,`document_id`) VALUES ('" . $_POST['event_id'] . "','" . $_POST['asset_event_details'] . "','" . $_POST['event_name'] . "','$evtDateH','$tskend','$empID',null,'$cat','$uID','" . $row_pl['a1'] . "',0,'$document_id')";
                    mysqli_query($dbc, $qry_insert);
                }
                $startDate = date('Y-m-d H:i:s', strtotime($startDate . " + " . $interval . $typ));
            }
            $sql_event_update = "update events set target = 1 where event_id = " . $_POST['event_id'];
            mysqli_query($dbc, $sql_event_update);
        }

        break;

    case 35:


        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT
                        event_id,
                        cat_id,
                        event_name AS details,
                        event_name AS event_name_child,
                        details AS event_name,
                        info AS information,
                        employee_id,
                        event_pid,
                        DATE_FORMAT(start_date, '%Y-%m-%d')AS start_date,
                        DATE_FORMAT(end_date, '%Y-%m-%d')AS end_date,
                        DATE_FORMAT(start_date, '%H:%i')AS begn,
                        DATE_FORMAT(end_date, '%H:%i')AS `end`,
                        event_length AS freq,
                        rec_type,
                        is_variable AS variable,
                        map,
                        masterrecord,
                        duration,
                        reoccur_map
                FROM
                        events
                WHERE
                        event_id =" . $id;

        $res = mysqli_query($dbc, $query) or die(mysqli_error($dbc) . $qry);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        print("<data>");

        while ($row = mysqli_fetch_assoc($res)) {
            print("<event_id>" . $row['event_id'] . "</event_id>");
            print("<event_name><![CDATA[" . $row['event_name'] . "]]></event_name>");
            print("<variable><![CDATA[" . $row['variable'] . "]]></variable>");
            print("<event_name_child><![CDATA[" . $row['event_name_child'] . "]]></event_name_child>");
            print("<asset_event_details><![CDATA[" . $row['details'] . "]]></asset_event_details>");
            print("<freq>" . $row['freq'] . "</freq>");
            print("<rec_type>" . $row['rec_type'] . "</rec_type>");
            print("<emp>" . $row['employee_id'] . "</emp>");
            print("<asset_event_info><![CDATA[" . $row['information'] . "]]></asset_event_info>");
            print("<event_pid>" . $row['event_pid'] . "</event_pid>");
            print("<cat_id>" . $row['cat_id'] . "</cat_id>");
            print("<start_date>" . $row['start_date'] . "</start_date>");
            print("<end_date>" . $row['end_date'] . "</end_date>");
            print("<period>" . $row['duration'] . "</period>");
            print("<begn>" . $row['begn'] . "</begn>");
            print("<end>" . $row['end'] . "</end>");
            print("<map>" . $row['map'] . "</map>");
            print("<masterrecord>" . $row['masterrecord'] . "</masterrecord>");
            print("<reoccur_map>" . $row['reoccur_map'] . "</reoccur_map>");
        }
        print("</data>");

        break;

    case 36:

        $eventId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $qry = "SELECT
                        event_id,
                        cat_id,
                        event_name details,
                        event_name event_name_child,
                        details event_name,
                        info,
                        employee_id emp,
                        event_pid,
                        DATE_FORMAT(start_date, '%Y-%m-%d') start_date,
                        DATE_FORMAT(end_date, '%Y-%m-%d') end_date,
                        DATE_FORMAT(start_date, '%H:%i') begn,
                        DATE_FORMAT(end_date, '%H:%i')`end`,
                        event_length freq,
                        rec_type,
                        is_variable variable,
                        map,
                        masterrecord,
                        duration period,
                        reoccur_map
                FROM
                        `events`
                WHERE
                        event_id = " . $eventId;

        $res = mysqli_query($dbc, $qry) or die(mysqli_error($dbc) . $qry);
        $row = mysqli_fetch_array($res);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0" encoding="UTF-8"?>' . PHP_EOL;
        echo '<data>';
        echo '<event_id><![CDATA[' . $row["event_id"] . ']]></event_id>';
        echo '<cat_id><![CDATA[' . $row["cat_id"] . ']]></cat_id>';
//        echo '<details><![CDATA[' . $row["details"] . ']]></details>';
        echo '<asset_event_name_child><![CDATA[' . $row["event_name_child"] . ']]></asset_event_name_child>';
        echo '<event_name><![CDATA[' . $row["event_name"] . ']]></event_name>';
        echo '<asset_event_information><![CDATA[' . $row["info"] . ']]></asset_event_information>';
        echo '<employee_id><![CDATA[' . $row["emp"] . ']]></employee_id>';
        echo '<event_pid><![CDATA[' . $row["event_pid"] . ']]></event_pid>';
        echo '<start_date><![CDATA[' . $row["start_date"] . ']]></start_date>';
        echo '<end_date><![CDATA[' . $row["end_date"] . ']]></end_date>';
        echo '<begn><![CDATA[' . $row["begn"] . ']]></begn>';
        echo '<end><![CDATA[' . $row["end"] . ']]></end>';
        echo '<freq><![CDATA[' . $row["freq"] . ']]></freq>';
        echo '<rec_type><![CDATA[' . $row["rec_type"] . ']]></rec_type>';
        echo '<variable><![CDATA[' . $row["variable"] . ']]></variable>';
        echo '<map><![CDATA[' . $row["map"] . ']]></map>';
        echo '<masterrecord><![CDATA[' . $row["masterrecord"] . ']]></masterrecord>';
        echo '<period><![CDATA[' . $row["period"] . ']]></period>';
        echo '<reoccur_map><![CDATA[' . $row["reoccur_map"] . ']]></reoccur_map>';
        echo '</data>';
        break;

    case 37:
        $date1 = new DateTime($_POST['start_date']);
        $s1 = $date1->format('Y-m-d H:i:s');
        $date2 = new DateTime($_POST['end_date']);
        $s2 = $date2->format('Y-m-d H:i:s');
        $cat = $_POST['cat_id'];
        if ($cat == null || $cat == '')
            $cat = 45;

        //user logged in enters tasks
        $uID = $_GET['eid'];
        if ($uID == "") {
            $uID = 0;
        }
        //if the time is set:overite the time set on calendar popup.
        $bgntime = $_POST['begn'];
        $endtime = $_POST['end'];
        if ($bgntime != '') {
            $cdtstart = new DateTime($s1);
            $s1 = $cdtstart->format('Y-m-d');
            $s1 = $s1 . " " . $bgntime;
            $cdtend = new DateTime($s2);
            $s2 = $cdtend->format('Y-m-d');
            $s2 = $s2 . " " . $endtime;
        }
        $chkvalues = $_POST['days_select'];
        $dbString = array();
        foreach ($chkvalues as $key => $value) {//check for optin and opt out
            if ($value == 1) {
                $dbString[] = $key;
            }
        }
        $dbString = implode(',', $dbString);

        $apprv_str = $_GET['approved'];
        $event_id = $_POST['event_id'];

        //get the approved items
        if ($apprv_str == null) {
            $apprv_str = 0;
        }

        $update = "UPDATE nts_site.`events`
                            SET details = '{$_POST['event_name']}',
                             event_name = '{$_POST['asset_event_name_child']}',
                             start_date = '{$s1}',
                             end_date = '{$s2}',
                             event_length = '{$_POST['freq']}',
                             rec_type = '{$dbString}',
                             is_variable = '{$_POST['variable']}',
                             info = '{$_POST['asset_event_info']}',
                             approved_by = '{$apprv_str}',
                             map = '{$_POST['map']}',
                             reoccur_map = '{$_POST['reoccur_map']}',
                             duration = '{$_POST['period']}'
                            WHERE
                             event_id = '{$_POST['event_id']}'"; //echo $update;

        $updateResult = mysqli_query($dbc, $update) or die(mysqli_error($dbc) . $update);
        if ($updateResult)
            $data['data'] = array('response' => $updateResult, 'text' => 'Data Successfully Saved');
        else
            $data['data'] = array('response' => $updateResult, 'text' => 'Error: Data Not Saved');

        echo json_encode($data);
        break;

    case 38:
        $date1 = new DateTime($_POST['start_date']);
        $s1 = $date1->format('Y-m-d H:i:s');
        $date2 = new DateTime($_POST['end_date']);
        $s2 = $date2->format('Y-m-d H:i:s');
        $cat = $_POST['cat_id'];
        if ($cat == null || $cat == '')
            $cat = 45;

        //user logged in enters tasks
        $uID = $_GET['eid'];
        if ($uID == "") {
            $uID = 0;
        }
        //if the time is set:overite the time set on calendar popup.
        $bgntime = $_POST['begn'];
        $endtime = $_POST['end'];
        if ($bgntime != '') {
            $cdtstart = new DateTime($s1);
            $s1 = $cdtstart->format('Y-m-d');
            $s1 = $s1 . " " . $bgntime;
            $cdtend = new DateTime($s2);
            $s2 = $cdtend->format('Y-m-d');
            $s2 = $s2 . " " . $endtime;
        }
        $chkvalues = $_POST['days_select'];
        $dbString = array();
        foreach ($chkvalues as $key => $value) {//check for optin and opt out
            if ($value == 1) {
                $dbString[] = $key;
            }
        }
        $dbString = implode(',', $dbString);

        $apprv_str = $_GET['approved'];
        $event_id = $_POST['event_id'];

        //get the approved items
        if ($apprv_str == null) {
            $apprv_str = 0;
        }

        $update = "UPDATE nts_site.`events`
                            SET details = '{$_POST['event_name']}',
                             event_name = '{$_POST['toc_details']}',
                             start_date = '{$s1}',
                             end_date = '{$s2}',
                             event_length = '{$_POST['freq']}',
                             rec_type = '{$dbString}',
                             is_variable = '{$_POST['variable']}',
                             info = '{$_POST['toc_info']}',
                             approved_by = '{$apprv_str}',
                             map = '{$_POST['map']}',
                             reoccur_map = '{$_POST['reoccur_map']}',
                             duration = '{$_POST['period']}'
                            WHERE
                             event_id = '{$_POST['event_id']}'"; //echo $update;

        $updateResult = mysqli_query($dbc, $update) or die(mysqli_error($dbc) . $update);
        if ($updateResult)
            $data['data'] = array('response' => $updateResult, 'text' => 'Data Successfully Saved');
        else
            $data['data'] = array('response' => $updateResult, 'text' => 'Error: Data Not Saved');

        echo json_encode($data);
        break;

    case 39:

        $today = date("Y-m-d");
        $week = date('W', strtotime($today));
        $year = date("Y");
        echo getWeekDates($year, $week, false);
        break;

    case 40:

        $id = filter_input(INPUT_POST, 'id');
        $today = date("Y-m-d");

        $query = "SELECT event_id FROM `events` WHERE event_pid = " . $id . " AND (tag_id IS NULL OR event_pid = 0) AND (event_pjd IS NULL OR event_pjd = 0)  AND DATE(start_date) >= '" . $today . "'";
        $result = mysqli_query($dbc, $query);

        $count = mysqli_num_rows($result);

        if ($count > 0) {
            $data['data'] = array('response' => false, 'text' => 'Task has attached tasks');
        } else {
            $delete = "DELETE FROM `events` WHERE event_id =" . $id;
            $deleteResult = mysqli_query($dbc, $delete);
            if ($deleteResult) {
                $data['data'] = array('response' => $deleteResult, 'text' => 'Successfully Deleted');
            } else {
                $data['data'] = array('response' => $deleteResult, 'text' => 'An Error Occured While Deleting');
            }
        }
        echo json_encode($data);
        break;

    case 41:

        $event_name = filter_input(INPUT_POST, 'event_name', FILTER_SANITIZE_STRING);
        $event_id = filter_input(INPUT_POST, 'event_id', FILTER_SANITIZE_NUMBER_INT);

        $update = "UPDATE `events` SET `event_name`='" . $event_name . "' WHERE event_id =" . $event_id;
        $updateResult = mysqli_query($dbc, $update);


        $update_reccuring = "UPDATE `events` SET `event_name`='" . $event_name . "' WHERE event_pid =" . $event_id;
        mysqli_query($dbc, $update_reccuring);

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Deleting');
        }

        echo json_encode($data);
        break;
}

function getWeekDates($year, $week, $start = true)
{
    $from = date("Y-m-d", strtotime("{$year}-W{$week}-1")); //Returns the date of monday in week
    $to = date("Y-m-d", strtotime("{$year}-W{$week}-7"));   //Returns the date of sunday in week

    if ($start) {
        return $from;
    } else {
        return $to;
    }
    //return "Week {$week} in {$year} is from {$from} to {$to}.";
}

function xml_entities($string)
{
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

function convertXLStoCSV($infile, $outfile)
{
    $fileType = PHPExcel_IOFactory::identify($infile);
    $objReader = PHPExcel_IOFactory::createReader($fileType);

//    $objReader->setReadDataOnly(true);
    $objPHPExcel = $objReader->load($infile);

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
    $objWriter->save($outfile);
}

function get_string_between($string, $start, $end)
{
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0)
        return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function generateProjectId($itemId)
{
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
