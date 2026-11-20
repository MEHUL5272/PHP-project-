<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\ObjectId;


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

    $productsCollection = $db->products;

    $message = "";
    $messageType = "";


// ===============================
// DELETE PRODUCT
// ===============================

    if (
        $_SERVER["REQUEST_METHOD"] === "POST" &&
        isset($_POST["delete_product"])
    ) {

        $productId = $_POST["product_id"] ?? "";

        if ($productId !== "") {

            try {

                $productsCollection->deleteOne([
                    "_id" => new ObjectId($productId)
                ]);

                $message = "Product deleted successfully.";
                $messageType = "success";

            } catch (Exception $e) {

                $message = "Invalid product ID.";
                $messageType = "error";
            }
        }
    }


// ===============================
// ADD PRODUCT
// ===============================

    if (
        $_SERVER["REQUEST_METHOD"] === "POST" &&
        isset($_POST["add_product"])
    ) {

        $name = trim($_POST["name"] ?? "");
        $category = trim($_POST["category"] ?? "");

        $price250 = trim($_POST["price_250"] ?? "");
        $price350 = trim($_POST["price_350"] ?? "");


        if (
            $name === "" ||
            $category === "" ||
            $price250 === "" ||
            $price350 === ""
        ) {

            $message = "Please fill all product details.";
            $messageType = "error";

        } elseif (
            !is_numeric($price250) ||
            !is_numeric($price350)
        ) {

            $message = "Prices must be valid numbers.";
            $messageType = "error";

        } else {

            $productsCollection->insertOne([

                "name" => $name,

                "category" => $category,

                "price_250" => (float)$price250,

                "price_350" => (float)$price350,

                "created_at" =>
                    new MongoDB\BSON\UTCDateTime()

            ]);

            $message = "Product added successfully.";
            $messageType = "success";
        }
    }


// ===============================
// UPDATE PRODUCT
// ===============================

    if (
        $_SERVER["REQUEST_METHOD"] === "POST" &&
        isset($_POST["update_product"])
    ) {

        $productId = $_POST["product_id"] ?? "";

        $name = trim($_POST["name"] ?? "");

        $category = trim($_POST["category"] ?? "");

        $price250 = trim($_POST["price_250"] ?? "");

        $price350 = trim($_POST["price_350"] ?? "");


        if (
            $productId === "" ||
            $name === "" ||
            $category === "" ||
            $price250 === "" ||
            $price350 === ""
        ) {

            $message = "Please fill all product details.";
            $messageType = "error";

        } elseif (
            !is_numeric($price250) ||
            !is_numeric($price350)
        ) {

            $message = "Prices must be valid numbers.";
            $messageType = "error";

        } else {

            try {

                $productsCollection->updateOne(

                    [
                        "_id" => new ObjectId($productId)
                    ],

                    [
                        '$set' => [

                            "name" => $name,

                            "category" => $category,

                            "price_250" =>
                                (float)$price250,

                            "price_350" =>
                                (float)$price350

                        ]
                    ]

                );

                $message =
                    "Product updated successfully.";

                $messageType = "success";

            } catch (Exception $e) {

                $message =
                    "Unable to update product.";

                $messageType = "error";
            }
        }
    }


// ===============================
// GET PRODUCTS
// ===============================

    $products = $productsCollection->find(
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

<title>Manage Products - Shree Shyam Coffee</title>


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

    max-width: 1250px;

    margin: auto;

    padding: 40px 0 70px;
}


/* ===============================
   MESSAGE
================================ */

.message {

    padding: 14px 18px;

    border-radius: 8px;

    margin-bottom: 25px;

    font-weight: bold;
}

.success {

    background: #16351f;

    color: #72e69a;

    border: 1px solid #2c7041;
}

.error {

    background: #351919;

    color: #ff7777;

    border: 1px solid #713333;
}


/* ===============================
   ADD PRODUCT
================================ */

