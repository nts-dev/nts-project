<?php

ini_set('display_errors', '0');
require 'config_mysqli.php';
include_once 'GeneralFunctions.php';
$action = $_GET['action'];

switch ($action) {
    
    case 1:
        
        $SELECT = "INSERT INTO `sheet_pk` (`id`) VALUES (NULL)";
        mysqli_query($dbc,$SELECT) ;
        $newSheet = mysqli_insert_id($dbc);
        echo json_encode(array("sheetid" => $newSheet));
        break;

    case 2:
        
        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<rows>";
        $SQL = "SELECT * FROM project_to_spreadsheet WHERE project_id =  '".$_GET['id']."'";
        $RESULT = mysqli_query($dbc,$SQL) ;
        while($ROW = mysqli_fetch_array($RESULT)){

            echo "<row id = '{$ROW["sheet_id"]}'>";
            echo "<cell><![CDATA[" . $ROW['name'] . "]]></cell>";
            echo "<cell></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;
        
    case 3:
        
        $SELECT = "SELECT * FROM project_to_spreadsheet WHERE project_id= '".$_GET['project_id']."' AND sheet_id= '".$_GET['sheet_id']."'";
        $RES = mysqli_query($dbc,$SELECT) ;
        if(mysqli_num_rows($RES)>0){
        
        $UPDATE = "UPDATE project_to_spreadsheet SET name = '".$_GET['name']."' WHERE project_id= '".$_GET['project_id']."' AND sheet_id= '".$_GET['sheet_id']."'";
        mysqli_query($dbc,$UPDATE) ;
        }
        else{
        $SQL = "INSERT INTO project_to_spreadsheet (project_id,sheet_id,name) VALUES ('".$_GET['project_id']."','".$_GET['sheet_id']."','".$_GET['name']."')";
        mysqli_query($dbc,$SQL) ;
        }
        echo json_encode(array("message" => "Success"));
        
        break;
    
    case 4:
        
        $SQL = "SELECT * FROM project_to_spreadsheet WHERE project_id ='".$_GET['id']."'";
        $RESULT = mysqli_query($dbc,$SQL) ;
        
        if(mysqli_num_rows($RESULT)>0){
        $SQLx = "SELECT max(sheet_id) as sheet FROM project_to_spreadsheet WHERE project_id ='".$_GET['id']."'";
        $RESULTx = mysqli_query($dbc,$SQLx) ;
        $ROWx = mysqli_fetch_array($RESULTx);
        $sheetID = $ROWx['sheet'];
        }
        else{
        $SELECT = "INSERT INTO `sheet_pk` (`id`) VALUES (NULL)";
        mysqli_query($dbc,$SELECT) ;
        $sheetID = mysqli_insert_id($dbc);

        }
        echo json_encode(array("sheetId" => $sheetID));
        break;
		
    case 5:
        
        $SELECT = "DELETE FROM project_to_spreadsheet WHERE project_id= '".$_GET['project_id']."' AND sheet_id= '".$_GET['sheet_id']."'";
        $RES = mysqli_query($dbc,$SELECT) ;
        echo json_encode(array("message" => "Deleted"));
        
        break;
}
