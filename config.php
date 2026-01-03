<?php
$host = 'foodorder-server.mysql.database.azure.com'; // Ganti dengan server name kamu
$username = 'admin1'; // Ganti dengan username kamu
$password = 'place123#'; // Ganti dengan password kamu
$db_name = 'foodorder';

// Inisialisasi koneksi
$conn = mysqli_init();

// Azure mewajibkan SSL. Baris ini penting agar tidak ditolak server.
// NULL = tidak pakai sertifikat file (membiarkan sistem handle atau skip verify)
mysqli_ssl_set($conn,NULL,NULL,NULL,NULL, NULL);

// Real Connect
// Perhatikan port 3306 di parameter terakhir
if (!mysqli_real_connect($conn, $host, $username, $password, $db_name, 3306, NULL, MYSQLI_CLIENT_SSL)) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}

echo "Berhasil Konek ke Azure!";
?>
