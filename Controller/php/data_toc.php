<?php

require 'config_mysqli.php';
include("GeneralFunctions.php");

$action = $_GET['action'];

switch ($action) {

    default:
        break;

    case 1:
        $docId = filter_input(INPUT_GET, 'doc_id', FILTER_SANITIZE_NUMBER_INT);
        $parentId = filter_input(INPUT_GET, 'parent_id', FILTER_SANITIZE_NUMBER_INT);
        $parentId = $parentId ? $parentId : '0';

        $insert = "INSERT INTO document_toc (`parent_id`,`doc_id`,`sort`,`status`) SELECT " . $parentId . ",'" . $docId . "',IF((MAX(sort)>0),MAX(sort)+1,1)sort,'To do' FROM document_toc WHERE parent_id = " . $parentId . " AND doc_id = " . $docId;

        $insertResult = mysqli_query($dbc, $insert);
        if ($insertResult) {
            $newId = mysqli_insert_id($dbc);
            $data['data'] = array('response' => $insertResult, 'text' => 'Successfully Added', 'row_id' => $newId);
        } else {
            $data['data'] = array('response' => $insertResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);

        break;

    case 2:

        header("Content-type:text/xml");
        print('<menu id="0" >');
        print('<item text="Add Root Item"  img="new.gif"  id="new_parent" />');
        print('<item text="Add Child Item"  img="new.gif"  id="new_child" />');
        print('<item text="Delete Item"  img="deleteall.png"  id="delete"/>');
        print('</menu>');
        break;


    case 3:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        // Get details of selected record.
        $sql = "SELECT parent_id,sort FROM document_toc WHERE id = " . $id;
        $result = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_assoc($result);
        $sortorder = $row['sort'];
        $parent_id = $row['parent_id'];

        $delete = "DELETE FROM document_toc WHERE id = " . $id;
        $deleteResult = mysqli_query($dbc, $delete);
        if ($deleteResult) {
            if ($sortorder > 1) {
                // Update remaining records.
                $sql = "UPDATE document_toc SET sort = sort-1 WHERE parent_id = $parent_id AND sort > $sortorder";
                $updated = mysqli_query($dbc, $sql);
            }
            $data['data'] = array('response' => $deleteResult, 'text' => 'Successfully Deleted');
        } else {
            $data['data'] = array('response' => $deleteResult, 'text' => 'An Error Occured While Deleting');
        }
        echo json_encode($data);
        break;

    case 4:

        $id = filter_input(INPUT_GET, 'doc_id', FILTER_SANITIZE_NUMBER_INT);
        header('Content-type:text/xml');
        echo '<?xml version="1.0"?>' . PHP_EOL;
        echo '<rows>';
        elementsTreeGridXML($id);
        echo '</rows>';
        break;

    case 5:
        $parentid = filter_input(INPUT_GET, 'parentId');
        $parentfield = 'parent_id';
        $itemid = filter_input(INPUT_GET, 'itemId');
        $itemidfield = 'id';
        $sortid = filter_input(INPUT_GET, 'sortId');
        $sortfield = 'sort';
        $table = 'document_toc';
        $direction = filter_input(INPUT_GET, 'direction');

        echo json_encode(moveItemUpDownGrid($parentid, $parentfield, $itemid, $itemidfield, $sortid, $sortfield, $table, $direction));
        break;

    case 6:

        //update report document
        $id = filter_input(INPUT_POST, 'id');
        $doc_id = filter_input(INPUT_POST, 'doc_id');

        $content = filter_input(INPUT_POST, 'notes');
        $userlggd = filter_input(INPUT_POST, 'eid', FILTER_SANITIZE_NUMBER_INT);
        $content = mysqli_real_escape_string($dbc, $content);

        if ($id == -1) {
            $sql = "UPDATE tradestar_reports SET Report_Body = '" . $content . "' WHERE Report_ID =" . $doc_id;
            if (mysqli_query($dbc, $sql)) {
                setArchive($doc_id, $content, $userlggd);
                $msg = "Successfully saved!";
            } else {
                $msg = "Error : " . mysqli_error($dbc);
            }
        } else {
            $sql = "UPDATE document_toc SET content = '" . $content . "' WHERE id =" . $id;
            if (mysqli_query($dbc, $sql)) {
                setTocArchive($id, $content, $userlggd);
                $msg = "Successfully saved!";
            } else {
                $msg = "Error : " . mysqli_error($dbc);
            }
        }

        echo json_encode(array("message" => $msg));
        break;

    case 7:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $doc_id = filter_input(INPUT_GET, 'doc_id', FILTER_SANITIZE_NUMBER_INT);

        if ($id == -1) {
            $result = mysqli_query($dbc, "SELECT Report_Body FROM tradestar_reports WHERE Report_ID=" . $doc_id);
            $row = mysqli_fetch_array($result);
        } else {
            $result = mysqli_query($dbc, "SELECT content FROM document_toc WHERE id =" . $id);
            $row = mysqli_fetch_array($result);
        }

        $content = $row[0];
        $image_path = "http://localhost";

        //format article text
        $content = str_replace('"../../Controller/files', '"' . $image_path . '/projects_new/Controller/files', $content);
        $content = str_replace('"../userfiles', '"' . $image_path . '/userfiles', $content);
//        $content = str_replace("../video", $image_path . "/video", $content);
//        $content = str_replace("../nts_admin", $image_path . "/nts_admin", $content);
//        $content = str_replace("tinymce/jscripts", $image_path . "/script/tinymce/jscripts", $content);

        echo json_encode(array("content" => $content));
        break;

    case 8:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $goal = filter_input(INPUT_POST, 'goal');
        $scope = filter_input(INPUT_POST, 'scope');
        $supervisor = filter_input(INPUT_POST, 'supervisor', FILTER_SANITIZE_NUMBER_INT);
        $employee = filter_input(INPUT_POST, 'employee', FILTER_SANITIZE_NUMBER_INT);
        $category = filter_input(INPUT_POST, 'category');
        $frequency = filter_input(INPUT_POST, 'doc_frequency');
        $doc_input = filter_input(INPUT_POST, 'doc_input');
        $doc_output = filter_input(INPUT_POST, 'doc_output');
        $explorer_id = filter_input(INPUT_POST, 'explorer_id', FILTER_SANITIZE_NUMBER_INT);
        $explorer_id = $explorer_id ? $explorer_id : 'NULL';
        $frequency = $frequency ? $frequency : 'NULL';

        $update = "UPDATE tradestar_reports SET goal='$goal',scope='$scope',supervisor='$supervisor',doc_input='$doc_input',doc_output='$doc_output',explorer_id='$explorer_id',doc_frequency='$frequency',category_id='$category' WHERE Report_ID=" . $id;

        $updateResult = mysqli_query($dbc, $update);

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Updating');
        }
        echo json_encode($data);
        break;

    case 9:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT explorer_id,`goal`,`scope`,`supervisor`,doc_input,doc_output,doc_frequency,category_id
        FROM
	tradestar_reports
        WHERE Report_ID = " . $id;
        $result = mysqli_query($dbc, $query);
        $row = mysqli_fetch_assoc($result);

        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<data>';
        echo "<goal><![CDATA[" . $row['goal'] . "]]></goal>";
        echo "<scope><![CDATA[" . $row['scope'] . "]]></scope>";
        echo "<supervisor><![CDATA[" . $row['supervisor'] . "]]></supervisor>";
        echo "<category><![CDATA[" . $row['category_id'] . "]]></category>";
        echo "<doc_input><![CDATA[" . $row['doc_input'] . "]]></doc_input>";
        echo "<doc_output><![CDATA[" . $row['doc_output'] . "]]></doc_output>";
        echo "<doc_frequency><![CDATA[" . $row['doc_frequency'] . "]]></doc_frequency>";
        echo "<explorer_id><![CDATA[" . $row['explorer_id'] . "]]></explorer_id>";
        echo '</data>';
        break;

    case 10:
        $index = filter_input(INPUT_POST, 'index');
        $fieldvalue = filter_input(INPUT_POST, 'fieldvalue');
        $id = filter_input(INPUT_POST, 'id');
        $field = filter_input(INPUT_POST, 'colId');
        $colType = filter_input(INPUT_POST, 'colType');
//        $fieldvalue = mysqli_real_escape_string($dbc,$fieldvalue);

        $updateResult = updateSQL("document_toc", $field, $fieldvalue, $id, "id", $colType);

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);

        break;

    case 11:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $query = "
            SELECT
                    document_toc.title,
                    document_toc.topics,
                    document_toc.comments,
                    document_toc.`status`,
                    '' date_edited,
                    c2.contact_attendent author
            FROM
                    nts_site.document_toc
            JOIN document_toc_history history ON history.toc_id = document_toc.id
            JOIN relation_contact c2 ON c2.contact_id = history.employee_id
            WHERE
                    document_toc.id = " . $id;

        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
        $row = mysqli_fetch_assoc($result);

        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<data>';
        echo "<title><![CDATA[" . $row['title'] . "]]></title>";
        echo "<topics><![CDATA[" . $row['topics'] . "]]></topics>";
        echo "<comments><![CDATA[" . $row['comments'] . "]]></comments>";
        echo "<status><![CDATA[" . $row['status'] . "]]></status>";
        echo "<chapter_date><![CDATA[" . $row['date_edited'] . "]]></chapter_date>";
        echo "<chapter_author><![CDATA[" . $row['author'] . "]]></chapter_author>";
        echo '</data>';
        break;


    case 12:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $title = filter_input(INPUT_POST, 'title');
        $topics = filter_input(INPUT_POST, 'topics');
        $comments = filter_input(INPUT_POST, 'comments');
        $status = filter_input(INPUT_POST, 'status');

        $update = "UPDATE document_toc SET title='$title',topics='$topics',comments='$comments',`status`='$status' WHERE id=" . $id;

        $updateResult = mysqli_query($dbc, $update);

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Deleted');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Deleting');
        }
        echo json_encode($data);
        break;

    case 13:

        $sId = filter_input(INPUT_GET, 'sId', FILTER_SANITIZE_NUMBER_INT);
        $tId = filter_input(INPUT_GET, 'tId', FILTER_SANITIZE_NUMBER_INT);
        $docId = filter_input(INPUT_GET, 'doc_id', FILTER_SANITIZE_NUMBER_INT);

        // Get details of selected record.
        $sql = "SELECT * FROM document_toc WHERE id = " . $sId;
        $result = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_assoc($result);
        $sortorder = $row['sort'];
        $parent = $row['parent_id'];


        $result = mysqli_query($dbc, "SELECT IF((MAX(sort)>0),MAX(sort)+1,1)sort FROM document_toc WHERE parent_id =" . $tId . " AND doc_id=" . $docId);
        $row = mysqli_fetch_array($result);
        $sort_id = $row[0];

        $update = "UPDATE document_toc SET parent_id=" . $tId . ",sort=" . $sort_id . " WHERE id = " . $sId;
        $updateResult = mysqli_query($dbc, $update);
        if ($updateResult) {
            if ($sortorder > 1) {
                // Update remaining records.
                $sql = "UPDATE document_toc SET sort = sort-1 WHERE sort > $sortorder AND parent_id = " . $parent . " AND doc_id=" . $docId;
                $updated = mysqli_query($dbc, $sql);
            }
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Moved');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Deleting');
        }

        echo json_encode($data);
        break;

    case 14:

        $docId = filter_input(INPUT_POST, 'docId', FILTER_SANITIZE_NUMBER_INT);
        $employeeId = filter_input(INPUT_POST, 'employeeId', FILTER_SANITIZE_NUMBER_INT);
        $fieldvalue = filter_input(INPUT_POST, 'nValue');

        switch ($fieldvalue) {
            case 0:
                $query = "DELETE FROM tradestar_reports_to_employees WHERE report_id =" . $docId . " AND employee_id = " . $employeeId;
                break;
            case 1:
                $query = "INSERT INTO tradestar_reports_to_employees (report_id,employee_id) VALUES ($docId,$employeeId)";
                break;
            default :
                $query = "DELETE FROM tradestar_reports_to_employees WHERE report_id =" . $docId . " AND employee_id = " . $employeeId;
                break;
        }

        $updateResult = mysqli_query($dbc, $query);
        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);
        break;

    case 15:

        $docId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $query = "SELECT employee_id,CONCAT(COALESCE(FirstName,''),' ',COALESCE(SecondName,''),' ',COALESCE(LastName,'')) employee_name FROM `tradestar_reports_to_employees` JOIN trainees ON trainees.ID = tradestar_reports_to_employees.employee_id WHERE tradestar_reports_to_employees.report_id =" . $docId;
        $result = mysqli_query($dbc, $query);
        while ($row = mysqli_fetch_array($result)) {
            $values[] = array('id' => $row["employee_id"], "name" => $row["employee_name"]);
        }
        echo json_encode(array('options' => $values));
        break;

    case 16:

        $docId = filter_input(INPUT_GET, 'doc_id', FILTER_SANITIZE_NUMBER_INT);
        $projectId = filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT);
        $tocId = filter_input(INPUT_GET, 'toc_id', FILTER_SANITIZE_NUMBER_INT);
        $uID = filter_input(INPUT_GET, 'eid', FILTER_SANITIZE_NUMBER_INT);

        $query = "SELECT document_toc.title chapter,Report_Subject,tradestar_reports.Report_Employee_ID FROM document_toc JOIN tradestar_reports ON tradestar_reports.Report_ID = document_toc.doc_id WHERE document_toc.id =" . $tocId;
        $result = mysqli_query($dbc, $query);
        $row = mysqli_fetch_assoc($result);
        $rptSubject = $row['Report_Subject'];
        $Report_Employee_ID = $row['Report_Employee_ID'];
        $chapter = $row['chapter'];

        if ($Report_Employee_ID > 0) {

            $result = mysqli_query($dbc, "SELECT CONCAT(COALESCE(FirstName,''),' ',COALESCE(SecondName,''),' ',COALESCE(LastName,'')) employee FROM nts_site.trainees WHERE ID = " . $Report_Employee_ID);
            $row = mysqli_fetch_array($result);
            $assigned_eid = $row[0];
        }

        $startDate = date("Y-m-d H:i:s", mktime(9, 00, 0, date('n'), date('j') + 1, date('Y')));
        $endDate = date("Y-m-d H:i:s", mktime(17, 30, 0, date('n'), date('j') + 1, date('Y')));


        $no = generateProjectId($projectId);
        $prct_details = '[' . $no . '/' . $docId . ']' . ' ' . $rptSubject . ' |' . $chapter;
        $insert = "INSERT INTO events(`event_name`,`details`,`start_date`,`end_date`,`entered_by`,`cat_id`,`event_pid`,`visible`,`completed`,`masterrecord`,`employee_id`,`assigned_eid`,toc_id,doc_id)
                            VALUES
                            ('$prct_details','" . $no . "| " . $rptSubject . "| " . $chapter . "','" . $startDate . "','" . $endDate . "',$uID,1,null,1,0,'" . $projectId . "','" . (($Report_Employee_ID > 0) ? $Report_Employee_ID : "NULL") . "','" . (($Report_Employee_ID > 0) ? mysqli_real_escape_string($dbc, $assigned_eid) : "NULL") . "',$tocId,$docId)";

        $insertTOEvents = mysqli_query($dbc, $insert) or die(mysqli_error($dbc) . $insert);
        if ($insertTOEvents) {
            $eventId = mysqli_insert_id($dbc);
//            mysqli_query($dbc,"INSERT INTO document_toc_plan(`project_id`,report_id,toc_id,`event_id`) VALUES('" . $projectId . "',$docId,$tocId,'" . $eventId . "')") ;
            mysqli_query($dbc, "INSERT INTO projects_planning(`parent`,`event_id`) VALUES('" . $projectId . "','" . $eventId . "')");
            $data['data'] = array('response' => $insertTOEvents, 'newId' => $eventId, 'text' => 'Successfully Added');
        } else {
            $data['data'] = array('response' => $insertTOEvents, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);

        break;

    case 17:

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
                        events e
                WHERE
                        e.toc_id = " . $id . " AND e.is_active=1 AND YEAR(start_date)= YEAR(CURDATE())";

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
            echo "</row>";
        }
        echo "</rows>";

        break;

    case 18:


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
            print("<toc_details><![CDATA[" . $row['details'] . "]]></toc_details>");
            print("<freq>" . $row['freq'] . "</freq>");
            print("<rec_type>" . $row['rec_type'] . "</rec_type>");
            print("<emp>" . $row['employee_id'] . "</emp>");
            print("<toc_info><![CDATA[" . $row['information'] . "]]></toc_info>");
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

    case 19:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $result = mysqli_query($dbc, "SELECT content FROM document_toc_history WHERE id=" . $id);
        $row = mysqli_fetch_array($result);
        $content = $row[0];

        echo json_encode(array("content" => $content));
        break;

    case 20:
        switch ($_GET['case']) {
            case 1:
                $QRYDEL = "DELETE FROM document_toc_history WHERE id = '" . $_GET['id'] . "'";
                $deleteResult = mysqli_query($dbc, $QRYDEL);
                if ($deleteResult) {
                    $data['data'] = array('response' => $deleteResult, 'text' => 'Successfully Deleted');
                } else {
                    $data['data'] = array('response' => $deleteResult, 'text' => 'An Error Occured While Deleting');
                }
                break;
            default:
                $QRYDEL = "DELETE FROM document_toc_history WHERE toc_id = '" . $_GET['id'] . "'";
                $deleteResult = mysqli_query($dbc, $QRYDEL);
                if ($deleteResult) {
                    $data['data'] = array('response' => $deleteResult, 'text' => 'Successfully Deleted');
                } else {
                    $data['data'] = array('response' => $deleteResult, 'text' => 'An Error Occured While Deleting');
                }
                break;
        }

        echo json_encode($data);

        break;

    case 21:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $qry = "SELECT
                        th.id,
                        th.toc_id,
                        th.content,
                        th.date_edited,
                        th.employee_id,
                        c2.contact_attendent Author
                FROM
                        document_toc_history th
                LEFT JOIN nts_site.relation_contact c2 ON c2.contact_id = th.employee_id
                WHERE
                        th.toc_id = " . $id . "
                ORDER BY
                        th.date_edited DESC";

        $res = mysqli_query($dbc, $qry) or die(mysqli_error($dbc) . $qry);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        while ($row = mysqli_fetch_array($res)) {

            $content = $row['content'];

            $trim = strip_tags($content);
            $chars = array(" ", "\n", "\t", "&ndash;", "&rsquo;", "&#39;", "&quot;", "&nbsp;");
            $trim = str_replace($chars, '', $trim);

            $totalCharacter = strlen(utf8_decode($trim));
            echo "<row id = '" . $row["id"] . "'>";
            echo "<cell> {$row["id"]} </cell>";
            echo "<cell><![CDATA[" . $row["date_edited"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["Author"] . "]]></cell>";
            echo "<cell><![CDATA[" . $totalCharacter . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 22:

        $query = "
    SELECT
            tr.Report_Subject title,
            tr.explorer_id,
            tr.goal,
            tr.scope,
            (SELECT CONCAT(COALESCE(FirstName, ''),' ',COALESCE(SecondName, ''),' ',COALESCE(LastName, '')) FROM nts_site.trainees WHERE ID = tr.supervisor)`supervisor`,
            tr.doc_input,
            tr.doc_output,
            trf.description doc_frequency,
            (SELECT GROUP_CONCAT(CONCAT(COALESCE(FirstName, ''))) FROM nts_site.trainees WHERE ID IN(SELECT employee_id FROM `tradestar_reports_to_employees` WHERE `report_id` = $id ))employees
    FROM
            tradestar_reports tr
    LEFT JOIN tradestar_report_frequency trf ON trf.id = tr.doc_frequency          
    WHERE
            tr.Report_ID = " . $id;
        $result = mysqli_query($dbc, $query);
        $row = mysqli_fetch_assoc($result);

        $doc_details = '<h4>Document Details</h4>
            <table class="table table-bordered table-condensed" style="width:40%">
            <tbody>
            <tr>
            <td class="col-md-2">Goal</td>
            <td>' . $row['goal'] . '</td>
            </tr>
            <tr>
            <td class="col-md-2">Scope</td>
            <td>' . $row['scope'] . '</td>
            </tr>
            <tr>
            <td class="col-md-2">Supervisor</td>
            <td>' . $row['supervisor'] . '</td>
            </tr>
            <tr>
            <td class="col-md-2">Employee</td>
            <td>' . $row['employees'] . '</td>
            </tr>
            <tr>
            <td class="col-md-2">Frequency</td>
            <td>' . $row['doc_frequency'] . '</td>
            </tr>
            <tr>
            <td class="col-md-2">Input</td>
            <td>' . $row['doc_input'] . '</td>
            </tr>
            <tr>
            <td class="col-md-2">Output</td>
            <td>' . $row['doc_output'] . '</td>
            </tr>
            <tr>
            <td class="col-md-2">Procedures</td>
            <td>' . $row['explorer_id'] . '</td>
            </tr>
            </tbody>
            </table>
            <h4>Table of Content</h4>';

        $toc = '';
        $doc_str = '';
        $doc_str = generateDocument($id, $doc_str);

        $full_document = $doc_details . $toc . $doc_str;

        $insert = "INSERT INTO document_toc_content (document_id,document_content) VALUES (" . $id . ",'" . mysqli_real_escape_string($dbc, $full_document) . "')";
        $insertResult = mysqli_query($dbc, $insert);

        if ($insertResult) {
            $data['data'] = array('response' => $insertResult, 'text' => 'Successfully Saved');
        } else {
            $data['data'] = array('response' => $insertResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);


        break;

    case 23:

        $query = "SELECT Branch_ID,Branch_Name FROM branch WHERE visible_in_projects = 1 ORDER BY Branch_ID";
        $result = mysqli_query($dbc, $query);
        header("Content-type:text/xml");
        print('<?xml version="1.0" encoding="utf-8"?>');
        echo "<complete>";
        while ($row = mysqli_fetch_array($result)) {
            echo "<option value='" . $row['Branch_ID'] . "'><![CDATA[" . $row['Branch_Name'] . "]]></option>";
        }
        echo "</complete>";

        break;

    case 24:

        $docId = filter_input(INPUT_POST, 'docId', FILTER_SANITIZE_NUMBER_INT);
        $branchId = filter_input(INPUT_POST, 'branchId', FILTER_SANITIZE_NUMBER_INT);
        $fieldvalue = filter_input(INPUT_POST, 'nValue');

        if ($fieldvalue == '1') {

            $query = "INSERT INTO document_to_branch (document_id,branch_id) VALUES ($docId,$branchId)";
        } else {
            $query = "DELETE FROM document_to_branch WHERE document_id =" . $docId . " AND branch_id = " . $branchId;
        }

        $updateResult = mysqli_query($dbc, $query);
        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);
        break;

    case 25:

        $docId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $query = "SELECT branch.Branch_ID,branch.Branch_Name FROM `document_to_branch` JOIN branch ON branch.Branch_ID = document_to_branch.branch_id WHERE document_to_branch.document_id =" . $docId;
        $result = mysqli_query($dbc, $query);
        while ($row = mysqli_fetch_array($result)) {
            $values[] = array('id' => $row["Branch_ID"], "name" => $row["Branch_Name"]);
        }
        echo json_encode(array('options' => $values));
        break;

    case 26:

        $docId = filter_input(INPUT_GET, 'document_id', FILTER_SANITIZE_NUMBER_INT);

        $query = "SELECT branch.Branch_ID,Branch_Name,";
        $query .= $docId > 0 ? "(IF(ISNULL(document_to_branch.id),NULL,1)) is_assigned" : "NULL is_assisgned";
        $query .= "  FROM branch";
        $query .= $docId > 0 ? " LEFT JOIN document_to_branch ON branch.Branch_ID = document_to_branch.branch_id  AND document_to_branch.document_id = " . $docId : "";
        $query .= "    WHERE
                    branch.visible_in_projects = 1
            ORDER BY
                    Branch_ID";

        $result = mysqli_query($dbc, $query);

        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<rows>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<row id = '" . $row['Branch_ID'] . "'>";
            echo "<cell><![CDATA[" . $row["is_assigned"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["Branch_Name"] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";

        break;

    case 27:

        $fileId = filter_input(INPUT_GET, 'file_id', FILTER_SANITIZE_NUMBER_INT);

        $query = "SELECT branch.Branch_ID,Branch_Name,";
        $query .= $fileId > 0 ? "(IF(ISNULL(projects_uploads_to_branch.id),NULL,1)) is_assigned" : "NULL is_assisgned";
        $query .= "  FROM branch";
        $query .= $fileId > 0 ? " LEFT JOIN projects_uploads_to_branch ON branch.Branch_ID = projects_uploads_to_branch.branch_id  AND projects_uploads_to_branch.file_id = " . $fileId : "";
        $query .= "    WHERE
                    branch.visible_in_projects = 1
            ORDER BY
                    Branch_ID";

        $result = mysqli_query($dbc, $query);

        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<rows>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<row id = '" . $row['Branch_ID'] . "'>";
            echo "<cell><![CDATA[" . $row["is_assigned"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["Branch_Name"] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";

        break;

    case 28:

        $fileId = filter_input(INPUT_POST, 'fileId', FILTER_SANITIZE_NUMBER_INT);
        $branchId = filter_input(INPUT_POST, 'branchId', FILTER_SANITIZE_NUMBER_INT);
        $fieldvalue = filter_input(INPUT_POST, 'nValue');

        if ($fieldvalue == '1') {

            $query = "INSERT INTO projects_uploads_to_branch (file_id,branch_id) VALUES ($fileId,$branchId)";
        } else {
            $query = "DELETE FROM projects_uploads_to_branch WHERE file_id =" . $fileId . " AND branch_id = " . $branchId;
        }

        $updateResult = mysqli_query($dbc, $query);
        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);
        break;

    case 29:

        $docId = filter_input(INPUT_GET, 'document_id', FILTER_SANITIZE_NUMBER_INT);

        $query = "SELECT branch_id FROM document_to_branch WHERE document_id =" . $docId;
        $result = mysqli_query($dbc, $query);

        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            $values = array('id' => $row["branch_id"], "response" => true);
        } else {
            $values = array("response" => false);
        }

        echo json_encode($values);
        break;

    case 30:

        $fileId = filter_input(INPUT_GET, 'file_id', FILTER_SANITIZE_NUMBER_INT);

        $query = "SELECT branch_id FROM projects_uploads_to_branch WHERE file_id =" . $fileId;
        $result = mysqli_query($dbc, $query);

        if (mysqli_num_rows($result) === 1) {
            $values = array('id' => $row["branch_id"], "response" => true);
        } else {
            $values = array("response" => false);
        }

        echo json_encode($values);
        break;

    case 31:

        $eid = intval($_POST["eid"]);
        $chapter_id = intval($_POST["chapter_id"]);

        mysqli_query($dbc, "INSERT INTO documents_comments (comment_date,eid,chapter_id) VALUES (NOW(),$eid,$chapter_id)");

        if (!mysqli_error($dbc))
            $data['data'] = array('success' => true, 'id' => mysqli_insert_id($dbc));
        else
            $data['data'] = array('success' => false, 'message' => mysqli_error($dbc));

        echo json_encode($data);

        break;

    case 32:

        $chapter_id = intval($_GET["id"]);

        $query = "SELECT
            id,
            comment_date,
            eid,
            details
        FROM
            documents_comments
        WHERE
            chapter_id = $chapter_id
        ORDER BY  
            id";

        $result = mysqli_query($dbc, $query);

        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<rows>";

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<row id = '" . $row['id'] . "'>";
            echo "<cell><![CDATA[" . $row["id"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["comment_date"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["eid"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["details"] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";

        break;

    case 33:

        $comment_id = intval($_POST["comment_id"]);

        mysqli_query($dbc, "DELETE FROM documents_comments WHERE id = " . $comment_id);

        if (!mysqli_error($dbc))
            $data['data'] = array('success' => true);
        else
            $data['data'] = array('success' => false, 'message' => mysqli_error($dbc));

        echo json_encode($data);

        break;

    case 34:
        $index = filter_input(INPUT_POST, 'index');
        $fieldvalue = filter_input(INPUT_POST, 'fieldvalue');
        $id = filter_input(INPUT_POST, 'id');
        $field = filter_input(INPUT_POST, 'colId');
        $colType = filter_input(INPUT_POST, 'colType');
        $fieldvalue = mysqli_real_escape_string($dbc, $fieldvalue);
        $table = filter_input(INPUT_POST, 'table');

        $updateResult = updateSQL($table, $field, $fieldvalue, $id, "id", $colType);

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);

        break;
}

function generateDocument($id, $doc_str)
{
    global $toc;
    global $dbc;
    $query = "SELECT
                        id,
                        parent_id,
                        title,
                        sort,
                        content
                FROM
                        document_toc
                WHERE
                        doc_id = " . $id . "
                ORDER BY
                        parent_id = 0 DESC,
                        sort ASC";
    $result = mysqli_query($dbc, $query);
    $objects = array();
    $roots = array();
    while ($row = mysqli_fetch_assoc($result)) {
        if (!isset($objects[$row['id']])) {
            $objects[$row['id']] = new stdClass;
            $objects[$row['id']]->children = array();
        }

        $obj = $objects[$row['id']];
        $obj->id = $row['id'];
        $obj->title = $row['title'];
        $obj->sort = $row['sort'];
        $obj->content = $row['content'];

        if ($row['parent_id'] == 0) {
            $roots[] = $obj;
        } else {
            if (!isset($objects[$row['parent_id']])) {
                $objects[$row['parent_id']] = new stdClass;
                $objects[$row['parent_id']]->children = array();
            }

            $objects[$row['parent_id']]->children[$row['id']] = $obj;
        }
    }

    foreach ($roots as $obj) {
        $doc_str = printDocument($obj, $doc_str, '', true);
    }

    return $doc_str;
}

function printDocument(stdClass $obj, $doc_str, $chapter, $isRoot = false)
{
    global $toc;
    if ($isRoot) {
        $link = $obj->sort;
        $chapter = $obj->sort . '.';
        $toc .= '<a href="#C' . $link . '">' . $chapter . ' ' . $obj->title . '</a><br />';
        $doc_str .= '<h4 id="C' . $link . '">' . $chapter . ' ' . $obj->title . '</h4>';
    } else {
        $link = '_' . $obj->sort;
        $chapter .= $obj->sort . '.';
        $toc .= '<a href="#C' . $link . '">' . $chapter . ' ' . $obj->title . '</a><br />';
        $doc_str .= '<h5 id="C' . $link . '">' . $chapter . ' ' . $obj->title . '</h5>';
    }

    $doc_str .= $obj->content;
    $doc_str .= '<br />';

    foreach ($obj->children as $child) {
        $doc_str = printDocument($child, $doc_str, $chapter);
    }

    return $doc_str;
}

function setTocArchive($toc_id, $content, $employee_id)
{
    global $dbc;
    $date = new DateTime();
    $today = $date->format('Y-m-d H:i:s');

    $insert = "INSERT INTO document_toc_history (`toc_id`,`employee_id`,`date_edited`,`content`) VALUES (" . $toc_id . "," . $employee_id . ",now(),'" . $content . "')";

    $insertResult = mysqli_query($dbc, $insert);

    //update time of report on changes
    $sql = "UPDATE tradestar_reports SET Report_Date = '$today' WHERE Report_ID = (SELECT doc_id FROM document_toc WHERE id =$toc_id)";
    $res = mysqli_query($dbc, $sql) or die(mysqli_error($dbc) . " INSERT TRADESTAR HISTORY ERROR");

    $result = mysqli_query($dbc, "SELECT id FROM document_toc_history WHERE toc_id = " . $toc_id . " ORDER BY id DESC LIMIT 29,1");
    if (mysqli_num_rows($result) > 0) {
        $fetch = mysqli_fetch_assoc($result);
        mysqli_query($dbc, "DELETE FROM document_toc_history WHERE id <" . $fetch['id'] . " AND toc_id = " . $toc_id);
    }
}

function setArchive($tradestar_report_id, $report_editor, $userlggd)
{
    global $dbc;
    $date = new DateTime();
    $today = $date->format('Y-m-d H:i:s');
//get author of the already logged
    $author = $userlggd;
    if ($author == null) {
        $author = 'NULL';
    }
    $category = getTableDetailField("tradestar_reports", $tradestar_report_id, "Report_ID", "Report_Category");
    $subject = getTableDetailField("tradestar_reports", $tradestar_report_id, "Report_ID", "Report_Subject");
    $rptCategory = getTableDetailField("tradestar_reports", $tradestar_report_id, "Report_ID", "PrId");
//    $report_editor = mysqli_real_escape_string($dbc,$report_editor);
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

function elementsTreeGridXML($id)
{
    global $dbc;

    $query = "
        SELECT
                toc.id,
                toc.parent_id,
                toc.title,
                toc.sort,
                toc.topics,
                toc.`status`,
                toc.comments,
                history.date_edited date_edited,
                c2.contact_attendent author,
                toc.`visible`
        FROM
                document_toc toc
        LEFT JOIN document_toc_history history ON(history.toc_id = toc.id)
        AND history.date_edited =(
                SELECT
                        MAX(date_edited)
                FROM
                        document_toc_history history2
                WHERE
                        history2.toc_id = toc.id
        )
        LEFT JOIN relation_contact c2 ON c2.contact_id = history.employee_id
        WHERE
                toc.doc_id = $id       
        ORDER BY
                toc.parent_id = 0 DESC,
                toc.sort ASC";
    $result = mysqli_query($dbc, $query);
    $objects = array();
    $roots = array();
    while ($row = mysqli_fetch_assoc($result)) {
        if (!isset($objects[$row['id']])) {
            $objects[$row['id']] = new stdClass;
            $objects[$row['id']]->children = array();
        }

        $obj = $objects[$row['id']];
        $obj->id = $row['id'];
        $obj->title = $row['title'];
        $obj->sort = $row['sort'];
        $obj->topics = $row['topics'];
        $obj->status = $row['status'];
        $obj->comments = $row['comments'];
        $obj->date = $row['Report_Date'];
        $obj->employee = $row['employee'];
        $obj->visible = $row['visible'];


        if ($row['parent_id'] == 0) {
            $roots[] = $obj;
        } else {
            if (!isset($objects[$row['parent_id']])) {
                $objects[$row['parent_id']] = new stdClass;
                $objects[$row['parent_id']]->children = array();
            }

            $objects[$row['parent_id']]->children[$row['id']] = $obj;
        }
    }

    echo '<row id="-1"><cell>0</cell><cell image="blank.gif">Old Document</cell></row>';

//    print '<pre>';
//    print_r($objects);exit;
    foreach ($roots as $obj) {
        printElementsTreeGridXML($obj, '', true);
    }
}

function printElementsTreeGridXML(stdClass $obj, $chapter, $isRoot = false)
{

    if ($isRoot) {
        $chapter = $obj->sort;
    } else {
        $chapter .= '.' . $obj->sort;
    }

    echo '<row id="' . $obj->id . '">';
    echo '<cell>' . $chapter . '</cell>';
    if (count($obj->children) == 0) {
        echo "<cell image=\"blank.gif\"><![CDATA[" . $obj->title . "]]></cell>";
    } else {
        echo "<cell image=\"folder.gif\"><![CDATA[" . $obj->title . "]]></cell>";
    }
    echo "<cell><![CDATA[" . $obj->sort . "]]></cell>";
    echo "<cell><![CDATA[" . $obj->topics . "]]></cell>";
    echo "<cell><![CDATA[" . $obj->comments . "]]></cell>";
    echo "<cell><![CDATA[" . $obj->date . "]]></cell>";
    echo "<cell><![CDATA[" . $obj->employee . "]]></cell>";
    echo "<cell><![CDATA[" . $obj->status . "]]></cell>";
    echo "<cell><![CDATA[" . $obj->visible . "]]></cell>";

    foreach ($obj->children as $child) {
        printElementsTreeGridXML($child, $chapter);
    }
    echo '</row>';
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
