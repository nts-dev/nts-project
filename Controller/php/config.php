<?php
require_once('settings.php');

ini_set('display_errors', '1');

// Make the connection:
$dbc = mysqli_connect(NTS_REPL_HOSTNAME, NTS_USER, NTS_PASS, DATABASE);

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

