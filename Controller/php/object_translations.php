<?php

include_once '../../../config/config.php';
require("GeneralClass.php");

switch ($_GET['action']) {
    case 1:
        $bool = false;
        $lang_id = $_COOKIE['lang_id'];
        if ($lang_id == null) {
            $lang_id = 1;
        }
        //check if translation exists for the selected language
        $sql_check = "SELECT * FROM projects_object_translation WHERE  tree_id = '" . $_GET['id'] . "' AND language_id = '" . $lang_id . "' ";
        $res_check = mysqli_query($dbc,$sql_check) ;
        $cnt = mysqli_num_rows($res_check);
        if ($cnt > 0) {
            $msg = "There exist a translation in the selected language!";
            $bool = true;
        } else {
            $QRY_INSERT_TRANSLATION = "INSERT INTO projects_object_translation(`object_description`,`language_id`,`object_name`,`tree_id`) 
           VALUES('','" . $lang_id . "','','" . $_GET['id'] . "')";
            if (mysqli_query($dbc,$QRY_INSERT_TRANSLATION)) {
                $msg = "new translation created!";
            } else {
                $msg = mysqli_error($dbc);
                $bool = true;
            }
        }
        echo json_encode(array("response" => $msg, "newId" => mysqli_insert_id($dbc), "bool" => $bool));
        break;
    case 2:
        $value = $_POST['fieldvalue'];
        $column = $_POST['colId'];
        $SQLUPDate = "UPDATE projects_object_translation SET $column = '" . $value . "' WHERE id = '" . $_POST['id'] . "'";
        if (mysqli_query($dbc,$SQLUPDate)) {
            $msg = "Successfully Updated!";
        } else {
            $msg = "Error :" . mysqli_error($dbc);
        }
        echo json_encode(array('response' => $msg));
        break;
    case 3:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $result = mysqli_query($dbc,"SELECT object_description FROM projects_object_translation WHERE id=" . $id);
        $row = mysqli_fetch_array($result);
        $content = $row[0];

        echo json_encode(array("content" => $content));
        break;
    case 4:
       
        $id = $_POST['id'];
        $content = mysqli_real_escape_string($dbc,$_POST['notes']);

        $sql = "UPDATE projects_object_translation SET object_description = '" . $content . "' WHERE id =" . $id;
        if (mysqli_query($dbc,$sql)) {
            $msg = "Successfully saved!";
        } else {
            $msg = "Error : " . mysqli_error($dbc);
        }
        echo json_encode(array("message" => $msg));
        break;
    case 5:
        $lang_id = $_COOKIE['lang_id'];
        if ($lang_id == null) {
            $lang_id = 1;
        }
        $QRYDEL_TRANSLATION = "DELETE FROM projects_object_translation WHERE id = '" . $_GET['id'] . "'";
        if (mysqli_query($dbc,$QRYDEL_TRANSLATION)) {
            $msg = "Deleted!";
        } else {
            $msg = mysqli_error($dbc);
        }
        echo json_encode(array("response" => $msg));
        break;
    default:
        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<rows>";
        $id = $_GET['id'];
        $qry = "SELECT * FROM projects_object_translation WHERE tree_id  ='" . $id . "'";
        $res = mysqli_query($dbc,$qry) or die(mysqli_error($dbc) . $qry);
        while ($row = mysqli_fetch_array($res)) {
            echo "<row id = '" . $row["id"] . "'>";
            echo "<cell><![CDATA[" . $row["id"] . "]]></cell>";
            echo "<cell> {$row["object_name"]} </cell>";
            echo "<cell><![CDATA[" . $row["language_id"] . "]]></cell>";
            echo "<cell> {$row["object_title"]} </cell>";
            echo "<cell><![CDATA[" . $row["object_description"] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";

        break;
}

