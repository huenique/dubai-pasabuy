<?php
require_once "header.php";
require_once __DIR__ . "/../db/connection.php";

session_start();

// redirect user to home if already authN
if (isset($_SESSION['sessionUser'])) {
    echo header("Location: home.php");
}

if (isset($_GET["login"])) {
    $username = $_GET["username"];
    $password = $_GET["password"];

    $conn = get_db_connection();
    $user = $conn->query("SELECT * FROM users WHERE username='$username'") or die($conn->error);
    $usercred = $user->fetch_assoc();

    if ($usercred) {
        if ($usercred["password"] != $password) {} else {
            $_SESSION['sessionUser'] = $usercred["username"];
            echo header("Location: home.php");
        }
    } else {}
    echo "<script>alert('incorrect email or password!')</script>";
}
?>
<title>Login</title>
<link rel="stylesheet" href="./static/css/login.css" type="text/css" />
<div class="pasabuy mt-4">
    <img src="./static/img/pasabuy_logo.png" alt="Logo" />
</div>
<div class="container">
    <div class="form-container">
        <form method="get">
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
        <a href="register.php">New here? Sign Up!</a>
    </div>
</div>
