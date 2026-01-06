<?php
session_start();

// Debug log
error_log("User logged out: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'unknown'));

// Destroy semua session
session_unset();
session_destroy();

// Redirect ke halaman login
header('Location: index.php');
exit();
?>
