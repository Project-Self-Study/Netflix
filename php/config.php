<?php
define('DB_SERVER', 'wheatley.cs.up.ac.za');
define('DB_USERNAME', 'u22506773');
define('DB_PASSWORD', 'MAIUBAXLVHG5R7XOPBG2RAU65YCINHDB');
define('DB_NAME', 'u22506773_dummy');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if(!$link) {
    die('Connection failed: ' . mysqli_connect_error());
}
$link->autocommit(TRUE);
?>
