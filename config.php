<?php
session_start();

$host = "foodgroup.mysql.database.azure.com";
$db   = "foodgroup";
$user = "FooodHivee";
$pass = "place123#";
$port = 3306;

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_SSL_CA => true,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false    
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
