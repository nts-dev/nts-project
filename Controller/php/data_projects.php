<?php

session_start(); // Starting Session
ini_set('display_errors', '0');
require 'config_mysqli.php';
date_default_timezone_set('UTC');
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_NUMBER_INT);
$date = date('Y-m-d H:i:s');

switch ($action) {

    default:

        break;
    case 1:

        $projectId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $templateId = filter_input(INPUT_GET, 'template_id', FILTER_SANITIZE_NUMBER_INT);

        $insert = "INSERT INTO ntk_assetcat_to_project(`asset_cat_id`,`project_id`,default_value) SELECT " . $templateId . "," . $projectId . ",IF(COUNT(*)>0,0,1) c FROM ntk_assetcat_to_project WHERE project_id = " . $projectId . " AND default_value = 1";
        $result = mysqli_query($dbc,$insert) ;

        if ($result) {
            $id = mysqli_insert_id($dbc);
            $data['data'] = array('success' => $result, 'id' => $id, 'text' => 'Successfully Added');
        } else
            $data['data'] = array('success' => $result, 'text' => 'An Error Occured While Adding');

        echo json_encode($data);

        break;

    case 2:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $sql = "SELECT * FROM ntk_assetcat_to_project WHERE id = " . $id;
        $result = mysqli_query($dbc,$sql);
        $row = mysqli_fetch_assoc($result);
        $default_value = $row['default_value'];
        $projectId = $row['project_id'];

        $delete = "DELETE FROM ntk_assetcat_to_project WHERE id = " . $id;
        $deleteResult = mysqli_query($dbc,$delete) ;
        if ($deleteResult) {
            if ($default_value > 0) {
                mysqli_query($dbc,"UPDATE ntk_assetcat_to_project SET default_value=1 WHERE project_id = " . $projectId . " ORDER BY id DESC LIMIT 1");
            }
            $data['data'] = array('response' => $deleteResult, 'text' => 'Successfully Deleted');
        } else {
            $data['data'] = array('response' => $deleteResult, 'text' => 'An Error Occured While Deleting');
        }
        echo json_encode($data);
        break;

    case 3:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $qry = "SELECT
                        ntk_assetcat_to_project.*,devices.name
                FROM
                        ntk_assetcat_to_project
                JOIN nts_network.ntk_templates ON ntk_templates.id = ntk_assetcat_to_project.asset_cat_id
                JOIN nts_network.ntk_devices devices ON ntk_templates.device_id = devices.id
                WHERE
                        project_id = " . $id;

        $res = mysqli_query($dbc,$qry) or die(mysqli_error($dbc) . $qry);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        while ($row = mysqli_fetch_array($res)) {

            echo "<row id = '" . $row["id"] . "'>";
            echo "<cell></cell>";
            echo "<cell><![CDATA[" . $row["asset_cat_id"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["name"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["default_value"] . "]]></cell>";
            echo "<cell><![CDATA[" . html_entity_decode($row["query"]) . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;


    case 4:

        $fieldvalue = filter_input(INPUT_POST, 'fieldvalue');
        $id = filter_input(INPUT_POST, 'id');
        $field = filter_input(INPUT_POST, 'colId');
        $colType = filter_input(INPUT_POST, 'colType');
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
        $projectId = $_POST["projectId"];
        $id = $_POST["id"];
        $field = $_POST["colId"];

        if ($fieldvalue > 0) {
            $res = mysqli_query($dbc,"UPDATE ntk_assetcat_to_project SET default_value = 0 WHERE project_id =" . $projectId) ;
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

        $deviceId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $projectId = filter_input(INPUT_GET, 'projectId', FILTER_SANITIZE_NUMBER_INT);
        $templateId = filter_input(INPUT_GET, 'templateId', FILTER_SANITIZE_NUMBER_INT);

        $result = mysqli_query($dbc,"SELECT query FROM `ntk_assetcat_to_project` WHERE `asset_cat_id` = " . $templateId . " AND `project_id` = " . $projectId);
        $row = mysqli_fetch_array($result);
        $string = $row[0];

        if ($string) {
            $filter = createQuery($string);
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
                                                            nts_site.branch
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
}



function updateSQL($table, $field, $fieldvalue, $id, $idfield, $colType) {
    $updateSQL = "UPDATE {$table} SET {$field} =";

    if ($colType == "int") {
        $updateSQL .= "{$fieldvalue}";
    } else {
        $updateSQL .= "'{$fieldvalue}'";
    }
    $updateSQL .= " WHERE  {$idfield} = '{$id}'";
    //$updateSQL .= $condition;

    $updateResult = mysqli_query($dbc,$updateSQL) or die("SQL Error saving {$field} in {$table} table: " . mysqli_error($dbc) . $updateSQL);

    return $updateResult;
}

