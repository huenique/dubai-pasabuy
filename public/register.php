<?php
require_once "header.php";
require_once __DIR__ . "/../db/connection.php";

session_start();

// redirect user to home if already authN
if (isset($_SESSION['sessionUser'])) {
    echo header("Location: home.php");
}

$pwNotMatch = <<<PW_MISMATCH
<label for="validationServerPw" class="form-label mt-2">
  Confirm Password:
</label>
<div class="input-group has-validation">
    <input
        name="password"
        type="password"
        class="form-control is-invalid"
        id="validationServerPw"
        aria-describedby="inputGroupPrepend validationServerUsernameFeedback"
        required
    />
    <div id="validationServerUsernameFeedback" class="invalid-feedback">
        Passwords do not match.
    </div>
</div>
PW_MISMATCH;
$registerUsername = isset($_SESSION["registerUsername"]) ? $_SESSION["registerUsername"] : "";
$registerPassword = isset($_SESSION["registerUsername"]) ? $pwNotMatch : <<<PASSWORD
<label class="form-label mt-2">Confirm Password:</label>
<input
    class="form-control"
    type="password"
    name="confirmPassword"
    id="password"
    required
/>
PASSWORD;
$registerPage = <<<REGISTER
<div class="container">
    <div class="form-container">
        <form method="post">
            <div class="field-input">
                <label class="form-label">E-mail:</label>
                <input
                    class="form-control"
                    type="email"
                    name="username"
                    value="$registerUsername"
                    required
                />
                <label class="form-label mt-2">Password:</label>
                <input
                    class="form-control"
                    type="password"
                    name="password"
                    id="password"
                    required
                />
                $registerPassword
            </div>
            <input
                class="btn btn-primary"
                type="submit"
                name="signup"
                value="SIGN UP"
            />
        </form>
        <a href="login.php">Already have an account?</a>
    </div>
</div>
REGISTER;

if (isset($_POST["signup"])) {
    $insertStmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $selectStmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $insertStmt->bind_param("ss", $username, $password);
    $selectStmt->bind_param("s", $username);

    $username = $_POST["username"];
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];

    // ensure users recognize their password 
    if ($password != $confirmPassword) {
        $_SESSION["registerUsername"] = $username;
        echo header("Location: register.php");
    }

    if (isset($_SESSION["registerUsername"]))
        $_SESSION["registerUsername"] = null;

    // $user = $conn->query("SELECT * FROM users WHERE username='$username'") or die($conn->error);
    $selectStmt->execute();
    $result = $selectStmt->get_result();

    if ($result->fetch_array(MYSQLI_NUM)) {
        // notify_user("USERNAME ALREADY EXISTS", "error");
        echo "<script>alert('email already registered')</script>";
    } else {
        // $conn->query("INSERT INTO users (username, password) VALUES ('$username', '$password')") or die($conn->error);
        $insertStmt->execute();
        echo header("Location: login.php");
        // notify_user("USERNAME AND PASSWORD ADDED", "success");
    }
}
?>
<title>Register</title>
<link rel="stylesheet" href="./static/css/register.css" type="text/css" />
<div class="pasabuy mt-4">
    <img src="./static/img/pasabuy_logo.png" alt="Logo" />
</div>
<?php echo $registerPage; ?>
