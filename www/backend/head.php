<?php
require_once(__DIR__ . '/../../etc/config.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mysqli = mysqli_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);
if (!$mysqli) {
    echo 'Connection failed<br>';
    echo 'Error number: ' . mysqli_connect_errno() . '<br>';
    echo 'Error message: ' . mysqli_connect_error() . '<br>';
    die();
}

$auth_key = false;
if (isset($_COOKIE["auth_key"])) {    
    $stmt = $mysqli->prepare('SELECT role, 1 as found FROM users WHERE authkey = ? && timeout > NOW()');
    $stmt->bind_param("s", $_COOKIE['auth_key']);
    $stmt->execute();
    $stmt->bind_result($role, $found);
    $stmt->fetch();
    $auth_key = $found == 1;
}  
if (!$auth_key) {
    header("Location: /backend/login.php");
    exit();
}
