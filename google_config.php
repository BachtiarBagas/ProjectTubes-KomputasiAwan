<?php
// Google OAuth Configuration with Auto Environment Detection

function getBaseUrl() {
    // Untuk Azure, SELALU gunakan HTTPS tanpa port
    if (strpos($_SERVER['HTTP_HOST'], 'azurewebsites.net') !== false) {
        return 'https://' . explode(':', $_SERVER['HTTP_HOST'])[0]; // Remove port if exists
    }
    
    // Untuk local
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . $host;
}

// Deteksi environment
$http_host = $_SERVER['HTTP_HOST'];
$is_azure = (strpos($http_host, 'azurewebsites.net') !== false);
$is_local = (strpos($http_host, 'localhost') !== false || strpos($http_host, '127.0.0.1') !== false);

// Google OAuth Credentials
$google_client_id = '889173134800-v3a3gvg12u85oops6gbkvjqf5kihpb93.apps.googleusercontent.com';
$google_client_secret = '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';

// Set redirect URI based on environment
if ($is_azure) {
    // Production - Azure (SELALU HTTPS, tanpa port)
    $google_redirect_uri = 'https://foodsite.azurewebsites.net/google_callback.php';
} elseif ($is_local) {
    // Local development
    $google_redirect_uri = 'http://localhost:8000/google_callback.php';
} else {
    // Fallback - auto detect
    $google_redirect_uri = getBaseUrl() . '/google_callback.php';
}

// Log for debugging
error_log("Google Config - HTTP_HOST: " . $http_host);
error_log("Google Config - Is Azure: " . ($is_azure ? 'YES' : 'NO'));
error_log("Google Config - Redirect URI: " . $google_redirect_uri);

return [
    'client_id' => $google_client_id,
    'client_secret' => $google_client_secret,
    'redirect_uri' => $google_redirect_uri
];
?>
