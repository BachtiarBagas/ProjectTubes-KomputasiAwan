<?php
$host = 'foodgroup.mysql.database.azure.com';
$username = 'FooodHivee';
$password = 'place123#';
$db_name = 'foodgroup';

$conn = mysqli_init();

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
?>
