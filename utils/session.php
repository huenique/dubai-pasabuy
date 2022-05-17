<?php

// start a session and check if user is authenticated
function get_session_user(): mixed {
    session_start();
    $username = isset($_SESSION["sessionUser"]) ? $_SESSION["sessionUser"] : header("Location: login.php");
    return $username;
}

// redirect user to home if already authN and prevent them from accessing this page
function verify_session_user() {
    if (isset($_SESSION['sessionUser'])) {
        echo header("Location: home.php");
    }
}