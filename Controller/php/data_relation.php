<?php

include_once '../../../config/config.php';
include_once 'GeneralFunctions.php';

$action=$_GET['action'];

switch ($action) {
    default:
        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<rows>";

        $SQL = "SELECT * FROM project_to_relation where project_id='".$_GET['id']."'";
        $RESULT = mysqli_query($dbc,$SQL) ;
        
        while ($row = mysqli_fetch_array($RESULT)) {
            
            $SQL2 = "SELECT * FROM relation WHERE relation_id = $row[relation_id]";
            $RES2 = mysqli_query($dbc,$SQL2) ;
            
            while ($ROWS = mysqli_fetch_array($RES2)) {
                
            echo "<row id = '{$row["relation_id"]}'>";
            echo "<cell><![CDATA[" . $ROWS['relation_id'] . "]]></cell>";
            echo "<cell><![CDATA[" . $ROWS['search_code'] . "]]></cell>";
            echo "<cell><![CDATA[" . $ROWS['relation_company'] . "]]></cell>";
            echo "<cell><![CDATA[" . $ROWS['RelCountryId'] . "]]></cell>";
            echo "<cell><![CDATA[" . $ROWS['StatusID'] . "]]></cell>";
            echo "</row>";  
            }
        }
        echo "</rows>";
        break;
        
    case 1:
        
        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<rows>";

        $SQL = "SELECT * FROM relation_contact where relation_id = '".$_GET['id']."'";
        $RESULT = mysqli_query($dbc,$SQL) ;

        while ($row = mysqli_fetch_array($RESULT)) {

            echo "<row id = '{$row["contact_id"]}'>";
            echo "<cell><![CDATA[" . $row['contact_id'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['contact_firstname'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['contact_secondname'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['contact_lastname'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['contact_birthday'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['contact_gender'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['contact_telephone'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['email'] . "]]></cell>";
            echo "<cell></cell>";
            echo "<cell><![CDATA[" . $row['contact_status_id'] . "]]></cell>";            
            echo "</row>";
        }
        echo "</rows>";
        break;
    case 2:

//        //render  table/grid  
//        require("dhtmlx_connector_classic/grid_connector.php");
//        $grid = new GridConnector($res);
//        // $grid->enable_log("templates/temp.log", true);
//
//        $qry = "CREATE TEMPORARY TABLE TempTable 
//            SELECT * FROM relation ORDER BY relation_id ASC";
//
//        $merge_qry = mysqli_query($dbc,$qry);
//        $grid->render_table("TempTable", "relation_id", "relation_id,search_code,relation_company,RelCountryId,StatusID");
        
        
        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<rows>";

        $SQL = "SELECT * FROM relation_contact where relation_id = '".$_GET['id']."'";
        $RESULT = mysqli_query($dbc,$SQL) ;

        while ($row = mysqli_fetch_array($RESULT)) {

            echo "<row id = '{$row["contact_id"]}'>";
            echo "<cell><![CDATA[" . $row['contact_id'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['contact_firstname'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['contact_secondname'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['contact_lastname'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['contact_birthday'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['contact_gender'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['contact_telephone'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['email'] . "]]></cell>";
            echo "<cell></cell>";
            echo "<cell><![CDATA[" . $row['contact_status_id'] . "]]></cell>";            
            echo "</row>";
        }
        echo "</rows>";

        break;
        
    case 3:
     
        
        $sql = "Insert into project_to_relation (project_id, relation_id)
          values ('".$_POST['project_id']."','".$_POST['relation_id']."')";
        
         $result = mysqli_query($dbc,$sql) ;
         
         if ($result) {
            $msg = "Relation Added";
        } else {
            $msg = "Error : " . mysqli_error($dbc);
        }
             
        echo json_encode(array("message" => $msg));
        break;
        
    case 4:
        
        $SQL = "DELETE FROM project_to_relation WHERE relation_id=" . $_GET['relation_id'] . " AND project_id = " . $_GET['project_id'] . "";
        mysqli_query($dbc,$SQL) ;
        echo json_encode(array("response" => 'Deleted'));
        break;
}

