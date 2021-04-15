<?php

include_once '../../../config.php';
include("GeneralFunctions.php");

$action = $_GET['action'];

switch ($action) {

    default:
        break;

    case 1:
        $parentId = filter_input(INPUT_GET, 'parent_id', FILTER_SANITIZE_NUMBER_INT);
        $parentId = $parentId ? $parentId : '0';

        $insert = "INSERT INTO libraries (`parent_id`,`sort`) SELECT " . $parentId . ",IF((MAX(sort)>0),MAX(sort)+1,1)sort FROM libraries WHERE parent_id = " . $parentId;

        $insertResult = mysqli_query($dbc,$insert) ;
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
        break;

    case 3:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        // Get details of selected record.
        $sql = "SELECT parent_id,sort FROM libraries WHERE id = " . $id;
        $result = mysqli_query($dbc,$sql);
        $row = mysqli_fetch_assoc($result);
        $sortorder = $row['sort'];
        $parent_id = $row['parent_id'];

        $delete = "DELETE FROM libraries WHERE id = " . $id;
        $deleteResult = mysqli_query($dbc,$delete);
        if ($deleteResult) {
            if ($sortorder > 1) {
                // Update remaining records.
                $sql = "UPDATE libraries SET sort = sort-1 WHERE parent_id = $parent_id AND doc_id = $docId AND sort > $sortorder";
                $updated = mysqli_query($dbc,$sql);
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
        elementsTreeGridXML();
        echo '</rows>';
        break;

    case 5:
        $parentid = filter_input(INPUT_GET, 'parentId');
        $parentfield = 'parent_id';
        $itemid = filter_input(INPUT_GET, 'itemId');
        $itemidfield = 'id';
        $sortid = filter_input(INPUT_GET, 'sortId');
        $sortfield = 'sort';
        $table = 'libraries';
        $direction = filter_input(INPUT_GET, 'direction');

        echo json_encode(moveItemUpDownGrid($parentid, $parentfield, $itemid, $itemidfield, $sortid, $sortfield, $table, $direction));
        break;

    case 6:

        //update report document
        $id = filter_input(INPUT_POST, 'id');
        $parentId = filter_input(INPUT_POST, 'parent_id');
        $content = filter_input(INPUT_POST, 'notes');
        $userlggd = filter_input(INPUT_POST, 'eid', FILTER_SANITIZE_NUMBER_INT);
        $content = mysqli_real_escape_string($dbc,$content);

        $sql = "UPDATE libraries SET content = '" . $content . "' WHERE id =" . $id;
        if (mysqli_query($dbc,$sql)) {
            setTocArchive($id, $content, $userlggd);
//            $toc = '';
//            saveDocumentation($parentId);
            $msg = "Successfully saved!";
        } else {
            $msg = "Error : " . mysqli_error($dbc);
        }
        echo json_encode(array("message" => $msg));
        break;

    case 7:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $result = mysqli_query($dbc,"SELECT content FROM libraries WHERE id =" . $id);
        $row = mysqli_fetch_array($result);
        $content = $row[0];

        $image_path = "http://213.201.143.89";

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
        $frequency = filter_input(INPUT_POST, 'doc_frequency');
        $doc_input = filter_input(INPUT_POST, 'doc_input');
        $doc_output = filter_input(INPUT_POST, 'doc_output');
        $explorer_id = filter_input(INPUT_POST, 'explorer_id', FILTER_SANITIZE_NUMBER_INT);
        $explorer_id = $explorer_id ? $explorer_id : 'NULL';
        $frequency = $frequency ? $frequency : 'NULL';

        $update = "UPDATE tradestar_reports SET goal='$goal',scope='$scope',supervisor='$supervisor',doc_input='$doc_input',doc_output='$doc_output',explorer_id='$explorer_id',doc_frequency='$frequency' WHERE Report_ID=" . $id;

        $updateResult = mysqli_query($dbc,$update) ;

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Updating');
        }
        echo json_encode($data);
        break;

    case 9:

        $query = "
            SELECT 
                IntranetID,
                CONCAT(COALESCE(FirstName, ''),
                        ' ',
                        COALESCE(SecondName, ''),
                        ' ',
                        COALESCE(LastName, '')) employee
            FROM
                trainees
            WHERE
                status_id = 1
                    AND (IntranetId <> 0
                    || IntranetId IS NOT NULL)
                    AND ID <> 33
                    AND branch_id IS NOT NULL
            ORDER BY ID";
        $result = mysqli_query($dbc,$query) ;
        header("Content-type:text/xml");
        print('<?xml version="1.0" encoding="utf-8"?>');
        echo "<complete>";
        echo "<option value='0'></option>";
        while ($row = mysqli_fetch_array($result)) {
            echo"<option value='" . $row['IntranetID'] . "'><![CDATA[" . $row['employee'] . "]]></option>";
        }
        echo "</complete>";
        break;

    case 10:
        $index = filter_input(INPUT_POST, 'index');
        $fieldvalue = filter_input(INPUT_POST, 'fieldvalue');
        $id = filter_input(INPUT_POST, 'id');
        $field = filter_input(INPUT_POST, 'colId');
        $colType = filter_input(INPUT_POST, 'colType');
//        $fieldvalue = mysqli_real_escape_string($dbc,$fieldvalue);

        $updateResult = updateSQL("libraries", $field, $fieldvalue, $id, "id", $colType);

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
                    libraries.title,
                    libraries.comments,
                    libraries.date_created,
                    libraries.author
                    -- c2.contact_attendent author
            FROM
                    libraries
            WHERE
                    libraries.id = " . $id;
        $result = mysqli_query($dbc,$query) ;
        $row = mysqli_fetch_assoc($result);

        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<data>';
        echo "<title><![CDATA[" . $row['title'] . "]]></title>";
        echo "<comments><![CDATA[" . $row['comments'] . "]]></comments>";
        echo "<date_created><![CDATA[" . $row['date_created'] . "]]></date_created>";
        echo "<author><![CDATA[" . $row['author'] . "]]></author>";
        echo '</data>';
        break;


    case 12:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $title = filter_input(INPUT_POST, 'title');
        $date_created = filter_input(INPUT_POST, 'date_created');
        $comments = filter_input(INPUT_POST, 'comments');
        $author = filter_input(INPUT_POST, 'author');

        $update = "UPDATE libraries SET title='$title',date_created='$date_created',comments='$comments',`author`='$author' WHERE id=" . $id;

        $updateResult = mysqli_query($dbc,$update) ;

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Saved');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Deleting');
        }
        echo json_encode($data);
        break;

    case 13:

        $sId = filter_input(INPUT_GET, 'sId', FILTER_SANITIZE_NUMBER_INT);
        $tId = filter_input(INPUT_GET, 'tId', FILTER_SANITIZE_NUMBER_INT);

        // Get details of selected record.
        $sql = "SELECT * FROM libraries WHERE id = " . $sId;
        $result = mysqli_query($dbc,$sql) ;
        $row = mysqli_fetch_assoc($result);
        $sortorder = $row['sort'];
        $parent = $row['parent_id'];

        $result = mysqli_query($dbc,"SELECT IF((MAX(sort)>0),MAX(sort)+1,1)sort FROM libraries WHERE parent_id =" . $tId);
        $row = mysqli_fetch_array($result);
        $sort_id = $row[0];

        $update = "UPDATE libraries SET parent_id=" . $tId . ",sort=" . $sort_id . " WHERE id = " . $sId;
        $updateResult = mysqli_query($dbc,$update) ;
        if ($updateResult) {
            if ($sortorder > 1) {
                // Update remaining records.
                $sql = "UPDATE libraries SET sort = sort-1 WHERE sort > $sortorder AND parent_id = " . $parent;
                $updated = mysqli_query($dbc,$sql) ;
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

        $updateResult = mysqli_query($dbc,$query) ;
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
        $result = mysqli_query($dbc,$query) ;
        while ($row = mysqli_fetch_array($result)) {
            $values[] = array('id' => $row["employee_id"], "name" => $row["employee_name"]);
        }
        echo json_encode(array('options' => $values));
        break;

    case 16:

        $docId = filter_input(INPUT_GET, 'doc_id', FILTER_SANITIZE_NUMBER_INT);
        $projectId = filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT);
        $libraries_id = filter_input(INPUT_GET, 'libraries_id', FILTER_SANITIZE_NUMBER_INT);
        $uID = filter_input(INPUT_GET, 'eid', FILTER_SANITIZE_NUMBER_INT);

        $rptSubject = 'Libraries';

        $employee_query = mysqli_query($dbc,"SELECT ID FROM trainees WHERE IntranetID = " . $uID);
        $row = mysqli_fetch_array($employee_query);
        $Report_Employee_ID = $row[0];

        $chapter_query = mysqli_query($dbc,"SELECT libraries.title FROM libraries WHERE libraries.id =" . $libraries_id);
        $row = mysqli_fetch_array($chapter_query);
        $chapter = $row[0];

        if ($Report_Employee_ID > 0) {
            $assigned_query = mysqli_query($dbc,"SELECT CONCAT(COALESCE(FirstName,''),' ',COALESCE(SecondName,''),' ',COALESCE(LastName,'')) employee FROM trainees WHERE ID = " . $Report_Employee_ID);
            $row = mysqli_fetch_array($assigned_query);
            $assigned_eid = $row[0];
        }

        $startDate = date("Y-m-d H:i:s", mktime(9, 00, 0, date('n'), date('j') + 1, date('Y')));
        $endDate = date("Y-m-d H:i:s", mktime(17, 30, 0, date('n'), date('j') + 1, date('Y')));

        $no = generateProjectId($projectId);
        $prct_details = '[' . $no . '/' . $docId . ']' . ' ' . $rptSubject . ' |' . $chapter;
        $insert = "INSERT INTO events(`event_name`,`details`,`start_date`,`end_date`,`entered_by`,`cat_id`,`event_pid`,`visible`,`completed`,`masterrecord`,`employee_id`,`assigned_eid`,libraries_id,doc_id)
                            VALUES
                            ('$prct_details','" . $no . "| " . $rptSubject . "| " . $chapter . "','" . $startDate . "','" . $endDate . "',$uID,1,null,1,0,'" . $projectId . "','" . (($Report_Employee_ID > 0) ? $Report_Employee_ID : "NULL") . "','" . (($Report_Employee_ID > 0) ? mysqli_real_escape_string($dbc,$assigned_eid) : "NULL") . "',$libraries_id,$docId)";

        $insertTOEvents = mysqli_query($dbc,$insert)or die(mysqli_error($dbc) . $insert);
        if ($insertTOEvents) {
            $eventId = mysqli_insert_id($dbc);
//            mysqli_query($dbc,"INSERT INTO libraries_plan(`project_id`,report_id,libraries_id,`event_id`) VALUES('" . $projectId . "',$docId,$tocId,'" . $eventId . "')") ;
            mysqli_query($dbc,"INSERT INTO projects_planning(`parent`,`event_id`) VALUES('" . $projectId . "','" . $eventId . "')") ;
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
                        e.libraries_id = " . $id . " AND e.is_active=1 AND YEAR(start_date)= YEAR(CURDATE())";

        $res = mysqli_query($dbc,$qry) or die(mysqli_error($dbc) . $qry);
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

        $res = mysqli_query($dbc,$query) or die(mysqli_error($dbc) . $query);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        print("<data>");

        while ($row = mysqli_fetch_assoc($res)) {
            print("<event_id>" . $row['event_id'] . "</event_id>");
            print("<event_name><![CDATA[" . $row['event_name'] . "]]></event_name>");
            print("<variable><![CDATA[" . $row['variable'] . "]]></variable>");
            print("<event_name_child><![CDATA[" . $row['event_name_child'] . "]]></event_name_child>");
            print("<libraries_details><![CDATA[" . $row['details'] . "]]></libraries_details>");
            print("<freq>" . $row['freq'] . "</freq>");
            print("<rec_type>" . $row['rec_type'] . "</rec_type>");
            print("<emp>" . $row['employee_id'] . "</emp>");
            print("<info><![CDATA[" . $row['information'] . "]]></info>");
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


        $result = mysqli_query($dbc,"SELECT content FROM libraries_history WHERE id=" . $id);
        $row = mysqli_fetch_array($result);
        $content = $row[0];

        echo json_encode(array("content" => $content));
        break;

    case 20:
        switch ($_GET['case']) {
            case 1:
                $QRYDEL = "DELETE FROM libraries_history WHERE id = '" . $_GET['id'] . "'";
                $deleteResult = mysqli_query($dbc,$QRYDEL) ;
                if ($deleteResult) {
                    $data['data'] = array('response' => $deleteResult, 'text' => 'Successfully Deleted');
                } else {
                    $data['data'] = array('response' => $deleteResult, 'text' => 'An Error Occured While Deleting');
                }
                break;
            default:
                $QRYDEL = "DELETE FROM libraries_history WHERE libraries_id = '" . $_GET['id'] . "'";
                $deleteResult = mysqli_query($dbc,$QRYDEL) ;
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
                        th.libraries_id,
                        th.content,
                        th.date_edited,
                        th.employee_id,
                        c2.contact_attendent Author
                FROM
                        libraries_history th
                LEFT JOIN relation_contact c2 ON c2.contact_id = th.employee_id
                WHERE
                        th.libraries_id = " . $id . "
                ORDER BY
                        th.date_edited DESC";

        $res = mysqli_query($dbc,$qry) or die(mysqli_error($dbc) . $qry);
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
        $toc = '';
        saveDocumentation();
        echo json_encode($data);
        break;
}

function saveDocumentation() {
    global $toc;
    $heading = '<h4>Table of Content</h4>';
    $doc_str = generateDocument($id, $doc_str = '');

    $full_document = $heading . $toc . $doc_str;

    $insert = "INSERT INTO libraries_content (`id`,`content`) VALUES (" . $id . ",'" . mysqli_real_escape_string($dbc,$full_document) . "') ON DUPLICATE KEY UPDATE content=VALUES(`content`)";
    $insertResult = mysqli_query($dbc,$insert) ;

    if ($insertResult) {
        $data['data'] = array('response' => $insertResult, 'text' => 'Successfully Saved');
    } else {
        $data['data'] = array('response' => $insertResult, 'text' => 'An Error Occured While Saving');
    }
//    echo json_encode($data);
}

/*
  function generateDocument($doc_str) {
  global $toc;
  $query = "SELECT
  id,
  parent_id,
  title,
  sort,
  content
  FROM
  libraries
  WHERE
  parent_id = " . $id . "
  ORDER BY
  sort ASC";
  $result = mysqli_query($dbc,$query) ;
  while ($row = mysqli_fetch_assoc($result)) {
  $link = $row['sort'];
  $chapter = $row['sort'] . '.';
  $toc .= '<a href = "#C' . $link . '">' . $chapter . ' ' . $row['title'] . '</a><br />';
  $doc_str .= '<h4 id = "C' . $link . '">' . $chapter . ' ' . $row['title'] . '</h4>';
  $doc_str .= $row['content'];
  $doc_str .= '<br />';
  }
  return $doc_str;
  }
 */

function generateDocument($doc_str) {
    global $toc;
    $query = "SELECT
                        id,
                        parent_id,
                        title,
                        sort,
                        content
                FROM
                        libraries
                ORDER BY
                        parent_id = 0 DESC,
                        sort ASC";
    $result = mysqli_query($dbc,$query) ;
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

function printDocument(stdClass $obj, $doc_str, $chapter, $isRoot = false) {
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

function setTocArchive($libraries_id, $content, $employee_id) {

    $date = new DateTime();
    $today = $date->format('Y-m-d H:i:s');

    $insert = "INSERT INTO libraries_history (`libraries_id`,`employee_id`,`date_edited`,`content`) VALUES (" . $libraries_id . "," . $employee_id . ",now(),'" . $content . "')";

    $insertResult = mysqli_query($dbc,$insert) ;

    //update time of report on changes
    $sql = "UPDATE tradestar_reports SET Report_Date = '$today' WHERE Report_ID = (SELECT doc_id FROM libraries WHERE id =$libraries_id)";
    $res = mysqli_query($dbc,$sql) or die(mysqli_error($dbc) . " INSERT TRADESTAR HISTORY ERROR");

    $result = mysqli_query($dbc,"SELECT id FROM libraries_history WHERE libraries_id = " . $libraries_id . " ORDER BY id DESC LIMIT 29,1");
    if (mysqli_num_rows($result) > 0) {
        $fetch = mysqli_fetch_assoc($result);
        mysqli_query($dbc,"DELETE FROM libraries_history WHERE id <" . $fetch['id'] . " AND libraries_id = " . $libraries_id);
    }
}

function elementsTreeGridXML() {

    $query = "
        SELECT
                id,
                parent_id,
                title
        FROM
                libraries
        ";
    $result = mysqli_query($dbc,$query) ;
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
        printElementsTreeGridXML($obj, true);
    }
}

function printElementsTreeGridXML(stdClass $obj, $isRoot = false) {

    echo '<row id = "' . $obj->id . '">';
    if (count($obj->children) == 0) {
        echo "<cell image=\"blank.gif\"><![CDATA[" . $obj->title . "]]></cell>";
    } else {
        echo "<cell image=\"folder.gif\"><![CDATA[" . $obj->title . "]]></cell>";
    }
    foreach ($obj->children as $child) {
        printElementsTreeGridXML($child);
    }
    echo '</row>';
}

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