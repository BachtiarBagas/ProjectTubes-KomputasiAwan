<?php
$host = 'foodorder.mysql.database.azure.com';
$username = 'admin1@foodorder-server';
$password = 'place123#';
$db_name = 'foodorder';

$conn = mysqli_init();

// Azure MySQL WAJIB SSL
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

// Koneksi
if (!mysqli_real_connect(
    $conn,
    $host,
    $username,
    $password,
    $db_name,
    3306,
    NULL,
    MYSQLI_CLIENT_SSL
)) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}

echo "✅ Berhasil Konek ke Azure MySQL!";
?>

