<?php

include_once '../../../config.php';
$language_id = 1;
$id = $_GET['id'];
switch ($_GET['action']) {

    case 3:
        //new blank recur
        $qry_Evt = "Select Max(event_id) id_mx from events";
        $res_Evt = mysqli_query($dbc,$qry_Evt) ;
        $row_Evt = mysqli_fetch_array($res_Evt);
        $max_Evt = $row_Evt['id_mx'];
        $max_Evt++;
        $SQL = "SELECT FirstName,ID FROM trainees WHERE IntranetID = '" . $_POST['contact'] . "'";
        $RES = mysqli_query($dbc,$SQL) ;
        $ROW = mysqli_fetch_assoc($RES);
        $qryEvent = "INSERT INTO events(`event_id`,`event_name`,`details`,`employee_id`,`start_date`,`end_date`,`entered_by`,`cat_id`,`event_pid`,`assigned_eid`)
                             VALUES
                             ('$max_Evt','','','" . $ROW['ID'] . "',now(),now(),'" . $_POST['contact'] . "',1,'" . $_POST['parent'] . "','" . $ROW['FirstName'] . "') ";
        if (mysqli_query($dbc,$qryEvent)) {
            $msg = "Successfully created event!";
        } else {
            $msg = "Error " . mysqli_error($dbc);
        }
        echo json_encode(array("response" => $msg, "event_name" => $rowDoc['doc_topic'], "new_id" => mysqli_insert_id($dbc)));
        break;
    case 4:
        //get the trainee id for the intranet id sent
        $SQL = "SELECT * FROM trainees WHERE IntranetID = '" . $_GET['id'] . "'";
        $RES = mysqli_query($dbc,$SQL) ;
        $ROW_RES = mysqli_fetch_assoc($RES);
        echo json_encode(array("response" => $ROW_RES['IntranetID']));
        break;
    case 5:
        $proj_id = $_POST['proj_id'];
        $userString = implode(",", $_POST['arrayObj']);
        $sql_update = "UPDATE projects_dir SET proj_uID = '" . $userString . "' WHERE id = '" . $_POST['proj_id'] . "'";
        mysqli_query($dbc,$sql_update) ;
        break;

    case 7:
        //Get descriibed Items
        $langQuery = "Select Branch_ID as a1,Branch_Name as a2 from branch  order by Branch_ID asc";
        $resLang = mysqli_query($dbc,$langQuery);

        $sql_get_user_string = "SELECT default_privilege FROM project_privileges WHERE project_id = '" . $_GET['id'] . "'";
        $res_get_user = mysqli_query($dbc,$sql_get_user_string) ;
        $row_get_user = mysqli_fetch_assoc($res_get_user);
        $userArray = explode(",", $row_get_user['default_privilege']);

        header("Content-type:text/xml");
        ini_set('max_execution_time', 600);
        print("<?xml version=\"1.0\"?>");

        echo " <complete>";
        while ($row = mysqli_fetch_array($resLang)) {

            if (in_array($row['a1'], $userArray)) {
                $checked = "checked = '1'";
            } else {
                $checked = "";
            }

            print("<option " . $checked . " value=\"" . $row["a1"] . "\">");
            print($row["a2"]);
            print("</option>");
        }
        print("</complete>");

        break;
    case 8:
        //render  table/grid  
        require("dhtmlx_connector_classic/grid_connector.php");
        $grid = new GridConnector($res);
        // $grid->enable_log("templates/temp.log", true);
        
        if ($id == '') {
            $id = 0;
        }
        $is_dir = explode("_", $id);
        if ($is_dir[0] == 'dir') {
            $id = $is_dir[1];
            $sqlDir = "AND t.is_dir = 1";
        } //temp for dirs 


        $sql_get_user_string = "SELECT proj_uID FROM projects_dir WHERE id = '" . $id . "'";
        $res_get_user = mysqli_query($dbc,$sql_get_user_string) ;
        $row_get_user = mysqli_fetch_assoc($res_get_user);
        //$userArray = explode(",", $row_get_user['doc_uID']);


        $qry = "CREATE TEMPORARY TABLE TempTable 
            SELECT c.contact_id, c.contact_attendent ,xl1.groupid as NTSUser ,xl2.groupid as WebAdmin,t.E,t.S,t.A FROM  trainees tr ,relation_contact c
            LEFT JOIN xoops_groups_users_link xl1 ON xl1.contact_id = c.contact_id AND xl1.groupid = 4
            LEFT JOIN xoops_groups_users_link xl2 ON xl2.contact_id = c.contact_id AND xl2.groupid = 1
            LEFT JOIN project_privileges t ON t.contact_id = c.contact_id AND t.project_id = '" . $id . "'
            WHERE (c.relation_id =6374 ||  c.relation_id = 1) " . $sqlDir . "
            AND tr.IntranetID = c.contact_id AND tr.status_id = 1 AND c.contact_id in (" . $row_get_user['proj_uID'] . ") 
            group by c.contact_id";
        //echo $qry ; exit(); 
        $merge_qry = mysqli_query($dbc,$qry);
        $grid->render_table("TempTable", "contact_id", "contact_id,contact_attendent,NTSUser,WebAdmin,E,S,A");
        break;

    case 9:
        $proj_id = $_POST['proj_id'];
        $userString = implode(",", $_POST['arrayObj']);
        $sql_update = "UPDATE projects_dir SET proj_uID = '" . $userString . "' WHERE id = '" . $_POST['proj_id'] . "'";
        mysqli_query($dbc,$sql_update) ;
        break;
    case 10 :

        $get_id = $_POST['doc_id'];
        $column = $_POST['column'];
        $is_dir = 1;
        $selDoc_id = explode("_", $get_id);
        if ($selDoc_id[0] == "dir") {
            $doc_id = $selDoc_id[1];
            $sql_dir = " AND is_dir = 1";
            
        } else {
            $doc_id = $_POST['doc_id'];

        }
        //$cId = $_COOKIE['ad_u'];
        $cId = $_POST['contact_id'];
        $SQL = "SELECT * FROM project_privileges  WHERE project_id = '" . $doc_id . "' AND contact_id = '" . $cId . $sql_dir . "' ";
        $resSQL = mysqli_query($dbc,$SQL) ;
        if (mysqli_num_rows($resSQL) == 0) {
            //new
            $SQL_ = "INSERT INTO project_privileges(`project_id`,`contact_id`,`$column`,`privileges`,`is_dir`) VALUES('" . $doc_id . "','" . $cId . "','" . $_POST['value'] . "','3',$is_dir)";
            $msg = "Saved!";
        } else {
            //update
            $SQL_ = "UPDATE project_privileges SET " . $_POST['column'] . " = '" . $_POST['value'] . "' WHERE contact_id = '" . $cId . $sql_dir . "' AND project_id = '" . $doc_id . "'";
            $msg = "Updated!";
        }
        mysqli_query($dbc,$SQL_) ;
        echo json_encode(array("response" => $msg, "debug" => $SQL_));

        break;
    default:

        $qry_Evt = "Select Max(event_id) id_mx from events";
        $res_Evt = mysqli_query($dbc,$qry_Evt) ;
        $row_Evt = mysqli_fetch_array($res_Evt);
        $max_Evt = $row_Evt['id_mx'];
        $max_Evt++;

        $selDoc = "SELECT * FROM tbdocuments WHERE doc_id = '" . $_POST['doc_id'] . "' AND doc_lang_id = '" . $_POST['lang_id'] . "'";
        $resDoc = mysqli_query($dbc,$selDoc) ;
        $rowDoc = mysqli_fetch_assoc($resDoc);
        $empl = $_POST['contact_id']; //$_POST['contact_id']
        //get the trainee
        $SQL = "SELECT FirstName,ID FROM trainees WHERE IntranetID = '" . $empl . "'";
        $RES = mysqli_query($dbc,$SQL) ;
        $ROW = mysqli_fetch_assoc($RES);
        $tID = $ROW['ID'];
        if ($tID == null) {
            $tID = $empl;
        }
        $enddt = date("Y-m-d", mktime(0, 0, 0, 12, 31));
        $day_number = date('N');
        $qryEvent = "INSERT INTO events(`event_id`,`event_name`,`details`,`employee_id`,`start_date`,`end_date`,`entered_by`,`cat_id`,`event_pjd`,`document_id`,`rec_type`)
                            VALUES
                            ('$max_Evt','$rowDoc[doc_topic]','$rowDoc[doc_topic]','$tID',now(),'$enddt','$empl',45,'" . $max_Evt . "','" . $_POST['doc_id'] . "','" . $day_number . "') ";

        $sql_is_planned = "SELECT * FROM tbdocuments_privileges WHERE doc_id = '" . $_POST['doc_id'] . "' AND contact_id = '" . $empl . "'";
        $res_is_planned = mysqli_query($dbc,$sql_is_planned) ;
        $row_is_planned = mysqli_fetch_assoc($res_is_planned);
        if ($row_is_planned['planning'] == 1) {
            $msg = "<b>" . $ROW['FirstName'] . "</b> has already been assigned on <br><b>" . $rowDoc['doc_topic'] . "</b> document!";
            $newId = $row_is_planned['event_id'];
        } else {

            if (mysqli_query($dbc,$qryEvent)) {
                $msg = "Successfully created event!";
                $newId = mysqli_insert_id($dbc);
            } else {
                $msg = "Error " . mysqli_error($dbc);
            }
        }
        echo json_encode(array("response" => $msg, "event_name" => $rowDoc['doc_topic'], "new_id" => $newId));
        break;
}
?>
