<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

try {

    $client = new Client("mongodb://127.0.0.1:27017");

    $database = $client->selectDatabase("shree_shyam_coffee");

    // Collections
    $usersCollection = $database->selectCollection("users");
    $productsCollection = $database->selectCollection("products");
    $ordersCollection = $database->selectCollection("orders");
    $adminsCollection = $database->selectCollection("admins");

} catch (Exception $e) {

    die("MongoDB Connection Failed: " . $e->getMessage());

}

?>