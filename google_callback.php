<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// TAMPILKAN ERROR (untuk debugging sementara)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// =======================
// GOOGLE CONFIG
// =======================
$client_id     = '889173134800-v3a3gvg12u85oops6gbkvjqf5kihpb93.apps.googleusercontent.com';
$client_secret = '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';
$redirect_uri  = 'http://localhost:8000/google_callback.php';

// =======================
// CEK CODE GOOGLE
// =======================
if (!isset($_GET['code'])) {
    die("Tidak ada authorization code");
}

// CEK STATE
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    die("Invalid state.");
}

// =======================
// AMBIL TOKEN
// =======================
$token_url = 'https://oauth2.googleapis.com/token';

$token_data = http_build_query([
    'code'          => $_GET['code'],
    'client_id'     => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri'  => $redirect_uri,
    'grant_type'    => 'authorization_code'
]);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $token_url,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $token_data,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

$response = curl_exec($ch);
curl_close($ch);

$token = json_decode($response, true);

if (!isset($token['access_token'])) {
    die("Gagal ambil access token");
}

// =======================
// AMBIL DATA USER GOOGLE
// =======================
$user_url = "https://www.googleapis.com/oauth2/v2/userinfo?access_token=" . $token['access_token'];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $user_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

$user_json = curl_exec($ch);
curl_close($ch);

$user_data = json_decode($user_json, true);

if (!isset($user_data['email'])) {
    die("Data user Google tidak valid");
}

$email   = $user_data['email'];
$name    = $user_data['name']   ?? '';
$picture = $user_data['picture'] ?? '';

// =======================
// DATABASE
// =======================
require_once "config.php";

try {

    // CEK USER
    $stmt = $conn->prepare("
        SELECT id, full_name 
        FROM users 
        WHERE email = :email
    ");

    $stmt->execute([
        ':email' => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

        // user sudah ada
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];

    } else {

        // tambah user baru
        $stmt = $conn->prepare("
            INSERT INTO users (full_name, email, password, created_at)
            VALUES (:full_name, :email, '', NOW())
        ");

        $stmt->execute([
            ':full_name' => $name,
            ':email'     => $email
        ]);

        $_SESSION['user_id']   = $conn->lastInsertId();
        $_SESSION['full_name'] = $name;
    }

    $_SESSION['email'] = $email;

    // REDIRECT
    header("Location: dashboard.php");
    exit();

} catch (PDOException $e) {

    die("DB ERROR: " . $e->getMessage());
}
