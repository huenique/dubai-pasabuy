<?php

// start a session and check if user is authenticated
function get_session_user(): mixed {
    session_start();
    $username = isset($_SESSION["sessionUser"]) ? $_SESSION["sessionUser"] : header("Location: login.php");
    return $username;
}

function rm_session_user() {
    
}