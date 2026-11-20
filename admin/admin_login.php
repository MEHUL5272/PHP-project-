<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $message = "Please enter email and password.";

    } else {

        try {

            $client = new Client(
                "mongodb://127.0.0.1:27017"
            );

            $db = $client->selectDatabase(
                "shree_shyam_coffee"
            );

            $admins = $db->admins;

            $admin = $admins->findOne([
                "email" => $email
            ]);

            if (
                $admin &&
                password_verify(
                    $password,
                    $admin["password"]
                )
            ) {

                $_SESSION["admin_id"] =
                    (string)$admin["_id"];

                $_SESSION["admin_name"] =
                    $admin["name"];

                $_SESSION["admin_email"] =
                    $admin["email"];

                header("Location: dashboard.php");
                exit;

            } else {

                $message =
                    "Invalid admin email or password.";

            }

        } catch (Exception $e) {

            $message =
                "Error: " . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Admin Login - Shree Shyam Coffee</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    min-height: 100vh;

    background:
        radial-gradient(
            circle at top,
            #38220d,
            #090909 60%
        );

    color: white;

    font-family: Arial, sans-serif;

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 20px;
}

.login-box {

    width: 420px;

    max-width: 100%;

    background: #151515;

    border: 1px solid #e7a33a;

    border-radius: 16px;

    padding: 35px;

    box-shadow:
        0 0 30px
        rgba(231,163,58,0.20);
}

.logo {

    text-align: center;

    color: #e7a33a;

    font-size: 28px;

    font-weight: bold;

    margin-bottom: 10px;
}

.subtitle {

    text-align: center;

    color: #aaa;

    margin-bottom: 30px;
}

h2 {

    text-align: center;

    margin-bottom: 25px;
}

label {

    display: block;

    margin-bottom: 7px;

    color: #ddd;

    font-weight: bold;
}

input {

    width: 100%;

    padding: 13px;

    margin-bottom: 18px;

    background: #222;

    border: 1px solid #444;

    border-radius: 7px;

    color: white;

    font-size: 15px;

    outline: none;
}

input:focus {

    border-color: #e7a33a;
}

.message {

    background: #351919;

    color: #ff7777;

    padding: 12px;

    border-radius: 7px;

    margin-bottom: 18px;

    text-align: center;
}

button {

    width: 100%;

    padding: 14px;

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

.back {

    display: block;

    text-align: center;

    margin-top: 20px;

    color: #aaa;

    text-decoration: none;
}

.back:hover {

    color: #e7a33a;
}

</style>

</head>

<body>

<div class="login-box">

    <div class="logo">
        ☕ Shree Shyam Coffee
    </div>

    <div class="subtitle">
        Admin Panel
    </div>

    <h2>
        Admin Login 🔐
    </h2>

    <?php if ($message !== ""): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <label>
            Admin Email
        </label>

        <input
            type="email"
            name="email"
            placeholder="Enter admin email"
            required
        >


        <label>
            Password
        </label>

        <input
            type="password"
            name="password"
            placeholder="Enter admin password"
            required
        >


        <button type="submit">
            Login
        </button>

    </form>


    <a
        href="../index.php"
        class="back"
    >
        ← Back to Website
    </a>

</div>

</body>

</html>