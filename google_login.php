<?php
require_once 'base_url.php'; // Pakai file helper tadi
session_start();

// ============ KONFIGURASI GOOGLE ============
$client_id     = '889173134800-v3a3gvg12u85oops6gbkvjqf5kihpb93.apps.googleusercontent.com';
// Pastikan Redirect URI ini SAMA PERSIS dengan yang didaftarkan di Google Cloud Console
$redirect_uri  = BASE_URL . '/google_callback.php';

// ============================================

$scope = 'email profile';
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id'     => $client_id,
    'redirect_uri'  => $redirect_uri,
    'response_type' => 'code',
    'scope'         => $scope,
    'access_type'   => 'offline',
    'state'         => $state,
    'prompt'        => 'consent'
]);

header('Location: ' . $auth_url);
exit();
?>