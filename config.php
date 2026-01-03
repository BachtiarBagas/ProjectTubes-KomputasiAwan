<?php
$host = 'foodorder.mysql.database.azure.com';
$dbname = 'foodorder';
$username = 'admin1@foodorder';
$password = 'PASSWORD_KAMU';

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;port=3306;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
