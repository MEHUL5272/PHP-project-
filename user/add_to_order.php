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
| Only Pending Order Can Add Item
|--------------------------------------------------------------------------
*/

if (($order["status"] ?? "") !== "Pending") {
    die("Items cannot be added to this order now.");
}

/*
|--------------------------------------------------------------------------
| Get Products
|--------------------------------------------------------------------------
*/

$products = $productsCollection->find(
    [],
    [
        "sort" => [
            "category" => 1,
            "name" => 1
        ]
    ]
)->toArray();

/*
|--------------------------------------------------------------------------
| Add Item
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $productId = $_POST["product_id"] ?? "";
    $size = $_POST["size"] ?? "";
    $quantity = (int) ($_POST["quantity"] ?? 1);

    try {
        $productObjectId = new ObjectId($productId);
    } catch (Exception $e) {
        header(
            "Location: add_to_order.php?order_id=" .
            urlencode($orderId) .
            "&error=Invalid+product"
        );
        exit;
    }

    if (!in_array($size, ["250ml", "350ml"], true)) {
        header(
            "Location: add_to_order.php?order_id=" .
            urlencode($orderId) .
            "&error=Invalid+size"
        );
        exit;
    }

    if ($quantity < 1) {
        $quantity = 1;
    }

    if ($quantity > 20) {
        $quantity = 20;
    }

    /*
    |--------------------------------------------------------------------------
    | Find Product
    |--------------------------------------------------------------------------
    */

    $product = $productsCollection->findOne([
        "_id" => $productObjectId
    ]);

    if (!$product) {
        header(
            "Location: add_to_order.php?order_id=" .
            urlencode($orderId) .
            "&error=Product+not+found"
        );
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Price
    |--------------------------------------------------------------------------
    */

    $price = 0;

    // New price structure
    if (
        isset($product["prices"]) &&
        isset($product["prices"][$size])
    ) {
        $price = (float) $product["prices"][$size];
    }

    // Legacy price structure
    if ($price <= 0 && $size === "250ml") {
        $price = (float) ($product["price_250"] ?? 0);
    }

    if ($price <= 0 && $size === "350ml") {
        $price = (float) ($product["price_350"] ?? 0);
    }

    if ($price <= 0) {
        header(
            "Location: add_to_order.php?order_id=" .
            urlencode($orderId) .
            "&error=Price+not+available"
        );
        exit;
    }

    $productName = $product["name"] ?? "Product";

    /*
    |--------------------------------------------------------------------------
    | Existing Items
    |--------------------------------------------------------------------------
    */

    $items = $order["items"] ?? [];

    $itemFound = false;

    /*
    |--------------------------------------------------------------------------
    | If Same Product + Same Size Exists
    | Increase Quantity
    |--------------------------------------------------------------------------
    */

    foreach ($items as $index => $item) {

        $existingProductId = (string) ($item["product_id"] ?? "");
        $existingSize = $item["size"] ?? "";

        if (
            $existingProductId === $productId &&
            $existingSize === $size
        ) {

            $oldQuantity = (int) ($item["quantity"] ?? 0);

            $newQuantity = $oldQuantity + $quantity;

            if ($newQuantity > 20) {
                $newQuantity = 20;
            }

            $items[$index]["quantity"] = $newQuantity;
            $items[$index]["price"] = $price;
            $items[$index]["subtotal"] = $price * $newQuantity;

            $itemFound = true;

            break;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Add New Item
    |--------------------------------------------------------------------------
    */

    if (!$itemFound) {

        $items[] = [
            "product_id" => $productId,
            "name" => $productName,
            "size" => $size,
            "price" => $price,
            "quantity" => $quantity,
            "subtotal" => $price * $quantity
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate New Total
    |--------------------------------------------------------------------------
    */

    $newTotal = 0;

    foreach ($items as $item) {

        $itemPrice = (float) ($item["price"] ?? 0);
        $itemQuantity = (int) ($item["quantity"] ?? 0);

        $newTotal += $itemPrice * $itemQuantity;
    }

    /*
    |--------------------------------------------------------------------------
    | Update Order
    |--------------------------------------------------------------------------
    */

    try {

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
            "Location: my_orders.php?success=Item+added+to+order"
        );

        exit;

    } catch (Exception $e) {

        header(
            "Location: add_to_order.php?order_id=" .
            urlencode($orderId) .
            "&error=Unable+to+update+order"
        );

        exit;
    }
}

$error = $_GET["error"] ?? "";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Item - Shree Shyam Coffee</title>

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
            max-width: 650px;
            margin: 45px auto;
        }

        .title {
            text-align: center;
            margin-bottom: 30px;
        }

        .title h1 {
            color: #d4a017;
            margin-bottom: 10px;
        }

        .order-id {
            color: #aaa;
        }

        .form-box {
            background: #181818;
            border: 1px solid #333;
            border-radius: 14px;
            padding: 30px;
        }

        .message {
            background: #3a2510;
            border: 1px solid #9b6c16;
            color: #f5d47b;
            padding: 13px;
            border-radius: 7px;
            margin-bottom: 20px;
        }

        .field {
            margin-bottom: 22px;
        }

        .field label {
            display: block;
            margin-bottom: 8px;
            color: #ddd;
            font-weight: bold;
        }

        .field select,
        .field input {
            width: 100%;
            padding: 13px;
            background: #222;
            color: #fff;
            border: 1px solid #555;
            border-radius: 7px;
            font-size: 15px;
        }

        .field select:focus,
        .field input:focus {
            outline: none;
            border-color: #d4a017;
        }

        .buttons {
            display: flex;
            gap: 12px;
            margin-top: 25px;
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

        .add-btn {
            background: #d4a017;
            color: #000;
        }

        .add-btn:hover {
            background: #f0bd28;
        }

        .back-btn {
            background: #333;
            color: #fff;
        }

        .back-btn:hover {
            background: #444;
        }

        .info {
            margin-top: 20px;
            padding: 15px;
            background: #201d14;
            border: 1px solid #5c4814;
            border-radius: 8px;
            color: #e8d08b;
            line-height: 1.6;
        }

        @media (max-width: 600px) {

            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .nav-links a {
                margin: 0 6px;
            }

            .form-box {
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

        <div class="title">

            <h1>➕ Add Item to Order</h1>

            <div class="order-id">
                Order ID:
                <?= htmlspecialchars((string) $order["_id"]) ?>
            </div>

        </div>


        <?php if ($error !== ""): ?>

            <div class="message">
                <?= htmlspecialchars(str_replace("+", " ", $error)) ?>
            </div>

        <?php endif; ?>


        <div class="form-box">

            <form method="POST">

                <input type="hidden" name="order_id" value="<?= htmlspecialchars((string) $order["_id"]) ?>">


                <div class="field">

                    <label>
                        Select Product
                    </label>

                    <select name="product_id" required>

                        <option value="">
                            -- Select Product --
                        </option>

                        <?php foreach ($products as $product): ?>

                            <option value="<?= htmlspecialchars((string) $product["_id"]) ?>">
                                <?= htmlspecialchars($product["name"] ?? "Product") ?>
                                <?php if (!empty($product["category"])): ?>
                                    - <?= htmlspecialchars($product["category"]) ?>
                                <?php endif; ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="field">

                    <label>
                        Select Size
                    </label>

                    <select name="size" required>

                        <option value="">
                            -- Select Size --
                        </option>

                        <option value="250ml">
                            250ml
                        </option>

                        <option value="350ml">
                            350ml
                        </option>

                    </select>

                </div>


                <div class="field">

                    <label>
                        Quantity
                    </label>

                    <input type="number" name="quantity" value="1" min="1" max="20" required>

                </div>


                <div class="buttons">

                    <button type="submit" class="btn add-btn">
                        ➕ Add Item
                    </button>

                    <a href="my_orders.php" class="btn back-btn">
                        ← Back
                    </a>

                </div>

            </form>


            <div class="info">

                💡 <strong>Note:</strong>

                If the same product and size already exists
                in your order, its quantity will be increased
                instead of creating a duplicate item.

            </div>

        </div>

    </div>

</body>

</html>