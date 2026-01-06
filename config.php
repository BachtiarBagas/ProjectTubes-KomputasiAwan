<?php
session_start();

/* =========================
   DATABASE CONFIG (AZURE)
   ========================= */
$host     = 'foodgroup.mysql.database.azure.com';
$dbname   = 'foodgroup';
$username = 'FooodHivee@foodgroup'; // ⚠️ WAJIB format user@servername
$password = 'place123#';
$port     = 3306;

/* =========================
   SSL CERT PATH
   ========================= */
// Pastikan file ini ADA di project & ikut ke-push ke GitHub
$ssl_ca = __DIR__ . '/DigiCertGlobalRootCA.crt.pem';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;port=$port;charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,

        // 🔐 SSL Azure (WAJIB)
        PDO::MYSQL_ATTR_SSL_CA       => $ssl_ca,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ];

    $conn = new PDO($dsn, $username, $password, $options);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
