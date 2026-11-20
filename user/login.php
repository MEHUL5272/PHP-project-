
<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $login = trim($_POST["login"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($login == "" || $password == "") {

        $message = "Please enter email/mobile and password.";

    } else {

        try {

            $client = new Client("mongodb://127.0.0.1:27017");

            $db = $client->selectDatabase("shree_shyam_coffee");

            $users = $db->users;

            // Login using Email OR Mobile
            $user = $users->findOne([
                '$or' => [
                    ["email" => $login],
                    ["mobile" => $login]
                ]
            ]);

            if ($user && password_verify($password, $user["password"])) {

                $_SESSION["user_id"] = (string)$user["_id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];

                header("Location: ../index.php");
                exit;

            } else {

                $message = "Invalid email/mobile or password.";

            }

        } catch (Exception $e) {

            $message = "Error: " . $e->getMessage();

        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login - Shree Shyam Coffee</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;

    font-family: Arial, sans-serif;

    background: #090909;

    color: white;

    display: flex;
    justify-content: center;
    align-items: center;

    padding: 30px;
}

.login-box {
    width: 420px;

    background: #151515;

    padding: 35px;

    border-radius: 18px;

    border: 1px solid #d99a2b;

    box-shadow: 0 0 30px rgba(218,155,45,0.25);
}

.logo {
    text-align: center;

    font-size: 32px;

    font-weight: bold;

    color: #e7a33a;

    margin-bottom: 5px;
}

.subtitle {
    text-align: center;

    color: #ccc;

    margin-bottom: 25px;
}

label {
    display: block;

    color: #e7a33a;

    margin-bottom: 7px;

    font-weight: bold;
}

input {
    width: 100%;

    padding: 13px;

    margin-bottom: 18px;

    border-radius: 8px;

    border: 1px solid #555;

    background: #222;

    color: white;

    outline: none;

    font-size: 15px;
}

input:focus {
    border-color: #e7a33a;
}

.password-row {
    position: relative;
}

.password-row input {
    padding-right: 65px;
}

.show-password {
    position: absolute;

    right: 10px;
    top: 12px;

    color: #e7a33a;

    cursor: pointer;

    font-size: 13px;
}

button {
    width: 100%;

    padding: 13px;

    border: none;

    border-radius: 8px;

    background: #e7a33a;

    color: #111;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;
}

button:hover {
    background: #f5b84c;
}

.message {
    background: #222;

    color: #ff6b6b;

    padding: 12px;

    margin-bottom: 18px;

    border-radius: 7px;

    text-align: center;
}

.forgot {
    text-align: right;

    margin-top: -8px;

    margin-bottom: 20px;
}

.forgot a {
    color: #e7a33a;

    text-decoration: none;

    font-size: 14px;
}

.signup-link {
    text-align: center;

    margin-top: 20px;

    color: #bbb;
}

.signup-link a {
    color: #e7a33a;

    text-decoration: none;

    font-weight: bold;
}

</style>

</head>

<body>

<div class="login-box">

    <div class="logo">
        ☕ Shree Shyam
    </div>

    <div class="subtitle">
        Welcome Back!
    </div>

    <?php if ($message != ""): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <label>Email or Mobile Number</label>

        <input
            type="text"
            name="login"
            placeholder="Enter email or mobile number"
            required
        >


        <label>Password</label>

        <div class="password-row">

            <input
                type="password"
                name="password"
                id="password"
                placeholder="Enter password"
                required
            >

            <span
                class="show-password"
                onclick="togglePassword()"
            >
                Show
            </span>

        </div>


        <div class="forgot">

            <a href="forgot_password.php">
                Forgot Password?
            </a>

        </div>


        <button type="submit">
            Login
        </button>

    </form>


    <div class="signup-link">

        Don't have an account?

        <a href="signup.php">
            Create Account
        </a>

    </div>

</div>


<script>

function togglePassword() {

    const password = document.getElementById("password");

    const button = document.querySelector(".show-password");

    if (password.type === "password") {

        password.type = "text";

        button.innerText = "Hide";

    } else {

        password.type = "password";

        button.innerText = "Show";
    }
}

</script>

</body>

</html>