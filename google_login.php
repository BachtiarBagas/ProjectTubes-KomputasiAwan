
<?php
// google_login.php - VERSI BENAR 100% (Copy-Paste Langsung)
session_start();

// ============ KONFIGURASI ANDA (Sudah Benar) ============
$client_id = '889173134800-v3a3gvg12u85oops6gbkvjqf5kihpb93.apps.googleusercontent.com';
$client_secret = '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';
$redirect_uri = 'https://foodsite.azurewebsites.net/google_callback.php';

// ========================================================

// Generate state untuk keamanan
$scope = 'email profile';
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

// Buat Google Auth URL
$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id'     => $client_id,
    'redirect_uri'  => $redirect_uri,
    'response_type' => 'code',
    'scope'         => $scope,
    'access_type'   => 'offline',
    'state'         => $state,
    'prompt'        => 'consent'
]);

// Redirect ke Google
header('Location: ' . $auth_url);
exit();
?>
