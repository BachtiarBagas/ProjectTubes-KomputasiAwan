<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'google_config.php';

error_log("Google login initiated");

// Load Google config
$google_config = include 'google_config.php';

// Generate state untuk keamanan (CSRF protection)
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

error_log("Generated state: " . $state);

// Buat Google Auth URL
$auth_params = [
    'client_id'     => $google_config['client_id'],
    'redirect_uri'  => $google_config['redirect_uri'],
    'response_type' => 'code',
    'scope'         => $google_config['scope'],
    'access_type'   => 'online',
    'state'         => $state,
    'prompt'        => 'select_account'
];

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($auth_params);

error_log("Redirecting to Google: " . $auth_url);

// Redirect ke Google
header('Location: ' . $auth_url);
exit();
?>
