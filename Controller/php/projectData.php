<?php

include_once '../../../config.php';
mysqli_select_db($dbc,'nts_network');

include("GeneralFunctions.php");
date_default_timezone_set('UTC');
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_NUMBER_INT);

function defineFieldIdsForTemplate($templateId) {

    $statement = "
        SELECT
                ntk_template_fields.id,
                ntk_template_fields.`name`
        FROM
                ntk_template_fields
        JOIN ntk_templates ON ntk_templates.id = ntk_template_fields.templ_id
        AND ntk_template_fields.common = 1
        AND ntk_template_fields.visible_in_form = 1
        WHERE
                ntk_templates.id = " . $templateId . "
        AND ntk_template_fields.`name` IN('branch', 'room')
        ORDER BY
                ntk_template_fields.sort_id ASC";

    $result = mysqli_query($dbc,$statement);

    while ($row = mysqli_fetch_assoc($result)) {
        switch (strtolower($row['name'])) {
            case 'branch':
                define('TEMPLATE_FIELD_BRANCH', $row['id']);
                break;
            case 'room':
                define('TEMPLATE_FIELD_ROOM', $row['id']);
                break;
        }
    }
}

switch ($action) {

    default:

        break;
    case 1:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $selected_array = array();
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<toolbar>';
        echo '<item type="buttonSelect" id="assets" text="Select" img=""  openAll="true" renderSelect="true" mode="select">';

        if ($id > 0) {
            $qry = "SELECT
                        ntk_devices.id,
                        ntk_devices.`name`,
                        ntk_templates.id template,
                        ntk_assetcat_to_project.default_value
                FROM
                        ntk_assetcat_to_project
                JOIN `ntk_templates` ON `ntk_templates`.id = ntk_assetcat_to_project.asset_cat_id       
                JOIN ntk_devices ON ntk_devices.id = ntk_templates.device_id
                WHERE
                        ntk_assetcat_to_project.project_id =" . $id;

            $res = mysqli_query($dbc,$qry) or die(mysqli_error($dbc) . $qry);



            while ($row = mysqli_fetch_array($res)) {
                $selected = (($row["default_value"] == 1) ? 'selected="true"' : '');
                if ($row["default_value"] == 1) {
                    $selected_array[] = $row["id"];
                }
                echo '<item type="button" id="' . $row["id"] . '_' . $row["template"] . '" text="[' . $row["id"] . '] ' . $row["name"] . '" ' . $selected . '/>';
            }
        }

        $qry = "
            SELECT
                    ntk_devices.id,
                    ntk_devices.`name`,
                    ntk_templates.id template
            FROM
            `ntk_templates`
            JOIN ntk_devices ON ntk_devices.id = ntk_templates.device_id
            WHERE
                    `ntk_templates`.id = 1314";

        $res = mysqli_query($dbc,$qry) or die(mysqli_error($dbc) . $qry);
        $row = mysqli_fetch_assoc($res);

        $selected = (count($selected_array) > 0 ? '' : 'selected="true"');

        echo '<item type="button" id="' . $row["id"] . '_' . $row["template"] . '" text="[' . $row["id"] . '] ' . $row["name"] . '" ' . $selected . '/>';
        echo '</item>';
        echo '</toolbar>';

        break;

    case 2:

        $deviceId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $result = mysqli_query($dbc,"SELECT id FROM `ntk_templates` WHERE `device_id` =" . $deviceId);
        $row = mysqli_fetch_array($result);
        $templId = $row[0];

        echo json_encode(array("templ_id" => $templId));

        break;

    case 3:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $qry = "SELECT
                        ntk_assetcat_to_project.*,prjct.project_name
                FROM
                        ntk_assetcat_to_project
                LEFT JOIN projects_dir prjct ON prjct.id = ntk_assetcat_to_project.project_id
                WHERE
                        asset_cat_id = " . $id;

        $res = mysqli_query($dbc,$qry) or die(mysqli_error($dbc) . $qry);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        while ($row = mysqli_fetch_array($res)) {

            $itemId = $row["project_id"];
            $no = generateProjectId($itemId);
            echo "<row id = '" . $row["id"] . "'>";
            echo "<cell></cell>";
            echo "<cell><![CDATA[" . $no . "]]></cell>";
            echo "<cell><![CDATA[" . $row["project_name"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["default_value"] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;


    case 4:

        $fieldvalue = filter_input(INPUT_GET, 'fieldvalue', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        $id = $_GET["id"];
        $field = $_GET["colId"];
        $colType = $_GET["colType"];
        $fieldvalue = mysqli_real_escape_string($dbc,$fieldvalue);

        $updateResult = updateSQL("ntk_assetcat_to_project", $field, $fieldvalue, $id, "id", $colType);
        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);

        break;

    case 5:

        $fieldvalue = $_POST["nValue"];
        $catId = $_POST["catId"];
        $id = $_POST["id"];
        $field = $_POST["colId"];

        if ($fieldvalue > 0) {
            $res = mysqli_query($dbc,"UPDATE ntk_assetcat_to_project SET default_value = 0 WHERE asset_cat_id =" . $catId) ;
        }

        $updateResult = updateSQL("ntk_assetcat_to_project", $field, $fieldvalue, $id, "id", "int");
        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);

        break;

    case 6:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $qry = "SELECT
                        ntk_devices.id,
                        ntk_devices.`name`,
                        `ntk_templates`.id template
                FROM
                        `ntk_templates`
                JOIN ntk_devices ON `ntk_templates`.device_id = ntk_devices.id
                WHERE
                        ntk_templates.id = " . $id;

        $res = mysqli_query($dbc,$qry) or die(mysqli_error($dbc) . $qry);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<toolbar>';
        echo '<item type="buttonSelect" id="assets" text="Select" img=""  openAll="true" renderSelect="true" mode="select">';
        while ($row = mysqli_fetch_array($res)) {
            $selected = 'selected="true"';
            $name = $row["name"];
            echo '<item type="button" id="' . $row["id"] . '_' . $row["template"] . '" text="[' . $row["id"] . '] ' . $row["name"] . '" ' . $selected . '/>';
        }
        echo '</item>';
        echo '<item type="text" id="name" text="' . $name . '"></item>';
        echo '</toolbar>';
        break;

    case 7:

        $templateId = filter_input(INPUT_GET, 'templateId', FILTER_SANITIZE_NUMBER_INT);
        $branches = array();
        $rooms = array();

        defineFieldIdsForTemplate($templateId);

        $queryBranches = "SELECT DISTINCT field_value FROM ntk_field_values WHERE templ_id = " . $templateId . " AND field_id =  " . TEMPLATE_FIELD_BRANCH . " AND field_value > 0";
        $resBranches = mysqli_query($dbc,$queryBranches) or die(mysqli_error($dbc) . $queryBranches);
        while ($row = mysqli_fetch_array($resBranches)) {
            $branches[] = $row['field_value'];
        }

        $queryRooms = "SELECT DISTINCT field_value FROM ntk_field_values WHERE templ_id = " . $templateId . " AND field_id =  " . TEMPLATE_FIELD_ROOM . " AND field_value > 0";
        $resRooms = mysqli_query($dbc,$queryRooms) or die(mysqli_error($dbc) . $queryRooms);
        while ($row = mysqli_fetch_array($resRooms)) {
            $rooms[] = $row['field_value'];
        }

        $query = "SELECT `ntk_rooms`.*,branch.Branch_Name FROM `ntk_rooms` JOIN branch ON branch.Branch_ID = `ntk_rooms`.branch_id WHERE `ntk_rooms`.id IN  (" . implode(',', $rooms) . ") ORDER BY `ntk_rooms`.branch_id,`ntk_rooms`.id";
        $result = mysqli_query($dbc,$query) ;
        while ($row = mysqli_fetch_array($result)) {
            $branchMenu[$row['branch_id']] = $row['Branch_Name'];
            $roomsMenu[$row['branch_id']][$row['id']] = $row['name'];
        }
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        print ('<menu>');
        print('<item id="branches"  img="home.png" imgdis="home.png" text="Select Room">');
        foreach ($branchMenu as $branch_id => $branch_name) {
            print('<item id="branch_' . $branch_id . '"  img="home.png" imgdis="home.png" text="' . $branch_name . '">');
            foreach ($roomsMenu[$branch_id] as $room_id => $room_name) {
                print('<item type="checkbox" id="room_' . $room_id . '"  img="home.png" imgdis="home.png" text="' . $room_name . '"/>');
            }
            print('</item>');
        }
        print('</item>');
        print ('</menu>');

        break;

    case 8:

        $templateId = filter_input(INPUT_GET, 'templateId', FILTER_SANITIZE_NUMBER_INT);
        $rooms = array();

        defineFieldIdsForTemplate($templateId);

        $queryRooms = "SELECT DISTINCT field_value FROM ntk_field_values WHERE templ_id = " . $templateId . " AND field_id =  " . TEMPLATE_FIELD_ROOM . " AND field_value > 0";
        $resRooms = mysqli_query($dbc,$queryRooms) or die(mysqli_error($dbc) . $queryRooms);
        while ($row = mysqli_fetch_array($resRooms)) {
            $rooms[] = $row['field_value'];
        }

        if (count($rooms) == '1') {
            $data['data'] = array('response' => true, 'values' => implode(",", $rooms));
        } else {
            $data['data'] = array('response' => false);
        }
        echo json_encode($data);
        break;

    case 9:


        $query = "SELECT
                        ntk_template_fields.`name`,
                        ntk_device_records.id,
                        ntk_template_fields.id field_id,	
                        field_value
                    FROM
                        ntk_device_records
                    JOIN ntk_templates ON ntk_templates.device_id = ntk_device_records.device_id
                    JOIN ntk_field_values ON ntk_device_records.id = ntk_field_values.device_id
                    JOIN ntk_template_fields ON ntk_field_values.field_id = ntk_template_fields.id
                    AND ntk_template_fields.templ_id = ntk_templates.id
                    JOIN(
                        SELECT
                                device_id
                        FROM
                                ntk_field_values
                        WHERE
                                field_value = 'In Use'
                    )activeDevices ON ntk_device_records.id = activeDevices.device_id
                    WHERE
                        ntk_templates.device_id = 89
                    ORDER BY
                        ntk_device_records.id,
                        ntk_template_fields.sort_id";

        $result = mysqli_query($dbc,$query) ;
        $devices = array();
        $employeeMenu = array();
        $roomsMenu = array();
        $branchMenu = array();
        while ($row = mysqli_fetch_array($result)) {
            $devices[$row['id']][$row['name']] = $row['field_value'];
        }

//print '<pre>';
//        print_r($devices);
//        exit;
        foreach ($devices as $key => $row) {
            if ($row['Room'] > 0) {
                $rooms[$row['Room']] = $row['Room'];
            }

//            $roomsMenu[$row['Branch']][$row['Room']] = $row['Room'];
            if ($row['Contact ID'] > 0) {
                $employeeMenu[$row['Room']][$row['Contact ID']]['First Name'] = $row['First Name'];
                $employeeMenu[$row['Room']][$row['Contact ID']]['Second Name'] = $row['Second Name'];
                $employeeMenu[$row['Room']][$row['Contact ID']]['Last Name'] = $row['Last Name'];
            }
        }

        $branch = array();
        $query = "SELECT k.id,k.`name`,k.parent_id,branch.Branch_ID,branch.Branch_Name,(SELECT `name` FROM ntk_rooms WHERE id = k.parent_id) parent FROM `ntk_rooms` k JOIN branch ON branch.Branch_ID = k.branch_id WHERE k.id IN (" . implode(',', $rooms) . ") ORDER BY k.branch_id,k.id";
        $result = mysqli_query($dbc,$query) ;
        while ($row = mysqli_fetch_array($result)) {

            if ($row['Branch_ID'] === '6') {
                $branch[$row['Branch_ID']]['name'] = $row['Branch_Name'];
                $branch[$row['Branch_ID']]['rooms'][$row['parent_id']]['name'] = $row['parent'];
                $branch[$row['Branch_ID']]['rooms'][$row['parent_id']]['offices'][$row['id']]['name'] = $row['name'];
                $branch[$row['Branch_ID']]['rooms'][$row['parent_id']]['offices'][$row['id']]['employees'] = $employeeMenu[$row['id']];
            }
        }

        header('Content-type:text/xml');
        echo '<?xml version="1.0"?>' . PHP_EOL;
        echo '<rows>';
        foreach ($branch as $Branch_ID => $Branch_Details) {
            echo '<row id="branch_' . $Branch_ID . '">';
            if (count($Branch_Details['rooms']) == 0) {
                echo "<cell image=\"blank.gif\"><![CDATA[" . $Branch_Details['name'] . "]]></cell>";
            } else {
                echo "<cell image=\"folder.gif\"><![CDATA[" . $Branch_Details['name'] . "]]></cell>";
            }
            foreach ($Branch_Details['rooms'] as $Room_ID => $Room_Details) {
                echo '<row id="room_' . $Room_ID . '">';
                if (count($Room_Details['rooms']) == 0) {
                    echo "<cell image=\"blank.gif\"><![CDATA[" . $Room_Details['name'] . "]]></cell>";
                } else {
                    echo "<cell image=\"folder.gif\"><![CDATA[" . $Room_Details['name'] . "]]></cell>";
                }
                foreach ($Room_Details['offices'] as $Office_ID => $Office_Details) {
                    echo '<row id="office_' . $Office_ID . '">';
                    if (count($Office_Details['employees']) == 0) {
                        echo "<cell image=\"blank.gif\"><![CDATA[" . $Office_Details['name'] . "]]></cell>";
                    } else {
                        echo "<cell image=\"folder.gif\"><![CDATA[" . $Office_Details['name'] . "]]></cell>";
                    }
                    foreach ($Office_Details['employees'] as $Employee_ID => $Employee_Details) {
                        echo '<row id="' . $Employee_ID . '">';
                        echo "<cell image=\"blank.gif\"><![CDATA[" . $Employee_Details['First Name'] . " " . $Employee_Details['Second Name'] . " " . $Employee_Details['Last Name'] . "]]></cell>";

                        echo '</row>';
                    }
                    echo '</row>';
                }
                echo '</row>';
            }
            echo '</row>';
        }

        echo '</rows>';


//        print '<pre>';
//        print_r($branch);
        break;

    case 10:

        $deviceId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $projectId = filter_input(INPUT_GET, 'projectId', FILTER_SANITIZE_NUMBER_INT);
        $templateId = filter_input(INPUT_GET, 'templateId', FILTER_SANITIZE_NUMBER_INT);
        $string = '';

        if ($projectId > 0) {

            $result = mysqli_query($dbc,"SELECT `query` FROM `ntk_assetcat_to_project` WHERE `asset_cat_id` = " . $templateId . " AND `project_id` = " . $projectId);
            $row = mysqli_fetch_array($result);
            $string = $row[0];

            if ($string) {
                $words = array();
                $filter = createQuery($string);
            }
        }

        $query = "
            SELECT
                        ntk_template_fields.`name`,
                        ntk_device_records.id,
                        ntk_template_fields.id field_id,
                        ntk_template_fields.common,
                        ntk_template_fields.index_field,
                        ntk_template_fields.visible,
                        ntk_template_fields.description,
                        (
                                CASE
                                WHEN ntk_template_fields.`name` = 'Description'
                                AND ntk_template_fields.`common` = '1' THEN
                                        ''
                                WHEN ntk_template_fields.`name` = 'Branch'
                                AND ntk_template_fields.`common` = '1'
                                AND ntk_field_values.field_value > 0 THEN
                                        (
                                                SELECT
                                                        Branch_Name
                                                FROM
                                                        branch
                                                WHERE
                                                        visible = 1
                                                AND Branch_ID =(
                                                        ntk_field_values.field_value
                                                )
                                        )
                                WHEN ntk_template_fields.`name` = 'Room'
                                AND ntk_template_fields.`common` = '1'
                                AND ntk_field_values.field_value > 0 THEN
                                        (
                                                SELECT
                                                        `name`
                                                FROM
                                                        `ntk_rooms`
                                                WHERE
                                                        `id` =(
                                                                ntk_field_values.field_value
                                                        )
                                        )
                                WHEN ntk_template_fields.`name` = 'Main category'
                                AND ntk_template_fields.`common` = '0'
                                AND ntk_template_fields.`templ_id` = '362'
                                AND ntk_field_values.field_value > 0 THEN
                                        (
                                                SELECT
                                                        `name`
                                                FROM
                                                        `ntk_devices`
                                                WHERE
                                                        `id` =(
                                                                ntk_field_values.field_value
                                                        )
                                        )
                                WHEN ntk_template_fields.`name` = 'Sub category'
                                AND ntk_template_fields.`common` = '0'
                                AND ntk_template_fields.`templ_id` = '362'
                                AND ntk_field_values.field_value > 0 THEN
                                        (
                                                SELECT
                                                        `name`
                                                FROM
                                                        `ntk_devices`
                                                WHERE
                                                        `id` =(
                                                                ntk_field_values.field_value
                                                        )
                                        )        

                                ELSE
                                        ntk_field_values.field_value
                                END
                        )AS field_value
                FROM
                        ntk_device_records
                JOIN ntk_templates ON ntk_templates.device_id = ntk_device_records.device_id
                JOIN ntk_field_values ON ntk_device_records.id = ntk_field_values.device_id
                JOIN ntk_template_fields ON ntk_field_values.field_id = ntk_template_fields.id
                AND ntk_template_fields.templ_id = ntk_templates.id";
        $query .= $string ? $filter : '';
        $query .= " WHERE
                        ntk_templates.device_id = " . $deviceId . "
                
                AND ntk_template_fields.type <> 'password'
                ORDER BY
                        ntk_device_records.id,
                        ntk_template_fields.sort_id";
//echo $query; exit;
        $result = mysqli_query($dbc,$query) ;

        $previousDeviceId = null;
        $firstIsDone = false;
        $headers = array();
        $devices = array();
        while ($row = mysqli_fetch_assoc($result)) {
            if ($previousDeviceId !== null && $row['id'] != $previousDeviceId && !$firstIsDone) {
                $firstIsDone = true;
            } elseif (!$firstIsDone) {
                
            }
            if ($row['visible'] == '1') {
                $devices[$row['id']][$row['name']] = $row['field_value'];
                if ($row['index_field'] == 1 && !empty($row['field_value'])) {
                    $devices[$row['id']][$row['name']] = '';
                    $desc = mysqli_query($dbc,"SELECT
                                    ntk_field_values.id,
                                    ntk_template_fields.`name`,
                                    ntk_field_values.`device_id`,
                                    (
                                    CASE
                                    WHEN ntk_template_fields.`name` = 'Description'
                                    AND ntk_template_fields.`common` = '1' THEN
                                            ''
                                    WHEN ntk_template_fields.`name` = 'Branch'
                                    AND ntk_template_fields.`common` = '1'
                                    AND ntk_field_values.field_value > 0 THEN
                                            (
                                                    SELECT
                                                            Branch_Name
                                                    FROM
                                                            branch
                                                    WHERE
                                                            visible = 1
                                                    AND Branch_ID =(
                                                            ntk_field_values.field_value
                                                    )
                                            )
                                    WHEN ntk_template_fields.`name` = 'Room'
                                    AND ntk_template_fields.`common` = '1'
                                    AND ntk_field_values.field_value > 0 THEN
                                            (
                                                    SELECT
                                                            `name`
                                                    FROM
                                                            `ntk_rooms`
                                                    WHERE
                                                            `id` =(
                                                                    ntk_field_values.field_value
                                                            )
                                            )        
                                    ELSE
                                            ntk_field_values.field_value
                                    END
                            )AS field_value
                            FROM
                                    ntk_field_values
                            JOIN ntk_template_fields ON ntk_field_values.field_id = ntk_template_fields.id
                            WHERE
                                    device_id IN ({$row['field_value']}) AND ntk_template_fields.`description`= 1 ORDER BY ntk_template_fields.sort_id ASC");
                    while ($row_desc = mysqli_fetch_assoc($desc)) {
                        $fullDesc[$row_desc['device_id']][] = $row_desc['field_value'];
                    }
                    $description = '';
                    foreach ($fullDesc as $id1 => $columns1) {

                        foreach ($columns1 as $val1) {
                            $description .= $val1 . " ";
                        }
                        $description .= ",";
                    }
                    $description = substr($description, 0, strlen($description) - 1);
                    $devices[$row['id']][$row['name']] .= $description;
                    unset($fullDesc);
                }
                $previousDeviceId = $row['id'];
            }
            if ($row['description'] == '1') {
                $devices[$row['id']]['Description'] .= " " . $row['field_value'];
            }
        }

        $sel_header = mysqli_query($dbc,
                "SELECT
                        ntk_template_fields.id,
                        ntk_template_fields.`type`,
                        ntk_template_fields.`gridname`
                   FROM
                        ntk_template_fields
                JOIN ntk_templates ON ntk_template_fields.templ_id = ntk_templates.id
                AND visible = 1
                AND ntk_templates.device_id = " . $deviceId . "
                AND `type` <> 'password'
                ORDER BY
                        sort_id ASC"
                ) ;
        while ($row_header = mysqli_fetch_assoc($sel_header)) {

            $obj = new stdClass;
            $obj->id = $row_header['id'];
            $obj->name = $row_header['gridname'];
            $obj->type = $row_header['type'];
            $headers[$obj->id] = $obj;
        }

        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        echo '<head>';
        echo '<column id="counter" type="cntr" align="left" sort="int">Counter</column>';
        echo '<column id="id" type="ro" align="left" sort="str">ID</column>';

        $filters = '#numeric_filter,#numeric_filter';
        foreach ($headers as $fieldId => $fieldNames) {
            $filters .= ',#text_filter';
            if ($headers[$fieldId]->type == 'checkbox') {
                echo '<column id="' . $fieldId . '" type="ch" align="center" sort="str">' . $headers[$fieldId]->name . '</column>';
            } else {
                echo '<column id="' . $fieldId . '" type="ro" align="left" sort="str">' . $headers[$fieldId]->name . '</column>';
            }
        }
        echo '<afterInit>';
        echo '<call command="attachHeader">';
        echo '<param>' . $filters . '</param>';
        echo '</call>';
        echo '</afterInit>';

        echo '</head>';

        foreach ($devices as $id => $columns) {
            echo '<row id="' . $id . '">';
            echo '<cell></cell>';
            echo '<cell>' . $id . '</cell>';
            foreach ($headers as $val => $value) {
                echo "<cell><![CDATA[" . $columns[$headers[$val]->name] . "]]></cell>";
            }
            echo '</row>';
        }

        echo '</rows>';
        break;

    case 11:

        $deviceId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $projectId = filter_input(INPUT_GET, 'projectId', FILTER_SANITIZE_NUMBER_INT);
        $templateId = filter_input(INPUT_GET, 'templateId', FILTER_SANITIZE_NUMBER_INT);
        $string = '';

        if ($projectId > 0) {

            $result = mysqli_query($dbc,"SELECT `query` FROM `ntk_assetcat_to_project` WHERE `asset_cat_id` = " . $templateId . " AND `project_id` = " . $projectId);
            $row = mysqli_fetch_array($result);
            $string = $row[0];

            if ($string) {
                $words = array();
                $filter = createQuery($string);
            }
        }

        $query = "
            SELECT
                        ntk_template_fields.`name`,
                        ntk_device_records.id,
                        ntk_template_fields.id field_id,
                        ntk_template_fields.common,
                        ntk_template_fields.index_field,
                        ntk_template_fields.visible,
                        ntk_template_fields.description,
                        (
                                CASE
                                WHEN ntk_template_fields.`name` = 'Description'
                                AND ntk_template_fields.`common` = '1' THEN
                                        ''
                                WHEN ntk_template_fields.`name` = 'Branch'
                                AND ntk_template_fields.`common` = '1'
                                AND ntk_field_values.field_value > 0 THEN
                                        (
                                                SELECT
                                                        Branch_Name
                                                FROM
                                                        branch
                                                WHERE
                                                        visible = 1
                                                AND Branch_ID =(
                                                        ntk_field_values.field_value
                                                )
                                        )
                                WHEN ntk_template_fields.`name` = 'Room'
                                AND ntk_template_fields.`common` = '1'
                                AND ntk_field_values.field_value > 0 THEN
                                        (
                                                SELECT
                                                        `name`
                                                FROM
                                                        `ntk_rooms`
                                                WHERE
                                                        `id` =(
                                                                ntk_field_values.field_value
                                                        )
                                        )
                                WHEN ntk_template_fields.`name` = 'Main category'
                                AND ntk_template_fields.`common` = '0'
                                AND ntk_template_fields.`templ_id` = '362'
                                AND ntk_field_values.field_value > 0 THEN
                                        (
                                                SELECT
                                                        `name`
                                                FROM
                                                        `ntk_devices`
                                                WHERE
                                                        `id` =(
                                                                ntk_field_values.field_value
                                                        )
                                        )
                                WHEN ntk_template_fields.`name` = 'Sub category'
                                AND ntk_template_fields.`common` = '0'
                                AND ntk_template_fields.`templ_id` = '362'
                                AND ntk_field_values.field_value > 0 THEN
                                        (
                                                SELECT
                                                        `name`
                                                FROM
                                                        `ntk_devices`
                                                WHERE
                                                        `id` =(
                                                                ntk_field_values.field_value
                                                        )
                                        )        

                                ELSE
                                        ntk_field_values.field_value
                                END
                        )AS field_value
                FROM
                        ntk_device_records
                JOIN ntk_templates ON ntk_templates.device_id = ntk_device_records.device_id
                JOIN ntk_field_values ON ntk_device_records.id = ntk_field_values.device_id
                JOIN ntk_template_fields ON ntk_field_values.field_id = ntk_template_fields.id
                AND ntk_template_fields.templ_id = ntk_templates.id";
        $query .= $string ? $filter : '';
        $query .= " WHERE
                        ntk_templates.device_id = " . $deviceId . "
                
                AND ntk_template_fields.type <> 'password'
                ORDER BY
                        ntk_device_records.id,
                        ntk_template_fields.sort_id";
//echo $query; exit;
        $result = mysqli_query($dbc,$query) ;

        $previousDeviceId = null;
        $firstIsDone = false;
        $headers = array();
        $devices = array();
        while ($row = mysqli_fetch_assoc($result)) {
            if ($previousDeviceId !== null && $row['id'] != $previousDeviceId && !$firstIsDone) {
                $firstIsDone = true;
            } elseif (!$firstIsDone) {
                
            }
            if ($row['visible'] == '1') {
                $devices[$row['id']][$row['name']] = $row['field_value'];
                if ($row['index_field'] == 1 && !empty($row['field_value'])) {
                    $devices[$row['id']][$row['name']] = '';
                    $desc = mysqli_query($dbc,"SELECT
                                    ntk_field_values.id,
                                    ntk_template_fields.`name`,
                                    ntk_field_values.`device_id`,
                                    (
                                    CASE
                                    WHEN ntk_template_fields.`name` = 'Description'
                                    AND ntk_template_fields.`common` = '1' THEN
                                            ''
                                    WHEN ntk_template_fields.`name` = 'Branch'
                                    AND ntk_template_fields.`common` = '1'
                                    AND ntk_field_values.field_value > 0 THEN
                                            (
                                                    SELECT
                                                            Branch_Name
                                                    FROM
                                                            branch
                                                    WHERE
                                                            visible = 1
                                                    AND Branch_ID =(
                                                            ntk_field_values.field_value
                                                    )
                                            )
                                    WHEN ntk_template_fields.`name` = 'Room'
                                    AND ntk_template_fields.`common` = '1'
                                    AND ntk_field_values.field_value > 0 THEN
                                            (
                                                    SELECT
                                                            `name`
                                                    FROM
                                                            `ntk_rooms`
                                                    WHERE
                                                            `id` =(
                                                                    ntk_field_values.field_value
                                                            )
                                            )        
                                    ELSE
                                            ntk_field_values.field_value
                                    END
                            )AS field_value
                            FROM
                                    ntk_field_values
                            JOIN ntk_template_fields ON ntk_field_values.field_id = ntk_template_fields.id
                            WHERE
                                    device_id IN ({$row['field_value']}) AND ntk_template_fields.`description`= 1 ORDER BY ntk_template_fields.sort_id ASC");
                    while ($row_desc = mysqli_fetch_assoc($desc)) {
                        $fullDesc[$row_desc['device_id']][] = $row_desc['field_value'];
                    }
                    $description = '';
                    foreach ($fullDesc as $id1 => $columns1) {

                        foreach ($columns1 as $val1) {
                            $description .= $val1 . " ";
                        }
                        $description .= ",";
                    }
                    $description = substr($description, 0, strlen($description) - 1);
                    $devices[$row['id']][$row['name']] .= $description;
                    unset($fullDesc);
                }
                $previousDeviceId = $row['id'];
            }
            if ($row['description'] == '1') {
                $devices[$row['id']]['Description'] .= " " . $row['field_value'];
            }
        }

        $sel_header = mysqli_query($dbc,
                "SELECT
                        ntk_template_fields.id,
                        ntk_template_fields.`type`,
                        ntk_template_fields.`gridname`
                   FROM
                        ntk_template_fields
                JOIN ntk_templates ON ntk_template_fields.templ_id = ntk_templates.id
                AND visible = 1
                AND ntk_templates.device_id = " . $deviceId . "
                AND `type` <> 'password'
                ORDER BY
                        sort_id ASC"
                ) ;
        while ($row_header = mysqli_fetch_assoc($sel_header)) {

            $obj = new stdClass;
            $obj->id = $row_header['id'];
            $obj->name = $row_header['gridname'];
            $obj->type = $row_header['type'];
            $headers[$obj->id] = $obj;
        }

        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        foreach ($devices as $id => $columns) {
            echo '<row id="' . $id . '">';
            echo '<cell></cell>';
            echo '<cell>' . $id . '</cell>';
            foreach ($headers as $val => $value) {
                echo "<cell><![CDATA[" . $columns[$headers[$val]->name] . "]]></cell>";
            }
            echo '</row>';
        }

        echo '</rows>';
        break;

    case 12:

        $templ_id = filter_input(INPUT_GET, 'templ_id', FILTER_SANITIZE_NUMBER_INT);

        $query = "
            SELECT
                `ntk_device_to_document`.*,tradestar_reports.Report_Subject
            FROM
                `ntk_device_to_document`
            JOIN tradestar_reports ON tradestar_reports.Report_ID = `ntk_device_to_document`.document_id
            WHERE
                templ_id =" . $templ_id;

        $result = mysqli_query($dbc,$query) ;

        $documents = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $documents[] = array('id' => $row['document_id'], 'subject' => $row['Report_Subject']);
        }

        $response = count($documents) > 0 ? true : false;

        echo json_encode(array('response' => $response, 'documents' => $documents));

        break;

    case 13:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $query = "
            SELECT
                    `ntk_device_to_document`.*, tradestar_reports.Report_Subject
            FROM
                    `ntk_device_to_document`
            JOIN tradestar_reports ON tradestar_reports.Report_ID = `ntk_device_to_document`.document_id
            JOIN ntk_templates ON `ntk_device_to_document`.templ_id = ntk_templates.id
            JOIN ntk_devices ON ntk_devices.id = ntk_templates.device_id
            WHERE
                    ntk_devices.id =" . $id;

        $result = mysqli_query($dbc,$query) ;

        $documents = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $documents[] = array('id' => $row['document_id'], 'subject' => $row['Report_Subject']);
        }

        $response = count($documents) > 0 ? true : false;

        echo json_encode(array('response' => $response, 'documents' => $documents));

        break;

    case 14:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $templ_id = filter_input(INPUT_GET, 'templ_id', FILTER_SANITIZE_NUMBER_INT);

        $query = "
            SELECT
                `ntk_device_to_document`.*,tradestar_reports.Report_Subject,tradestar_reports.Report_Body
            FROM
                `ntk_device_to_document`
            JOIN tradestar_reports ON tradestar_reports.Report_ID = `ntk_device_to_document`.document_id
            WHERE
                ntk_device_to_document.device_id =" . $id . " AND ntk_device_to_document.templ_id =" . $templ_id;

        $result = mysqli_query($dbc,$query) ;

        $documents = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $documents[] = array('id' => $row['document_id'], 'subject' => $row['Report_Subject'], 'body' => $row['Report_Body']);
        }

        $response = count($documents) > 0 ? true : false;

        echo json_encode(array('response' => $response, 'documents' => $documents));

        break;
}

