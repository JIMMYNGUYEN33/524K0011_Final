<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../helpers/auth.php'; 


$_SESSION = array();
session_unset();
session_destroy();

header('Location: login.php');
exit();
?>