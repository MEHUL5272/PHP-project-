<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$message = "";
$messageType = "";

try {

    $client = new Client("mongodb://127.0.0.1:27017");

    $db = $client->selectDatabase("shree_shyam_coffee");

    $usersCollection = $db->users;

    $userId = new ObjectId($_SESSION["user_id"]);

    $user = $usersCollection->findOne([
        "_id" => $userId
    ]);

    if (!$user) {
        session_destroy();
        header("Location: login.php");
        exit;
    }


    /* =========================
       UPDATE PROFILE
    ========================= */

    if (
        $_SERVER["REQUEST_METHOD"] === "POST" &&
        isset($_POST["update_profile"])
    ) {

        $name = trim($_POST["name"] ?? "");
        $mobile = trim($_POST["mobile"] ?? "");
        $address = trim($_POST["address"] ?? "");

        if ($name === "" || $mobile === "") {

            $message = "Name and mobile number are required.";
            $messageType = "error";

        } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {

            $message = "Please enter a valid 10-digit mobile number.";
            $messageType = "error";

        } else {

            $usersCollection->updateOne(

                [
                    "_id" => $userId
                ],

                [
                    '$set' => [
                        "name" => $name,
                        "mobile" => $mobile,
                        "address" => $address
                    ]
                ]

            );

            $_SESSION["user_name"] = $name;

            $message = "Profile updated successfully.";
            $messageType = "success";

            $user = $usersCollection->findOne([
                "_id" => $userId
            ]);
        }
    }


    /* =========================
       CHANGE PASSWORD
    ========================= */

    if (
        $_SERVER["REQUEST_METHOD"] === "POST" &&
        isset($_POST["change_password"])
    ) {

        $currentPassword =
            $_POST["current_password"] ?? "";

        $newPassword =
            $_POST["new_password"] ?? "";

        $confirmPassword =
            $_POST["confirm_password"] ?? "";


        if (
            $currentPassword === "" ||
            $newPassword === "" ||
            $confirmPassword === ""
        ) {

            $message = "Please fill all password fields.";
            $messageType = "error";

        } elseif (
            !password_verify(
                $currentPassword,
                $user["password"]
            )
        ) {

            $message = "Current password is incorrect.";
            $messageType = "error";

        } elseif ($newPassword !== $confirmPassword) {

            $message = "New passwords do not match.";
            $messageType = "error";

        } elseif (
            strlen($newPassword) < 8 ||
            !preg_match("/[A-Z]/", $newPassword) ||
            !preg_match("/[a-z]/", $newPassword) ||
            !preg_match("/[0-9]/", $newPassword) ||
            !preg_match("/[^A-Za-z0-9]/", $newPassword)
        ) {

            $message =
                "Password must contain 8+ characters, uppercase, lowercase, number and special character.";

            $messageType = "error";

        } else {

            $hashedPassword =
                password_hash(
                    $newPassword,
                    PASSWORD_DEFAULT
                );

            $usersCollection->updateOne(

                [
                    "_id" => $userId
                ],

                [
                    '$set' => [
                        "password" => $hashedPassword
                    ]
                ]

            );

            $message =
                "Password changed successfully.";

            $messageType = "success";

            $user = $usersCollection->findOne([
                "_id" => $userId
            ]);
        }
    }


} catch (Exception $e) {

    die(
        "MongoDB Error: " .
        $e->getMessage()
    );

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

<title>
    My Profile - Shree Shyam Coffee
</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: #090909;

    color: white;

}


/* NAVBAR */

.navbar {

    min-height: 75px;

    background: #111;

    border-bottom: 1px solid #333;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 7%;

}

.logo {

    color: #e7a33a;

    font-size: 24px;

    font-weight: bold;

}

.nav-links {

    display: flex;

    gap: 22px;

    align-items: center;

}

.nav-links a {

    color: white;

    text-decoration: none;

}

.nav-links a:hover {

    color: #e7a33a;

}


/* HEADER */

