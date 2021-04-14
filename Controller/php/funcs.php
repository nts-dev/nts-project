<?php

$str = '';

function createTreeXML() {
    global $str;
    $str .= '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $str .= '<tree id="0">';
    treeDirXML();
    $str .= '</tree>';

    $filename = "../xml/";
    if (!file_exists($filename)) {
        mkdir($filename);
    }

    $filename .= "projects_tree.xml";

    $myfile = fopen($filename, "w+") or die("Unable to open file!");
    fwrite($myfile, $str);
    fclose($myfile);
}

function treeDirXML($showAll = false) {
    global $dbc;

    $str = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $str .= '<tree id="0">';

    $query = "SELECT id,parent_id,project_name,sort_id FROM projects_dir";
    if (!$showAll) {
        $query .= " WHERE archive = 0 ";
    }
    $query .= " ORDER BY parent_id = 0 DESC,project_name asc";

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
    $x = 0;
    foreach ($roots as $obj) {
        ++$x;
        printInnerXML($obj, $x, true);
    }

    $str .= '</tree>';

    $filename = "../xml/";
    if (!file_exists($filename)) {
        mkdir($filename);
    }

    $filename .= "projects_tree.xml";

    $myfile = fopen($filename, "w+") or die("Unable to open file!");
    fwrite($myfile, $str);
    fclose($myfile);
}

function printInnerXML(stdClass $obj, $x, $isRoot = false) {
    global $str;

    $itemName = xml_entities($obj->name);
    $itemId = $obj->id;
    $no = generateProjectId($itemId);

    $str .= "<item id='" . $obj->id . "' text='" . $x . ". " . $no . "| " . $itemName . "'>" . PHP_EOL;
    $str .= '<userdata name="thisurl">index.php?page=' . $obj->id . '</userdata>' . PHP_EOL;
    $y = 0;
    foreach ($obj->children as $child) {
        ++$y;
        printXML($child, $y);
    }
    $str .= '</item>';
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

function updateSQL($table, $field, $fieldvalue, $id, $idfield, $colType) {
    global $dbc;
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
