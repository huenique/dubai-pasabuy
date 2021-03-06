<?php

require_once "header.php";
require_once __DIR__ . "/../db/connection.php";
require_once __DIR__ . "/../utils/session.php";

$_ = get_session_user();

if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $conn = get_db_connection();
    $user = $conn->query("SELECT * FROM customers WHERE username='$username'") or die($conn->error);
    $usercred = $user->fetch_assoc();

    if ($usercred) {
        if ($usercred["password"] != $password) {} else {
            $_SESSION['sessionUser'] = $usercred["username"];
            echo header("Location: home");
        }
    } else {
        echo "<script>alert('incorrect email or password!')</script>";
    }
}
?>
<title>Login</title>
<link rel="stylesheet" href="./static/css/login.css" type="text/css" />
<div class="pasabuy mt-4">
    <img src="./static/img/pasabuy_logo.png" alt="Logo" />
</div>
<div class="container">
    <div class="form-container">
        <form method="post">
            <div class="field-input">
                <label class="form-label">E-mail:</label>
                <input
                    class="form-control"
                    type="email"
                    name="username"
                    required
                />
                <label class="form-label mt-2">Password:</label>
                <input
                    class="form-control"
                    type="password"
                    name="password"
                    required
                />
            </div>
            <button class="btn btn-primary" type="submit" name="login">
                LOG IN
            </button>
        </form>
        <a href="register">New here? Sign Up!</a>
    </div>
</div>
