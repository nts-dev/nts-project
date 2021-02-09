<?php

ini_set('display_errors', '1');

require_once('../config.php');

// Make the connection:
$dbc = mysqli_connect($NTS_CFG->dbhost, $NTS_CFG->dbuser, $NTS_CFG->dbpass, $NTS_CFG->dbname);

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}