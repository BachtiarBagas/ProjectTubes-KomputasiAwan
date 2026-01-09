<?php
session_start();

// HARDCODE untuk testing (TEMPORARY!)
$client_id = '889173134800-v3a3gvg12u85oops6gbkvjqf5kihpb93.apps.googleusercontent.com';
$client_secret = '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';

// Deteksi environment
if (strpos($_SERVER['HTTP_HOST'], 'azurewebsites.net') !== false) {
    // Azure - SELALU HTTPS
    $redirect_uri = 'https://foodsite.azurewebsites.net/google_callback.php';
} else {
    // Local
    $redirect_uri = 'http://localhost:8000/google_callback.php';
}

error_log("Redirect URI: " . $redirect_uri);

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$auth_params = [
    'client_id'     => $client_id,
    'redirect_uri'  => $redirect_uri,
    'response_type' => 'code',
    'scope'         => 'email profile',
    'access_type'   => 'online',
    'state'         => $state,
    'prompt'        => 'select_account'
];

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($auth_params);

header('Location: ' . $auth_url);
exit();
?>
