<?php

/**
* Start a session and check if user is authenticated.
* Redirect user to home if already authN.
*/
function get_session_user() {
    session_start();

    $path = preg_replace("~.*/~", "", $_SERVER['REQUEST_URI']);

    if (($path === "login" || $path === "register") && isset($_SESSION['sessionUser'])) {
        echo header("Location: home.php");
        return null;
    }

    if (($path = preg_replace("~.*/~", "", $_SERVER['REQUEST_URI'])) !== "login" && $path !== "register") {
        $username = isset($_SESSION["sessionUser"]) ? $_SESSION["sessionUser"] : header("Location: login.php");
        return $username;
    }
}