function getBrachId($branch) {
    switch ($branch) {
        case 2://NTS Computers Technology BV (Oudenbosch)
            $branchId = 1;
            break;
        case 37://TradeStar Kenya Ltd.
            $branchId = 6;
            break;
        case 52://Salland Computers BV
            $branchId = 11;
            break;
        case 111://NTS Computers SDN BHD
            $branchId = 8;
            break;
        case 135://Global Africa Initiative Foundation
            $branchId = 0;
            break;
        case 146://LOC Davinci Dordrecht
            $branchId = 10;
            break;
        case 153://Tanzania Office
            $branchId = 0;
            break;
    }
    return $branchId;
}

function createQuery($string) {
    global $words;
    $string = html_entity_decode(strtolower($string));
    $words = explode(' ', $string);

    $newords = getSubString($words);


    $first_chunk = implode(' ', $words);
    $first_chunk = str_replace(" and ", ",", $first_chunk);

    $q1 = explode(',', $first_chunk); //print_r($q1); exit;
    $q3 = '';
    $join = '';
    $count = 0;
    foreach ($q1 as $value) {

        $q2 = explode('=', $value);
        if (count($q2) > 1) {
            $operand = '=';
        } else {

            $q2 = explode('>', $value);
            if (count($q2) > 1) {
                $operand = '>';
            } else {
                $q2 = explode('<', $value);
                if (count($q2) > 1) {
                    $operand = '<';
                }
            }
        }

        $field = trim($q2[0]);
        $fvalue = trim($q2[1]);

        $result = mysqli_query($dbc,"SELECT id FROM nts_network.ntk_template_fields WHERE templ_id =5 and name = '" . $field . "'");
        $row = mysqli_fetch_array($result);
        $field_id = $row[0];

        if ($field === 'branch') {

            $result = mysqli_query($dbc,"SELECT Branch_ID FROM branch WHERE Branch_Name = '$fvalue'");
            $row = mysqli_fetch_array($result);
            $fieldvalue = $row[0];
        } else {
            $fieldvalue = $fvalue;
        }

        if ($count === 0) {
            $join .= " JOIN(SELECT device_id FROM ntk_field_values WHERE (field_id = " . $field_id . " AND field_value " . $operand . " '" . $fieldvalue . "') )activeDevices_" . $count . " ON ntk_device_records.id = activeDevices_" . $count . ".device_id" . PHP_EOL;
        } else {
            $num = $count - 1;
            $join .= " JOIN(SELECT device_id FROM ntk_field_values WHERE (field_id = " . $field_id . " AND field_value " . $operand . " '" . $fieldvalue . "') )activeDevices_" . $count . " ON activeDevices_" . $num . ".device_id = activeDevices_" . $count . ".device_id" . PHP_EOL;
        }
        ++$count;

        $q3 .= "(field_id = " . $field_id . " AND field_value='" . $fieldvalue . "'),";
    }
    $q3 = substr($q3, 0, strlen($q3) - 1);
    $q3 = str_replace(",", " OR ", $q3);

    return $join;
}

function getSubString() {
    global $words;
    $phrase = array_shift($words);
    if ($phrase === 'where') {
        return;
    } else {
        getSubString($words);
    }
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
