<?php

ini_set('display_errors', '0');
require 'config_mysqli.php';
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
                b.Branch_ID,
                b.Branch_Name";
        if ($itemId > 0) {
            $query .= ",priviledges.* ";
        }
        $query .= " FROM
                nts_site.trainees t
            JOIN nts_site.branch b ON b.Branch_ID = t.branch_id
            JOIN nts_site.relation_contact rc ON t.IntranetID = rc.contact_id ";

        if ($itemId > 0) {
            $query .= "LEFT JOIN project_user_privileges priviledges ON t.IntranetID = priviledges.user_id
            AND priviledges.item_id = {$itemId} ";
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
            $obj_1->read = $row['read_privilege'];
            $obj_1->write = $row['write_privilege'];
            $obj_1->create = $row['create_privilege'];
            $obj_1->delete = $row['delete_privilege'];
            $obj_1->change_template = $row['change_template'];
            $obj_1->create_maps = $row['create_maps'];
            $obj_1->rename_maps = $row['rename_maps'];
            $obj_1->delete_maps = $row['delete_maps'];
            $obj_1->default_new_read = $row['default_new_read'];
            $obj_1->default_new_write = $row['default_new_write'];
            $obj_1->default_new_create = $row['default_new_create'];
            $obj_1->default_new_delete = $row['default_new_delete'];
            $obj_1->default_new_change_template = $row['default_new_change_template'];
            $obj_1->default_self_read = $row['default_self_read'];
            $obj_1->default_self_write = $row['default_self_write'];
            $obj_1->default_self_create = $row['default_self_create'];
            $obj_1->default_self_delete = $row['default_self_delete'];
            $obj_1->default_self_change_template = $row['default_self_change_template'];
            $obj_1->master_rights = $row['master_rights'];
            $obj_1->new_location = $row['new_location'];
            $obj_1->new_own_location = $row['new_own_location'];
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
                printPrivilegesGridXML($child);
                echo '</row>';
            }
            echo '</row>';
        }
        echo '</rows>';
        break;

    case 2:

        $fieldvalue = $_POST["nValue"];
        $itemId = $_POST["projectId"];
        $field = $_POST["colId"];
        $userId = $_POST["userId"];
        $level = $_POST['level'];

        if ($level > 0) {

            $insert = "INSERT INTO project_user_privileges (item_id,user_id,`$field`) VALUES (" . $itemId . "," . $userId . ",'" . $fieldvalue . "') ON DUPLICATE KEY UPDATE `$field`='" . $fieldvalue . "'";
        } else {

            if ($fieldvalue == '1') {

                $insert = "INSERT IGNORE INTO project_to_branch (branch_id,project_id) VALUES ($userId,$itemId)";
            } else {
                $insert = "DELETE FROM project_to_branch WHERE branch_id =" . $userId . " AND project_id = " . $itemId;
            }
        }
//            echo $insert; exit;
        $result = mysqli_query($dbc,$insert) ;

        if ($result) {
            $data['data'] = array('response' => $result, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $result, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);
        break;
}

function printPrivilegesGridXML(stdClass $obj) {
    global $tab;

    switch ($tab) {

        case 1:

            echo '<cell>' . $obj->read . '</cell>';
            echo '<cell>' . $obj->write . '</cell>';
            echo '<cell>' . $obj->create . '</cell>';
            echo '<cell>' . $obj->delete . '</cell>';
            break;

        case 2:

            echo '<cell>' . $obj->create_maps . '</cell>';
            echo '<cell>' . $obj->rename_maps . '</cell>';
            echo '<cell>' . $obj->delete_maps . '</cell>';
            break;

        case 3:

            echo '<cell>' . $obj->default_new_read . '</cell>';
            echo '<cell>' . $obj->default_new_write . '</cell>';
            echo '<cell>' . $obj->default_new_create . '</cell>';
            echo '<cell>' . $obj->default_new_delete . '</cell>';
            break;

        case 4:

            echo '<cell>' . $obj->default_self_read . '</cell>';
            echo '<cell>' . $obj->default_self_write . '</cell>';
            echo '<cell>' . $obj->default_self_create . '</cell>';
            echo '<cell>' . $obj->default_self_delete . '</cell>';
            break;

        case 5:

            echo '<cell>' . $obj->master_rights . '</cell>';
            break;

        case 6:

            echo '<cell>' . $obj->new_location . '</cell>';
            echo '<cell>' . $obj->new_own_location . '</cell>';
            break;

        default:
            break;
    }
}
