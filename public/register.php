<?php

require_once "header.php";
require_once __DIR__ . "/../db/connection.php";
require_once __DIR__ . "/../utils/session.php";

$_ = get_session_user();

$pwNotMatch = <<<PW_MISMATCH
<label for="validationServerPw" class="form-label mt-2">
    Confirm Password:
</label>
<div class="input-group has-validation">
    <input
        name="confirmPassword"
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
        <a href="login">Already have an account?</a>
    </div>
</div>
REGISTER;

if (isset($_POST["signup"])) {
    $insertStmt = $conn->prepare("INSERT INTO customers (username, password) VALUES (?, ?)");
    $selectStmt = $conn->prepare("SELECT * FROM customers WHERE username=?");
    $insertStmt->bind_param("ss", $username, $password);
    $selectStmt->bind_param("s", $username);

    $username = $_POST["username"];
    $password = $_POST["password"];

    // ensure customers recognize their password
    if ($password !== $_POST["confirmPassword"]) {
        $_SESSION["registerUsername"] = $username;
        echo header("Location: register");
    } else {
        // prevent unexpected behaviors caused by session variables created or modified outside login
        session_unset();
        session_destroy();

        $selectStmt->execute();
        $result = $selectStmt->get_result();

        if ($result->fetch_array(MYSQLI_NUM)) {
            echo "<script>alert('email already registered')</script>";
        } else {
            $insertStmt->execute();
            echo header("Location: login");
        }
    }
}
?>
<title>Register</title>
<link rel="stylesheet" href="./static/css/register.css" type="text/css" />
<div class="pasabuy mt-4">
    <img src="./static/img/pasabuy_logo.png" alt="Logo" />
</div>
<?php echo $registerPage; ?>