.header {

    text-align: center;

    padding: 50px 20px;

    background:

        radial-gradient(

            circle,

            #38220d,

            #090909 65%

        );

}

.header h1 {

    margin: 0 0 10px;

    color: #e7a33a;

    font-size: 40px;

}

.header p {

    color: #aaa;

}


/* CONTAINER */

.container {

    width: 90%;

    max-width: 950px;

    margin: auto;

    padding: 40px 0 80px;

}


/* MESSAGE */

.message {

    padding: 14px 18px;

    border-radius: 8px;

    margin-bottom: 25px;

    font-weight: bold;

}

.success {

    background: #16351f;

    border: 1px solid #2c7041;

    color: #72e69a;

}

.error {

    background: #351616;

    border: 1px solid #713333;

    color: #ff7777;

}


/* PROFILE GRID */

.profile-grid {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 25px;

}


/* CARD */

.card {

    background: #161616;

    border: 1px solid #333;

    border-radius: 15px;

    padding: 28px;

}

.card h2 {

    margin-top: 0;

    color: #e7a33a;

    font-size: 23px;

}

.card p {

    color: #888;

    font-size: 14px;

}


/* AVATAR */

.avatar {

    width: 85px;

    height: 85px;

    border-radius: 50%;

    background: #33260f;

    border: 2px solid #e7a33a;

    display: flex;

    justify-content: center;

    align-items: center;

    font-size: 40px;

    margin-bottom: 20px;

}


/* LABEL */

label {

    display: block;

    color: #e7a33a;

    font-weight: bold;

    margin-bottom: 7px;

}


/* INPUT */

input,
textarea {

    width: 100%;

    padding: 13px;

    margin-bottom: 18px;

    background: #222;

    color: white;

    border: 1px solid #444;

    border-radius: 8px;

    outline: none;

    font-size: 15px;

}

input:focus,
textarea:focus {

    border-color: #e7a33a;

}

textarea {

    min-height: 100px;

    resize: vertical;

}


/* READ ONLY EMAIL */

.email-box {

    background: #1d1d1d;

    border: 1px solid #333;

    padding: 13px;

    border-radius: 8px;

    color: #aaa;

    margin-bottom: 18px;

}


/* BUTTON */

.btn {

    width: 100%;

    padding: 13px;

    border: none;

    border-radius: 8px;

    background: #e7a33a;

    color: #111;

    font-size: 15px;

    font-weight: bold;

    cursor: pointer;

}

.btn:hover {

    background: #f5b84c;

}


/* PROFILE INFO */

.info-row {

    padding: 14px 0;

    border-bottom: 1px solid #292929;

}

.info-row:last-child {

    border-bottom: none;

}

.info-label {

    color: #888;

    font-size: 13px;

    margin-bottom: 5px;

}

.info-value {

    color: white;

    word-break: break-word;

}


/* MOBILE */

@media(max-width: 750px) {

    .navbar {

        flex-direction: column;

        gap: 15px;

        padding: 18px;

    }

    .profile-grid {

        grid-template-columns: 1fr;

    }

}

</style>

</head>

<body>


<!-- NAVBAR -->

<nav class="navbar">

    <div class="logo">

        ☕ Shree Shyam Coffee

    </div>


    <div class="nav-links">

        <a href="../index.php">
            Home
        </a>

        <a href="products.php">
            Products
        </a>

        <a href="my_orders.php">
            My Orders
        </a>

        <a href="cart.php">
            🛒 Cart
        </a>

    </div>

</nav>


<!-- HEADER -->

<section class="header">

    <h1>
        My Profile 👤
    </h1>

    <p>
        Manage your account information
    </p>

</section>


<div class="container">


<?php if ($message !== ""): ?>

    <div class="message <?= $messageType ?>">

        <?= htmlspecialchars($message) ?>

    </div>

<?php endif; ?>


