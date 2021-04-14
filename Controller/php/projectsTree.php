<?php

require 'config_mysqli.php';
include 'funcs.php';
require_once 'curl.php';
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_NUMBER_INT);
switch ($action) {

    default:
        $eid = filter_input(INPUT_GET, 'eid', FILTER_SANITIZE_NUMBER_INT);
        $type = (isset($_GET['type'])) ? filter_input(INPUT_GET, 'type', FILTER_SANITIZE_NUMBER_INT) : 0;

        header('Content-type:text/xml');
        echo '<?xml version="1.0"?>' . PHP_EOL;
        echo '<tree id="0">';
//        if ($branchId > 0) {
//            generateTreeByBranch($eid,$branchId, $languageId);
//        } else {
        generateTree($eid, $type);
        if ($type == 2) {
            getMoodleTree();
        }

//        }
        echo '</tree>';
        break;

    case 1:

        $tId = filter_input(INPUT_POST, 'tId', FILTER_SANITIZE_NUMBER_INT);
        $sId = filter_input(INPUT_POST, 'sId', FILTER_SANITIZE_NUMBER_INT);
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

        $update = "UPDATE projects_dir SET parent_id = '" . $tId . "' WHERE id = '" . $sId . "'";
        $updateResult = mysqli_query($dbc, $update);

        if ($updateResult) {
            //do resort
            $SQL_SRT = "SELECT * FROM projects_dir WHERE parent_id = '" . $tId . "'";
            $RES_SRT = mysqli_query($dbc, $SQL_SRT);
            $srt = 0;
            while ($ROW_SRT = mysqli_fetch_assoc($RES_SRT)) {
                $srt++;
                $SQL_UPDATE_SRT = "UPDATE projects_dir SET sort_id = '" . $srt . "' WHERE id = '" . $ROW_SRT['id'] . "'";
                mysqli_query($dbc, $SQL_UPDATE_SRT);
            }
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated', 'id' => $sId);
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);
        break;

    case 2:

        header('Content-type:text/xml');
        echo '<?xml version="1.0"?>' . PHP_EOL;
        print('<menu id="0" >');
        print('<item text="Create Project"  img="new.gif"  id="main_add_dir">');
        print('<item text="Main Project"  img="new.gif"  id="add_root"/>');
        print('<item text="Sub Project"  img="new.gif"  id="add_sub"/>');
        print('</item>');

        print('<item text="Type"  id="type">');
        print('<item text="Video"  type="checkbox"  id="type_1"/>');
        print('<item text="Moodle"  type="checkbox"  id="type_2"/>');
        print('<item text="Project" type="checkbox"  id="type_3"/>');
        print('</item>');

        print('<item text="Rename Item"  img="rename.png"  id="rename"/>');
        print('<item text="Delete Item"  img="deleteall.png"  id="delete"/>');
        print('<item text="Archive" id="archive" type="checkbox"/>');
        print('<item text="Set Password"  img="new.gif"  id="set_password"/>');
        print('<item text="Change Password"  img="new.gif"  id="change_password"/>');
        print('</menu>');
        break;

    case 3:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        //select no of unarchived children
//        $amountChildRecords = mysql_result(mysqli_query($dbc,
//                        "SELECT COUNT(1) FROM projects_dir WHERE parent_id = '" . $id . "' and archive = 0"), 0, 0
//        );
//        if ($amountChildRecords > 0) {
//            $data['data'] = array('response' => $updateResult, 'text' => 'The Selected Map has unarchived projects!');
//        } else {
        $update = "UPDATE projects_dir SET archive = 1 WHERE id = " . $id;
        $updateResult = mysqli_query($dbc, $update);

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Archived', 'id' => $id);
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Archiving');
        }
