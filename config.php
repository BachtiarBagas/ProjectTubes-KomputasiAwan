<?php
session_start();

// Deteksi environment (Azure atau local)
$is_azure = getenv('WEBSITE_SITE_NAME') !== false;

if ($is_azure) {
    // Konfigurasi Azure MySQL
    $host = getenv('DB_HOST') ?: 'foodgroup.mysql.database.azure.com';
    $dbname = getenv('DB_NAME') ?: 'foodgroup';
    $username = getenv('DB_USERNAME') ?: 'FooodHivee';
    $password = getenv('DB_PASSWORD') ?: 'place123#';
    $port = 3306;
    
    // Azure MySQL memerlukan SSL
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ];
} else {
    // Konfigurasi local
    $host = 'localhost';
    $dbname = 'food_ordering';
    $username = 'root';
    $password = '';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ];
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
} catch(PDOException $e) {
    // Log error untuk debugging
    error_log("Database connection failed: " . $e->getMessage());
    die("Connection failed. Please check your database configuration.");
}
?>
