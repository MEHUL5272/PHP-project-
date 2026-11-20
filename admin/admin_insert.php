<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

try {

    $client = new Client("mongodb://127.0.0.1:27017");

    $db = $client->selectDatabase("shree_shyam_coffee");

    $admins = $db->admins;

    $email = "admin@shreeshyam.com";
    $password = "Admin@1234";

    // Check existing admin
    $existingAdmin = $admins->findOne([
        "email" => $email
    ]);

    if ($existingAdmin) {

        echo "Admin already exists.";

        exit;
    }

    $admins->insertOne([

        "name" => "Admin",

        "email" => $email,

        "password" => password_hash(
            $password,
            PASSWORD_DEFAULT
        ),

        "created_at" =>
            new MongoDB\BSON\UTCDateTime()

    ]);

    echo "<h2>Admin Created Successfully!</h2>";

    echo "<p>Email: <b>admin@shreeshyam.com</b></p>";

    echo "<p>Password: <b>Admin@1234</b></p>";

    echo "<p><b>Now delete admin_insert.php for security.</b></p>";

} catch (Exception $e) {

    echo "Error: " . $e->getMessage();

}
?>