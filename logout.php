<?php 
session_start();
 require('../conexiune.php');
 require('fragmente/functii.php'); 
$_SESSION = array();


function destroySession() {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
    session_destroy();
}

destroySession();

log_accesare($db, $_SERVER['REQUEST_URI'], (isset($_SESSION['id_user'])?$_SESSION['id_user']:0), $_SERVER['REMOTE_ADDR']);

header('Location: index.php');
 ?>
