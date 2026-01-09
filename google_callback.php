<?php
// Start session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

error_log("Google Callback accessed");
error_log("GET params: " . print_r($_GET, true));

// =======================
// GOOGLE CONFIG
// =======================
$client_id     = '889173134800-v3a3gvg12u85oops6gbkvjqf5kihpb93.apps.googleusercontent.com';
$client_secret = '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';
$redirect_uri  = 'https://foodsite.azurewebsites.net/google_callback.php';

// =======================
// CEK ERROR DARI GOOGLE
// =======================
if (isset($_GET['error'])) {
    die("Google OAuth Error: " . htmlspecialchars($_GET['error']));
}

if (!isset($_GET['code'])) {
    die("Error: Tidak ada authorization code dari Google");
}

// =======================
// CEK STATE (Security)
// =======================
if (!isset($_GET['state']) || !isset($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    die("Error: Invalid state parameter. Silakan login ulang.");
}

// =======================
// AMBIL TOKEN
// =======================
$token_url = 'https://oauth2.googleapis.com/token';

$token_data = [
    'code'          => $_GET['code'],
    'client_id'     => $google_config['client_id'],
    'client_secret' => $google_config['client_secret'],
    'redirect_uri'  => $google_config['redirect_uri'],
    'grant_type'    => 'authorization_code'
];

$ch = curl_init();
$curl_options = [
    CURLOPT_URL            => $token_url,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($token_data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_TIMEOUT        => 30
];

// SSL Configuration
if (file_exists($google_config['ca_cert_path'])) {
    $curl_options[CURLOPT_SSL_VERIFYPEER] = true;
    $curl_options[CURLOPT_CAINFO] = $google_config['ca_cert_path'];
} else {
    $curl_options[CURLOPT_SSL_VERIFYPEER] = false;
    $curl_options[CURLOPT_SSL_VERIFYHOST] = false;
}

curl_setopt_array($ch, $curl_options);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

error_log("Token HTTP Code: " . $http_code);

if ($curl_error) {
    die("CURL Error: " . htmlspecialchars($curl_error));
}

$token = json_decode($response, true);

if (!isset($token['access_token'])) {
    die("Gagal mendapatkan access token. Error: " . print_r($token, true));
}

// =======================
// AMBIL DATA USER GOOGLE
// =======================
$user_url = "https://www.googleapis.com/oauth2/v2/userinfo";

$ch = curl_init();
$user_curl_options = [
    CURLOPT_URL            => $user_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token['access_token']],
    CURLOPT_TIMEOUT        => 30
];

if (file_exists($google_config['ca_cert_path'])) {
    $user_curl_options[CURLOPT_SSL_VERIFYPEER] = true;
    $user_curl_options[CURLOPT_CAINFO] = $google_config['ca_cert_path'];
} else {
    $user_curl_options[CURLOPT_SSL_VERIFYPEER] = false;
}

curl_setopt_array($ch, $user_curl_options);

$user_json = curl_exec($ch);
$user_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$user_data = json_decode($user_json, true);

if (!isset($user_data['email'])) {
    die("Error: Data user Google tidak valid.");
}

$email = $user_data['email'];
$name = $user_data['name'] ?? $email;

// =======================
// DATABASE
// =======================
require_once "config.php";

try {
    $stmt = $conn->prepare("SELECT id, username, full_name, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $user['role'] ?? 'customer';
    } else {
        // Create new user
        $username = explode('@', $email)[0];
        $username = preg_replace('/[^a-zA-Z0-9]/', '', $username);
        
        $counter = 1;
        $original_username = $username;
        while (true) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if (!$stmt->fetch()) break;
            $username = $original_username . $counter++;
        }
        
        $stmt = $conn->prepare("INSERT INTO users (username, full_name, email, password, role, created_at) VALUES (?, ?, ?, '', 'customer', NOW())");
        $stmt->execute([$username, $name, $email]);
        
        $_SESSION['user_id'] = $conn->lastInsertId();
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = 'customer';
    }

    error_log("Google login successful: " . $email);
    header("Location: dashboard.php");
    exit();

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("Database Error: " . $e->getMessage());
}
?>
