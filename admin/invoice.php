<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit;
}

$orderId = $_GET["order_id"] ?? "";

if ($orderId === "") {
    die("Invalid Order ID.");
}

try {

    $client = new Client("mongodb://127.0.0.1:27017");

    $db = $client->selectDatabase("shree_shyam_coffee");

    $ordersCollection = $db->orders;

    $order = $ordersCollection->findOne([
        "_id" => new ObjectId($orderId)
    ]);

    if (!$order) {
        die("Order not found.");
    }

} catch (Exception $e) {

    die("MongoDB Error: " . $e->getMessage());

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Invoice - Shree Shyam Coffee</title>

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

.invoice {

    width: 90%;

    max-width: 800px;

    margin: 40px auto;

    background: white;

    color: #222;

    padding: 40px;

    border-radius: 10px;

}

.top {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    border-bottom: 2px solid #e7a33a;

    padding-bottom: 20px;

    margin-bottom: 25px;

}

.shop-name {

    font-size: 28px;

    font-weight: bold;

    color: #9b6415;

}

.shop-info {

    color: #666;

    margin-top: 8px;

    line-height: 1.6;

}

.invoice-title {

    text-align: right;

}

.invoice-title h1 {

    margin: 0;

    color: #9b6415;

    font-size: 30px;

}

.invoice-title p {

    color: #666;

    margin: 8px 0 0;

}

.customer {

    background: #f7f7f7;

    padding: 18px;

    border-radius: 8px;

    margin-bottom: 25px;

}

.customer h3 {

    margin-top: 0;

    color: #9b6415;

}

.customer-grid {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 12px;

}

.label {

    color: #777;

    font-size: 12px;

}

table {

    width: 100%;

    border-collapse: collapse;

    margin-top: 20px;

}

th {

    background: #9b6415;

    color: white;

    padding: 12px;

    text-align: left;

}

td {

    padding: 12px;

    border-bottom: 1px solid #ddd;

}

.right {

    text-align: right;

}

.total-box {

    margin-top: 25px;

    display: flex;

    justify-content: flex-end;

}

.total {

    width: 280px;

}

.total-row {

    display: flex;

    justify-content: space-between;

    padding: 8px 0;

}

.grand-total {

    border-top: 2px solid #9b6415;

    margin-top: 8px;

    padding-top: 12px;

    font-size: 21px;

    font-weight: bold;

    color: #9b6415;

}

.status {

    margin-top: 25px;

    display: flex;

    gap: 20px;

}

.badge {

    padding: 7px 12px;

    border-radius: 5px;

    background: #eee;

    font-weight: bold;

}

.footer {

    text-align: center;

    margin-top: 40px;

    padding-top: 20px;

    border-top: 1px solid #ddd;

    color: #777;

}

.buttons {

    text-align: center;

    margin: 25px 0;

}

.btn {

    display: inline-block;

    padding: 12px 20px;

    margin: 5px;

    border-radius: 6px;

    text-decoration: none;

    font-weight: bold;

    border: none;

    cursor: pointer;

}

.print-btn {

    background: #e7a33a;

    color: #111;

}

.back-btn {

    background: #333;

    color: white;

}


/* PRINT */

@media print {

    body {

        background: white;

    }

    .invoice {

        width: 100%;

        margin: 0;

        padding: 20px;

        box-shadow: none;

    }

    .buttons {

        display: none;

    }

}


/* MOBILE */

@media(max-width: 600px) {

    .invoice {

        width: 95%;

        padding: 20px;

    }

    .top {

        flex-direction: column;

        gap: 20px;

    }

    .invoice-title {

        text-align: left;

    }

    .customer-grid {

        grid-template-columns: 1fr;

    }

    .status {

        flex-direction: column;

        gap: 8px;

    }

}

</style>

</head>

<body>


<div class="invoice">


    <!-- HEADER -->

    <div class="top">

        <div>

            <div class="shop-name">
                ☕ Shree Shyam Coffee
            </div>

            <div class="shop-info">

                Fresh Coffee & Shakes<br>

                Ahmedabad, Gujarat<br>

                Thank you for ordering!

            </div>

        </div>


        <div class="invoice-title">

            <h1>
                INVOICE
            </h1>

            <p>

                Order ID:<br>

                <strong>
                    <?= htmlspecialchars(
                        (string)$order["_id"]
                    ) ?>
                </strong>

            </p>

            <p>

                <?php

                if (
                    isset($order["created_at"]) &&
                    $order["created_at"]
                    instanceof MongoDB\BSON\UTCDateTime
                ) {

                    echo $order["created_at"]
                        ->toDateTime()
                        ->format("d M Y, h:i A");

                } else {

                    echo "-";

                }

                ?>

            </p>

        </div>

    </div>


    <!-- CUSTOMER -->

    <div class="customer">

        <h3>
            Customer Details
        </h3>

        <div class="customer-grid">


            <div>

                <div class="label">
                    Name
                </div>

                <?= htmlspecialchars(
                    $order["customer_name"] ?? "-"
                ) ?>

            </div>


            <div>

                <div class="label">
                    Mobile
                </div>

                <?= htmlspecialchars(
                    $order["mobile"] ?? "-"
                ) ?>

            </div>


            <div>

                <div class="label">
                    Email
                </div>

                <?= htmlspecialchars(
                    $order["email"] ?? "-"
                ) ?>

            </div>


            <div>

                <div class="label">
                    Address
                </div>

                <?= htmlspecialchars(
                    $order["address"] ?? "-"
                ) ?>

            </div>


        </div>

    </div>


    <!-- ITEMS -->

    <h3>
        Ordered Items
    </h3>

    <table>

        <thead>

            <tr>

                <th>
                    #
                </th>

                <th>
                    Product
                </th>

                <th>
                    Size
                </th>

                <th>
                    Qty
                </th>

                <th class="right">
                    Price
                </th>

                <th class="right">
                    Subtotal
                </th>

            </tr>

        </thead>


        <tbody>

        <?php

        $number = 1;

        foreach (($order["items"] ?? []) as $item):

        ?>

            <tr>

                <td>
                    <?= $number++ ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $item["name"] ?? "-"
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $item["size"] ?? "-"
                    ) ?>
                </td>

                <td>
                    <?= (int)(
                        $item["quantity"] ?? 0
                    ) ?>
                </td>

                <td class="right">

                    ₹<?= number_format(
                        (float)(
                            $item["price"] ?? 0
                        ),
                        2
                    ) ?>

                </td>

                <td class="right">

                    ₹<?= number_format(
                        (float)(
                            $item["subtotal"] ?? 0
                        ),
                        2
                    ) ?>

                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>


    <!-- TOTAL -->

    <div class="total-box">

        <div class="total">

            <div class="total-row">

                <span>
                    Subtotal
                </span>

                <span>
                    ₹<?= number_format(
                        (float)(
                            $order["total"] ?? 0
                        ),
                        2
                    ) ?>
                </span>

            </div>


            <div class="total-row grand-total">

                <span>
                    Grand Total
                </span>

                <span>
                    ₹<?= number_format(
                        (float)(
                            $order["total"] ?? 0
                        ),
                        2
                    ) ?>
                </span>

            </div>

        </div>

    </div>


    <!-- STATUS -->

    <div class="status">

        <div class="badge">

            Order Status:

            <?= htmlspecialchars(
                $order["status"] ?? "Pending"
            ) ?>

        </div>


        <div class="badge">

            Payment:

            <?= htmlspecialchars(
                $order["payment_status"] ?? "Pending"
            ) ?>

        </div>

    </div>


    <!-- FOOTER -->

    <div class="footer">

        Thank you for choosing
        <strong>Shree Shyam Coffee</strong> ☕

        <br>

        Have a great day!

    </div>


</div>


<!-- BUTTONS -->

<div class="buttons">

    <button
        class="btn print-btn"
        onclick="window.print()"
    >
        🖨️ Print Bill
    </button>


    <a
        href="orders.php"
        class="btn back-btn"
    >
        ← Back to Orders
    </a>

</div>


</body>

</html>