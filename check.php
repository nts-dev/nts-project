<?php
include 'dbconn.php';

session_start();

$userId = filter_input(INPUT_POST, 'dhxform_demo_login');
$pass = filter_input(INPUT_POST, 'dhxform_demo_pwd');

$query_check_credentials = "SELECT contact_attendent,contact_id,branch_id FROM relation_contact JOIN trainees ON trainees.IntranetID = relation_contact.contact_id WHERE (contact_id = '" . $userId . "') AND (pass = '" . md5($pass) . "')";
//echo $query_check_credentials; exit;
$result_check_credentials = mysqli_query($dbc, $query_check_credentials);
if (!$result_check_credentials) {//If the QUery Failed 
    echo 'Query Failed ';
}
if (@mysqli_num_rows($result_check_credentials) == 1) {//if Query is successfull  // A match was made.
    $_SESSION = mysqli_fetch_array($result_check_credentials, MYSQLI_ASSOC); //Assign the result of this query to SESSION Global Variable
    $state = 1;
} else {
    $state = 0;
}
header("Content-Type: text/html; charset=utf-8");
print_r("<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/>");
print_r("<script> try { parent.submitCallback(" . $state . "," . $userId . "); } catch(e) {};</script>");

