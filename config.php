<?php
session_start();

$host = 'foodhiveprecious-server.mysql.database.azure.com'; 
$dbname = 'food_ordering';
$username = 'mmlykcgtkp';
$password = 'sB7IpNr8Hi$ObZGX';

try {
    // Menambahkan array SSL agar diizinkan oleh Azure
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, array(
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ));
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
