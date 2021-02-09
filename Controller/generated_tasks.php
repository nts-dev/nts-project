<?php

//generate the tasks
require_once("php/config.php");

switch ($_GET['action']) {
    case 1:
        //generate for the project tasks 
        //get start and end date 
        // generateSubProjects(5); 
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        $cat = $_POST['cat_id'];
        if ($cat == null || $cat == '')
            $cat = 45;
        $qry_par = "Select document_id from events where event_id = '$_POST[event_id]'";
        $res_par = mysqli_query($dbc, $qry_par) or die(mysqli_error($dbc));
        $row_par = mysqli_fetch_array($res_par);
        $document_id = $row_par['document_id'];
        $empl = $_GET['emp'];
        if ($empl == "undefined" || $empl == "") {
            $empl = 1;
        }
        //update for form 2                              
        $chkvalues = $_POST['days_select'];
        $dbString = array();
        foreach ($chkvalues as $key => $value) {//check for optin and opt out
            if ($value == 1) {
                $dbString[] = $key;
            }
        }
        $typ = "day";
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
        switch ($_POST['freq']) {
            //measured in classic days
            case 1:
                $interval = 7;
                break; //Freq every week  (days = 7)
            case 2:
                $interval = 14;
                break; //Every two week   (days = 14)
            case 3:
                $interval = 1;
                $typ = "month";
                createInt($max_pl, $s1, $s2, $interval, $typ, $endtime, $dbString, $document_id);
                exit();
                break; //Every month     (days = 30)
            case 4:
                $interval = 84;
                break; //every 12 weeks  (days = 84)
            case 5:
                $interval = 168;
                break; //every half year(days = 180)
            case 6:
                $interval = 1;
                $typ = "year";
                createInt($max_pl, $s1, $s2, $interval, $typ, $endtime, $dbString, $document_id);
                exit();
                break; //every year  (days = 365)
            case 7:
                $interval = 28;
                break; //interval 4weeks
            case 8:
                $interval = 2;
                $typ = "month";
                createInt($max_pl, $s1, $s2, $interval, $typ, $endtime, $dbString, $document_id);
                exit();
                break;
            case 9:
                $interval = 56;
                break; //interval 8weeks
            case 10:
                $interval = 21;
                break; //interval 3wks
            default :
                $interval = 365;
                break; //handle date infinite 							 
        }
        if ($variable == 1) { //clears and work with the variable day set 
            unset($dbString);
            $dbString[] = date('N', strtotime($startDate));
        }
        $seldays = $dbString;
        $s = $s1;
        //get the day number                             
        $day_number = date('N', strtotime($s)); //=5  
        //get fixed day of week                                                          
        foreach ($seldays as $key => $value) {
            $endDate = $_POST['end_date'];
            $endDate = date('Y-m-d H:i:s', strtotime($endDate . " - " . $interval . $typ));
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
            $emp = explode(",", $_GET['ass_emp']);
            $new_string = "" . implode("','", $emp) . "";
            while (strtotime($startDate) < strtotime($endDate)) {

                //select employee_id sent by category table 
                $empQuery = "Select ID as a1,FirstName as a2 from nts_site.trainees where ID in ($_GET[ass_emp]) order by ID asc";
                $empRes = mysqli_query($dbc, $empQuery);
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
                    $qry_insert = "Insert into events(`event_pid`,`event_name`,`details`,`start_date`,`end_date`,`employee_id`,`event_length`,`cat_id`,`entered_by`,`assigned_eid`,`visible`,`document_id`)values('$_POST[event_id]','$_POST[details]','$_POST[event_name]','$startDate','$tskend','$empID',null,'$cat','$uID','$row_pl[a2]',0,'$document_id') ";
                    mysqli_query($dbc, $qry_insert) or die(mysqli_error($dbc));
                    $idInsert = mysqli_insert_id($dbc);
                    //generate reoccur map if flagged true
                    if ($_POST['reoccur_map'] == true) {
                        generateSubProjects($idInsert, $startDate, $tskend, $document_id);
                    }
                    $startDate = date('Y-m-d H:i:s', strtotime($startDate . " + " . $interval . $typ));
                }
            }
        }
        break;

    case 2:
        $itemsGrd = $_GET['itms'];
        $mainItems = explode(",", $itemsGrd);
        $new_search_string = "" . implode(",", $mainItems) . "";

        $qryDel = "Select * from events where event_pjd in ($new_search_string)";
        $resDel = mysqli_query($dbc, $qryDel) or die(mysqli_error($dbc));
        while ($rowDel = mysqli_fetch_array($resDel)) {
            $qry1 = "Delete from events where event_id = '$rowDel[event_id]'";
            mysqli_query($dbc, $qry1) or die(mysqli_error($dbc));
        }

        //perform deletion of item and siblings
        $qry2 = "Delete from events where event_id in ($new_search_string)";
        mysqli_query($dbc, $qry2) or die(mysqli_error($dbc));
        $info = "Deleted";
        echo json_encode(array("info" => $info));
        break;

    case 3:
        //update the recurring tasks event_name and details same as master task
        $tsk_id = $_GET['childtsk'];
        $qry_child = "Select * from events where event_id = '$tsk_id'";
        $res_child = mysqli_query($dbc, $qry_child) or die(mysqli_error($dbc));
        $row_child = mysqli_fetch_array($res_child);

        $master_parent = $row_child['event_pid'];
        //handle sibling on the recurring 
        if ($master_parent == null || $master_parent == '') {//sub child in recurring tasks
            $qry_child2 = "Select * from events where event_id = '$tsk_id'";
            $res_child2 = mysqli_query($dbc, $qry_child2) or die(mysqli_error($dbc));
            $row_child2 = mysqli_fetch_array($res_child2);
            $sibling = $row_child2['event_pjd'];
            $qry_child = "Select * from events where event_id = '$sibling'";
            $res_child = mysqli_query($dbc, $qry_child) or die(mysqli_error($dbc));
            $row_child = mysqli_fetch_array($res_child);
            $master_parent = $row_child['event_pid'];
        }
        //get master items
        $qry_master = "Select * from events where event_id = '$master_parent'";
        $res_master = mysqli_query($dbc, $qry_master) or die(mysqli_error($dbc));
        $row_master = mysqli_fetch_array($res_master);
        //do update
        $qry_update = "Update events set event_name = '$row_master[event_name]',details = '$row_master[details]' where event_id = '$tsk_id'";
        mysqli_query($dbc, $qry_update) or die(mysqli_error($dbc));
        break;

    case 4:

        //do visible/invisible selection
        $parentRecord = $_GET['grdRow'];
        //get total children/checked records
        $res_child = mysqli_query($dbc, "Select * from events where event_pid = '$parentRecord' and tag_id is null") or die(mysqli_error($dbc));
        $res_child_checked = mysqli_query($dbc, "Select * from events where event_pid = '$parentRecord' and visible = 1 and tag_id is null") or die(mysqli_error($dbc));
        $bool = true;
        if (mysqli_num_rows($res_child) == mysqli_num_rows($res_child_checked)) {
            $info = "Task have been made invisible to the schedule!";
            $qry = "Update events set visible = 0 where event_pid = '$parentRecord' and tag_id is null";
            $bool = false;
        } else {
            $info = "Tasks are now visible in the schedule.";
            $qry = "Update events set visible = 1 where event_pid = '$parentRecord' and tag_id is null";
        }
        mysqli_query($dbc, $qry) or die(mysqli_error($dbc));
        echo json_encode(array("info" => $info, "bool" => $bool));
        break;


    case 5:

        $date = date('Y-m-d H:i:s');
        session_start();
        $ref_id = $_SESSION['emp'];
        $empl = $_COOKIE['userlggd'];
        $ct_id = $_COOKIE['tsk_cat_id'];
        if ($empl == "undefined" || $empl == "") {
            $empl = 0;
        }
        if ($ct_id == "undefined" || $ct_id == "") {
            $ct_id = 45;
        }
        // set end date default one day ahead.
        //$endDate = date('Y-m-d H:i:s', strtotime($date . " + 1 day"));
        //default assigned to
        if ($empl == null) {
            $empl = 0;
        }
        $qry_Apprv = "Select group_concat(contact_firstname) as FirstName from relation_contact where contact_id in($empl)";
        $res_Apprv = mysqli_query($dbc, $qry_Apprv) or die(mysqli_error($dbc));
        $rowApprv = mysqli_fetch_array($res_Apprv);
        $emp_apprv = $rowApprv['FirstName'];
        if ($ct_id == 46 || $ct_id == 48) {
            $ref_id = 51;
            $emp_apprv = 'General';
        }

        $obj = getSettings($ct_id);
        $freq = $obj['freq'];
        $begin_time = $obj['begin_time'];
        $end_date = $obj['end_date'];
        $event_name = $obj['event_name'];
        $default_days = $obj['default_days'];
        $is_visible = $obj['is_visible'];
        if ($is_visible == null || $is_visible == '') {
            $is_visible = 1;
        }
        $evt_prt = $_COOKIE['tsk_evt'];
        //get the default items from the master item
        $qry_master = "Select * from events where event_id = '$evt_prt' ";
        $res_master = mysqli_query($dbc, $qry_master) or die(mysqli_error($dbc));
        $row_Master = mysqli_fetch_array($res_master);

        $qry = "Select ID,Firstname from trainees where IntranetID = '$_COOKIE[userlggd]'";
        $res = mysqli_query($dbc, $qry) or die(mysqli_error($dbc));
        $row_Eid = mysqli_fetch_array($res);
        $eid = $row_Eid['ID'];
        $is_visible = 0;
        $qry = "Insert into events(`event_id`,`event_name`,`start_date`,`end_date`,`details`,`employee_id`,`completed`,`cat_id`,`entered_by`,`target`,`assigned_eid`,`event_length`,`rec_type`,`visible`,`event_pid`,`document_id`)
                      values ('$rowId','$row_Master[event_name]','$row_Master[start_date]','$row_Master[end_date]','$row_Master[details]','$eid','0','$ct_id','$empl',1,'$emp_apprv','$freq','$default_days','$is_visible','$evt_prt','$row_Master[document_id]')";
        $res = mysqli_query($dbc, $qry) or die(mysqli_error($dbc));

        echo json_encode(array("event_id" => mysqli_insert_id($dbc), "event_name" => trim($row_Master['event_name']), "start_date" => $row_Master['start_date'], "end_date" => $row_Master['end_date'], "details" => trim($row_Master['details']), "assigned_to" => $row_Eid['Firstname']));

        break;

    default:
        //render tree grd
        $id = 60116;
        $id = $_GET['id'];
        $sql = "Select * from events where event_pid = '$id' and (tag_id is null or event_pid = 0) and (event_pjd is null or event_pjd = 0) order by start_date asc";
        treeGrid($sql);
        break;
}

