<?php
// MySQL connection
$mysqli = new mysqli("localhost", "username", "password", "elderly_care_sql");

// MongoDB connection
require 'vendor/autoload.php'; // Use Composer to install MongoDB PHP library
$mongoClient = new MongoDB\Client("mongodb+srv://vaishnavi2005:vaishnavi@cluster0.ddzlgcu.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$mongoCollection = $mongoClient->elderly_care->sensor_data;
?>
