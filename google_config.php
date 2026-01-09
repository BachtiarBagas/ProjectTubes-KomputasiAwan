<?php
/**
 * Google OAuth Configuration - Auto Detect Environment
 * Support Local Development + Azure Production
 */

// Prevent direct access
if (!defined('GOOGLE_CONFIG_LOADED')) {
    define('GOOGLE_CONFIG_LOADED', true);
}

/**
 * Get base URL berdasarkan environment
 */
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $port = ($_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443) ? '' : ':' . $_SERVER['SERVER_PORT'];
    return $protocol . $host . $port;
}

/**
 * Deteksi environment
 */
function detectEnvironment() {
    // Azure App Service
    if (getenv('WEBSITE_SITE_NAME') !== false) {
        return 'azure';
    }
    
    // Local development
    $host = strtolower($_SERVER['HTTP_HOST']);
    if (strpos($host, 'localhost') !== false || 
        strpos($host, '127.0.0.1') !== false ||
        strpos($host, '0.0.0.0') !== false) {
        return 'local';
    }
    
    // Production lainnya
    return 'production';
}

// Get environment
$env = detectEnvironment();
error_log("Google Config - Detected environment: " . $env);

// ========== GOOGLE CREDENTIALS ==========
$google_config = [
    'client_id' => '889173134800-v3a3gvg12u85oops6gbkvjqf5kihpb93.apps.googleusercontent.com',
    'client_secret' => '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',
    'scope' => 'email profile https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email openid'
];

// ========== REDIRECT URI ==========
switch ($env) {
    case 'local':
        $google_config['redirect_uri'] = 'http://localhost:8000/google_callback.php';
        break;
        
    case 'azure':
        $app_url = getenv('APP_URL') ?: getBaseUrl();
        $google_config['redirect_uri'] = rtrim($app_url, '/') . '/google_callback.php';
        break;
        
    case 'production':
        $app_url = getenv('APP_URL') ?: getBaseUrl();
        $google_config['redirect_uri'] = rtrim($app_url, '/') . '/google_callback.php';
        break;
}

error_log("Google Config - Redirect URI: " . $google_config['redirect_uri']);

// ========== SSL CERTIFICATE PATH ==========
$google_config['ca_cert_path'] = __DIR__ . '/cacert.pem';

// ========== RETURN CONFIG ==========
return $google_config;
?>
