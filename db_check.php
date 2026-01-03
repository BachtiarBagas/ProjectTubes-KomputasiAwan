<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'foodorder-server.mysql.database.azure.com';
$user = 'admin1@foodorder-server';
$pass = 'place123#';
$db   = 'foodorder';

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

if (!mysqli_real_connect(
    $conn,
    $host,
    $user,
    $pass,
    $db,
    3306,
    NULL,
    MYSQLI_CLIENT_SSL
)) {
    die("❌ DB CONNECT ERROR: " . mysqli_connect_error());
}

echo "✅ DATABASE CONNECTED SUCCESSFULLY";
