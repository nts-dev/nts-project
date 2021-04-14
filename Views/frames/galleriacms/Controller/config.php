<?php
$host="192.168.2.25";
$username="root";
$password="kenya1234";
$db_name="nts_websites";

mysql_connect("$host", "$username", "$password")or die(mysqli_error($dbc)."cannot connect nts_websitess");

$conn = mysql_connect($host,$username,$password);

mysql_select_db($db_name);

mysqli_query($dbc,"SET NAMES 'utf8'"); //this is new
mysqli_query($dbc,"SET CHARACTER SET 'utf8'"); //this is new
mysql_set_charset('utf8');

