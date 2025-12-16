<?php
require_once dirname(__FILE__) . '/vendor/autoload.php';

// Set Midtrans Configuration
\Midtrans\Config::$serverKey = 'Mid-server-Emdteo_SpXaiwq00oIeYKFxt';
\Midtrans\Config::$isProduction = false; // Set false untuk sandbox/testing
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;
?>