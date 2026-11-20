<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

$message = "";
$step = 1;

/* =========================
   STEP 1 - FIND USER
========================= */

if (isset($_POST["send_otp"])) {

    $login = trim($_POST["login"] ?? "");

    if ($login == "") {

        $message = "Please enter your email or mobile number.";

    } else {

        try {

            $client = new Client("mongodb://127.0.0.1:27017");

            $db = $client->selectDatabase("shree_shyam_coffee");

            $users = $db->users;

            $user = $users->findOne([
                '$or' => [
                    ["email" => $login],
                    ["mobile" => $login]
                ]
            ]);

            if ($user) {

                $_SESSION["forgot_user_id"] = (string)$user["_id"];

                $_SESSION["forgot_otp"] = "123456";

                $step = 2;

                $message = "Demo OTP sent successfully. OTP: 123456";

            } else {

                $message = "User not found.";

            }

        } catch (Exception $e) {

            $message = "Error: " . $e->getMessage();

        }
    }
}


/* =========================
   STEP 2 - VERIFY OTP
========================= */

if (isset($_POST["verify_otp"])) {

    $otp = trim($_POST["otp"] ?? "");

    if (
        $otp === "123456" &&
        isset($_SESSION["forgot_user_id"])
    ) {

        $step = 3;

        $message = "OTP verified successfully.";

    } else {

        $step = 2;

        $message = "Invalid OTP. Demo OTP is 123456.";

    }
}


/* =========================
   STEP 3 - RESET PASSWORD
========================= */

if (isset($_POST["reset_password"])) {

    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($password !== $confirm_password) {

        $step = 3;

        $message = "Passwords do not match.";

    }

    elseif (
        strlen($password) < 8 ||
        !preg_match("/[A-Z]/", $password) ||
        !preg_match("/[a-z]/", $password) ||
        !preg_match("/[0-9]/", $password) ||
        !preg_match("/[^A-Za-z0-9]/", $password)
    ) {

        $step = 3;

        $message =
            "Password must contain 8+ characters, uppercase, lowercase, number and special character.";

    }

    elseif (!isset($_SESSION["forgot_user_id"])) {

        $message = "Session expired. Please try again.";

        $step = 1;

    }

    else {

        try {

            $client = new Client("mongodb://127.0.0.1:27017");

            $db = $client->selectDatabase("shree_shyam_coffee");

            $users = $db->users;

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $users->updateOne(
                [
                    "_id" => new MongoDB\BSON\ObjectId(
                        $_SESSION["forgot_user_id"]
                    )
                ],
                [
                    '$set' => [
                        "password" => $hashedPassword
                    ]
                ]
            );

            unset($_SESSION["forgot_user_id"]);
            unset($_SESSION["forgot_otp"]);

            $message = "Password reset successfully!";

            $step = 4;

        } catch (Exception $e) {

            $message = "Error: " . $e->getMessage();

            $step = 3;
        }
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Forgot Password - Shree Shyam Coffee</title>

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

.box {

    width: 420px;

    background: #151515;

    padding: 35px;

    border-radius: 18px;

    border: 1px solid #d99a2b;

    box-shadow:
        0 0 30px rgba(218,155,45,0.25);
}

.logo {

    text-align: center;

    font-size: 30px;

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

    color: #e7a33a;

    padding: 12px;

    margin-bottom: 18px;

    border-radius: 7px;

    text-align: center;

    font-size: 14px;
}

.link {

    text-align: center;

    margin-top: 20px;
}

.link a {

    color: #e7a33a;

    text-decoration: none;

    font-weight: bold;
}

.demo {

    text-align: center;

    margin-top: 10px;

    color: #aaa;

    font-size: 13px;
}

.demo span {

    color: #e7a33a;

    font-weight: bold;
}

</style>

</head>

<body>

<div class="box">

    <div class="logo">
        ☕ Shree Shyam
    </div>

    <div class="subtitle">
        Reset Your Password
    </div>


    <?php if ($message != ""): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <?php if ($step == 1): ?>

        <!-- FIND USER -->

        <form method="POST">

            <label>
                Email or Mobile Number
            </label>

            <input
                type="text"
                name="login"
                placeholder="Enter email or mobile"
                required
            >

            <button type="submit" name="send_otp">
                Send OTP
            </button>

        </form>


    <?php elseif ($step == 2): ?>

        <!-- VERIFY OTP -->

        <form method="POST">

            <label>
                Enter OTP
            </label>

            <input
                type="text"
                name="otp"
                maxlength="6"
                placeholder="Enter 6 digit OTP"
                required
            >

            <button type="submit" name="verify_otp">
                Verify OTP
            </button>

        </form>

        <div class="demo">
            Demo OTP: <span>123456</span>
        </div>


    <?php elseif ($step == 3): ?>

        <!-- RESET PASSWORD -->

        <form method="POST">

            <label>
                New Password
            </label>

            <input
                type="password"
                name="password"
                placeholder="Create new strong password"
                required
            >

            <label>
                Confirm New Password
            </label>

            <input
                type="password"
                name="confirm_password"
                placeholder="Confirm new password"
                required
            >

            <button type="submit" name="reset_password">
                Reset Password
            </button>

        </form>


    <?php elseif ($step == 4): ?>

        <!-- SUCCESS -->

        <div class="message">

            Password reset successfully! ✅

        </div>

        <button
            onclick="window.location.href='login.php'"
        >
            Go to Login
        </button>

    <?php endif; ?>


    <?php if ($step != 4): ?>

        <div class="link">

            <a href="login.php">
                ← Back to Login
            </a>

        </div>

    <?php endif; ?>

</div>

</body>

</html>