.add-box {

    background: #161616;

    border: 1px solid #333;

    border-radius: 14px;

    padding: 28px;

    margin-bottom: 35px;
}

.section-title {

    color: #e7a33a;

    font-size: 25px;

    margin-bottom: 20px;
}

.form-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 18px;
}

.form-group {

    display: flex;

    flex-direction: column;
}

.form-group label {

    color: #ddd;

    margin-bottom: 7px;

    font-weight: bold;
}

.form-group input,
.form-group select {

    width: 100%;

    padding: 12px;

    background: #222;

    color: white;

    border: 1px solid #444;

    border-radius: 7px;

    outline: none;

    font-size: 15px;
}

.form-group input:focus,
.form-group select:focus {

    border-color: #e7a33a;
}

.add-btn {

    margin-top: 20px;

    padding: 13px 25px;

    border: none;

    border-radius: 7px;

    background: #e7a33a;

    color: #111;

    font-weight: bold;

    font-size: 15px;

    cursor: pointer;
}

.add-btn:hover {

    background: #f5b84c;
}


/* ===============================
   PRODUCTS
================================ */

.products-box {

    background: #161616;

    border: 1px solid #333;

    border-radius: 14px;

    overflow: hidden;
}

.table-wrapper {

    width: 100%;

    overflow-x: auto;
}

table {

    width: 100%;

    border-collapse: collapse;

    min-width: 900px;
}

th {

    background: #111;

    color: #e7a33a;

    text-align: left;

    padding: 16px;

    border-bottom: 1px solid #333;
}

td {

    padding: 15px;

    border-bottom: 1px solid #292929;

    color: #ddd;

    vertical-align: middle;
}

tr:last-child td {

    border-bottom: none;
}

tr:hover td {

    background: #1b1b1b;
}

.product-name {

    color: white;

    font-weight: bold;

    font-size: 16px;
}

.category {

    color: #aaa;

    font-size: 14px;
}

.price {

    color: #e7a33a;

    font-weight: bold;
}


/* ===============================
   EDIT FORM
================================ */

.edit-form {

    display: grid;

    grid-template-columns:
        1.3fr 1fr 0.7fr 0.7fr auto;

    gap: 8px;

    align-items: center;
}

.edit-form input {

    width: 100%;

    padding: 9px;

    background: #222;

    color: white;

    border: 1px solid #444;

    border-radius: 6px;

    outline: none;
}

.edit-form input:focus {

    border-color: #e7a33a;
}

.update-btn {

    border: none;

    background: #e7a33a;

    color: #111;

    padding: 9px 13px;

    border-radius: 6px;

    font-weight: bold;

    cursor: pointer;
}

.update-btn:hover {

    background: #f5b84c;
}


/* ===============================
   DELETE
================================ */

