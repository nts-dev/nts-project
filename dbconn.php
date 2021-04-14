<?php

ini_set('display_errors', '1');

//require '../includes.php';

// Make the connection:
$dbc = mysqli_connect('192.168.1.2:3308', "root", "kenya1234", 'nts_site');

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}