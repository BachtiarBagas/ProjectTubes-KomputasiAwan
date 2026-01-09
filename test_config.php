<?php
require_once 'google_config.php';
$config = app_config();

echo "<h2>App Configuration</h2>";
echo "<p><strong>Environment:</strong> " . $config['environment'] . "</p>";
echo "<p><strong>Base URL:</strong> " . $config['base_url'] . "</p>";
echo "<p><strong>Google Redirect URI:</strong> " . $config['google']['redirect_uri'] . "</p>";
echo "<pre>";
print_r($config);
echo "</pre>";
?>