//        }
        echo json_encode($data);
        break;

    case 4:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        //update archive
        $update = "UPDATE projects_dir SET archive = 0 WHERE id = '" . $id;
        $updateResult = mysqli_query($dbc, $update);

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Unarchived', 'id' => $id);
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);
        break;

    case 5:

        $level = filter_input(INPUT_GET, 'level', FILTER_SANITIZE_NUMBER_INT);
        $itemName = filter_input(INPUT_POST, 'item_name', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        $admins = array('15683,131,9392,9068,9562,16848');
        $userlggd = filter_input(INPUT_GET, 'eid', FILTER_SANITIZE_NUMBER_INT);
        $branchId = filter_input(INPUT_GET, 'branch', FILTER_SANITIZE_NUMBER_INT);
        $access_rights = 0;

        if (in_array($userlggd, $admins)) {
            $users = implode(",", $admins);
        } else {
            $users = '15683,131,9392,9068,9562,16848,' . $userlggd;
        }
        if ($level > 0) {
            $parentId = filter_input(INPUT_POST, 'parent', FILTER_SANITIZE_NUMBER_INT);

            $insert = "INSERT INTO projects_dir (`parent_id`,`project_name`,`sort_id`,`proj_uID`)SELECT " . $parentId . ",'" . $itemName . "',IF((MAX(sort_id)>0),MAX(sort_id)+1,1)sort_id,'" . $users . "' FROM projects_dir WHERE parent_id = " . $parentId;
        } else {
            $insert = "INSERT INTO projects_dir (`parent_id`,`project_name`,`sort_id`,`proj_uID`)SELECT 0,'" . $itemName . "',IF((MAX(sort_id)>0),MAX(sort_id)+1,1)sort_id,'" . $users . "' FROM projects_dir WHERE parent_id = 0";
        }

        $insertResult = mysqli_query($dbc, $insert);

        if ($insertResult) {
            $newId = mysqli_insert_id($dbc);

            if ($branchId > 0) {
                $insert = "INSERT IGNORE INTO project_to_branch (branch_id,project_id) VALUES ($branchId,$newId)";
            } else {
                $insert = "INSERT IGNORE INTO project_to_branch (branch_id,project_id) SELECT Branch_ID,$newId FROM branch WHERE visible_in_projects = 1 ORDER BY Branch_ID";
            }

            $insertResult = mysqli_query($dbc, $insert);

            if ($insertResult) {
                $data['data'] = array('response' => $insertResult, 'text' => 'Successfully Added', 'item_id' => $newId);
            } else {
                $data['data'] = array('response' => $insertResult, 'text' => 'An Error Occured While Saving');
            }
        } else {
            $data['data'] = array('response' => $insertResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);
        break;

    case 6:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT project_name,parent_id FROM projects_dir WHERE id =" . $id;

        $result = mysqli_query($dbc, $query);
        $row = mysqli_fetch_assoc($result);
        header("Content-type:text/xml");
        print('<?xml version="1.0" encoding="utf-8"?>');
        echo "<data>";
        echo "<item_name><![CDATA[" . $row["project_name"] . "]]></item_name>";
        echo "</data>";
        break;

    case 7:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $itemName = filter_input(INPUT_POST, 'item_name', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

        $update = "UPDATE projects_dir
                    SET 
                     project_name = '" . $itemName . "'   
                   WHERE
                      id = " . $id;

        $updateResult = mysqli_query($dbc, $update);

        if ($updateResult) {

            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);
        break;

    case 8:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $sql = "UPDATE projects_dir SET archive = 1 WHERE id =" . $id;
        $updated = mysqli_query($dbc, $sql);
        if ($updated) {
            $data['data'] = array('response' => $updated, 'text' => 'Successfully Deleted');
        } else {
            $data['data'] = array('response' => $updated, 'text' => 'An Error Occured While Deleting');
        }

        echo json_encode($data);

        break;

    case 9:
        $eid = filter_input(INPUT_GET, 'eid', FILTER_SANITIZE_NUMBER_INT);

        header('Content-type:text/xml');
        echo '<?xml version="1.0"?>' . PHP_EOL;
        echo '<tree id="0">';
//        if ($branchId > 0) {
//            generateTreeByBranch($eid, true);
//        } else {
//        generateTree($eid, true);
        generateTree($eid, 0);
        getMoodleTree();
//        }
        echo '</tree>';
        break;

    case 10:

        $bool = true;
        $SQL_SEL = "SELECT * FROM projects_dir WHERE id = '" . $_POST['selId'] . "'";
        $RES_SEL = mysqli_query($dbc, $SQL_SEL);
        $ROW_SEL = mysqli_fetch_assoc($RES_SEL);

        //get the sort and parent
        $SORT_SEL = $ROW_SEL['sort_id'];
        $PARENT = $ROW_SEL['parent_id'];
        //sort for the destination 
        switch ($_POST['type']) {
            case 'up':
                $SORT_SEL--;
                break;
            case 'down':
                $SORT_SEL++;
                break;
        }

        //query
        $SQL_DEST = "SELECT * FROM projects_dir WHERE sort_id = '" . $SORT_SEL . "' AND parent_id = '" . $PARENT . "'";
        $RES_DEST = mysqli_query($dbc, $SQL_DEST);
        $ROW_DEST = mysqli_fetch_assoc($RES_DEST);
        if ($ROW_DEST['id'] == null) {
            $bool = false;
        }

        //update the sort 
        $SQL_UPDATE = "UPDATE projects_dir SET sort_id = '" . $ROW_SEL['sort_id'] . "' WHERE id = '" . $ROW_DEST['id'] . "'";

        $SQL_UPDATE_ = "UPDATE projects_dir SET sort_id = '" . $SORT_SEL . "' WHERE  id = '" . $_POST['selId'] . "'";

        if ($bool == true) {

            mysqli_query($dbc, $SQL_UPDATE);
            mysqli_query($dbc, $SQL_UPDATE_);
        }
        echo json_encode(array("dest" => $ROW_DEST['id'], "bool" => $bool, "parent" => $PARENT));
        break;

    case 11:
        //search filter
        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<rows>";

        $SQL = "SELECT
                        a.id,
                        a.project_name,
                        (
                                SELECT
                                        CONCAT(
                                                IFNULL(
                                                        CONCAT(M4.project_name, ' > '),
                                                        ''
                                                ),
                                                IFNULL(
                                                        CONCAT(M3.project_name, ' > '),
                                                        ''
                                                ),
                                                IFNULL(
                                                        CONCAT(M2.project_name, ' > '),
                                                        ''
                                                ),
                                                IFNULL(
                                                        CONCAT(M1.project_name, ' > '),
                                                        ''
                                                ),
                                                M.project_name
                                        )project_path_name
                                FROM
                                        projects_dir M
                                LEFT JOIN projects_dir M1 ON M.parent_id = M1.id
                                LEFT JOIN projects_dir M2 ON M1.parent_id = M2.id
                                LEFT JOIN projects_dir M3 ON M2.parent_id = M3.id
                                LEFT JOIN projects_dir M4 ON M3.parent_id = M4.id
                                WHERE
                                        M.id = a.id
                        )path
                FROM
                        projects_dir a
                WHERE
                        a.`project_name` LIKE '%" . $_GET['value'] . "%' LIMIT 0,100";
        $RESULT = mysqli_query($dbc, $SQL);

        while ($row = mysqli_fetch_array($RESULT)) {
            $itemId = $row['id'];
            $projectId = generateProjectId($itemId);
            echo "<row id = '{$row["id"]}'>";
            echo "<cell><![CDATA[" . $projectId . "]]></cell>";
            echo "<cell><![CDATA[" . $row['project_name'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['path'] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 12:

        $userId = $_POST['user'];
        $pass = md5($_POST['password']);
        $projectId = $_POST['project'];
        $code = $_POST['code'];

        $insert = "INSERT INTO project_to_user (project_id,user_id,password,level_id) VALUES ('$projectId','$userId','$pass','$code')";
        $insertResult = mysqli_query($dbc, $insert);
        if ($insertResult) {

            $data['data'] = array('response' => $insertResult, 'text' => 'Password Successfully Set');
        } else {
            $data['data'] = array('response' => $insertResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);

        break;

    case 13:

        $userId = $_POST['user'];
        $old_pass = md5($_POST['old_password']);
        $new_pass = md5($_POST['new_password']);
        $projectId = $_POST['project'];
        $code = $_POST['code'];
        $SELECT = "SELECT * FROM project_to_user WHERE project_id = '$projectId' AND user_id =" . $userId;
        $RESULT = mysqli_query($dbc, $SELECT);
        $ROW = mysqli_fetch_assoc($RESULT);
        $existing_pass = $ROW['password'];
        if ($existing_pass == $old_pass || $code == '9898') {

            $update = "UPDATE project_to_user SET password = '$new_pass' WHERE project_id = '$projectId' AND user_id = " . $userId;
            $updateResult = mysqli_query($dbc, $update);
            if ($updateResult) {

                $data['data'] = array('response' => $updateResult, 'text' => 'Password Successfully Changed');
            } else {
                $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
            }
        } else {
            $data['data'] = array('response' => false, 'text' => 'Wrong password and code combination!');
        }
        echo json_encode($data);

        break;

    case 14:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $catId = filter_input(INPUT_GET, 'cat_id', FILTER_SANITIZE_NUMBER_INT);

        $update = "UPDATE projects_dir SET spec_tpl_cat='" . $catId . "' WHERE id=" . $id;
        $updateResult = mysqli_query($dbc, $update);
        if ($updateResult) {

            $insert = "INSERT INTO project_specification (project_spec_id,project_tpl_id,project_value) SELECT " . $id . ",spec_tpl_id,spec_tpl_default_value FROM specification_template WHERE spec_tpl_cat = " . $catId;
            $insertResult = mysqli_query($dbc, $insert);
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Saved');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);

        break;

    case 15:

        $id = filter_input(INPUT_GET, 'value', FILTER_SANITIZE_NUMBER_INT) ? filter_input(INPUT_GET, 'value', FILTER_SANITIZE_NUMBER_INT) : 0;

        $SQL = "SELECT
                    a.id,
                    a.project_name,
                    (
                            SELECT
                                    CONCAT(
                                            IFNULL(
                                                    CONCAT(M4.project_name, ' > '),
                                                    ''
                                            ),
                                            IFNULL(
                                                    CONCAT(M3.project_name, ' > '),
                                                    ''
                                            ),
                                            IFNULL(
                                                    CONCAT(M2.project_name, ' > '),
                                                    ''
                                            ),
                                            IFNULL(
                                                    CONCAT(M1.project_name, ' > '),
                                                    ''
                                            ),
                                            M.project_name
                                    )project_path_name
                            FROM
                                    projects_dir M
                            LEFT JOIN projects_dir M1 ON M.parent_id = M1.id
                            LEFT JOIN projects_dir M2 ON M1.parent_id = M2.id
                            LEFT JOIN projects_dir M3 ON M2.parent_id = M3.id
                            LEFT JOIN projects_dir M4 ON M3.parent_id = M4.id
                            WHERE
                                    M.id = a.id
                    )path
            FROM
                    projects_dir a
            WHERE
                    a.id = " . $id . " LIMIT 0,100";
        $RESULT = mysqli_query($dbc, $SQL);
        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<rows>";
        while ($row = mysqli_fetch_array($RESULT)) {
            $itemId = $row['id'];
            $projectId = generateProjectId($itemId);
            echo "<row id = '{$row["id"]}'>";
            echo "<cell><![CDATA[" . $projectId . "]]></cell>";
            echo "<cell><![CDATA[" . $row['project_name'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['path'] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 16:

        $id = filter_input(INPUT_GET, 'value', FILTER_SANITIZE_NUMBER_INT) ? filter_input(INPUT_GET, 'value', FILTER_SANITIZE_NUMBER_INT) : 0;

        $SQL = "
            SELECT
                    projects_to_documents.id,
                    projects_to_documents.project_id,
                    projects_to_documents.report_id,
                    tradestar_reports.Report_Subject project_name,
                    (
                            SELECT
                                    CONCAT(
                                            IFNULL(
                                                    CONCAT(M4.project_name, ' > '),
                                                    ''
                                            ),
                                            IFNULL(
                                                    CONCAT(M3.project_name, ' > '),
                                                    ''
                                            ),
                                            IFNULL(
                                                    CONCAT(M2.project_name, ' > '),
                                                    ''
                                            ),
                                            IFNULL(
                                                    CONCAT(M1.project_name, ' > '),
                                                    ''
                                            ),
                                            M.project_name
                                    )project_path_name
                            FROM
                                    projects_dir M
                            LEFT JOIN projects_dir M1 ON M.parent_id = M1.id
                            LEFT JOIN projects_dir M2 ON M1.parent_id = M2.id
                            LEFT JOIN projects_dir M3 ON M2.parent_id = M3.id
                            LEFT JOIN projects_dir M4 ON M3.parent_id = M4.id
                            WHERE
                                    M.id = a.id
                    )path
            FROM
                    projects_to_documents
            JOIN tradestar_reports ON projects_to_documents.report_id = tradestar_reports.Report_ID
            LEFT JOIN projects_dir a ON a.id = projects_to_documents.project_id
            WHERE
                    projects_to_documents.report_id = " . $id . " AND projects_to_documents.is_active = 1
            LIMIT 0,
             100";
        $RESULT = mysqli_query($dbc, $SQL);
        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<rows>";
        while ($row = mysqli_fetch_array($RESULT)) {
            $itemId = $row['project_id'];
            $projectId = generateProjectId($itemId);
            echo "<row id = '{$row["project_id"]}'>";
            echo "<cell><![CDATA[" . $projectId . "]]></cell>";
            echo "<cell><![CDATA[" . $row['project_name'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['path'] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 17:

        $id = filter_input(INPUT_GET, 'value', FILTER_SANITIZE_STRING) ? filter_input(INPUT_GET, 'value', FILTER_SANITIZE_STRING) : '';

        $SQL = "
            SELECT
                    projects_to_documents.id,
                    projects_to_documents.project_id,
                    projects_to_documents.report_id,
                    tradestar_reports.Report_Subject project_name,
                    (
                            SELECT
                                    CONCAT(
                                            IFNULL(
                                                    CONCAT(M4.project_name, ' > '),
                                                    ''
                                            ),
                                            IFNULL(
                                                    CONCAT(M3.project_name, ' > '),
                                                    ''
                                            ),
                                            IFNULL(
                                                    CONCAT(M2.project_name, ' > '),
                                                    ''
                                            ),
                                            IFNULL(
                                                    CONCAT(M1.project_name, ' > '),
                                                    ''
                                            ),
                                            M.project_name
                                    )project_path_name
                            FROM
                                    projects_dir M
                            LEFT JOIN projects_dir M1 ON M.parent_id = M1.id
                            LEFT JOIN projects_dir M2 ON M1.parent_id = M2.id
                            LEFT JOIN projects_dir M3 ON M2.parent_id = M3.id
                            LEFT JOIN projects_dir M4 ON M3.parent_id = M4.id
                            WHERE
                                    M.id = a.id
                    )path
            FROM
                    projects_to_documents
            JOIN tradestar_reports ON projects_to_documents.report_id = tradestar_reports.Report_ID AND tradestar_reports.Report_Subject LIKE '%" . $_GET['value'] . "%'
            LEFT JOIN projects_dir a ON a.id = projects_to_documents.project_id
            WHERE
                    projects_to_documents.is_active = 1
            LIMIT 0,
             100";
        $RESULT = mysqli_query($dbc, $SQL);
        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<rows>";
        while ($row = mysqli_fetch_array($RESULT)) {
            $itemId = $row['project_id'];
            $projectId = generateProjectId($itemId);
            echo "<row id = '{$row["project_id"]}'>";
            echo "<cell><![CDATA[" . $projectId . "]]></cell>";
            echo "<cell><![CDATA[" . $row['project_name'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['path'] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 18:

        $id = filter_input(INPUT_GET, 'value', FILTER_SANITIZE_STRING) ? filter_input(INPUT_GET, 'value', FILTER_SANITIZE_STRING) : '';

        $SQL = "SELECT
                        a.id,
                        projects_uploads.file_name project_name,
                        (
                                SELECT
                                        CONCAT(
                                                IFNULL(
                                                        CONCAT(M4.project_name, ' > '),
                                                        ''
                                                ),
                                                IFNULL(
                                                        CONCAT(M3.project_name, ' > '),
                                                        ''
                                                ),
                                                IFNULL(
                                                        CONCAT(M2.project_name, ' > '),
                                                        ''
                                                ),
                                                IFNULL(
                                                        CONCAT(M1.project_name, ' > '),
                                                        ''
                                                ),
                                                M.project_name
                                        )project_path_name
                                FROM
                                        projects_dir M
                                LEFT JOIN projects_dir M1 ON M.parent_id = M1.id
                                LEFT JOIN projects_dir M2 ON M1.parent_id = M2.id
                                LEFT JOIN projects_dir M3 ON M2.parent_id = M3.id
                                LEFT JOIN projects_dir M4 ON M3.parent_id = M4.id
                                WHERE
                                        M.id = a.id
                        )path
                FROM
                projects_uploads
                JOIN 
                        projects_dir a ON a.id = projects_uploads.file_parent
                WHERE
                        projects_uploads.file_name LIKE '%" . $_GET['value'] . "%' LIMIT 0,100";
        $RESULT = mysqli_query($dbc, $SQL);
        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<rows>";
        while ($row = mysqli_fetch_array($RESULT)) {
            $itemId = $row['id'];
            $projectId = generateProjectId($itemId);
            echo "<row id = '{$row["id"]}'>";
            echo "<cell><![CDATA[" . $projectId . "]]></cell>";
            echo "<cell><![CDATA[" . $row['project_name'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['path'] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 19:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);

        $query = "SELECT * FROM projects_dir WHERE parent_id = " . $id;
        $result = mysqli_query($dbc, $query);
        $amount = mysqli_num_rows($result);

        echo json_encode(array("rowNum" => $amount));
        break;

    case 20:
        //select archive
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);

        $result = mysqli_query($dbc,
            "SELECT archive FROM projects_dir WHERE id = " . $id
        );
        $row = mysqli_fetch_array($result);
        $value = $row[0];

        echo json_encode(array("value" => $value));
        break;

    case 21:

        $id = filter_input(INPUT_GET, 'value') ? filter_input(INPUT_GET, 'value') : '';

        $SQL = "SELECT
                        a.id,
                        projects_uploads.file_name project_name,
                        (
                                SELECT
                                        CONCAT(
                                                IFNULL(
                                                        CONCAT(M4.project_name, ' > '),
                                                        ''
                                                ),
                                                IFNULL(
                                                        CONCAT(M3.project_name, ' > '),
                                                        ''
                                                ),
                                                IFNULL(
                                                        CONCAT(M2.project_name, ' > '),
                                                        ''
                                                ),
                                                IFNULL(
                                                        CONCAT(M1.project_name, ' > '),
                                                        ''
                                                ),
                                                M.project_name
                                        )project_path_name
                                FROM
                                        projects_dir M
                                LEFT JOIN projects_dir M1 ON M.parent_id = M1.id
                                LEFT JOIN projects_dir M2 ON M1.parent_id = M2.id
                                LEFT JOIN projects_dir M3 ON M2.parent_id = M3.id
                                LEFT JOIN projects_dir M4 ON M3.parent_id = M4.id
                                WHERE
                                        M.id = a.id
                        )path
                FROM
                projects_uploads
                JOIN 
                        projects_dir a ON a.id = projects_uploads.file_parent
                WHERE
                        projects_uploads.id =" . $_GET['value'] . " LIMIT 0,100";
        $RESULT = mysqli_query($dbc, $SQL);

        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<rows>";
        while ($row = mysqli_fetch_array($RESULT)) {
            $itemId = $row['id'];
            $projectId = generateProjectId($itemId);
            echo "<row id = '{$row["id"]}'>";
            echo "<cell><![CDATA[" . $projectId . "]]></cell>";
            echo "<cell><![CDATA[" . $row['project_name'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['path'] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 22:

        header('Content-type:text/xml');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        echo '<ribbon>';
        echo '<item type="block" text="Actions" mode="cols" text_pos="top">';
        echo '<item type="button" id="up" text="Move Up" img="up.png"/>';
        echo '<item type="button" id="down" text="Move Down" img="down.png" />';
        echo '<item type="buttonSelect" id="type" text="Show" mode="cols" img="db.png">';
        echo '<menu>';
        echo '<item  id = "1" text = "Video Content"/>';
        echo '<item id = "2" text = "Moodle Content"/>';
        echo '<item id = "3" text = "Project Content"/>';
        echo '<item id = "0" text = "All Content"/>';
        echo '</menu>';
        echo '</item>';
        echo '<item type="newLevel" />';
        echo '<item type="button" id="refresh" text="Refresh" img="refresh.png" />';
        echo '<item type="button" id="restore" text="Restore Project" img="restore.png" />';
        echo '<item type="buttonTwoState" id="show_all" text="Show All" img="db.png" />';
        echo '<item type="newLevel" />';
        echo '<item type="button" id="search_pop" text="Search" img="search.png" isbig="true" />';
        echo '</item>';
        echo '</ribbon>';
        break;

    case 23:

        $query = "SELECT id,project_name FROM projects_dir WHERE archive = 1";

        $result = mysqli_query($dbc, $query);

        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<row id = '" . $row["id"] . "'>";
            echo "<cell></cell>";
            echo "<cell><![CDATA[" . generateProjectId($row["id"]) . "]]></cell>";
            echo "<cell><![CDATA[" . $row["project_name"] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 24:

        $id = filter_input(INPUT_POST, 'id');
        //update archive
        $update = "UPDATE projects_dir SET archive = 0 WHERE id IN ('" . $id . "')";
        $updateResult = mysqli_query($dbc, $update);

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Restored');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);
        break;

    case 25:

        $query = "SELECT Branch_ID,Branch_Name FROM branch WHERE visible_in_projects = 1 ORDER BY Branch_ID";
//        $query = "SELECT id,project_name FROM projects_dir WHERE parent_id = 0 AND archive = 0 ORDER BY project_name asc";
        $result = mysqli_query($dbc, $query);
        $values[] = array('type' => 'button', 'id' => '0', "text" => 'All Branches');
        while ($row = mysqli_fetch_assoc($result)) {
            $values[] = array('type' => 'button', 'id' => $row["Branch_ID"], "text" => $row["Branch_Name"]);
        }

        echo json_encode(array('options' => $values));
        break;

    case 26:

        $query = "SELECT * from xoops_shop_languages WHERE languages_id IN(1,4) ORDER BY sort_order ASC";
        $result = mysqli_query($dbc, $query);
        $values[] = array('type' => 'button', 'id' => 'l_0', "text" => 'All Languages');
        while ($row = mysqli_fetch_assoc($result)) {
            $values[] = array('type' => 'button', 'id' => 'l_' . $row["languages_id"], "text" => $row["name"]);
        }

        echo json_encode(array('options' => $values));
        break;

    case 27:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $delete = "DELETE FROM projects_dir_translation WHERE id=" . $id;
        $deleteResult = mysqli_query($dbc, $delete);

        if ($deleteResult) {
            $data['data'] = array('response' => $deleteResult, 'text' => 'Translation Successfully Deleted');
        } else {
            $data['data'] = array('response' => $insertResult, 'text' => 'An Error Occured While Deleting');
        }
        echo json_encode($data);
        break;

    case 28:

        $projectId = filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT);
        $languageId = filter_input(INPUT_GET, 'language_id', FILTER_SANITIZE_NUMBER_INT);

        $query_check = "SELECT id FROM projects_dir_translation WHERE project_id = " . $projectId . " AND language_id=" . $languageId;

        $result_check = mysqli_query($dbc, $query_check);
        if (mysqli_num_rows($result_check) == 1) {//if Query is successfull  // A match was made.
            $data['data'] = array('response' => false, 'text' => 'Translation already exists');
        } else {

            $insert = "INSERT INTO projects_dir_translation (`project_id`,`language_id`)VALUES (" . $projectId . "," . $languageId . ")";

            $insertResult = mysqli_query($dbc, $insert);

            if ($insertResult) {
                $newId = mysqli_insert_id($dbc);
                $data['data'] = array('response' => $insertResult, 'text' => 'Successfully Added', 'new_id' => $newId);
            } else {
                $data['data'] = array('response' => $insertResult, 'text' => 'An Error Occured While Saving');
            }
        }
        echo json_encode($data);
        break;

    case 29:

        $id = filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT);

        $query = "
            SELECT
                projects_dir_translation.id,
                projects_dir_translation.title,
                xoops_shop_languages.`name` `language`
            FROM
                projects_dir_translation
            JOIN xoops_shop_languages ON xoops_shop_languages.languages_id = projects_dir_translation.language_id
            WHERE
                project_id = " . $id;
        $result = mysqli_query($dbc, $query);

        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        while ($row = mysqli_fetch_array($result)) {
            echo "<row id = '" . $row["id"] . "'>";
            echo "<cell></cell>";
            echo "<cell><![CDATA[" . $row["language"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["title"] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 30:
        $index = filter_input(INPUT_POST, 'index');
        $fieldvalue = filter_input(INPUT_POST, 'fieldvalue');
        $id = filter_input(INPUT_POST, 'id');
        $field = filter_input(INPUT_POST, 'colId');
        $colType = filter_input(INPUT_POST, 'colType');
//        $fieldvalue = mysqli_real_escape_string($dbc,$fieldvalue);

        $updateResult = updateSQL("projects_dir_translation", $field, $fieldvalue, $id, "id", $colType);

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);

        break;

    case 31:

        $projectId = filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT);

        $query = "SELECT branch.Branch_ID, Branch_Name,";
        $query .= $projectId > 0 ? "(IF(ISNULL(project_to_branch.id),NULL,1)) is_assigned" : "NULL is_assisgned";
        $query .= "  FROM branch";
        $query .= $projectId > 0 ? " LEFT JOIN project_to_branch ON branch.Branch_ID = project_to_branch.branch_id  AND project_to_branch.project_id = " . $projectId : "";
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

    case 32:

        $projectId = filter_input(INPUT_POST, 'project_id', FILTER_SANITIZE_NUMBER_INT);
        $branchId = filter_input(INPUT_POST, 'branch_id', FILTER_SANITIZE_NUMBER_INT);
        $fieldvalue = filter_input(INPUT_POST, 'nValue');

        if ($fieldvalue == '1') {

            $query = "INSERT INTO project_to_branch (branch_id,project_id) VALUES ($branchId,$projectId)";
        } else {
            $query = "DELETE FROM project_to_branch WHERE branch_id =" . $branchId . " AND project_id = " . $projectId;
        }

        $updateResult = mysqli_query($dbc, $query);
        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);
        break;

    case 33:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $branchId = filter_input(INPUT_GET, 'branch', FILTER_SANITIZE_NUMBER_INT);
        $languageId = filter_input(INPUT_GET, 'language', FILTER_SANITIZE_NUMBER_INT);

        if ($languageId > 0) {
            $query = "SELECT projects_dir.id,projects_dir.parent_id,projects_dir.duration,projects_dir.description,projects_dir.has_training,IF(ISNULL(projects_dir_translation.title),projects_dir.project_name,projects_dir_translation.title) project_name,sort_id FROM projects_dir LEFT JOIN projects_dir_translation ON projects_dir_translation.project_id = projects_dir.id AND projects_dir_translation.language_id = " . $languageId . " WHERE projects_dir.id=" . $id;
        } else {
            $query = "SELECT projects_dir.id,projects_dir.parent_id,projects_dir.duration,projects_dir.description,projects_dir.has_training,IF(ISNULL(projects_dir_translation.title),projects_dir.project_name,projects_dir_translation.title) project_name,sort_id FROM projects_dir LEFT JOIN projects_dir_translation ON projects_dir_translation.project_id = projects_dir.id AND projects_dir_translation.language_id = 1 WHERE projects_dir.id=" . $id;
        }


        $result = mysqli_query($dbc, $query);
        $row = mysqli_fetch_assoc($result);

        header('Content-type:text/xml;charset=ISO-8859-1;');
        print('<?xml version="1.0" encoding="utf-8"?>');
        echo "<data>";
        echo "<ProjectID>{$row["id"]}</ProjectID>";
        echo "<ProjectName><![CDATA[" . $row["project_name"] . "]]></ProjectName>";
        echo "<ProjectDuration>{$row["duration"]}</ProjectDuration>";
        echo "<ProjectDescription><![CDATA[" . $row["description"] . "]]></ProjectDescription>";
        echo "<has_training>{$row["has_training"]}</has_training>";
        echo "</data>";
        break;

    case 34:

        $ProjectName = filter_input(INPUT_POST, 'ProjectName');
        $ProjectDescription = filter_input(INPUT_POST, 'ProjectDescription');
        $ProjectID = filter_input(INPUT_POST, 'ProjectID');
        $ProjectDuration = filter_input(INPUT_POST, 'ProjectDuration') ? filter_input(INPUT_POST, 'ProjectDuration') : 'NULL';
        $has_training = filter_input(INPUT_POST, 'has_training', FILTER_SANITIZE_NUMBER_INT);
        $has_moodle = filter_input(INPUT_POST, 'has_moodle', FILTER_SANITIZE_NUMBER_INT);
        $has_project = filter_input(INPUT_POST, 'has_project', FILTER_SANITIZE_NUMBER_INT);

        $update = "UPDATE projects_dir SET project_name='" . $ProjectName . "',description='" . $ProjectDescription . "',has_training=" . $has_training . ",has_moodle=" . $has_moodle . ",has_project=" . $has_project . ",duration=" . $ProjectDuration . " WHERE id=" . $ProjectID;

        $updateResult = mysqli_query($dbc, $update) or die(mysqli_error($dbc) . $update);

        if ($updateResult) {
            $data['data'] = array('success' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('success' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);
        break;

    case 35:

        $fieldvalue = filter_input(INPUT_POST, 'notes');
        $id = filter_input(INPUT_POST, 'id');

        $query = "UPDATE projects_dir SET comments = '" . mysqli_real_escape_string($dbc, $fieldvalue) . "' WHERE id=" . $id;
        $updateResult = mysqli_query($dbc, $query);

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);
        break;

    case 36:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $result = mysqli_query($dbc, "SELECT comments FROM projects_dir WHERE id =" . $id);
        $row = mysqli_fetch_array($result);
        $doc_content = $row[0];

        echo json_encode(array("content" => $doc_content));
        break;

    case 37:

        $type = filter_input(INPUT_POST, 'type_id', FILTER_SANITIZE_NUMBER_INT);
        $n_value = filter_input(INPUT_POST, 'n_value', FILTER_SANITIZE_NUMBER_INT);
        $ids = $_POST['ids'];

        $values = array();

        if ($n_value == '1') {

            foreach ($ids as $id) {
                $values[] = "(" . $id . "," . $type . ")";
            }

            if (count($values) > 0) {
                $query = "INSERT IGNORE INTO project_type (project_id,type_id) VALUES " . implode(",", $values);
            }
        }

        if ($n_value == '0') {
            $query = "DELETE FROM project_type WHERE project_id IN (" . implode(",", $ids) . ") AND type_id =" . $type;
        }

        $result = mysqli_query($dbc, $query);

        if ($result) {
            $data['data'] = array('response' => $result, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $result, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);
        break;
}

function generateTreeByBranch($eid, $branchId, $languageId, $showAll = false)
{

    $nodes = array();

    $query = "SELECT project_id FROM project_to_branch WHERE branch_id =" . $branchId;
    $result = mysqli_query($dbc, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $nodes[] = $row['project_id'];
    }

    if ($languageId > 0) {
        $query = "SELECT projects_dir.id,projects_dir.parent_id,IF(ISNULL(projects_dir_translation.title),projects_dir.project_name,projects_dir_translation.title) project_name,sort_id,`privileges`.map_access,`privileges`.doc_access,`privileges`.file_access FROM projects_dir LEFT JOIN projects_dir_translation ON projects_dir_translation.project_id = projects_dir.id AND projects_dir_translation.language_id = " . $languageId . " LEFT JOIN project_map_privileges `privileges` ON `privileges`.project_id = projects_dir.id AND `privileges`.employee_id =" . $eid;
    } else {
        $query = "
            SELECT 
               projects_dir.id,
               projects_dir.parent_id,
               IF(
                 ISNULL(projects_dir_translation.title),
                 projects_dir.project_name,
                 projects_dir_translation.title
               ) project_name,
               sort_id,
               `privileges`.map_access,
               `privileges`.doc_access,
               `privileges`.file_access 
             FROM
               projects_dir 
               LEFT JOIN projects_dir_translation 
                 ON projects_dir_translation.project_id = projects_dir.id 
                 AND projects_dir_translation.language_id = 1 
               LEFT JOIN project_map_privileges `privileges` 
                 ON `privileges`.project_id = projects_dir.id 
                 AND `privileges`.employee_id =" . $eid;
    }
    if (!$showAll) {
        $query .= " WHERE archive = 0 ";
    }
    $query .= " ORDER BY parent_id = 0 DESC"; //echo $query; exit;

    $result = mysqli_query($dbc, $query);

    $objects = array();
    $roots = array();
    while ($row = mysqli_fetch_assoc($result)) {
        if (!isset($objects[$row['id']])) {
            $objects[$row['id']] = new stdClass;
            $objects[$row['id']]->children = array();
            $objects[$row['id']]->types = array();
        }

        $obj = $objects[$row['id']];
        $obj->id = $row['id'];
        $obj->name = $row['project_name'];
        $obj->parent_id = $row['parent_id'];
        $obj->map_access = $row['map_access'];
        $obj->doc_access = $row['doc_access'];
        $obj->file_access = $row['file_access'];

        if (in_array($row['id'], $nodes)) {
            $roots[] = $obj;
        } else {
            if (!isset($object[$row['parent_id']])) {
                $object[$row['parent_id']] = new stdClass;
                $object[$row['parent_id']]->children = array();
            }

            $objects[$row['parent_id']]->children[$row['id']] = $obj;
        }
    }
    $x = 0;
    foreach ($roots as $obj) {
        ++$x;
        printXML($obj, $x, $eid, true);
    }
}

function generateTree($eid, $type = null, $showAll = false)
{

    global $dbc;

    $query = "
            SELECT 
              projects_dir.id,
              projects_dir.parent_id,
              project_type.type_id type_id,
              types.`name` type_name,
              IF(
                ISNULL(projects_dir_translation.title),
                projects_dir.project_name,
                projects_dir_translation.title
              ) project_name,
              sort_id
            FROM
              projects_dir 
              LEFT JOIN project_type 
                ON project_type.project_id = projects_dir.id 
              LEFT JOIN `types` 
                ON types.id = project_type.type_id 
              LEFT JOIN projects_dir_translation 
                ON projects_dir_translation.project_id = projects_dir.id 
                AND projects_dir_translation.language_id = 1";
//              LEFT JOIN project_map_privileges `privileges`
//                ON `privileges`.project_id = projects_dir.id
//                AND `privileges`.employee_id =" . $eid;

    if (!$showAll) {
        $query .= " WHERE archive = 0 ";
        if ($type > 0) {
            $query .= " AND project_type.type_id  = " . $type;
        }
    } else {
        if ($type > 0) {
            $query .= " WHERE project_type.type_id  = " . $type;
        }
    }

    $query .= " ORDER BY parent_id = 0 DESC,project_name asc";

    $result = mysqli_query($dbc, $query);

    $objects = array();
    $roots = array();
    while ($row = mysqli_fetch_assoc($result)) {
        if (!isset($objects[$row['id']])) {
            $objects[$row['id']] = new stdClass;
            $objects[$row['id']]->children = array();
            $objects[$row['id']]->types = array();

            if ($row['type_id']) {
                $objects[$row['id']]->types[$row['type_id']] = new stdClass;
                $objects[$row['id']]->types[$row['type_id']]->id = $row['type_id'];
                $objects[$row['id']]->types[$row['type_id']]->name = $row['type_name'];
            }
        } else {

            if ($row['type_id']) {
                $objects[$row['id']]->types[$row['type_id']] = new stdClass;
                $objects[$row['id']]->types[$row['type_id']]->id = $row['type_id'];
                $objects[$row['id']]->types[$row['type_id']]->name = $row['type_name'];
            }
        }

        $obj = $objects[$row['id']];
        $obj->id = $row['id'];
        $obj->name = $row['project_name'];
        $obj->parent_id = $row['parent_id'];
        $obj->has_training = $row['has_training'];

        if ($eid == 24743) {
            if ($row['id'] == '461') {
                $roots[] = $obj;
            } else {
                if (!isset($object[$row['parent_id']])) {
                    $object[$row['parent_id']] = new stdClass;
                    $object[$row['parent_id']]->children = array();
                }

                $objects[$row['parent_id']]->children[$row['id']] = $obj;
            }
        } else if ($eid == 25001) {
            if ($row['id'] == '5092') {
                $roots[] = $obj;
            } else {
                if (!isset($object[$row['parent_id']])) {
                    $object[$row['parent_id']] = new stdClass;
                    $object[$row['parent_id']]->children = array();
                }

                $objects[$row['parent_id']]->children[$row['id']] = $obj;
            }
        } else if ($eid == 22185) {
            if ($row['id'] == '5344') {
                $roots[] = $obj;
            } else {
                if (!isset($object[$row['parent_id']])) {
                    $object[$row['parent_id']] = new stdClass;
                    $object[$row['parent_id']]->children = array();
                }

                $objects[$row['parent_id']]->children[$row['id']] = $obj;
            }
        } else if ($eid == 1960) {
            if ($row['id'] == '376') {
                $roots[] = $obj;
            } else {
                if (!isset($object[$row['parent_id']])) {
                    $object[$row['parent_id']] = new stdClass;
                    $object[$row['parent_id']]->children = array();
                }

                $objects[$row['parent_id']]->children[$row['id']] = $obj;
            }
        } else if ($eid == 26907) {
            if ($row['id'] == '2172') {
                $roots[] = $obj;
            } else {
                if (!isset($object[$row['parent_id']])) {
                    $object[$row['parent_id']] = new stdClass;
                    $object[$row['parent_id']]->children = array();
                }

                $objects[$row['parent_id']]->children[$row['id']] = $obj;
            }
        } else {
            if ($row['parent_id'] == 0) {
                $roots[] = $obj;
            } else {
                if (!isset($object[$row['parent_id']])) {
                    $object[$row['parent_id']] = new stdClass;
                    $object[$row['parent_id']]->children = array();
                }

                $objects[$row['parent_id']]->children[$row['id']] = $obj;
            }
        }
    }
//    
//    print '<pre>';
//    print_r($objects);
//    exit;

    $x = 0;
    foreach ($roots as $obj) {
        ++$x;
        printXML($obj, $x, $eid, true);
    }
}

function printXML(stdClass $obj, $x, $eid, $isRoot = false)
{

    $itemName = xml_entities($obj->name);
    $itemId = $obj->id;
    $projectId = generateProjectId($itemId);

    if ($itemId === '9856') {
//        if ($obj->map_access > 0) {

        echo "<item id='" . $obj->id . "' text='" . $projectId . "| " . $itemName . "'>" . PHP_EOL;

        foreach ($obj->types as $type) {
            echo "<userdata name='" . $type->name . "'>1</userdata>" . PHP_EOL;
        }

        echo '<userdata name="thisurl">index.php?page=' . $obj->id . '</userdata>' . PHP_EOL;
        echo '<userdata name="has_video">' . $obj->has_training . '</userdata>' . PHP_EOL;
        $y = 0;
        foreach ($obj->children as $child) {
            ++$y;
            printXML($child, $y, $eid);
        }

        echo '</item>';
//        }
    } else {

        echo "<item id='" . $obj->id . "' text='" . $projectId . "| " . $itemName . "'>" . PHP_EOL;


        foreach ($obj->types as $type) {
            echo "<userdata name='" . $type->name . "'>1</userdata>" . PHP_EOL;
        }
        echo '<userdata name="thisurl">index.php?page=' . $obj->id . '</userdata>' . PHP_EOL;
        echo '<userdata name="has_video">' . $obj->has_training . '</userdata>' . PHP_EOL;
        $y = 0;
        foreach ($obj->children as $child) {
            ++$y;
            printXML($child, $y, $eid);
        }

        echo '</item>';
    }
}

function getMoodleTree()
{

    global $dbc;

    $query = "SELECT * FROM moodle_servers";
    $result = mysqli_query($dbc, $query);

    $objects = array();
    $roots = array();


    while ($row = mysqli_fetch_array($result)) {

        echo "<item id='m_" . $row['id'] . "' text='" . $row['name'] . "'>";
        echo "<userdata name='path'>" . $row['path'] . "</userdata>";
        echo "<userdata name='token'>" . $row['token'] . "</userdata>";
        echo "<userdata name='Moodle'>1</userdata>";

        $domainname = $row['path']; //paste your domain here
        $wstoken = $row['token']; //here paste your enrol token 
        $wsfunctionname = 'core_course_get_courses';
        $restformat = 'json';

        $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;
        $curl = new curl;
        $restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';
        $resp = $curl->post($serverurl . $restformat);
        $courses = json_decode($resp);

        foreach ($courses as $course) {
            echo "<item id='" . $row['id'] . '_' . $course->id . "' text='" . $course->fullname . "' >";
            echo "<userdata name='Moodle'>1</userdata>";
            echo "</item>";
        }

        echo '</item>';
    }
}
