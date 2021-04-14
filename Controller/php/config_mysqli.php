<?php

require_once('../../../includes.php');

ini_set('display_errors', '0');

// Make the connection:
$dbc = mysqli_connect(Boot::DBHOST, Boot::DBUSER, Boot::DBPASS, Boot::DBNAME);

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}
