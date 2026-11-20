<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../vendor/autoload.php";
require_once "../config/db.php";

use MongoDB\BSON\ObjectId;

$userId = $_SESSION["user_id"];
$orderId = $_GET["order_id"] ?? $_POST["order_id"] ?? "";

try {
    $objectId = new ObjectId($orderId);
} catch (Exception $e) {
    die("Invalid Order ID.");
}

/*
|--------------------------------------------------------------------------
| Find User Order
|--------------------------------------------------------------------------
*/

$order = $ordersCollection->findOne([
    "_id" => $objectId,
    "user_id" => $userId
]);

if (!$order) {
    die("Order not found.");
}

/*
|--------------------------------------------------------------------------
| Only Pending Order Can Be Cancelled
|--------------------------------------------------------------------------
*/

if (($order["status"] ?? "") !== "Pending") {
    die("This order cannot be cancelled now.");
}

/*
|--------------------------------------------------------------------------
| Cancel Order
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {

        $ordersCollection->updateOne(
            [
                "_id" => $objectId,
                "user_id" => $userId,
                "status" => "Pending"
            ],
            [
                '$set' => [
                    "status" => "Cancelled",
                    "updated_at" => new MongoDB\BSON\UTCDateTime()
                ]
            ]
        );

        header(
            "Location: my_orders.php?success=Order+cancelled+successfully"
        );

        exit;

    } catch (Exception $e) {

        header(
            "Location: my_orders.php?error=Unable+to+cancel+order"
        );

        exit;
    }
}

$total = (float)($order["total"] ?? 0);
$items = $order["items"] ?? [];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Cancel Order - Shree Shyam Coffee</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #0d0d0d;
            color: #fff;
        }

        .navbar {
            background: #111;
            padding: 18px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #333;
        }

        .logo {
            color: #d4a017;
            font-size: 24px;
            font-weight: bold;
        }

        .nav-links a {
            color: #fff;
            text-decoration: none;
            margin-left: 20px;
        }

        .nav-links a:hover {
            color: #d4a017;
        }

        .container {
            width: 90%;
            max-width: 700px;
            margin: 45px auto;
        }

        .box {
            background: #181818;
            border: 1px solid #333;
            border-radius: 14px;
            padding: 30px;
        }

        .warning {
            text-align: center;
            margin-bottom: 25px;
        }

        .warning-icon {
            font-size: 55px;
            margin-bottom: 10px;
        }

        .warning h1 {
            color: #e05a47;
            margin-bottom: 10px;
        }

        .warning p {
            color: #aaa;
            line-height: 1.6;
        }

        .order-info {
            background: #222;
            border-radius: 10px;
            padding: 18px;
            margin: 25px 0;
        }

        .order-id {
            color: #d4a017;
            font-weight: bold;
            margin-bottom: 15px;
            word-break: break-all;
        }

        .item {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #333;
        }

        .item:last-child {
            border-bottom: none;
        }

        .item-name {
            color: #fff;
        }

        .item-details {
            color: #999;
            font-size: 14px;
            margin-top: 4px;
        }

        .item-price {
            color: #d4a017;
            white-space: nowrap;
        }

        .total {
            display: flex;
            justify-content: space-between;
            margin-top: 18px;
            padding-top: 15px;
            border-top: 1px solid #555;
            font-size: 20px;
            font-weight: bold;
        }

        .total span:last-child {
            color: #d4a017;
        }

        .question {
            background: #301817;
            border: 1px solid #73322c;
            color: #f1aaa2;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 22px;
            line-height: 1.6;
        }

        .buttons {
            display: flex;
            gap: 12px;
        }

        .btn {
            flex: 1;
            padding: 13px;
            border: none;
            border-radius: 7px;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            font-size: 15px;
        }

        .cancel-btn {
            background: #d9534f;
            color: #fff;
        }

        .cancel-btn:hover {
            background: #c9302c;
        }

        .back-btn {
            background: #333;
            color: #fff;
        }

        .back-btn:hover {
            background: #444;
        }

        @media (max-width: 600px) {

            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .nav-links a {
                margin: 0 6px;
            }

            .box {
                padding: 20px;
            }

            .buttons {
                flex-direction: column;
            }

            .item {
                flex-direction: column;
            }

        }

    </style>

</head>

<body>

<nav class="navbar">

    <div class="logo">
        ☕ Shree Shyam Coffee
    </div>

    <div class="nav-links">
        <a href="../index.php">Home</a>
        <a href="products.php">Products</a>
        <a href="cart.php">Cart</a>
        <a href="my_orders.php">My Orders</a>
    </div>

</nav>


<div class="container">

    <div class="box">

        <div class="warning">

            <div class="warning-icon">
                ⚠️
            </div>

            <h1>Cancel Order?</h1>

            <p>
                You are about to cancel this order.
                This action cannot be undone.
            </p>

        </div>


        <div class="order-info">

            <div class="order-id">
                Order ID:
                <?= htmlspecialchars((string)$order["_id"]) ?>
            </div>


            <?php foreach ($items as $item): ?>

                <?php
                $name = $item["name"] ?? "Product";
                $size = $item["size"] ?? "";
                $quantity = (int)($item["quantity"] ?? 0);
                $subtotal = (float)($item["subtotal"] ?? 0);
                ?>

                <div class="item">

                    <div>

                        <div class="item-name">
                            <?= htmlspecialchars($name) ?>
                        </div>

                        <div class="item-details">
                            <?= htmlspecialchars($size) ?>
                            ×
                            <?= $quantity ?>
                        </div>

                    </div>

                    <div class="item-price">
                        ₹<?= number_format($subtotal, 2) ?>
                    </div>

                </div>

            <?php endforeach; ?>


            <div class="total">

                <span>
                    Total
                </span>

                <span>
                    ₹<?= number_format($total, 2) ?>
                </span>

            </div>

        </div>


        <div class="question">

            Are you sure you want to cancel this order?

        </div>


        <form method="POST">

            <input
                type="hidden"
                name="order_id"
                value="<?= htmlspecialchars((string)$order["_id"]) ?>"
            >

            <div class="buttons">

                <button
                    type="submit"
                    class="btn cancel-btn"
                >
                    ❌ Yes, Cancel Order
                </button>

                <a
                    href="my_orders.php"
                    class="btn back-btn"
                >
                    ← No, Keep Order
                </a>

            </div>

        </form>

    </div>

</div>

</body>

</html> 