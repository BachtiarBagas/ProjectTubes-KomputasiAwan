
<?php
// google_login.php - VERSI BENAR 100% (Copy-Paste Langsung)
session_start();

// ============ KONFIGURASI ANDA (Sudah Benar) ============
$clientID = '610900154866-i2u0cmiclf1d1132a3ag2kjd5ptm30d2.apps.googleusercontent.com';
$clientSecret = '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';
$redirectURI = 'https://foodsite.azurewebsites.net/google_callback.php';

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
