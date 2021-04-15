<?php

include_once '../../../config.php';

//$id=$_GET['id'];

$action = $_GET['action'];
$date = date('Y-m-d H:i:s');

switch ($action) {

    case 1:

        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<rows>";

        $SQL = "SELECT * FROM project_properties WHERE project_id = '" . $_GET['project_id'] . "' AND  document_id = '" . $_GET['document_id'] . "' ORDER BY sort_id ASC";
        $RESULT = mysqli_query($dbc,$SQL) ;

        while ($row = mysqli_fetch_array($RESULT)) {

            echo "<row id = '{$row["label_id"]}'>";
            echo "<cell><![CDATA[" . $row['label_id'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['sort_id'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['label_name'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['variable_name'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['information'] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;


    case 2:
        $sql_sort_max = "SELECT max(sort_id) as mx FROM project_properties WHERE project_id = '" . $_GET['project_id'] . "' AND  document_id = '" . $_GET['document_id'] . "'";
        $res_mx = mysqli_query($dbc,$sql_sort_max) ;
        $row_mx = mysqli_fetch_assoc($res_mx);
        $sort = $row_mx['mx'];
        $sort++;
        $SQL = "INSERT INTO project_properties (project_id,document_id,sort_id) VALUES ('" . $_GET['project_id'] . "','" . $_GET['document_id'] . "','" . $sort . "')";
        $RESULT = mysqli_query($dbc,$SQL) ;

        if ($RESULT){
        $id = mysqli_insert_id($dbc);
        $SQL_DATA = "SELECT * FROM project_data WHERE project_data_project_id = '" . $_GET['project_id'] . "' AND  project_data_document_id = '" . $_GET['document_id'] . "' ORDER BY project_data_id ASC";
        $RESULT_DATA = mysqli_query($dbc,$SQL_DATA) ;

        while ($ROW_DATA = mysqli_fetch_array($RESULT_DATA)) {
            $SQL_INSERT_LABELS = "INSERT INTO project_data_items (project_data_id,project_data_label_id)"
                . " VALUES ('" . $ROW_DATA['project_data_id']. "','" . $id . "')";
            $RESULT_INSERT_LABELS = mysqli_query($dbc,$SQL_INSERT_LABELS) ;
        }
        $data['data'] = array('success' => $RESULT, 'id' => $id);
        
        }
        else
            $data['data'] = array('success' => $RESULT);

        echo json_encode($data);

        break;

    case 3:

        $SQL = "DELETE FROM project_properties WHERE label_id = " . $_GET['id'] . "";
        mysqli_query($dbc,$SQL) ;
        echo json_encode(array("response" => 'Deleted'));

        break;


    case 4:

        $index = $_GET["index"];
        $fieldvalue = $_GET["fieldvalue"];
        $id = $_GET["id"];
        $field = $_GET["colId"];
        $colType = $_GET["colType"];
        $fieldvalue = mysqli_real_escape_string($dbc,$fieldvalue);

        $updateResult = updateSQL("project_properties", $field, $fieldvalue, $id, "label_id", $colType);
        if ($updateResult)
            $data['data'] = array('response' => $updateResult, 'value' => $index);
        else
            $data['data'] = array('response' => $updateResult);

        echo json_encode($data);

        break;

    case 5:

        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<rows>";

        $SQL = "SELECT * FROM project_data LEFT JOIN trainees ON project_data.project_data_employee_id = trainees.IntranetID WHERE project_data_project_id = '" . $_GET['project_id'] . "' AND  project_data_document_id = '" . $_GET['document_id'] . "' ORDER BY project_data_id ASC";
        $RESULT = mysqli_query($dbc,$SQL) ;

        while ($row = mysqli_fetch_array($RESULT)) {
            $name = $row['FirstName'].' '.$row['SecondName'];
            echo "<row id = '{$row["project_data_id"]}'>";
            echo "<cell><![CDATA[" . $row['project_data_id'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['project_data_date_entry'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['FirstName'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['relation'] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 6:
        $date = date('Y-m-d H:i:s');
        $userlggd = filter_input(INPUT_GET, 'eid', FILTER_SANITIZE_NUMBER_INT);
        $SQL = "INSERT INTO project_data (project_data_date_entry,project_data_employee_id,project_data_project_id,project_data_document_id)"
                . " VALUES ('" . $date. "','" . $userlggd . "','" . $_GET['project_id'] . "','" . $_GET['document_id'] . "')";
        $RESULT = mysqli_query($dbc,$SQL) ;

        $project_data_id = mysqli_insert_id($dbc);

        if ($RESULT){
        $SQL_SELECT_LABELS = "SELECT * FROM project_properties WHERE project_id = '" . $_GET['project_id'] . "' AND  document_id = '" . $_GET['document_id'] . "' ORDER BY label_id ASC";
        $RESULT_LABELS = mysqli_query($dbc,$SQL_SELECT_LABELS) ;

        while ($row = mysqli_fetch_array($RESULT_LABELS)) {
            
            $SQL_INSERT_LABELS = "INSERT INTO project_data_items (project_data_id,project_data_label_id)"
                . " VALUES ('" . $project_data_id. "','" . $row['label_id'] . "')";
            $RESULT_INSERT_LABELS = mysqli_query($dbc,$SQL_INSERT_LABELS) ;

        }            
            
            $data['data'] = array('success' => true, 'id' => $id);
        }
        else{
            $data['data'] = array('success' => false,);
        }
        echo json_encode($data);

        break;

    case 7:

            $SQL = "DELETE FROM project_data WHERE id = " . $_GET['id'] . "";
            $RES = mysqli_query($dbc,$SQL);
            if ($RES) {
                
                echo json_encode(array("response" => $RES));
            } else {
                
                echo json_encode(array("response" => $RES));
            }
        break;

    case 8:

        $index = $_GET["index"];
        $fieldvalue = $_GET["fieldvalue"];
        $id = $_GET["id"];
        $field = $_GET["colId"];
        $colType = $_GET["colType"];
        $fieldvalue = mysqli_real_escape_string($dbc,$fieldvalue);

        $updateResult = updateSQL("project_data", $field, $fieldvalue, $id, "project_data_id", $colType);
        //echo $updateResult; exit;
        if ($updateResult)
            $data['data'] = array('response' => $updateResult, 'value' => $index);
        else
            $data['data'] = array('response' => $updateResult);

        echo json_encode($data);

        break;

    case 9:
        
        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo"<rows>";        
        $SQL = "SELECT * FROM project_properties WHERE project_id = '" . $_GET['project_id'] . "' AND  document_id = '" . $_GET['document_id'] . "' ORDER BY sort_id ASC";
        $RESULT = mysqli_query($dbc,$SQL) ;

        while ($row = mysqli_fetch_array($RESULT)) {
        $SQL_LABELS = "SELECT * FROM project_data_items WHERE project_data_id = '" . $_GET['project_data_id'] . "' AND  project_data_label_id = '{$row["label_id"]}' ORDER BY project_data_item_id ASC";
        $RESULT_LABELS = mysqli_query($dbc,$SQL_LABELS) ;
        $rows = mysqli_fetch_array($RESULT_LABELS);
            echo"<row id = '{$rows["project_data_item_id"]}'>";
                
                echo"<cell>{$row['label_name']}</cell>";
                echo"<cell type='ed'>{$rows["project_data_label_data"]}</cell>";
                echo"</row>";
        }        
        echo " </rows>";
        break;

    case 10:

        $id = $_GET["id"];
        $field = $_GET["field"];
        $fieldvalue = $_GET["fieldvalue"];

            $qry = "UPDATE project_data_items SET {$field} = '{$fieldvalue}' WHERE project_data_item_id = '{$id}'";
            $res = mysqli_query($dbc,$qry) or die(mysqli_error($dbc) . $qry);

       
        if ($res)
            $data['data'] = array('success' => $res);
        else
            $data['data'] = array('success' => $res);

        echo json_encode($data);
        break;

    case 11:
        ini_set("display_errors", 1);
        define('SMARTY_DIR', 'Smarty/libs/');
        require_once(SMARTY_DIR . 'Smarty.class.php');

        $template = new Smarty();

        $template->setTemplateDir('Smarty/templates/');
        $template->setCompileDir('Smarty/templates_c/');
        $template->setConfigDir('Smarty/configs/');
        $template->setCacheDir('Smarty/cache/');
        
        $SQL = "SELECT * FROM project_properties WHERE project_id = '" . $_GET['project_id'] . "' AND  document_id = '" . $_GET['document_id'] . "' ORDER BY label_id ASC";
        $RESULT = mysqli_query($dbc,$SQL) ;

        while ($row = mysqli_fetch_array($RESULT)) {
        $SQL_LABELS = "SELECT * FROM project_data_items WHERE project_data_id = '" . $_GET['project_data_id'] . "' AND  project_data_label_id = '{$row["label_id"]}' ORDER BY project_data_item_id ASC";
        $RESULT_LABELS = mysqli_query($dbc,$SQL_LABELS) ;
        $rows = mysqli_fetch_array($RESULT_LABELS);
        $label_data = $rows["project_data_label_data"];
        if (strlen($label_data) == 0){
        $template->assign($row['variable_name'], '................{'.$row['variable_name'].'}');
        }
        else{
        $template->assign($row['variable_name'], $rows["project_data_label_data"]);
        }
        }
        $SQL_NAME = "SELECT project_name FROM projects_dir WHERE id = '" . $_GET['project_id'] . "' ";
        $RES_NAME =  mysqli_query($dbc,$SQL_NAME) ;
        $ROW_NAME = mysqli_fetch_array($RES_NAME);
        $templateName = $ROW_NAME['project_name'];
 
        //get the template
        $report_id = $_GET['document_id'];
        $template_data = getItemByID("tradestar_reports",$report_id,"Report_ID");

        //write the document
        $fh = fopen("Smarty/templates/template.tpl", 'w') or die("can't open file");
        fwrite($fh,$template_data['Report_Body']);
        fclose($fh);         
        
        $html = $template->fetch('template.tpl');
//        $html = $template->fetch($templateName.'.tpl');
        $filename = 'files/'.$templateName.'_'.date('d-m-Y_hia') . '.pdf';

        require('dompdf/dompdf_config.inc.php'); 

        $dompdf = new DOMPDF();
        $dompdf->load_html($html);
        $dompdf->set_paper('a4', 'portrait');
        $dompdf->render();
        file_put_contents($filename, $dompdf->output());
        
        $data['data'] = array('success' => true, 'filename' => $filename);

        echo json_encode($data);
        
        break;

    case 12:

        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<rows>";

        echo "<head>";
        echo ("<column id='project_data_id' type='ro' align='left' sort='str'>ID</column>");
        echo ("<column id='project_data_date_entry' type='ro' align='left' sort='str'>Entry Date</column>");
        echo ("<column id='project_data_employee_id' type='ro' align='left' sort='str'>Employee Name</column>");
        
        $SQL_SELECT_LABELS = "SELECT * FROM project_properties WHERE project_id = '" . $_GET['project_id'] . "' AND  document_id = '" . $_GET['document_id'] . "' ORDER BY label_id ASC";
        $RESULT_LABELS = mysqli_query($dbc,$SQL_SELECT_LABELS) ;
        while ($rows = mysqli_fetch_array($RESULT_LABELS)) {

            echo ("<column id='" . $rows['label_id'] . "' type='ro' align='left' sort='str'><![CDATA[" . $rows['label_name'] . "]]></column>");
        }
        echo "</head>";

        $SQL = "SELECT * FROM project_data LEFT JOIN trainees ON project_data.project_data_employee_id = trainees.IntranetID WHERE project_data_project_id = '" . $_GET['project_id'] . "' AND  project_data_document_id = '" . $_GET['document_id'] . "' ORDER BY project_data_id ASC";
        $RESULT = mysqli_query($dbc,$SQL) ;

        while ($row = mysqli_fetch_array($RESULT)) {

            echo "<row id = '{$row["project_data_id"]}'>";
            echo "<cell><![CDATA[" . $row['project_data_id'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['project_data_date_entry'] . "]]></cell>";
            echo "<cell><![CDATA[" . $row['FirstName'] . "]]></cell>";
            
            $SQL_LABELS = "SELECT * FROM project_data_items WHERE project_data_id = '" . $row["project_data_id"] . "' ORDER BY project_data_label_id ASC";
            $RESULT_LABELS_Ex = mysqli_query($dbc,$SQL_LABELS) ;
            while ($rowx = mysqli_fetch_assoc($RESULT_LABELS_Ex)) {
            echo "<cell><![CDATA[" . $rowx['project_data_label_data'] . "]]></cell>";    
            }
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 13:
        $itemid = $_GET['itemId'];
        $documentId = $_GET['document_id'];
        $projectId = $_GET['project_id'];
        $sortid = $_GET['sortId'];
        $direction = $_GET['direction'];
        $projectfield = 'project_id';
        $documentfield = 'document_id';
        $itemidfield = 'label_id';
        $sortfield = 'sort_id';
        $table = 'project_properties';

        moveItemUpDownGrid($projectId, $projectfield, $documentId, $documentfield, $itemid, $itemidfield, $sortid, $sortfield, $table, $direction);
        break;

    case 14:
        $index = $_GET["index"];
        $fieldvalue = $_GET["fieldvalue"];
        $id = $_GET["id"];
        $field = $_GET["colId"];
        $colType = $_GET["colType"];
        $fieldvalue = mysqli_real_escape_string($dbc,$fieldvalue);

        $updateResult = updateSQL("project_specification", "project_value", $fieldvalue, $id, "id", $colType);
        //echo $updateResult; exit;
        if ($updateResult)
            $data['data'] = array('response' => $updateResult, 'value' => $index);
        else
            $data['data'] = array('response' => $updateResult);

        echo json_encode($data);

        break;
}
function getMinMaxSortID($projectId, $projectfield, $documentId, $documentfield, $sortid, $sortfield, $table, $minmax) {

    $sql = "SELECT {$minmax}({$sortfield}) AS minmax FROM {$table} WHERE {$sortid} > 0 AND {$projectfield} = {$projectId} AND {$documentfield} = {$documentId}";

    $res = mysqli_query($dbc,$sql) or die(mysqli_error($dbc) . " " . $sql);
    $row = mysqli_fetch_array($res);

    return $row["minmax"];
}

function moveItemUpDownGrid($projectId, $projectfield, $documentId, $documentfield, $itemid, $itemidfield, $sortid, $sortfield, $table, $direction) {

    if ($direction == "up") {

        $minmax = getMinMaxSortID($projectId, $projectfield, $documentId, $documentfield, $sortid, $sortfield, $table, "MIN");

        if ($sortid > $minmax) {

            $xsql = "UPDATE {$table} SET {$sortfield} = {$sortid} WHERE {$sortfield} = {$sortid}-1 AND {$projectfield} = {$projectId} AND {$documentfield} = {$documentId}";
            $xres = mysqli_query($dbc,$xsql) or die(mysqli_error($dbc) . " " . $xsql);

            $sql = "UPDATE {$table} SET {$sortfield} = {$sortid}-1 WHERE {$itemidfield} = {$itemid} AND {$projectfield} = {$projectId} AND {$documentfield} = {$documentId}";
            $res = mysqli_query($dbc,$sql) or die(mysqli_error($dbc) . " " . $sql);
        }
    } else if ($direction == "down") {

        $minmax = getMinMaxSortID($projectId, $projectfield, $documentId, $documentfield, $sortid, $sortfield, $table, "MAX");

        if ($sortid < $minmax) {

            $xsql = "UPDATE {$table} SET {$sortfield} = {$sortid} WHERE {$sortfield} = {$sortid}+1 AND {$projectfield} = {$projectId} AND {$documentfield} = {$documentId}";
            $xres = mysqli_query($dbc,$xsql) or die(mysqli_error($dbc) . " " . $xsql);

            $sql = "UPDATE {$table} SET {$sortfield} = {$sortid}+1 WHERE {$itemidfield} = {$itemid} AND {$projectfield} = {$projectId} AND {$documentfield} = {$documentId}";
            $res = mysqli_query($dbc,$sql) or die(mysqli_error($dbc) . " " . $sql);
        }
    }

    $data['data'] = array('success' => true, 'u' => $minmax, 'x' => $xsql);

    return $data;
}
function updateSQL($table,$field,$fieldvalue,$id,$idfield,$colType)
{
    $updateSQL = "UPDATE {$table} SET {$field} =";
    
    if ($colType == "int") 
    {
        $updateSQL .= "{$fieldvalue}";
    }
    else {
        $updateSQL .= "'{$fieldvalue}'";
    }
    $updateSQL .= " WHERE  {$idfield} = '{$id}'";
    //$updateSQL .= $condition;
    
    $updateResult = mysql_query ( $updateSQL ) or die ( "SQL Error saving {$field} in {$table} table: " . mysql_error ().$updateSQL); 
        
    return $updateResult;               
}
function getItemByID($table,$id,$idfield)
{
    if(!empty($id))
    {
        $sql= "SELECT * FROM {$table} WHERE {$idfield} = {$id} LIMIT 1";
        $rs = mysqli_query($dbc,$sql) or die(mysql_error ().$sql);
        if ($item = mysqli_fetch_array($rs))
        {
            return $item;
        }
        else
        {
            return NULL;
        }
    }
    else
    {
        return NULL;
    }   
}