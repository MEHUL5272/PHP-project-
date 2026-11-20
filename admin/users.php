<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;


// ===============================
// ADMIN LOGIN CHECK
// ===============================

if (!isset($_SESSION["admin_id"])) {

    header("Location: admin_login.php");
    exit;
}


// ===============================
// MONGODB CONNECTION
// ===============================

try {

    $client = new Client(
        "mongodb://127.0.0.1:27017"
    );

    $db = $client->selectDatabase(
        "shree_shyam_coffee"
    );

    $usersCollection = $db->users;


    // ===============================
    // DELETE USER
    // ===============================

    if (
        $_SERVER["REQUEST_METHOD"] === "POST" &&
        isset($_POST["delete_user"])
    ) {

        $userId = $_POST["user_id"] ?? "";

        if ($userId !== "") {

            try {

                $usersCollection->deleteOne([
                    "_id" => new MongoDB\BSON\ObjectId($userId)
                ]);

            } catch (Exception $e) {

                // Ignore invalid ObjectId
            }
        }

        header("Location: users.php");
        exit;
    }


    // ===============================
    // GET USERS
    // ===============================

    $users = $usersCollection->find(
        [],
        [
            "sort" => [
                "created_at" => -1
            ]
        ]
    );

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

<title>Manage Users - Shree Shyam Coffee</title>


<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    background: #090909;

    color: white;

    font-family: Arial, sans-serif;
}


/* ===============================
   NAVBAR
================================ */

.navbar {

    min-height: 75px;

    background: #111;

    border-bottom: 1px solid #333;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 15px 6%;

    gap: 20px;
}

.logo {

    color: #e7a33a;

    font-size: 24px;

    font-weight: bold;
}

.nav-links {

    display: flex;

    align-items: center;

    gap: 20px;
}

.nav-links a {

    color: white;

    text-decoration: none;
}

.nav-links a:hover {

    color: #e7a33a;
}


/* ===============================
   HEADER
================================ */

.header {

    text-align: center;

    padding: 45px 20px;

    background:
        radial-gradient(
            circle,
            #38220d,
            #090909 65%
        );
}

.header h1 {

    color: #e7a33a;

    font-size: 40px;

    margin: 0 0 10px;
}

.header p {

    color: #aaa;

    margin: 0;
}


/* ===============================
   CONTAINER
================================ */

.container {

    width: 90%;

    max-width: 1200px;

    margin: auto;

    padding: 40px 0 70px;
}


/* ===============================
   USERS BOX
================================ */

.users-box {

    background: #161616;

    border: 1px solid #333;

    border-radius: 14px;

    overflow: hidden;
}


/* ===============================
   TABLE
================================ */

.table-wrapper {

    width: 100%;

    overflow-x: auto;
}

table {

    width: 100%;

    border-collapse: collapse;

    min-width: 850px;
}

th {

    background: #111;

    color: #e7a33a;

    text-align: left;

    padding: 16px;

    border-bottom: 1px solid #333;
}

td {

    padding: 16px;

    border-bottom: 1px solid #292929;

    color: #ddd;
}

tr:last-child td {

    border-bottom: none;
}

tr:hover td {

    background: #1b1b1b;
}


/* ===============================
   USER ID
================================ */

.user-id {

    color: #888;

    font-size: 12px;

    max-width: 130px;

    word-break: break-all;
}


/* ===============================
   NAME
================================ */

.user-name {

    color: white;

    font-weight: bold;
}


/* ===============================
   EMAIL
================================ */

.email {

    color: #bbb;
}


/* ===============================
   MOBILE
================================ */

.mobile {

    color: #ccc;
}


/* ===============================
   DELETE BUTTON
================================ */

.delete-btn {

    border: 1px solid #ff5555;

    background: transparent;

    color: #ff7777;

    padding: 8px 13px;

    border-radius: 6px;

    cursor: pointer;

    font-weight: bold;
}

.delete-btn:hover {

    background: #ff5555;

    color: #111;
}


/* ===============================
   EMPTY
================================ */

.empty {

    text-align: center;

    padding: 70px 20px;

    color: #888;
}

.empty-icon {

    font-size: 60px;

    margin-bottom: 15px;
}


/* ===============================
   MOBILE
================================ */

@media(max-width: 700px) {

    .navbar {

        flex-direction: column;

        justify-content: center;
    }

    .nav-links {

        flex-wrap: wrap;

        justify-content: center;
    }

    .header h1 {

        font-size: 32px;
    }

}

</style>

</head>


<body>


<!-- ===============================
     NAVBAR
================================ -->

<nav class="navbar">

    <div class="logo">

        ☕ Shree Shyam Coffee

    </div>


    <div class="nav-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="products.php">
            Products
        </a>

        <a href="orders.php">
            Orders
        </a>

        <a href="admin_logout.php">
            Logout
        </a>

    </div>

</nav>


<!-- ===============================
     HEADER
================================ -->

<section class="header">

    <h1>
        Manage Users 👥
    </h1>

    <p>
        View and manage registered customers
    </p>

</section>


<!-- ===============================
     USERS
================================ -->

<div class="container">

    <div class="users-box">

        <div class="table-wrapper">

            <?php

            $hasUsers = false;

            foreach ($users as $user):

                $hasUsers = true;

            ?>

                <?php if ($hasUsers): ?>

                <?php endif; ?>

            <?php endforeach; ?>


            <?php if (!$hasUsers): ?>

                <div class="empty">

                    <div class="empty-icon">
                        👥
                    </div>

                    <h2>
                        No Users Found
                    </h2>

                    <p>
                        No registered users are available.
                    </p>

                </div>

            <?php else: ?>

                <?php
                // Re-fetch users because the cursor
                // was used for checking above.
                $users = $usersCollection->find(
                    [],
                    [
                        "sort" => [
                            "created_at" => -1
                        ]
                    ]
                );
                ?>


                <table>

                    <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                Name
                            </th>

                            <th>
                                Email
                            </th>

                            <th>
                                Mobile
                            </th>

                            <th>
                                Registered
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php

                    $number = 1;

                    foreach ($users as $user):

                    ?>


                        <tr>

                            <td>
                                <?= $number++ ?>
                            </td>


                            <td>

                                <div class="user-name">

                                    <?= htmlspecialchars(
                                        $user["name"] ?? "Unknown"
                                    ) ?>

                                </div>

                                <div class="user-id">

                                    ID:
                                    <?= htmlspecialchars(
                                        (string)$user["_id"]
                                    ) ?>

                                </div>

                            </td>


                            <td>

                                <div class="email">

                                    <?= htmlspecialchars(
                                        $user["email"] ?? "-"
                                    ) ?>

                                </div>

                            </td>


                            <td>

                                <div class="mobile">

                                    <?= htmlspecialchars(
                                        $user["mobile"] ?? "-"
                                    ) ?>

                                </div>

                            </td>


                            <td>

                                <?php

                                if (
                                    isset($user["created_at"]) &&
                                    $user["created_at"] instanceof MongoDB\BSON\UTCDateTime
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

                            </td>


                            <td>

                                <form
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this user?');"
                                >

                                    <input
                                        type="hidden"
                                        name="user_id"
                                        value="<?= htmlspecialchars(
                                            (string)$user["_id"]
                                        ) ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="delete_user"
                                        class="delete-btn"
                                    >
                                        🗑️ Delete
                                    </button>

                                </form>

                            </td>

                        </tr>


                    <?php endforeach; ?>

                    </tbody>

                </table>

            <?php endif; ?>

        </div>

    </div>

</div>


</body>

</html>