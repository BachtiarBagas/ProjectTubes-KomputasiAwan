<?php
require_once 'base_url.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Config
$client_id     = '889173134800-v3a3gvg12u85oops6gbkvjqf5kihpb93.apps.googleusercontent.com';
$client_secret = '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';
$redirect_uri  = BASE_URL . '/google_callback.php'; // Harus match dengan login

if (!isset($_GET['code'])) die("Error: Tidak ada authorization code.");

// 1. Tukar Code dengan Token
$token_url = 'https://oauth2.googleapis.com/token';
$token_data = [
    'code'          => $_GET['code'],
    'client_id'     => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri'  => $redirect_uri,
    'grant_type'    => 'authorization_code'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Di Azure, kadang perlu set false jika sertifikat bermasalah, tapi idealnya true
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
$response = curl_exec($ch);
curl_close($ch);

$token = json_decode($response, true);
if (!isset($token['access_token'])) die("Gagal ambil token Google.");

// 2. Ambil Data User
$user_url = "https://www.googleapis.com/oauth2/v2/userinfo?access_token=" . $token['access_token'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $user_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$user_json = curl_exec($ch);
curl_close($ch);

$user_data = json_decode($user_json, true);
$email = $user_data['email'];
$name  = $user_data['name'];

// 3. Login/Register ke Database
try {
    $stmt = $conn->prepare("SELECT id, full_name, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
    } else {
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, oauth_provider) VALUES (?, ?, '', 'customer', 'google')");
        $stmt->execute([$name, $email]);
        $_SESSION['user_id'] = $conn->lastInsertId();
        $_SESSION['full_name'] = $name;
        $_SESSION['role'] = 'customer';
    }

    // Redirect ke Dashboard setelah sukses
    header("Location: dashboard.php");
    exit();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>