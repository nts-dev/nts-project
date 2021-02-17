<?php

require 'config_mysqli.php';
require_once("SpecialCharacter.php");

$language_id = 1;
$id = $_GET['id'];

switch ($_GET['action']) {

    case 1:
        //do insert to event
        //first get max_id from event
        $qry_pl = "Select Max(event_id) id_mx from events";
        $res_pl = mysqli_query($dbc, $qry_pl);
        $row_pl = mysqli_fetch_array($res_pl);
        $max_pl = $row_pl['id_mx'];
        $max_pl++;

        $empl = $_GET['emp'];
        if ($empl == "undefined" || $empl == "") {
            $empl = 31;
        }

        switch ($_GET['fm']) {
            case 1:
                //update for form 1                            
                break;
            case 2:
                //update for form 2 
                $date1 = new DateTime($_POST['start_date']);
                $s1 = $date1->format('Y-m-d H:i:s'); /* $s1 = $date1->format('Y-m-d'); */
                $date2 = new DateTime($_POST['end_date']);
                $s2 = $date2->format('Y-m-d H:i:s');
                $qry_update2 = "Update events set start_date = '$s1',end_date = '$s2' where event_id = '$_GET[tbpl]' ";
                mysqli_query($dbc, $qry_update2);
                break;
            case 3:
                //update form 3
                break;

            default:
                //insert item on the events master item 
                $uID = $_COOKIE['userlggd'];
                if ($uID == "") {
                    $uID = 0;
                }

                $qry_insert = "Insert into events(`event_id`,`event_name`,`details`,`employee_id`,`start_date`,`end_date`,`entered_by`,`cat_id`,`event_pid`)
                            values
                            ('$max_pl','$_POST[event_name]','$_POST[details]','$empl',now(),now(),'$uID',1,null) "; //new item created is a normal task //$_POST[cat_id]

                mysqli_query($dbc, $qry_insert);
                break;
        }
        echo json_encode(array("response" => $max_pl, "dt_ev" => $max_pl));
        break;


    case 2:

        //get maximum event for the event_child
        $qry_evt = "Select Max(event_id) evt_mx from events";
        $res_evt = mysqli_query($dbc, $qry_evt);
        $row_evt = mysqli_fetch_array($res_evt);
        $max_evt = $row_evt['evt_mx'];
        $max_evt++;


        //run the occuring event on scheduler        
        switch ($_GET['fm']) {
            case 1:
                //update for form 1                            
                break;
            case 2:
                //update for form 2                              
                $chkvalues = $_POST['days_select'];
                $dbString = array();
                foreach ($chkvalues as $key => $value) {//check for optin and opt out
                    if ($value == 1) {
                        $dbString[] = $key;
                    }
                }
                $dbString = implode(',', $dbString);
                switch ($_POST['freq']) {
                    case 1:
                        $rectype = "week_1___";
                        break; //Freq every week
                    case 2:
                        $rectype = "week_2___";
                        break; //Every two week
                    case 3:
                        $rectype = "month_1___";
                        break; //Every month
                    case 4:
                        $rectype = "week_12___";
                        break; //every 12 weeks
                    case 5:
                        $rectype = "month_6___";
                        break; //every half year
                    case 6:
                        $rectype = "year_1___";
                        break; //every year                              
                }
                $date1 = new DateTime($_POST['start_date']);
                $s1 = $date1->format('Y-m-d H:i:s'); /* $s1 = $date1->format('Y-m-d'); */
                $date2 = new DateTime($_POST['end_date']);
                $s2 = $date2->format('Y-m-d H:i:s');
                $rectype = $rectype . $dbString . "#no";

                $qry_update2 = "Update events set rec_type = '$rectype',start_date = '$s1',end_date = '$s2' where event_id = '$_GET[evtId]' ";
                mysqli_query($dbc, $qry_update2);
                break;
            case 3:
                //update form 3
                break;

            default:
                //create a default child in events table 
                $date = date('Y-m-d H:i:s');
                $uID = $_COOKIE['userlggd'];
                if ($uID == "") {
                    $uID = 0;
                }
                $qry_insert = "Insert into events(`event_id`,`event_pid`,`event_name`,`details`,`start_date`,`end_date`,`employee_id`,`event_length`,`entered_by`)
                             values('$max_evt','$_GET[evtId]','$_POST[event_name]','$_POST[details]','$date','$date','$_GET[emp]',300,'$uID') ";
                mysqli_query($dbc, $qry_insert);
                break;
        }
        echo json_encode(array("response" => $max_evt, "dt_ev" => $max_evt));

        break;

    case 3:
        $sql = "SELECT event_id,cat_id,event_name as details,event_name as event_name_child,details as event_name,info as information,employee_id,event_pid,DATE_FORMAT(start_date, '%Y-%m-%d') as start_date,DATE_FORMAT(end_date, '%Y-%m-%d') as end_date,DATE_FORMAT(start_date, '%H:%i') as begn,DATE_FORMAT(end_date, '%H:%i') as end,event_length as freq,rec_type,is_variable as variable,map,masterrecord,duration,reoccur_map from events where event_id = '$id'";
        header("Content-type: text/xml");
        echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n");

        $res = mysqli_query($dbc, $sql);
        print("<data>");

        while ($row = mysqli_fetch_array($res)) {
            print("<event_id>" . $row['event_id'] . "</event_id>");
            print("<event_name><![CDATA[" . $row['event_name'] . "]]></event_name>");
            print("<variable><![CDATA[" . $row['variable'] . "]]></variable>");
            print("<event_name_child><![CDATA[" . $row['event_name_child'] . "]]></event_name_child>");
            print("<details><![CDATA[" . $row['details'] . "]]></details>");
            print("<freq>" . $row['freq'] . "</freq>");
            print("<rec_type>" . $row['rec_type'] . "</rec_type>");
            print("<emp>" . $row['employee_id'] . "</emp>");
            print("<info><![CDATA[" . $row['information'] . "]]></info>");
            print("<event_pid>" . $row['event_pid'] . "</event_pid>");
            print("<cat_id>" . $row['cat_id'] . "</cat_id>");
            print("<start_date>" . $row['start_date'] . "</start_date>");
            print("<end_date>" . $row['end_date'] . "</end_date>");
            print("<period>" . $row['duration'] . "</period>");
            print("<begn>" . $row['begn'] . "</begn>");
            print("<end>" . $row['end'] . "</end>");
            print("<map>" . $row['map'] . "</map>");
            print("<masterrecord>" . $row['masterrecord'] . "</masterrecord>");
            print("<reoccur_map>" . $row['reoccur_map'] . "</reoccur_map>");
        }
        print("</data>");

        break;

    case 4:
        //render categories
        require("../../../../../../script/dhtmlx_connector_classic/grid_connector.php");
        $grid = new GridConnector($res);
        $grid->enable_log("templates/temp.log", true);
        $grid->dynamic_loading(1000);
        $sql = "Select * from events_categories";
        $grid->render_sql($sql, "id", "id,categories_name,assigned_to");
        break;
    case 6:
        //get start and end date

        $stdate = explode("GMT", $_POST['start_date']);
        $edate = explode("GMT", $_POST['end_date']);

        $startDate = $stdate[0];
        $dateSpan = new DateTime($stdate[0]);
        $sPan = $dateSpan->format('Y-m-d');
        $endDate = $edate[0];
        $cat = $_POST['cat_id'];
        if ($cat == null || $cat == '')
            $cat = 45;

        $qry_par = "Select document_id from events where event_id =" . $_POST['event_id'];
        $res_par = mysqli_query($dbc, $qry_par) or die(mysqli_error($dbc));
        $row_par = mysqli_fetch_array($res_par);
        $document_id = $row_par['document_id']; //
        $empl = $_GET['emp'];
        if ($empl == "undefined" || $empl == "") {
            $empl = 1;
        }

        //set main task field = true
        mysqli_query($dbc, "UPDATE `events` SET main_task = 1 WHERE event_id=" . $_POST['event_id']) or die(mysqli_error($dbc));

        //update for form 2                              
        $chkvalues = $_POST['days_select'];
        $dbString = array();
        foreach ($chkvalues as $key => $value) {//check for optin and opt out
            if ($value == 1) {
                $dbString[] = $key;
            }
        }
        $typ = "day";
        $date1 = new DateTime($stdate[0]);
        $s1 = $date1->format('Y-m-d H:i:s');
        $date2 = new DateTime($edate[0]);
        $s2 = $date2->format('Y-m-d H:i:s');
        //if the time is set:overite the time set on calendar popup.
        $bgntime = $_POST['begn'];
        $endtime = $_POST['end'];
        if ($bgntime != '') {
            $cdtstart = new DateTime($s1);
            $s1 = $cdtstart->format('Y-m-d');
            $s1 = $s1 . " " . $bgntime;
            $cdtend = new DateTime($s2);
            $s2 = $cdtend->format('Y-m-d');
            $s2 = $s2 . " " . $endtime;
        }


        $empResBranch = mysqli_query($dbc, "SELECT DISTINCT ID,branch_id FROM trainees WHERE ID IN (" . $_GET['ass_emp'] . ") ORDER BY ID ASC") or die(mysqli_error($dbc));
        $branchOfEmp = array();
        while ($rowBranch = mysqli_fetch_assoc($empResBranch)) {
            $branchOfEmp[] = $rowBranch['branch_id'];
        }

        if (in_array('6', $branchOfEmp)) {
            $evtCountry = 1;
        } else {
            $evtCountry = 2;
        }

//        $resHols = mysqli_query($dbc, "SELECT * FROM `hrm_events` WHERE `country` = '" . $evtCountry . "'") or die(mysqli_error($dbc));
//        while ($rowsH = mysqli_fetch_assoc($resHols)) {
//            $holdays[] = $rowsH['start_date'];
//        }

        $holdays = array();

        switch ($_POST['freq']) {
            //switch days days
            case 1:
                $interval = 7;
                break;
            case 2:
                $interval = 14;
                break;
            case 3:
                $interval = 1;
                $typ = "month";
                createInt($max_pl, $s1, $s2, $interval, $typ, $endtime, $dbString, $document_id, $holdays);
                exit();
                break;
            case 4:
                $interval = 84;
                break;
            case 5:
                $interval = 6;
                $typ = "month";
                createInt($max_pl, $s1, $s2, $interval, $typ, $endtime, $dbString, $document_id, $holdays);
                exit();
                break;
            case 6:
                $interval = 366;
                /* $interval = 1;
                  $typ = "year";
                  createInt($max_pl, $s1, $s2, $interval, $typ, $endtime, $dbString, $document_id, $holdays);
                  exit(); */
                break;
            case 7:
                $interval = 28;
                break;
            case 8:
                $interval = 2;
                $typ = "month";
                createInt($max_pl, $s1, $s2, $interval, $typ, $endtime, $dbString, $document_id, $holdays);
                exit();
                break;
            case 9:
                $interval = 56;
                break;
            case 10:
                $interval = 21;
                break;
            default :
                $interval = 365;
                break; //handle date infinite 							 
        }
        //clears and work with the variable day set 
        if ($variable == 1) {
            unset($dbString);
            $dbString[] = date('N', strtotime($startDate));
        }
        $seldays = $dbString;
        $s = $s1;
        //get the day number                             
        $day_number = date('N', strtotime($s));
        foreach ($seldays as $key => $value) {
            $emp = explode(",", $_GET['ass_emp']);
            $new_string = "" . implode("','", $emp) . "";
            $endDate = $edate[0];
            $startDate = $s;
            if ($day_number != $value) {
                $z = $day_number - $value;
                $w = 7;
                $y = $w - $z;
                $y = str_replace("-", "", $y);

                if ($z < 0) {
                    $z = str_replace("-", "", $z);
                    $startDate = date('Y-m-d H:i:s', strtotime($startDate . $z . " day"));
                } else {
                    $startDate = date('Y-m-d H:i:s', strtotime($startDate . $y . " day"));
                }
            }


            while (strtotime($startDate) < strtotime($endDate)) {
                //select employee_id sent by category table 
                $empQuery = "Select DISTINCT ID as a1,FirstName as a2 from trainees where ID IN(" . $_GET['ass_emp'] . ") order by ID asc";
                $empRes = mysqli_query($dbc, $empQuery) or die(mysqli_error($dbc));
                while ($row_pl = mysqli_fetch_array($empRes)) {
                    $max_pl++;
                    $empID = $row_pl['a1'];
                    //task period withing time set
                    $tskend = new DateTime($startDate);
                    $tskend = $tskend->format('Y-m-d');
                    $tskend = $tskend . " " . $endtime;
                    //user logged in enters tasks
                    $uID = $_COOKIE['userlggd'];
                    if ($uID == "") {
                        $uID = 0;
                    }
                    //filter for saturday and sunday events
                    if ($day_weeknd == 6 && $_POST['variable'] == 1) {
                        $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -1 day"));
                    }
                    if ($day_weeknd == 7 && $_POST['variable'] == 1) {
                        $startDate = date('Y-m-d H:i:s', strtotime($startDate . " +1 day"));
                    }
                    $emp_strdb = $row_pl['FirstName'];

                    $checkHolidayDate = new DateTime($startDate);
                    $checkHolidayDate = $checkHolidayDate->format('Y-m-d');
                    if (in_array($checkHolidayDate, $holdays)) {
                        $evtDateH = date('Y-m-d H:i:s', strtotime($startDate . " -1 week"));
                    } else {
                        $evtDateH = $startDate;
                    }

                    $qry_insert = "INSERT INTO `events` (`event_pid`,`event_name`,`details`,`start_date`,`end_date`,`employee_id`,`event_length`,`cat_id`,`entered_by`,`assigned_eid`,`visible`,`document_id`,`is_procedure`) SELECT '" . $_POST['event_id'] . "','" . $_POST['details'] . "','" . $_POST['event_name'] . "','$evtDateH','$tskend','$empID',null,'$cat','$uID','" . $row_pl['a1'] . "',0,'$document_id',`is_procedure` FROM `events` WHERE event_id =" . $_POST['event_id'];
                    mysqli_query($dbc, $qry_insert) or die(mysqli_error($dbc));
                }
                $startDate = date('Y-m-d H:i:s', strtotime($startDate . " + " . $interval . $typ));
            }
            $sql_event_update = "update events set target = 1 where event_id = " . $_POST['event_id'];
            mysqli_query($dbc, $sql_event_update) or die(mysqli_error($dbc));
        }

        break;


    case 7:

        //handle the save for assign to employees                         
        function gridManipulations($mode, $rowId, $ref_id)
        {
            $sql = "UPDATE events_categories SET
                             `categories_name` =  '" . $_GET["c1"] . "', 
                             `assigned_to` =  '" . $_GET["c2"] . "'                    
                              WHERE id =" . $_GET["gr_id"];
            mysqli_query($dbc, $sql);
            return "update";
        }

        error_reporting(E_ALL ^ E_NOTICE);
        header("Content-type: text/xml");
        echo('<?xml version="1.0" encoding="iso-8859-1"?>');
        $mode = $_GET["!nativeeditor_status"];
        $rowId = $_GET["gr_id"];
        $action = gridManipulations($mode, $rowId, $ref_id);
        echo "<data>";
        echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
        echo "</data>";
        break;


    case 8:

        /* $empQuery= "Select eid as a1,Naam as a2 from employee order by eid asc"; 
          $empQuery = mysqli_query($dbc,$empQuery);
          $empString = array();
          while($row_pl =mysqli_fetch_array($empQuery))
          {
          $empString[]=$row_pl['a2'];
          }
          $dbString = implode('","',$empString);
          print '["'.$dbString.'"]'; */

        $empQuery = "SELECT ID,status_id,FirstName,SecondName,LastName,Gender,DayOfBirth,DateIn,Position,PrivateEmail,CompanyEmail,Telephone,Mobile,Sip,AIM,Teamviewer,VOIP as v,Paypal,XLite,IntranetID as intra
                            FROM
                            trainees where status_id = 1 "; //PUT THIS TO FILTER USING BRANCH //and branch_id = $_GET[brcn_type]
        $empQuery = mysqli_query($dbc, $empQuery);
        $empString = array();
        while ($row_pl = mysqli_fetch_array($empQuery)) {
            $empString[] = $row_pl['FirstName'];
        }
        $dbString = implode('","', $empString);
        print '["' . $dbString . '"]';

        break;


    case 9:
        require("../../../../../../script/dhtmlx_connector_classic/grid_connector.php");
        $grid = new GridConnector($res);
        $grid->enable_log("templates/temp.log", true);
        $grid->dynamic_loading(1000);
        //default query
        $sql = "Select * from events where cat_id like '%" . $_GET['evt_nm'] . "%' and event_pid  is null order by start_date asc";
        $grid->render_sql($sql, "event_id", "details,start_date,end_date,event_name,completed,target,result,tag_id");
        break;


    case 10:
        //clear parent 
        $qry_parent = "Delete from events where event_id = '$_GET[evt_parent]'";
        mysqli_query($dbc, $qry_parent);
        //clear children
        $qry_parent = "Delete from events where event_pid = '$_GET[evt_parent]'";
        mysqli_query($dbc, $qry_parent);
        $info = "Successfully cleared recurring events for selected employee!";
        echo json_encode(array("info" => $info));
        break;


    case 11:

        $productComboQuery = 'Select id,FirstName,SecondName ,concat( FirstName," ",SecondName ) as name from trainees where status_id = 1';
        $productComboResult = mysqli_query($dbc, $productComboQuery);

        header("Content-type:text/xml");
        ini_set('max_execution_time', 600);
        print("<?xml version=\"1.0\"?>");
        echo " <complete>";
        print "<option value='' selected='true'>Assign to</option>";
        while ($row = mysqli_fetch_array($productComboResult)) {
            $id = $row["id"];
            print("<option value=\"" . $id . "\">");
            print($row["name"]);
            print("</option>");
        }

        print("</complete>");

        break;

    case 110:

        //load all employees with default:checkbox case 1:select emp string saved in assigned value                        
        header("Content-type:text/xml");
        ini_set('max_execution_time', 600);
        print("<?xml version=\"1.0\"?>");
        echo "<complete>";

        switch ($_GET['load']) {
            case 1:

                $emplist = array();
                //$query = "SELECT event_id,employee_id FROM `events` WHERE `event_pid` =" . $_GET['evt_id'];
                $query = "SELECT event_id,employee_id FROM `events` WHERE `event_id` =" . $_GET['evt_id'];
                $result = mysqli_query($dbc, $query);
                while ($row = mysqli_fetch_array($result)) {
                    $emplist[] = $row['employee_id'];
                }


                $query = "SELECT ID,CONCAT(COALESCE(FirstName,''),' ',COALESCE(SecondName,''),' ',COALESCE(LastName,'')) employee FROM trainees WHERE status_id = 1 || id = 51 ORDER BY branch_id,ID";
                $result = mysqli_query($dbc, $query);


                while ($row = mysqli_fetch_array($result)) {
                    if (in_array($row['ID'], $emplist)) {
                        echo "<option value='" . $row['ID'] . "' checked='1' selected='1'>" . $row['employee'] . "</option>";
                    } else {
                        echo "<option value='" . $row['ID'] . "'>" . $row['employee'] . "</option>";
                    }
                }
                break;
            default:
                $qryEmp = "Select id as a1,FirstName as a2,LastName as a3 ,IntranetID as a4  from trainees where status_id = 1 || id = 51 order by id asc";
                $resEmp = mysqli_query($dbc, $qryEmp) or die(mysqli_error($dbc) . $qryEmp);

                while ($rowEmp = mysqli_fetch_array($resEmp)) {
                    $checked = "";
                    echo "<option value='{$rowEmp["a1"]}' {$checked}>" . $rowEmp["a2"] . " " . $rowEmp["a3"] . "</option>";
                }
                break;
        }
        echo "</complete>";

        break;

    case 12:

        $stdate = explode("GMT", $_POST['start_date']);
        $edate = explode("GMT", $_POST['end_date']);

        $date1 = new DateTime($stdate[0]);
        $s1 = $date1->format('Y-m-d H:i:s');
        $date2 = new DateTime($edate[0]);
        $s2 = $date2->format('Y-m-d H:i:s');
        $cat = $_POST['cat_id'];
        if ($cat == null || $cat == '')
            $cat = 45;

        //user logged in enters tasks
        $uID = $_COOKIE['userlggd'];
        if ($uID == "") {
            $uID = 0;
        }
        //if the time is set:overite the time set on calendar popup.
        $bgntime = $_POST['begn'];
        $endtime = $_POST['end'];
        if ($bgntime != '') {
            $cdtstart = new DateTime($s1);
            $s1 = $cdtstart->format('Y-m-d');
            $s1 = $s1 . " " . $bgntime;
            $cdtend = new DateTime($s2);
            $s2 = $cdtend->format('Y-m-d');
            $s2 = $s2 . " " . $endtime;
        }
        $chkvalues = $_POST['days_select'];
        $dbString = array();
        foreach ($chkvalues as $key => $value) {//check for optin and opt out
            if ($value == 1) {
                $dbString[] = $key;
            }
        }
        $dbString = implode(',', $dbString);

        $emp_str = $_GET['eid_assigned'];
        $apprv_str = $_GET['approved'];
        $event_id = $_POST['event_id'];
        //$emp = $_GET['emplist'];

        $emp = $_GET['eid_assigned'];

        $emplist = explode(",", $emp);
        if (count($emplist) > 1) {
            //get the employees
            $qry_Emp = "SELECT GROUP_CONCAT(COALESCE(FirstName, ''),' ',COALESCE(SecondName, ''),' ',COALESCE(LastName, ''))AS FirstName FROM trainees WHERE id IN($emp)";
            $res_Emp = mysqli_query($dbc, $qry_Emp);
            $rowEmp = mysqli_fetch_array($res_Emp);
            $emp_strdb = mysqli_real_escape_string($dbc, $rowEmp['FirstName']);

            mysqli_query($dbc, "UPDATE `events`
                            SET details = '{$_POST['event_name']}',
                             event_name = '{$_POST['details']}',
                             start_date = '{$s1}',
                             employee_id = NULL,
                             end_date = '{$s2}',
                             event_length = '{$_POST['freq']}',
                             rec_type = '{$dbString}',
                             is_variable = '{$_POST['variable']}',
                             assigned_eid = '{$emp_strdb}',
                             info = '{$_POST['info']}',
                             approved_by = '{$emp_apprv}',
                             map = '{$_POST['map']}',
                             duration = '{$_POST['period']}',
                             reoccur_map = '{$_POST['reoccur_map']}',
                             visible = 0
                         WHERE
                             event_id = '{$event_id}'");


            $sql = "DELETE FROM `events` WHERE event_pid = '{$_POST['event_id']}'";
            $res = mysqli_query($dbc, $sql);

            $qry_par = "SELECT document_id FROM `events` WHERE event_id = '{$event_id}'";
            $res_par = mysqli_query($dbc, $qry_par);
            $row_par = mysqli_fetch_array($res_par);
            $document_id = $row_par['document_id'];

            foreach ($emplist as $key => $value) {
                $qry_insert = "INSERT INTO `events`(
                                    `event_pid`,
                                    `event_name`,
                                    `details`,
                                    `start_date`,
                                    `end_date`,
                                    `employee_id`,
                                    `event_length`,
                                    `cat_id`,
                                    `entered_by`,
                                    `assigned_eid`,
                                    `visible`,
                                    `document_id`,
                                    `duration`,
                                    `masterrecord`
                            )SELECT
                                    '{$_POST['event_id']}',
                                    '{$_POST['details']}',
                                    '{$_POST['event_name']}',
                                    '{$s1}',
                                    '{$s2}',
                                    '$value',
                                    `event_length`,
                                    `cat_id`,
                                    `entered_by`,
                                    '{$emp_strdb}',
                                     1,
                                    `document_id`,
                                    `duration`,
                                    `masterrecord`
                             FROM
                                    `events`
                             WHERE event_id = {$_POST['event_id']}";
                mysqli_query($dbc, $qry_insert);
            }
        } else {

            $qry_Emp = "Select CONCAT(COALESCE(FirstName, ''),' ',COALESCE(SecondName, ''),' ',COALESCE(LastName, ''))AS FirstName from trainees where id =" . $emp;
            $res_Emp = mysqli_query($dbc, $qry_Emp);
            $rowEmp = mysqli_fetch_array($res_Emp);
            $emp_strdb = mysqli_real_escape_string($dbc, $rowEmp['FirstName']);
            //get the approved items
            if ($apprv_str == null) {
                $apprv_str = 0;
            }

            $qry_Apprv = "Select CONCAT(COALESCE(FirstName, ''),' ',COALESCE(SecondName, ''),' ',COALESCE(LastName, ''))AS FirstName from trainees where id in($apprv_str)";
            $res_Apprv = mysqli_query($dbc, $qry_Apprv);
            $rowApprv = mysqli_fetch_array($res_Apprv);
            $emp_apprv = $rowApprv['FirstName'];


            $update = "UPDATE `events`
                            SET details = '{$_POST['event_name']}',
                             event_name = '{$_POST['details']}',
                             start_date = '{$s1}',
                             end_date = '{$s2}',
                             event_length = '{$_POST['freq']}',
                             rec_type = '{$dbString}',
                             is_variable = '{$_POST['variable']}',
                             assigned_eid = '{$emp_strdb}',
                             info = '{$_POST['info']}',
                             approved_by = '{$emp_apprv}',
                             map = '{$_POST['map']}',
                             reoccur_map = '{$_POST['reoccur_map']}',
                             duration = '{$_POST['period']}',
                             employee_id = '{$emp}'
                            WHERE
                             event_id = '{$_POST['event_id']}'"; //echo $update;
            mysqli_query($dbc, $update);
        }
        $info = "saved";
        echo json_encode(array("info" => $info));
        break;
    case 13:
        //to switch to forms
        switch ($_GET['fm']) {
            default:

                //overide the time set
                $date1 = new DateTime($_POST['start_date']);
                $s1 = $date1->format('Y-m-d H:i:s');
                $date2 = new DateTime($_POST['end_date']);
                $s2 = $date2->format('Y-m-d H:i:s');


                mysqli_query($dbc, "Update events set event_name = '$_POST[event_name]',details = '$_POST[details]',start_date = '$s1',end_date = '$s2'  
                             where event_id = '$_POST[event_id]'");

                echo "hey" . $sql;

                break;
            case 3:
                $date1 = new DateTime($_POST['start_date']);
                $s1 = $date1->format('Y-m-d H:i:s');
                $date2 = new DateTime($_POST['end_date']);
                $s2 = $date2->format('Y-m-d H:i:s');
                //if the time is set:overite the time set on calendar popup.
                $bgntime = $_POST['begn'];
                $endtime = $_POST['end'];
                if ($bgntime != '') {
                    $cdtstart = new DateTime($s1);
                    $s1 = $cdtstart->format('Y-m-d');
                    $s1 = $s1 . " " . $bgntime;
                    $cdtend = new DateTime($s2);
                    $s2 = $cdtend->format('Y-m-d');
                    $s2 = $s2 . " " . $endtime;
                }

                $sql = "Update events set start_date = '$s1',end_date = '$s2' where event_id = '$_POST[event_id]'";

                //echo $sql;


                mysqli_query($dbc, "Update events set start_date = '$s1',end_date = '$s2' where event_id = '$_POST[event_id]'");
                break;
        }

        break;


    case 14:
        //this handles the child elements for the selected item
        $selCat = $_GET['evt_nm'];
        //check the child elements of the selected item 
        $qry_Cat = "Select group_concat(asset_cat_id) as id  from events_asset_cat where parent_id = '$selCat' and tree_cat = 1";
        $res_Cat = mysqli_query($dbc, $qry_Cat);
        //rows returned
        $qry_Count = "Select * from events_asset_cat where parent_id = '$selCat' and tree_cat = 1";
        $counts = mysqli_num_rows(mysqli_query($dbc, $qry_Count));

        if ($counts == 0) {
            //no children for this node
            $allChildren = $selCat;
        } else {
            $row_Cat = mysqli_fetch_array($res_Cat);
            $chilCat = $row_Cat['id'];
            $allChildren = $selCat . "," . $chilCat;
        }
        require("../../../../../../script/dhtmlx_connector_classic/grid_connector.php");

        $grid = new GridConnector($res);
        $grid->enable_log("templates/temp.log", true);
        $grid->dynamic_loading(1000);
        //default query
        $sql = "Select * from events where cat_id in ($allChildren) and  (event_pid is null or event_pid = 0) and (event_pjd is null or event_pjd = 0) and assigned_eid =
					    (Select FirstName from trainees where id = '$_GET[emp_id]')
						order by start_date asc";
        treeGrid($sql);

        break;

    case 15:

        $date1 = new DateTime($_POST['start_date']);
        $s1 = $date1->format('Y-m-d H:i:s');
        $date2 = new DateTime($_POST['end_date']);
        $s2 = $date2->format('Y-m-d H:i:s');
        //if the time is not set:overite the time set on calendar popup.
        $bgntime = $_POST['begn'];
        $endtime = $_POST['end'];
        if ($bgntime != '') {
            $cdtstart = new DateTime($s1);
            $s1 = $cdtstart->format('Y-m-d');
            $s1 = $s1 . " " . $bgntime;
            $cdtend = new DateTime($s2);
            $s2 = $cdtend->format('Y-m-d');
            $s2 = $s2 . " " . $endtime;
        }
        //user logged in enters tasks
        $uID = $_COOKIE['userlggd'];
        if ($uID == "") {
            $uID = 0;
        }
        $str = "Update events set event_name = '" . $_POST['event_name_child'] . "',info = '" . $_POST['information'] . "',employee_id = " . $_POST['emp'] . ",start_date = '$s1',end_date = '$s2',entered_by = " . $uID . " where event_id = " . $_POST['event_id'];
        mysqli_query($dbc, $str);
        $info = "saved" . $str;
        echo json_encode(array("info" => $info, "event_id" => $_POST['event_id']));
        break;
    case 16:
        switch ($_GET['vis']) {
            case 1:
                $sql = "UPDATE events SET visible = 1 WHERE event_id = '" . $_GET['id'] . "' ";
                mysqli_query($dbc, $sql);
                $sql = "UPDATE events SET visible = 1 WHERE event_pid = '" . $_GET['id'] . "' ";
                mysqli_query($dbc, $sql);
                $sql = "UPDATE events SET visible = 1 WHERE event_pjd = '" . $_GET['id'] . "' ";
                mysqli_query($dbc, $sql);
                //set the reoccurences
                $sql = "SELECT * FROM events WHERE event_pid = '" . $_GET['id'] . "'";
                $result = mysqli_query($dbc, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    $sql = "UPDATE events SET visible = 1 WHERE event_pid = '" . $row['event_id'] . "' ";
                    mysqli_query($dbc, $sql);
                    $sql0 = "SELECT * FROM events WHERE event_pid = '" . $row['event_id'] . "'";
                    $result0 = mysqli_query($dbc, $sql0);
                    while ($row1 = mysqli_fetch_assoc($result0)) {
                        $sql1 = "UPDATE events SET visible = 1 WHERE event_pid = '" . $row1['event_id'] . "' ";
                        mysqli_query($dbc, $sql1);
                    }
                }
                $response = "Successfully set all tasks visible.";
                break;
            case 2:
                $sql = "UPDATE events SET visible = 0 WHERE event_id = '" . $_GET['id'] . "' ";
                mysqli_query($dbc, $sql);
                $sql = "UPDATE events SET visible = 0 WHERE event_pid = '" . $_GET['id'] . "' ";
                mysqli_query($dbc, $sql);
                $sql = "UPDATE events SET visible = 0 WHERE event_pjd = '" . $_GET['id'] . "' ";
                mysqli_query($dbc, $sql);

                //set the reoccurences
                $sql = "SELECT * FROM events WHERE event_pid = '" . $_GET['id'] . "'";
                $result = mysqli_query($dbc, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    $sql = "UPDATE events SET visible = 0 WHERE event_pid = '" . $row['event_id'] . "' ";
                    mysqli_query($dbc, $sql);
                    $sql0 = "SELECT * FROM events WHERE event_pid = '" . $row['event_id'] . "'";
                    $result0 = mysqli_query($dbc, $sql0);
                    while ($row1 = mysqli_fetch_assoc($result0)) {
                        $sql1 = "UPDATE events SET visible = 0 WHERE event_pid = '" . $row1['event_id'] . "' ";
                        mysqli_query($dbc, $sql1);
                    }
                }

                $response = "Successfully set all tasks invisible.";
                break;
        }
        echo json_encode(array("response" => $response));
        break;
}

function createInt($max_pl, $startDate, $endDate, $interval, $typ, $endtime, $dbString, $document_id, $holdays)
{
    global $dbc;
    $s = $startDate;
    $s2 = $startDate;
    $s = date('Y-m-d H:i:s', strtotime($startDate . " - " . $interval . $typ));
    $typ_dy = "day";
    //if no day is checked get the current day on the date field
    if (empty($dbString)) {
        unset($dbString);
        $dbString[] = date('N', strtotime($startDate));
    }

    //if($typ == 'month' && $interval != 2){$startDate = date('Y-m-d H:i:s', strtotime($startDate . " - ".$interval.$typ));}
    $endDate = date('Y-m-d H:i:s', strtotime($endDate . " - " . $interval . $typ));
    $p = $startDate;
    $day_number = date('N', strtotime($p));
    foreach ($dbString as $key => $value) {
        if ($_POST['variable'] == 0) {
            if ($day_number != $value) {
                $z = $day_number - $value;
                $w = 7;
                $y = $w - $z;
                $y = str_replace("-", "", $y);
                if ($z < 0) {
                    $z = str_replace("-", "", $z);
                    $startDate = date('Y-m-d H:i:s', strtotime($startDate . $z . " day"));
                } else {
                    $startDate = date('Y-m-d H:i:s', strtotime($startDate . $y . " day"));
                }
            }
        }

        while (strtotime($startDate) < strtotime($endDate)) {

            $p = $startDate;
            $day_number = date('N', strtotime($p));
            if ($_POST['variable'] == 0) {
                $wk_bal = $day_number - $value;
                $startDate = date('Y-m-d H:i:s', strtotime($startDate . "-" . $wk_bal . $typ_dy));
            }

            //select employee_id sent by category table 
            $empQuery = "Select ID as a1,FirstName as a2 from trainees where ID in (" . $_GET['ass_emp'] . ") order by ID asc";
            $empRes = mysqli_query($dbc, $empQuery);
            while ($row_pl = mysqli_fetch_array($empRes)) {
                $max_pl++;
                $empID = $row_pl['a1'];
                //user logged in enters tasks
                $uID = $_COOKIE['userlggd'];
                if ($uID == "") {
                    $uID = 0;
                }
                $day_number2 = date('N', strtotime($startDate));

                if ($typ != 'year') {
                    if (in_array(6, $dbString)) {
                        if ($day_number == 6 && $_POST['variable'] == 1) {
                            $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -1 day"));
                            $unselectedDays[] = $value;
                        }
                    }
                    if (in_array(7, $dbString)) {
                        if ($day_number == 7 && $_POST['variable'] == 1) {
                            $startDate = date('Y-m-d H:i:s', strtotime($startDate . " +1 day"));
                            $unselectedDays[] = $value;
                        }
                    }
                }
                //get deadline		
                $sysDate = date('d', strtotime($startDate));
                $orgDate = date('d', strtotime($s2));
                $evtDate = $startDate;

                if (str_replace("-", "", date('d', strtotime($evtDate)) - date('d', strtotime($s2))) > 0) {

                    $eDay = date('d', strtotime($s2));
                    $eMonth = date('m', strtotime($evtDate));
                    $eYear = date('Y', strtotime($evtDate));
                    $time = date('H:i:s', strtotime($evtDate));

                    $setDayNUmber = date('N', strtotime($eYear . "-" . $eMonth . "-" . $eDay . " " . $time));
                    $sysDayNumber = date('N', strtotime($evtDate));

                    $dayDifference = $setDayNUmber - $sysDayNumber;
                    if ($dayDifference < 0) {
                        $days = 7 + $dayDifference;
                    } else {
                        $days = $dayDifference;
                    }
                    $evtDate = date('Y-m-d H:i:s', strtotime($eYear . "-" . $eMonth . "-" . $eDay . " " . $time . "-" . $days . " day"));
                }
                //check for national holidays ~ jump a week				    
                $checkHolidayDate = new DateTime($evtDate);
                $checkHolidayDate = $checkHolidayDate->format('Y-m-d');
                if (in_array($checkHolidayDate, $holdays)) {
                    $evtDateH = date('Y-m-d H:i:s', strtotime($evtDate . " -1 week"));
                } else {
                    $evtDateH = $evtDate;
                }
                $tskend = new DateTime($evtDate);
                $tskend = $tskend->format('Y-m-d');
                $tskend = $tskend . " " . $endtime;
                $cat = $_POST['cat_id'];
                if ($cat == '') {
                    $cat = 45;
                }
                $qry_insert = "Insert into events(`event_pid`,`event_name`,`details`,`start_date`,`end_date`,`employee_id`,`event_length`,`cat_id`,`entered_by`,`assigned_eid`,`visible`,`document_id`)values('$_GET[evtId]','$_POST[details]','$_POST[event_name]','$evtDateH','$tskend','$empID',null,'$cat','$uID','$row_pl[a2]',0,'$document_id') ";
                if (strtotime($startDate) >= strtotime($s2)) {
                    mysqli_query($dbc, $qry_insert);
                }
                $startDate = date('Y-m-d H:i:s', strtotime($startDate . " + " . $interval . $typ));
            }

            $info = "Recurring events have been generated!";
        }
    }
}

function xmlEscape($string)
{
    return str_replace(array('&', '<', '>', '\'', '"', '-'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;', ''), $string);
}

function treeGrid($qryTasks)
{
    global $dbc;
    header("Content-type:text/xml");
    print("<?xml version = \"1.0\"?>");
    echo "<rows>";
    $resTasks = mysqli_query($dbc, $qryTasks);
    while ($rowTasks = mysqli_fetch_array($resTasks)) {
        echo "<row id = '{$rowTasks["event_id"]}'>";
        $qryTasksChildren = "Select * from events where event_pid='$rowTasks[event_id]' and tag_id = 1 order by sort_id asc";
        $resTasksChildren = mysqli_query($dbc, $qryTasksChildren);
        getRows($rowTasks, $resTasksChildren);
        while ($rowTasksChildren = mysqli_fetch_array($resTasksChildren)) {
            echo "<row id = '{$rowTasksChildren["event_id"]}'>";
            $qryTasksChildren2 = "Select * from events where event_pid='$rowTasksChildren[event_id]' and tag_id = 1 order by sort_id asc";
            $resTasksChildren2 = mysqli_query($dbc, $qryTasksChildren2);
            getRows($rowTasksChildren, $resTasksChildren2);
            while ($rowTasks2 = mysqli_fetch_array($resTasksChildren2)) {
                echo "<row id = '{$rowTasks2["event_id"]}'>";
                $qryTasksChildren3 = "Select * from events where event_pid='$rowTasks2[event_id]' and tag_id = 1 order by sort_id asc";
                $resTasksChildren3 = mysqli_query($dbc, $qryTasksChildren3);
                getRows($rowTasks2, $resTasksChildren3);
                while ($rowTasks3 = mysqli_fetch_array($resTasksChildren3)) {
                    echo "<row id = '{$rowTasks3["event_id"]}'>";
                    $qryTasksChildren4 = "Select * from events where event_pid='$rowTasks3[event_id]' and tag_id = 1 order by sort_id asc";
                    $resTasksChildren4 = mysqli_query($dbc, $qryTasksChildren4);
                    getRows($rowTasks3, $resTasksChildren4);
                    while ($rowTasks4 = mysqli_fetch_array($resTasksChildren4)) {
                        echo "<row id = '{$rowTasks4["event_id"]}'>";
                        getRows($rowTasks4, $resTasksChildren5);
                        echo "</row>";
                    }
                    echo "</row>";
                }
                echo "</row>";
            }

            echo "</row>";
        }
        echo "</row>";
    }
    echo "</rows>";
}

function getRows($a, $b)
{
    global $dbc;
    $cls = new SpecialCharacter();
    $count = mysqli_num_rows($b);
    if ($count > 0) {
        echo "<cell image='folder.gif'>{$a["details"]} </cell>";
    } else {
        echo "<cell> " . xmlEscape($a["details"]) . "</cell>";
    }

    echo "<cell> " . xmlEscape($a["assigned_eid"]) . "</cell>";
    echo "<cell> " . $a["start_date"] . "</cell>";
    echo "<cell> " . $a["end_date"] . "</cell>";
    $evtnm = $cls->cleanText($a["event_name"]);
    echo "<cell><![CDATA[" . xmlEscape(strip_tags($evtnm)) . "]]></cell>";
    echo "<cell> " . xmlEscape(strip_tags($a["info"])) . "</cell>";
    echo "<cell> " . xmlEscape(strip_tags($a["document_id"])) . "</cell>";
    echo "<cell> " . xmlEscape($a["approved_by"]) . "</cell>";
    echo "<cell> " . $a["protection"] . "</cell>";
    echo "<cell> " . $a["personal"] . "</cell>";
    echo "<cell> " . $a["visible"] . "</cell>";
    echo "<cell> " . $a["completed"] . "</cell>";
    echo "<cell> " . $a["target"] . "</cell>";
    echo "<cell> " . $a["result"] . "</cell>";
    echo "<cell> " . $a["tag_id"] . "</cell>";
}

function infiniteEvents()
{
    global $dbc;
    //this code is created to take care of static infinite events (//the function is not currently in use) 
    //this events do not change and may occur monthly/yearly/weekly e.g birthdays                              
    //get maximum event for the event_child
    $qry_evt = "Select Max(event_id) evt_mx from events";
    $res_evt = mysqli_query($dbc, $qry_evt);
    $row_evt = mysqli_fetch_array($res_evt);
    $max_evt = $row_evt['evt_mx'];
    $max_evt++;
    //run the occuring event on scheduler        
    switch ($_GET['fm']) {
        case 2:
            //update for form 2                              
            $chkvalues = $_POST['days_select'];
            $dbString = array();
            foreach ($chkvalues as $key => $value) {//days selected in array
                if ($value == 1) {
                    $dbString[] = $key;
                }
            }
            $dbString = implode(',', $dbString);
            switch ($_POST['freq']) {
                case 1:
                    $rectype = "week_1___";
                    break; //Freq every week
                case 2:
                    $rectype = "week_2___";
                    break; //Every two week
                case 3:
                    $rectype = "week_4___";
                    break; //Every month
                case 4:
                    $rectype = "week_12___";
                    break; //every 12 weeks
                case 5:
                    $rectype = "week_26___";
                    break; //every half year
                case 6:
                    $rectype = "week_52___";
                    break; //every year                              
            }
            $date1 = new DateTime($_POST['start_date']);
            $s1 = $date1->format('Y-m-d H:i:s'); /* $s1 = $date1->format('Y-m-d'); */
            $date2 = new DateTime($_POST['end_date']);
            $s2 = $date2->format('Y-m-d H:i:s');
            $rectype = $rectype . $dbString . "#no";
            $date = date('Y-m-d H:i:s');
            $uID = $_COOKIE['userlggd'];
            if ($uID == "") {
                $uID = 0;
            }
            $emp = $_GET['emp'];
            if ($_GET['emp'] = "") {
                $emp = 0;
            }
            $qry_insert = "Insert into events(`event_id`,`event_pid`,`event_name`,`details`,`start_date`,`end_date`,`employee_id`,`event_length`,`entered_by`,`rec_type`)
                             values('$max_evt','$_GET[evtId]','$_POST[event_name]','$_POST[details]','$date','$s2','$emp',300,'$uID','$rectype') ";
            mysqli_query($dbc, $qry_insert);
            //infinite event
            //now();
            $qry_update = "Update events set end_date = '0000-00-00 00:00:00' where event_id = '$_GET[evtId]'";
            mysqli_query($dbc, $qry_update);
            echo $qry_insert;
            break;
    }
    echo json_encode(array("response" => $max_evt, "dt_ev" => $max_evt));
}


