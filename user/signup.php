<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

$message = "";
$showOtp = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    // Basic validation
    if ($name == "" || $email == "" || $mobile == "" || $password == "") {

        $message = "Please fill all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {

        $message = "Mobile number must be 10 digits.";

    } elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";

    }
    // Strong password
    elseif (
        strlen($password) < 8 ||
        !preg_match("/[A-Z]/", $password) ||
        !preg_match("/[a-z]/", $password) ||
        !preg_match("/[0-9]/", $password) ||
        !preg_match("/[^A-Za-z0-9]/", $password)
    ) {

        $message = "Password must contain 8+ characters, uppercase, lowercase, number and special character.";

    } else {

        try {

            $client = new Client("mongodb://127.0.0.1:27017");

            $db = $client->selectDatabase("shree_shyam_coffee");

            $users = $db->users;

            // Check email
            $existingEmail = $users->findOne([
                "email" => $email
            ]);

            // Check mobile
            $existingMobile = $users->findOne([
                "mobile" => $mobile
            ]);

            if ($existingEmail) {

                $message = "Email already registered.";

            } elseif ($existingMobile) {

                $message = "Mobile number already registered.";

            } else {

                /*
                 * DEMO OTP
                 * Real SMS OTP નથી મોકલાતો.
                 */
                $_SESSION["signup_data"] = [
                    "name" => $name,
                    "email" => $email,
                    "mobile" => $mobile,
                    "password" => password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    )
                ];

                $_SESSION["signup_otp"] = "123456";

                $showOtp = true;

                $message = "Demo OTP sent successfully. Use OTP: 123456";
            }

        } catch (Exception $e) {

            $message = "Error: " . $e->getMessage();

        }
    }
}

/*
 * OTP Verification
 */
if (isset($_POST["verify_otp"])) {

    $otp = trim($_POST["otp"] ?? "");

    if ($otp === "123456" && isset($_SESSION["signup_data"])) {

        try {

            $client = new Client("mongodb://127.0.0.1:27017");

            $db = $client->selectDatabase("shree_shyam_coffee");

            $users = $db->users;

            $users->insertOne([
                "name" => $_SESSION["signup_data"]["name"],
                "email" => $_SESSION["signup_data"]["email"],
                "mobile" => $_SESSION["signup_data"]["mobile"],
                "password" => $_SESSION["signup_data"]["password"],
                "created_at" => new MongoDB\BSON\UTCDateTime()
            ]);

            unset($_SESSION["signup_data"]);
            unset($_SESSION["signup_otp"]);

            $message = "Account created successfully!";

            $showOtp = false;

        } catch (Exception $e) {

            $message = "Error: " . $e->getMessage();
        }

    } else {

        $showOtp = true;
        $message = "Invalid OTP. Demo OTP is 123456.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Signup - Shree Shyam Coffee</title>

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

.signup-box {
    width: 430px;

    background: #151515;

    padding: 35px;

    border-radius: 18px;

    border: 1px solid #d99a2b;

    box-shadow:
        0 0 30px rgba(218, 155, 45, 0.25);
}

.logo {
    text-align: center;

    color: #e7a33a;

    font-size: 32px;

    font-weight: bold;

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

    margin-bottom: 17px;

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
    padding: 12px;

    margin-bottom: 18px;

    border-radius: 7px;

    background: #222;

    color: #e7a33a;

    text-align: center;
}

.password-info {
    color: #999;

    font-size: 12px;

    margin-top: -10px;

    margin-bottom: 17px;
}

.login-link {
    text-align: center;

    margin-top: 20px;

    color: #bbb;
}

.login-link a {
    color: #e7a33a;

    text-decoration: none;

    font-weight: bold;
}

.demo-otp {
    text-align: center;

    margin-top: 10px;

    color: #aaa;

    font-size: 13px;
}

.demo-otp span {
    color: #e7a33a;

    font-weight: bold;
}

</style>

</head>

<body>

<div class="signup-box">

    <div class="logo">
        ☕ Shree Shyam
    </div>

    <div class="subtitle">
        Create Your Coffee Account
    </div>

    <?php if ($message != ""): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <?php if (!$showOtp): ?>

        <!-- SIGNUP FORM -->

        <form method="POST">

            <label>Full Name</label>

            <input
                type="text"
                name="name"
                placeholder="Enter your full name"
                required
            >


            <label>Email ID</label>

            <input
                type="email"
                name="email"
                placeholder="Enter your email"
                required
            >


            <label>Mobile Number</label>

            <input
                type="tel"
                name="mobile"
                placeholder="10 digit mobile number"
                maxlength="10"
                pattern="[0-9]{10}"
                required
            >


            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Create strong password"
                required
            >

            <div class="password-info">
                8+ characters, uppercase, lowercase, number & special character
            </div>


            <label>Confirm Password</label>

            <input
                type="password"
                name="confirm_password"
                placeholder="Confirm your password"
                required
            >


            <button type="submit">
                Create Account
            </button>

        </form>


        <div class="login-link">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </div>


    <?php else: ?>

        <!-- OTP FORM -->

        <form method="POST">

            <label>Enter OTP</label>

            <input
                type="text"
                name="otp"
                placeholder="Enter 6 digit OTP"
                maxlength="6"
                required
            >

            <button type="submit" name="verify_otp">
                Verify OTP
            </button>

        </form>

        <div class="demo-otp">
            Demo OTP: <span>123456</span>
        </div>

    <?php endif; ?>

</div>

</body>

</html>