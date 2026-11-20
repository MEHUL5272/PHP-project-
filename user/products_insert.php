<?php

require_once __DIR__ . '/vendor/autoload.php';

use MongoDB\Client;

try {

    $client = new Client("mongodb://127.0.0.1:27017");

    $db = $client->selectDatabase("shree_shyam_coffee");

    $products = $db->products;

    $products->deleteMany([]);

    $products->insertMany([

        [
            "name" => "Cold Coffee",
            "category" => "Cold Coffee",
            "prices" => [
                "250ml" => 60,
                "350ml" => 80
            ]
        ],

        [
            "name" => "Choco Mix Coffee",
            "category" => "Cold Coffee",
            "prices" => [
                "250ml" => 70,
                "350ml" => 100
            ]
        ],

        [
            "name" => "Bournvita Coffee",
            "category" => "Cold Coffee",
            "prices" => [
                "250ml" => 70,
                "350ml" => 100
            ]
        ],

        [
            "name" => "Bournvita Shake",
            "category" => "Cold Coffee",
            "prices" => [
                "250ml" => 90,
                "350ml" => 120
            ]
        ],

        [
            "name" => "Chocolate Shake",
            "category" => "Cold Coffee",
            "prices" => [
                "250ml" => 90,
                "350ml" => 120
            ]
        ],

        [
            "name" => "Oreo Shake",
            "category" => "Shake",
            "prices" => [
                "250ml" => 100,
                "350ml" => 150
            ]
        ],

        [
            "name" => "KitKat Shake",
            "category" => "Shake",
            "prices" => [
                "250ml" => 100,
                "350ml" => 150
            ]
        ],

        [
            "name" => "Cookies & Cream Shake",
            "category" => "Shake",
            "prices" => [
                "250ml" => 150,
                "350ml" => 200
            ]
        ],

        [
            "name" => "Lemon Ice Tea",
            "category" => "Ice Tea",
            "prices" => [
                "250ml" => 60,
                "350ml" => 80
            ]
        ],

        [
            "name" => "Peach Ice Tea",
            "category" => "Ice Tea",
            "prices" => [
                "250ml" => 70,
                "350ml" => 90
            ]
        ]

    ]);

    echo "10 Products Inserted Successfully!";

} catch (Exception $e) {

    echo "Error: " . $e->getMessage();

}

?>