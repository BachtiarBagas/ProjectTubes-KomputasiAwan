<?php
session_start();

$host = "foodgroup.mysql.database.azure.com";
$db   = "foodgroup";
$user = "FooodHivee";
$pass = "place123#";
$port = 3306;

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
