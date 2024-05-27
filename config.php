<?php
define('DB_SERVER', 'wheatley.cs.up.ac.za');
define('DB_USERNAME', 'u22506773');
define('DB_PASSWORD', 'MAIUBAXLVHG5R7XOPBG2RAU65YCINHDB');  // Use an appropriate password if set
define('DB_DATABASE', 'u22506773_Area221');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

if(!$link) {
    die('Connection failed: ' . mysqli_connect_error());
}

mysqli_select_db($link, DB_DATABASE);
