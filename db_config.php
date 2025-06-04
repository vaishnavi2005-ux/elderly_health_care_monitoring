<?php

$mysqli = new mysqli("localhost", "username", "password", "elderly_care_sql");


require 'vendor/autoload.php';
$mongoClient = new MongoDB\Client("mongodb+srv://vaishnavi2005:vaishnavi@cluster0.ddzlgcu.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$mongoCollection = $mongoClient->elderly_care->sensor_data;
?>