<div class="profile-grid">


    <!-- PROFILE INFORMATION -->

    <div class="card">

        <div class="avatar">
            👤
        </div>

        <h2>
            Account Information
        </h2>


        <div class="info-row">

            <div class="info-label">
                Full Name
            </div>

            <div class="info-value">

                <?= htmlspecialchars(
                    $user["name"] ?? "-"
                ) ?>

            </div>

        </div>


        <div class="info-row">

            <div class="info-label">
                Email
            </div>

            <div class="info-value">

                <?= htmlspecialchars(
                    $user["email"] ?? "-"
                ) ?>

            </div>

        </div>


        <div class="info-row">

            <div class="info-label">
                Mobile
            </div>

            <div class="info-value">

                <?= htmlspecialchars(
                    $user["mobile"] ?? "-"
                ) ?>

            </div>

        </div>


        <div class="info-row">

            <div class="info-label">
                Address
            </div>

            <div class="info-value">

                <?= htmlspecialchars(
                    $user["address"] ?? "Not added"
                ) ?>

            </div>

        </div>


        <div class="info-row">

            <div class="info-label">
                Account Created
            </div>

            <div class="info-value">

                <?php

                if (
                    isset($user["created_at"]) &&
                    $user["created_at"]
                    instanceof MongoDB\BSON\UTCDateTime
                ) {

                    echo $user["created_at"]
                        ->toDateTime()
                        ->format(
                            "d M Y, h:i A"
                        );

                } else {

                    echo "-";

                }

                ?>

            </div>

        </div>

    </div>


    <!-- EDIT PROFILE -->

    <div class="card">

        <h2>
            Edit Profile ✏️
        </h2>

        <p>
            Update your name, mobile number and address.
        </p>


        <form method="POST">

            <label>
                Full Name
            </label>

            <input
                type="text"
                name="name"
                value="<?= htmlspecialchars(
                    $user["name"] ?? ""
                ) ?>"
                required
            >


            <label>
                Email
            </label>

            <div class="email-box">

                <?= htmlspecialchars(
                    $user["email"] ?? ""
                ) ?>

            </div>


            <label>
                Mobile Number
            </label>

            <input
                type="text"
                name="mobile"
                maxlength="10"
                value="<?= htmlspecialchars(
                    $user["mobile"] ?? ""
                ) ?>"
                required
            >


            <label>
                Address
            </label>

            <textarea
                name="address"
                placeholder="Enter your delivery address"
            ><?= htmlspecialchars(
                $user["address"] ?? ""
            ) ?></textarea>


            <button
                type="submit"
                name="update_profile"
                class="btn"
            >
                💾 Update Profile
            </button>

        </form>

    </div>


    <!-- CHANGE PASSWORD -->

    <div class="card">

        <h2>
            Change Password 🔐
        </h2>

        <p>
            Create a strong password for your account.
        </p>


        <form method="POST">

            <label>
                Current Password
            </label>

            <input
                type="password"
                name="current_password"
                placeholder="Enter current password"
                required
            >


            <label>
                New Password
            </label>

            <input
                type="password"
                name="new_password"
                placeholder="Enter new password"
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


            <button
                type="submit"
                name="change_password"
                class="btn"
            >
                🔐 Change Password
            </button>

        </form>

    </div>


    <!-- QUICK LINKS -->

    <div class="card">

        <h2>
            Quick Links 🚀
        </h2>

        <p>
            Quickly access your account sections.
        </p>


        <div class="info-row">

            <a
                href="my_orders.php"
                style="color:#e7a33a;text-decoration:none;"
            >
                📦 My Orders →
            </a>

        </div>


        <div class="info-row">

            <a
                href="products.php"
                style="color:#e7a33a;text-decoration:none;"
            >
                ☕ Browse Products →
            </a>

        </div>


        <div class="info-row">

            <a
                href="cart.php"
                style="color:#e7a33a;text-decoration:none;"
            >
                🛒 My Cart →
            </a>

        </div>


        <div class="info-row">

            <a
                href="logout.php"
                style="color:#ff7777;text-decoration:none;"
            >
                🚪 Logout →
            </a>

        </div>

    </div>


</div>


</div>

</body>

</html>