.delete-btn {

    margin-top: 8px;

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
   RESPONSIVE
================================ */

@media(max-width: 800px) {

    .navbar {

        flex-direction: column;

        justify-content: center;
    }

    .nav-links {

        flex-wrap: wrap;

        justify-content: center;
    }

    .form-grid {

        grid-template-columns: 1fr;
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

        <a href="users.php">
            Users
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
        Manage Products ☕
    </h1>

    <p>
        Add, edit and delete coffee products
    </p>

</section>


<div class="container">


<?php if ($message !== ""): ?>

    <div class="message <?= $messageType ?>">

        <?= htmlspecialchars($message) ?>

    </div>

<?php endif; ?>


<!-- ===============================
     ADD PRODUCT
================================ -->

<div class="add-box">

    <div class="section-title">
        ➕ Add New Product
    </div>


    <form method="POST">


        <div class="form-grid">


            <div class="form-group">

                <label>
                    Product Name
                </label>

                <input
                    type="text"
                    name="name"
                    placeholder="Example: Cold Coffee"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Category
                </label>

                <select
                    name="category"
                    required
                >

                    <option value="">
                        Select Category
                    </option>

                    <option value="Cold Coffee">
                        Cold Coffee
                    </option>

                    <option value="Shake">
                        Shake
                    </option>

                    <option value="Ice Tea">
                        Ice Tea
                    </option>

                </select>

            </div>


            <div class="form-group">

                <label>
                    250ml Price
                </label>

                <input
                    type="number"
                    name="price_250"
                    min="0"
                    step="0.01"
                    placeholder="60"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    350ml Price
                </label>

                <input
                    type="number"
                    name="price_350"
                    min="0"
                    step="0.01"
                    placeholder="80"
                    required
                >

            </div>


        </div>


        <button
            type="submit"
            name="add_product"
            class="add-btn"
        >
            ➕ Add Product
        </button>


    </form>

</div>


<!-- ===============================
     PRODUCT LIST
================================ -->

<div class="section-title">

    ☕ All Products

</div>


<div class="products-box">

<div class="table-wrapper">


<?php

$hasProducts = false;

foreach ($products as $product):

    $hasProducts = true;

endforeach;


// Re-fetch cursor
$products = $productsCollection->find(
    [],
    [
        "sort" => [
            "created_at" => -1
        ]
    ]
);


if (!$hasProducts):

?>

    <div class="empty">

        <div class="empty-icon">
            ☕
        </div>

        <h2>
            No Products Found
        </h2>

        <p>
            Add your first product above.
        </p>

    </div>


<?php else: ?>


<table>

<thead>

<tr>

    <th>
        Product
    </th>

    <th>
        Category
    </th>

    <th>
        250ml
    </th>

    <th>
        350ml
    </th>

    <th>
        Edit
    </th>

    <th>
        Delete
    </th>

</tr>

</thead>


<tbody>


<?php foreach ($products as $product): ?>


<tr>


    <td>

        <div class="product-name">

            <?= htmlspecialchars(
                $product["name"] ?? "-"
            ) ?>

        </div>

    </td>


    <td>

        <div class="category">

            <?= htmlspecialchars(
                $product["category"] ?? "-"
            ) ?>

        </div>

    </td>


    <td>

        <div class="price">

            ₹<?= number_format(
                (float)(
                    $product["price_250"] ?? 0
                ),
                2
            ) ?>

        </div>

    </td>


    <td>

        <div class="price">

            ₹<?= number_format(
                (float)(
                    $product["price_350"] ?? 0
                ),
                2
            ) ?>

        </div>

    </td>


    <td>


        <form
            method="POST"
            class="edit-form"
        >


            <input
                type="hidden"
                name="product_id"
                value="<?= htmlspecialchars(
                    (string)$product["_id"]
                ) ?>"
            >


            <input
                type="text"
                name="name"
                value="<?= htmlspecialchars(
                    $product["name"] ?? ""
                ) ?>"
                required
            >


            <input
                type="text"
                name="category"
                value="<?= htmlspecialchars(
                    $product["category"] ?? ""
                ) ?>"
                required
            >


            <input
                type="number"
                name="price_250"
                value="<?= htmlspecialchars(
                    $product["price_250"] ?? 0
                ) ?>"
                min="0"
                step="0.01"
                required
            >


            <input
                type="number"
                name="price_350"
                value="<?= htmlspecialchars(
                    $product["price_350"] ?? 0
                ) ?>"
                min="0"
                step="0.01"
                required
            >


            <button
                type="submit"
                name="update_product"
                class="update-btn"
            >
                ✏️ Update
            </button>


        </form>


    </td>


    <td>


        <form
            method="POST"
            onsubmit="return confirm('Are you sure you want to delete this product?');"
        >

            <input
                type="hidden"
                name="product_id"
                value="<?= htmlspecialchars(
                    (string)$product["_id"]
                ) ?>"
            >


            <button
                type="submit"
                name="delete_product"
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