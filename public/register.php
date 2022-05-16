<?php
require_once "header.php";
require_once __DIR__ . "/../db/connection.php";

if (isset($_POST["signup"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $conn = get_db_connection();
    $user = $conn->query("SELECT * FROM users WHERE username='$username'") or die($conn->error);
    $usercred = $user->fetch_assoc();

    if ($usercred) {
        // notify_user("USERNAME ALREADY EXISTS", "error");
    } else {
        $conn->query("INSERT INTO users (username, password) VALUES ('$username', '$password')") or die($conn->error);
        // notify_user("USERNAME AND PASSWORD ADDED", "success");
    }
}
?>
<title>Register</title>

<div class="container">
    <div class="row mt-5 pt-5">
        <div class="col align-self-start">
        </div>
        <div class="col align-self-center">
            <h1 class="mt-5 pt-5">SIGN UP</h1>
            <form action="#" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">USERNAME</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">PASSWORD</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-dark" name="signup">SIGN UP</button>
                <span><a type="submit" class="btn btn-outline-secondary" href="login.php">BACK TO LOGIN</a></span>
            </form>
        </div>
        <div class="col align-self-end">
        </div>
    </div>
</div>