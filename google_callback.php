<?php
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'None');

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// AUTO BASE URL
$base_url = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST';

$client_id     = 'ISI_CLIENT_ID_KAMU';
$client_secret = 'ISI_CLIENT_SECRET_KAMU';
$redirect_uri  = $base_url . '/google_callback.php';

// VALIDASI CODE & STATE
if (!isset($_GET['code']) || !isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    die("OAuth validation failed");
}

// ================= TOKEN =================
$token_url = 'https://oauth2.googleapis.com/token';

$token_data = http_build_query([
    'code'          => $_GET['code'],
    'client_id'     => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri'  => $redirect_uri,
    'grant_type'    => 'authorization_code'
]);

$ch = curl_init($token_url);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $token_data,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2
]);

$response = curl_exec($ch);
curl_close($ch);

$token = json_decode($response, true);
if (!isset($token['access_token'])) {
    die("Token error");
}

// ================= USER INFO =================
$user_url = "https://www.googleapis.com/oauth2/v2/userinfo?access_token=" . $token['access_token'];

$ch = curl_init($user_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2
]);

$user_json = curl_exec($ch);
curl_close($ch);

$user = json_decode($user_json, true);
if (!isset($user['email'])) {
    die("Invalid Google user data");
}

require_once "config.php";

// ================= DATABASE =================
$stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = :email LIMIT 1");
$stmt->execute(['email' => $user['email']]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($data) {
    $_SESSION['user_id'] = $data['id'];
    $_SESSION['full_name'] = $data['full_name'];
} else {
    $stmt = $conn->prepare("
        INSERT INTO users (full_name, email, password, created_at)
        VALUES (:name, :email, '', NOW())
    ");
    $stmt->execute([
        'name'  => $user['name'] ?? '',
        'email' => $user['email']
    ]);

    $_SESSION['user_id'] = $conn->lastInsertId();
    $_SESSION['full_name'] = $user['name'] ?? '';
}

$_SESSION['email'] = $user['email'];

header("Location: dashboard.php");
exit;