function generateSubProjects($event_pjd, $startDate, $tskend, $document_id)
{
    global $dbc;
    $date_start_date = new DateTime($_POST['start_date']);
    $startDate_2_ = $date_start_date->format('Y-m-d');

    //format time
    $time_start = new DateTime($_POST['begn']);
    $time_start = $time_start->format('H:i:s');
    $startDate_2 = $startDate_2_ . " " . $time_start;
    $pDate = $startDate_2;

    //get the saved values of the child elements
    $qryTasks = "Select * from events where event_pid='$_GET[evtId]' and tag_id = 1 order by sort_id asc";
    $resTasks = mysqli_query($dbc, $qryTasks) or die(mysqli_error($dbc));

    while ($rowTasks = mysqli_fetch_array($resTasks)) {
        //saved siblings data             
        $endDate = $tskend;
        // $startDate = $rowTasks['start_date']; (uncomment if you want to make the sibling date overule the maps date)
        // $endDate = $rowTasks['end_date'];  (uncomment if you want to make the sibling date overule the maps date) 
        $cat_id = $rowTasks['cat_id'];
        if ($cat_id == null || $cat_id == '')
            $cat_id = 45;
        $employee = $rowTasks['emnployee_id'];
        $days_select = $rowTasks['rec_type'];
        $freq = $rowTasks['event_length'];
        $assigned = $rowTasks['assigned_eid'];
        $event_id_chd = $rowTasks['event_id'];

        $date1 = new DateTime($start_date);
        $s1 = $date1->format('Y-m-d H:i:s');
        $date2 = new DateTime($end_date);
        $s2 = $date2->format('Y-m-d H:i:s');
        $day_number = date('N', strtotime($s1));
        //end time
        $endtime = new DateTime($end_date);
        $end_time = $endtime->format('H:i:s');
        //default generation type   
        $typ = 'days';

        switch ($freq) {
            //measured: days
            case 1:
                $interval = 7;
                break; //Freq every week  (days = 7)
            case 2:
                $interval = 14;
                break; //Every two week   (days = 14)  
            case 3:
                $interval = 30;
                break;
            case 4:
                $interval = 84;
                break; //every 12 weeks  (days = 84)
            case 5:
                $interval = 168;
                break; //every half year(days = 180)
            case 6:
                $interval = 365;
                break;
            case 7:
                $interval = 28;
                break; //interval 4weeks 
            case 9:
                $interval = 56;
                break; //interval 8weeks  
        }
        //split to the days selected
        $days_select = explode(',', $days_select);
        //loop against the days selected in database    
        $s = $pDate;
        $day_number = date('N', strtotime($s));
        foreach ($days_select as $key => $value) {
            //set default start date
            switch ($value) {
                case 1:
                    $wk_bal = $day_number + 6;
                    $startDate_2 = date('Y-m-d H:i:s', strtotime($s . "- " . $wk_bal . $typ));
                    break; //m
                case 2:
                    $wk_bal = $day_number + 5;
                    $startDate_2 = date('Y-m-d H:i:s', strtotime($s . "- " . $wk_bal . $typ));
                    break; //t
                case 3:
                    $wk_bal = $day_number + 4;
                    $startDate_2 = date('Y-m-d H:i:s', strtotime($s . "- " . $wk_bal . $typ));
                    break; //w
                case 4:
                    $wk_bal = $day_number + 3;
                    $startDate_2 = date('Y-m-d H:i:s', strtotime($s . "- " . $wk_bal . $typ));
                    break; //th
                case 5:
                    $wk_bal = $day_number + 2;
                    $startDate_2 = date('Y-m-d H:i:s', strtotime($s . "- " . $wk_bal . $typ));
                    break; //fr
                case 6:
                    $wk_bal = $day_number + 1;
                    $startDate_2 = date('Y-m-d H:i:s', strtotime($s . "- " . $wk_bal . $typ));
                    break; //sart                                                 
                case 7:
                    $wk_bal = $day_number + 0;
                    $startDate_2 = date('Y-m-d H:i:s', strtotime($s . "- " . $wk_bal . $typ));
                    break; //sun
            }

            // }
            // $startDate_2 = date('Y-m-d H:i:s', strtotime($startDate_2 . " + ".$interval.$typ));
            echo $startDate_2 . "<br>";
            $emp = explode(",", $_GET['ass_emp']);
            $new_string = "" . implode("','", $emp) . "";
            $date_end_date = new DateTime($_POST['end_date']);
            $endDate = $date_end_date->format('Y-m-d H:i:s');
            $endDate = date('Y-m-d H:i:s', strtotime($endDate . " + " . $interval . $typ));
            while (strtotime($startDate_2) < strtotime($endDate)) {
                //generate time events                           
                // echo "ParentDate=>".$pDate."Start Day Selected".$value."Eventid=>".$event_id_chd."Days Selected=>".$days_select."Interval=>".$interval."StartDate=>".$startDate_2."End date=>".$endDate."<br>";   


                $date_a = new DateTime($startDate);
                $a = $date_a->format('Y-m-d');
                $date_b = new DateTime($startDate_2);
                $b = $date_b->format('Y-m-d');


                if ($b == $a) {
                    //save to database                                                 
                    //select employee_id sent by category table 
                    $empQuery = "Select ID as a1,FirstName as a2 from nts_site.trainees where id in ($_GET[ass_emp]) order by ID asc";
                    $empRes = mysqli_query($dbc, $empQuery);
                    while ($row_pl = mysqli_fetch_array($empRes)) {
                        $empID = $row_pl['a1'];
                        //task period withing time set
                        //user logged in enters tasks
                        $uID = $_COOKIE['userlggd'];
                        if ($uID == "") {
                            $uID = 0;
                        }
                        $emp_strdb = $row_pl['FirstName'];
                        //old time setting  $startDate // $tskend                                      
                        $child_start_time = $rowTasks['start_date'];
                        $time_a = new DateTime($child_start_time);
                        $st_time = $time_a->format('H:i:s');
                        $date_a = new DateTime($startDate_2);
                        $s_a = $date_a->format('Y-m-d');
                        $child_end_time = $rowTasks['end_date'];
                        $time_b = new DateTime($child_end_time);
                        $st_time_end = $time_b->format('H:i:s');
                        $date_b = new DateTime($tskend);
                        $s_b = $date_b->format('Y-m-d');
                        //set the child time as saved in database
                        $child_start_date = $s_a . " " . $st_time;
                        $child_end_date = $s_b . " " . $st_time_end;
                        $qry_insert = "Insert into events(`event_pid`,`event_name`,`details`,`start_date`,`end_date`,`employee_id`,`event_length`,`cat_id`,`entered_by`,`assigned_eid`,`visible`,`event_pjd`,`document_id`)
                                     values('$_POST[evt_id]','$rowTasks[details]','$rowTasks[event_name]','$child_start_date','$child_end_date','$empID',null,'$cat_id','$uID','$row_pl[a2]',0,$event_pjd,'$document_id') ";       //$_POST[cat_id]                                   

                        $qry_update = "Select * from events where event_name = '$rowTasks[details]' and details = '$rowTasks[event_name]' and start_date = '$child_start_date'";
                        $result = mysqli_query($dbc, $qry_update) or die(mysqli_error($dbc));
                        $count_up = mysqli_num_rows($result);

                        mysqli_query($dbc, $qry_insert) or die(mysqli_error($dbc));
                        $idInsert = mysqli_insert_id($dbc);

                    }
                }
                $startDate_2 = date('Y-m-d H:i:s', strtotime($startDate_2 . " + " . $interval . $typ));
            }
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
    $resTasks = mysqli_query($dbc, $qryTasks) or die(mysqli_error($dbc));
    while ($rowTasks = mysqli_fetch_array($resTasks)) {
        echo "<row id = '{$rowTasks["event_id"]}'>";
        $qryTasksChildren = "Select * from events where event_pjd='$rowTasks[event_id]'  order by start_date asc";              //and tag_id = 1
        $resTasksChildren = mysqli_query($dbc, $qryTasksChildren) or die(mysqli_error($dbc));
        getRows($rowTasks, $resTasksChildren);
        while ($rowTasksChildren = mysqli_fetch_array($resTasksChildren)) {
            echo "<row id = '{$rowTasksChildren["event_id"]}'>";
            $qryTasksChildren2 = "Select * from events where event_pjd='$rowTasksChildren[event_id]'  order by start_date asc";
            $resTasksChildren2 = mysqli_query($dbc, $qryTasksChildren2) or die(mysqli_error($dbc));
            getRows($rowTasksChildren, $resTasksChildren2);
            while ($rowTasks2 = mysqli_fetch_array($resTasksChildren2)) {
                echo "<row id = '{$rowTasks2["event_id"]}'>";
                $qryTasksChildren3 = "Select * from events where event_pjd='$rowTasks2[event_id]'  order by start_date asc";
                $resTasksChildren3 = mysqli_query($dbc, $qryTasksChildren3) or die(mysqli_error($dbc));
                getRows($rowTasks2, $resTasksChildren3);
                while ($rowTasks3 = mysqli_fetch_array($resTasksChildren3)) {
                    echo "<row id = '{$rowTasks3["event_id"]}'>";
                    $qryTasksChildren4 = "Select * from events where event_pjd='$rowTasks3[event_id]'  order by start_date asc";
                    $resTasksChildren4 = mysqli_query($dbc, $qryTasksChildren4) or die(mysqli_error($dbc));
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
    $count = mysqli_num_rows($b);
    if ($count > 0) {
        echo "<cell image='folder.gif'><![CDATA[" . $a["details"] . "]]></cell>";
    } else {
        echo "<cell> " . xmlEscape($a["details"]) . "</cell>";
    }

    if (is_numeric($a["assigned_eid"])) {
        $rowS = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT FirstName from trainees WHERE Id = '" . $a["assigned_eid"] . "'"));
        echo "<cell> " . xmlEscape($rowS["FirstName"]) . "</cell>";
    } else {
        echo "<cell> " . xmlEscape($a["assigned_eid"]) . "</cell>";
    }
    echo "<cell> " . $a["start_date"] . "</cell>";
    echo "<cell> " . $a["end_date"] . "</cell>";
    echo "<cell><![CDATA[" . $a["event_name"] . "]]></cell>";
    echo "<cell> " . $a["protection"] . "</cell>";
    echo "<cell> " . $a["personal"] . "</cell>";
    echo "<cell> " . $a["visible"] . "</cell>";
    echo "<cell> " . $a["completed"] . "</cell>";
    echo "<cell> " . $a["target"] . "</cell>";
    echo "<cell> " . $a["result"] . "</cell>";
    echo "<cell> " . $a["tag_id"] . "</cell>";
}

function getSettings($cat_id)
{
    global $dbc;

    $qry = "Select * from events_settings where cat_id = '$cat_id'";
    $result = mysqli_query($dbc, $qry);
    $arr_article_name = array();
    $article = array();
    $arr_desc = array();
    $row = mysqli_fetch_array($result);
    $freq = $row['freq'];
    $begin_time = $row['begin_time'];
    $end_date = $row['end_date'];
    $event_name = $row['event_name'];
    $employees = $row['employees'];
    $is_visible = $row['is_visible'];
    $default_days = $row['default_days'];

    return array(
        'freq' => $freq,
        'begin_time' => $begin_time,
        'end_date' => $end_date,
        'event_name' => $event_name,
        'employees' => $employees,
        'is_visible' => $is_visible,
        'default_days' => $default_days
    );
}

function createInt($max_pl, $startDate, $endDate, $interval, $typ, $endtime, $dbString, $document_id)
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
            $empQuery = "Select ID as a1,FirstName as a2 from nts_site.trainees where ID in ($_GET[ass_emp]) order by ID asc";
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

                $tskend = new DateTime($startDate);
                $tskend = $tskend->format('Y-m-d');
                $tskend = $tskend . " " . $endtime;
                $cat = $_POST['cat_id'];
                if ($cat == '') {
                    $cat = 45;
                }
                $qry_insert = "Insert into events(`event_pid`,`event_name`,`details`,`start_date`,`end_date`,`employee_id`,`event_length`,`cat_id`,`entered_by`,`assigned_eid`,`visible`,`document_id`)values('$_GET[evtId]','$_POST[details]','$_POST[event_name]','$startDate','$tskend','$empID',null,'$cat','$uID','$row_pl[a2]',0,'$document_id') ";

                mysqli_query($dbc, $qry_insert) or die(mysqli_error($dbc));
                $startDate = date('Y-m-d H:i:s', strtotime($startDate . " + " . $interval . $typ));
                $idInsert = mysqli_insert_id($dbc);
                //generate reoccur map if flagged true
                if ($_POST['reoccur_map'] == true) {
                    generateSubProjects($idInsert, $startDate, $tskend, $document_id);
                }
            }

            $info = "Recurring event have been generated!";
        }
    }
}

