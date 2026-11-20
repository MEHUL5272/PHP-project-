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
$itemIndex = isset($_GET["item_index"])
    ? (int) $_GET["item_index"]
    : (isset($_POST["item_index"]) ? (int) $_POST["item_index"] : -1);

try {
    $objectId = new ObjectId($orderId);
} catch (Exception $e) {
    die("Invalid Order ID.");
}

if ($itemIndex < 0) {
    die("Invalid Item.");
}

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
| Only Pending Order Can Be Modified
|--------------------------------------------------------------------------
*/

if (($order["status"] ?? "") !== "Pending") {
    die("This order can no longer be modified.");
}

$items = $order["items"] ?? [];

if ($items instanceof \MongoDB\Model\BSONArray) {
    $items = iterator_to_array($items, false);
}

if (!isset($items[$itemIndex])) {
    die("Item not found.");
}

$item = $items[$itemIndex];

$name = $item["name"] ?? "Product";
$size = $item["size"] ?? "";
$quantity = (int) ($item["quantity"] ?? 0);
$subtotal = (float) ($item["subtotal"] ?? 0);

/*
|--------------------------------------------------------------------------
| Remove Item
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {

        // Remove selected item
        unset($items[$itemIndex]);

        // Re-index array
        $items = array_values($items);

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
        | Recalculate Total
        |--------------------------------------------------------------------------
        */

        $newTotal = 0;

        foreach ($items as $orderItem) {

            $price = (float) ($orderItem["price"] ?? 0);
            $qty = (int) ($orderItem["quantity"] ?? 0);

            $subtotalValue = $price * $qty;

            $newTotal += $subtotalValue;
        }

        /*
        |--------------------------------------------------------------------------
        | Update MongoDB
        |--------------------------------------------------------------------------
        */

        $ordersCollection->updateOne(
            [
                "_id" => new ObjectId($orderId),
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

        header(
            "Location: my_orders.php?success=Item+removed+successfully"
        );

        exit;

    } catch (Exception $e) {

        header(
            "Location: my_orders.php?error=Unable+to+remove+item"
        );

        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Remove Item - Shree Shyam Coffee</title>

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
            max-width: 600px;
            margin: 50px auto;
        }

        .box {
            background: #181818;
            border: 1px solid #333;
            border-radius: 14px;
            padding: 30px;
        }

        .icon {
            text-align: center;
            font-size: 55px;
            margin-bottom: 15px;
        }

        h1 {
            text-align: center;
            color: #e05a47;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #aaa;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .item-box {
            background: #222;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .product-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .size {
            color: #d4a017;
            margin-bottom: 6px;
        }

        .quantity {
            color: #aaa;
            margin-bottom: 6px;
        }

        .price {
            color: #d4a017;
            font-weight: bold;
        }

        .warning {
            background: #301817;
            border: 1px solid #73322c;
            color: #f1aaa2;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 25px;
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

        .remove-btn {
            background: #d9534f;
            color: #fff;
        }

        .remove-btn:hover {
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

            <div class="icon">
                🗑️
            </div>

            <h1>
                Remove Item?
            </h1>

            <p class="subtitle">
                Are you sure you want to remove this item
                from your order?
            </p>


            <div class="item-box">

                <div class="product-name">
                    <?= htmlspecialchars($name) ?>
                </div>

                <div class="size">
                    Size: <?= htmlspecialchars($size) ?>
                </div>

                <div class="quantity">
                    Quantity: <?= $quantity ?>
                </div>

                <div class="price">
                    Subtotal:
                    ₹<?= number_format($subtotal, 2) ?>
                </div>

            </div>


            <div class="warning">

                ⚠️ This item will be permanently removed
                from this order.

            </div>


            <form method="POST">

                <input type="hidden" name="order_id" value="<?= htmlspecialchars((string) $order["_id"]) ?>">

                <input type="hidden" name="item_index" value="<?= $itemIndex ?>">

                <div class="buttons">

                    <button type="submit" class="btn remove-btn">
                        🗑️ Yes, Remove
                    </button>

                    <a href="edit_order.php?order_id=<?= urlencode((string) $order["_id"]) ?>" class="btn back-btn">
                        ← Keep Item
                    </a>

                </div>

            </form>

        </div>

    </div>

</body>

</html>