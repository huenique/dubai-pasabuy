<?php
require_once "header.php";
require_once __DIR__ . "/../db/connection.php";

session_start();

if (isset($_GET["login"])) {
    $username = $_GET["username"];
    $password = $_GET["password"];

    $conn = get_db_connection();
    $user = $conn->query("SELECT * FROM users WHERE username='$username'") or die($conn->error);
    $usercred = $user->fetch_assoc();

    if ($usercred) {
        if ($usercred["password"] != $password) {} else {
            $_SESSION['sessionUser'] = $usercred["username"];
            echo header("Location: index.php");
        }
    } else {}
    echo "<script>alert('not logged in!')</script>";
}
?>
<title>Login</title>

<div class="container">
    <div class="row mt-5 pt-5">
        <div class="col align-self-start">
        </div>
        <div class="col align-self-center">
            <h1 class="mt-5 pt-5">LOGIN</h1>
            <form metho="get">
                <div class="mb-3">
                    <label for="username" class="form-label">USERNAME</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">PASSWORD</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-dark" name="login">LOGIN</button>
                <span><a type="submit" class="btn btn-outline-secondary" href="register.php">SIGN UP</a></span>
            </form>
        </div>
        <div class="col align-self-end">
        </div>
    </div>
</div>
