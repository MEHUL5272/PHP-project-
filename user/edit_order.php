<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../vendor/autoload.php";
require_once "../config/db.php";

use MongoDB\BSON\ObjectId;
use MongoDB\Model\BSONArray;

$userId = $_SESSION["user_id"];

$orderId = $_GET["order_id"] ?? $_POST["order_id"] ?? "";
$error = $_GET["error"] ?? "";
$success = "";

/*
|--------------------------------------------------------------------------
| Validate Order ID
|--------------------------------------------------------------------------
*/

try {

    $objectId = new ObjectId($orderId);

} catch (Exception $e) {

    die("Invalid Order ID.");

}

/*
|--------------------------------------------------------------------------
| UPDATE ORDER
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $quantities = $_POST["quantities"] ?? [];

    /*
    |--------------------------------------------------------------------------
    | Find Order
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
    | Only Pending Orders Can Be Updated
    |--------------------------------------------------------------------------
    */

    if (($order["status"] ?? "") !== "Pending") {

        header(
            "Location: my_orders.php?error=Order+cannot+be+updated"
        );

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Existing Items
    |--------------------------------------------------------------------------
    */

    $oldItems = $order["items"] ?? [];

    if ($oldItems instanceof BSONArray) {
        $oldItems = iterator_to_array($oldItems, false);
    }

    $items = [];
    $newTotal = 0;

    /*
    |--------------------------------------------------------------------------
    | Update Items
    |--------------------------------------------------------------------------
    */

    foreach ($oldItems as $index => $item) {

        if ($item instanceof BSONArray) {
            $item = iterator_to_array($item, false);
        }

        $quantity = 0;

        if (isset($quantities[$index])) {
            $quantity = (int)$quantities[$index];
        }

        /*
        | Quantity 0 = Remove Item
        */

        if ($quantity <= 0) {
            continue;
        }

        /*
        | Maximum Quantity
        */

        if ($quantity > 20) {
            $quantity = 20;
        }

        $price = (float)($item["price"] ?? 0);

        $name = $item["name"] ?? "Product";
        $size = $item["size"] ?? "";
        $productId = $item["product_id"] ?? "";

        $subtotal = $price * $quantity;

        $items[] = [
            "product_id" => $productId,
            "name"       => $name,
            "size"       => $size,
            "price"      => $price,
            "quantity"   => $quantity,
            "subtotal"   => $subtotal
        ];

        $newTotal += $subtotal;
    }

    /*
    |--------------------------------------------------------------------------
    | Prevent Empty Order
    |--------------------------------------------------------------------------
    */

    if (empty($items)) {

        header(
            "Location: edit_order.php?order_id=" .
            urlencode($orderId) .
            "&error=Order+must+have+at+least+one+item"
        );

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Update MongoDB
    |--------------------------------------------------------------------------
    */

    try {

        $result = $ordersCollection->updateOne(
            [
                "_id" => $objectId,
                "user_id" => $userId,
                "status" => "Pending"
            ],
            [
                '$set' => [
                    "items" => $items,
                    "total" => $newTotal,
                    "updated_at" => new MongoDB\BSON\UTCDateTime()
                ]
            ]
        );

        if ($result->getMatchedCount() > 0) {

            header(
                "Location: my_orders.php?success=Order+updated+successfully"
            );

            exit;

        } else {

            header(
                "Location: my_orders.php?error=Order+was+not+updated"
            );

            exit;
        }

    } catch (Exception $e) {

        header(
            "Location: edit_order.php?order_id=" .
            urlencode($orderId) .
            "&error=Unable+to+update+order"
        );

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| GET ORDER FOR DISPLAY
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
| Only Pending Order Can Be Edited
|--------------------------------------------------------------------------
*/

if (($order["status"] ?? "") !== "Pending") {
    die("This order can no longer be edited.");
}

$items = $order["items"] ?? [];

if ($items instanceof BSONArray) {
    $items = iterator_to_array($items, false);
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

    <title>Edit Order - Shree Shyam Coffee</title>

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
            max-width: 900px;
            margin: 40px auto;
        }

        .title {
            text-align: center;
            margin-bottom: 30px;
        }

        .title h1 {
            color: #d4a017;
            margin-bottom: 8px;
        }

        .order-id {
            color: #aaa;
            word-break: break-all;
        }

        .message {
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #301817;
            border: 1px solid #73322c;
            color: #f1aaa2;
        }

        .note {
            background: #201d14;
            border: 1px solid #5c4814;
            color: #e8d08b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .item {
            background: #181818;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
        }

        .item-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .product-name {
            font-size: 19px;
            font-weight: bold;
        }

        .size {
            color: #d4a017;
            margin-top: 6px;
        }

        .price {
            color: #aaa;
            margin-top: 6px;
        }

        .subtotal {
            color: #d4a017;
            font-weight: bold;
            margin-top: 10px;
        }

        .quantity-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-box label {
            color: #ccc;
        }

        .quantity-box input {
            width: 75px;
            padding: 10px;
            background: #222;
            border: 1px solid #555;
            color: #fff;
            border-radius: 6px;
            text-align: center;
            font-size: 16px;
        }

        .remove-btn {
            display: inline-block;
            margin-top: 12px;
            padding: 8px 14px;
            background: #d9534f;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }

        .remove-btn:hover {
            background: #c9302c;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 7px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
        }

        .save-btn {
            background: #d4a017;
            color: #000;
        }

        .save-btn:hover {
            background: #f0bd28;
        }

        .back-btn {
            background: #333;
            color: #fff;
        }

        .back-btn:hover {
            background: #444;
        }

        .empty {
            background: #181818;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            color: #aaa;
        }

        @media (max-width: 600px) {

            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .nav-links a {
                margin: 0 6px;
            }

            .item-top {
                flex-direction: column;
                align-items: flex-start;
            }

            .quantity-box {
                width: 100%;
            }

            .buttons {
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

    <div class="title">

        <h1>✏️ Edit Order</h1>

        <div class="order-id">
            Order ID:
            <?= htmlspecialchars((string)$order["_id"]) ?>
        </div>

    </div>


    <?php if ($error !== ""): ?>

        <div class="message">
            <?= htmlspecialchars(urldecode($error)) ?>
        </div>

    <?php endif; ?>


    <div class="note">

        <strong>Note:</strong>

        Change quantity and click
        <strong>Save Changes</strong>.

        Set quantity to
        <strong>0</strong>
        to remove an item.

    </div>


    <?php if (empty($items)): ?>

        <div class="empty">
            This order has no items.
        </div>

    <?php else: ?>

        <form method="POST" action="edit_order.php">

            <input
                type="hidden"
                name="order_id"
                value="<?= htmlspecialchars((string)$order["_id"]) ?>"
            >


            <?php foreach ($items as $index => $item): ?>

                <?php

                if ($item instanceof BSONArray) {
                    $item = iterator_to_array($item, false);
                }

                $name = $item["name"] ?? "Product";
                $size = $item["size"] ?? "";
                $price = (float)($item["price"] ?? 0);
                $quantity = (int)($item["quantity"] ?? 1);

                $subtotal = $price * $quantity;

                ?>


                <div class="item">

                    <div class="item-top">

                        <div>

                            <div class="product-name">
                                <?= htmlspecialchars($name) ?>
                            </div>

                            <div class="size">
                                Size:
                                <?= htmlspecialchars($size) ?>
                            </div>

                            <div class="price">
                                Price:
                                ₹<?= number_format($price, 2) ?>
                            </div>

                            <div class="subtotal">
                                Current Subtotal:
                                ₹<?= number_format($subtotal, 2) ?>
                            </div>


                            <a
                                href="remove_order_item.php?order_id=<?= urlencode((string)$order["_id"]) ?>&item_index=<?= $index ?>"
                                class="remove-btn"
                            >
                                🗑️ Remove Item
                            </a>

                        </div>


                        <div class="quantity-box">

                            <label>
                                Quantity:
                            </label>

                            <input
                                type="number"
                                name="quantities[<?= $index ?>]"
                                value="<?= $quantity ?>"
                                min="0"
                                max="20"
                                required
                            >

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>


            <div class="buttons">

                <button
                    type="submit"
                    class="btn save-btn"
                >
                    💾 Save Changes
                </button>

                <a
                    href="my_orders.php"
                    class="btn back-btn"
                >
                    ← Back to Orders
                </a>

            </div>

        </form>

    <?php endif; ?>

</div>

</body>

</html>