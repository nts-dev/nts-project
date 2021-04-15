<?php

include_once '../../../config.php';
include("GeneralFunctions.php");

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_NUMBER_INT);
$tab = filter_input(INPUT_GET, 'tab', FILTER_SANITIZE_NUMBER_INT);


switch ($action) {

    default:
        break;

    case 1:

        $itemId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $query = "
            SELECT
                t.IntranetID,
                CONCAT(
                    COALESCE(rc.contact_firstname, ''),
                    ' ',
                    COALESCE(rc.contact_secondname, ''),
                    ' ',
                    COALESCE(rc.contact_lastname, '')
                )AS employee,
                rc.contact_attendent,
                b.Branch_ID,
                b.Branch_Name";
        if ($itemId > 0) {
            $query .= ",priviledges.map_access,
	priviledges.doc_access,
	priviledges.file_access";
        }
        $query .= " FROM
                trainees t
            JOIN branch b ON b.Branch_ID = t.branch_id
            JOIN relation_contact rc ON t.IntranetID = rc.contact_id ";

        if ($itemId > 0) {
            $query .= "LEFT JOIN project_map_privileges priviledges ON t.IntranetID = priviledges.employee_id
            AND priviledges.project_id = {$itemId} ";
        }
        $query .= "WHERE
                b.visible = 1
            AND b.Branch_ID > 0
            AND t.status_id > 0
            ORDER BY
                b.Branch_ID,
                t.IntranetID";
//        echo $query;
//        exit;
        $result = mysqli_query($dbc,$query) ;

        $objects = array();
        $children = array();
        while ($row = mysqli_fetch_assoc($result)) {
            if (!isset($objects[$row['Branch_ID']])) {
                $objects[$row['Branch_ID']] = new stdClass;
                $objects[$row['Branch_ID']]->children = array();
            }
            $obj = $objects[$row['Branch_ID']];
            $obj->branch_id = $row['Branch_ID'];
            $obj->branch_name = $row['Branch_Name'];

            if (!isset($children[$row['Branch_ID']][$row['IntranetID']])) {
                $children[$row['Branch_ID']][$row['IntranetID']] = new stdClass;
            }

            $obj_1 = $children[$row['Branch_ID']][$row['IntranetID']];
            $obj_1->id = $row['IntranetID'];
            $obj_1->name = $row['employee'];
            $obj_1->attendent = $row['contact_attendent'];
            $obj_1->map_access = $row['map_access'];
            $obj_1->doc_access = $row['doc_access'];
            $obj_1->file_access = $row['file_access'];
            $objects[$row['Branch_ID']]->children[$row['IntranetID']] = $obj_1;
        }

        header('Content-type:text/xml');
        echo '<?xml version="1.0"?>' . PHP_EOL;
        echo '<rows>';
        foreach ($objects as $obj) {
            echo '<row id="branch_' . $obj->branch_id . '">';
            echo '<cell image="folder.gif">' . $obj->branch_name . '</cell>';
//            printPrivilegesGridXML($obj);
            $employees = $attribute['children'];
            foreach ($obj->children as $child) {
                echo '<row id="' . $child->id . '">';
                echo '<cell image="blank.gif">' . $child->name . '</cell>';
                echo '<cell>' . $child->id . '</cell>';
                echo '<cell>' . $child->map_access . '</cell>';
                echo '<cell>' . $child->doc_access . '</cell>';
                echo '<cell>' . $child->file_access . '</cell>';
                echo '</row>';
            }
            echo '</row>';
        }
        echo '</rows>';
        break;


    case 2:
        $index = filter_input(INPUT_POST, 'index');
        $fieldvalue = filter_input(INPUT_POST, 'fieldvalue');
        $id = filter_input(INPUT_POST, 'id');
        $field = filter_input(INPUT_POST, 'colId');
        $colType = filter_input(INPUT_POST, 'colType');
        $projectId = filter_input(INPUT_POST, 'projectId');

        $insert = array();

        $insert[] = "(" . $projectId . "," . $id . "," . $fieldvalue . ")";

        $query = "
            SELECT
                projects_dir.id,
                projects_dir.parent_id,
                projects_dir.project_name
            FROM
                projects_dir
            WHERE
                archive = 0
            ORDER BY
                parent_id = 0 DESC,
                id ASC";

        $result = mysqli_query($dbc,$query);

        $objects = array();
        $roots = array();
        while ($row = mysqli_fetch_assoc($result)) {
            if (!isset($objects[$row['id']])) {
                $objects[$row['id']] = new stdClass;
                $objects[$row['id']]->children = array();
            }

            $obj = $objects[$row['id']];
            $obj->id = $row['id'];
            $obj->name = $row['project_name'];
            $obj->parent_id = $row['parent_id'];

            if ($row['parent_id'] == $projectId) {
                $roots[] = $obj;
            } else {
                if (!isset($object[$row['parent_id']])) {
                    $object[$row['parent_id']] = new stdClass;
                    $object[$row['parent_id']]->children = array();
                }

                $objects[$row['parent_id']]->children[$row['id']] = $obj;
            }
        }


        foreach ($roots as $obj) {
            printXML($obj, $id, $fieldvalue);
        }

        if (count($insert) > 0) {
            $query = "INSERT INTO project_map_privileges (project_id,employee_id,`$field`) VALUES " . implode(',', $insert) . " ON DUPLICATE KEY UPDATE `$field`=VALUES(`$field`)";
            $updateResult = mysqli_query($dbc,$query) ;
        }

//        print '<pre>';
//        print_r($insert);
//        exit;

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);

        break;

    case 3:

        $index = filter_input(INPUT_POST, 'index');
        $fieldvalue = filter_input(INPUT_POST, 'fieldvalue');
        $id = filter_input(INPUT_POST, 'id');
        $field = filter_input(INPUT_POST, 'colId');
        $colType = filter_input(INPUT_POST, 'colType');
        $projectId = filter_input(INPUT_POST, 'projectId');

        $select = "SELECT IF(`$field`>0,`$field`,1) field_value FROM project_map_privileges WHERE project_id = " . $projectId . " AND employee_id = " . $id;
        $result = mysqli_query($dbc,$query) ;
        $row = mysqli_fetch_assoc($result);
        $fieldvalue = $row['field_value'];

        if (!$fieldvalue) {
            $fieldvalue = 1;
        }

        $query = "INSERT INTO project_map_privileges (project_id,employee_id,`$field`) SELECT (" . $projectId . "," . $id . "," . $fieldvalue . ") ON DUPLICATE KEY UPDATE `$field`=VALUES(`$field`)";

        $updateResult = mysqli_query($dbc,$query) ;

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);

        break;
}

function printXML(stdClass $obj, $eid, $fieldvalue) {

    global $insert;

    $insert[] = "(" . $obj->id . ", " . $eid . ", " . $fieldvalue . ")";

    foreach ($obj->children as $child) {
        ++$y;
        printXML($child, $eid, $fieldvalue);
    }
}
