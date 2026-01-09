<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>";

echo "<h2>Database Connection Test</h2>";

// Test 1: Connection
echo "<h3>1. Testing Database Connection...</h3>";
try {
    $stmt = $conn->query("SELECT 1");
    echo "<p class='success'>✓ Database connected successfully!</p>";
} catch(Exception $e) {
    echo "<p class='error'>✗ Connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Users Table
echo "<h3>2. Testing Users Table...</h3>";
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p class='success'>✓ Users table exists!</p>";
    echo "<p class='info'>Total users: " . $result['count'] . "</p>";
} catch(Exception $e) {
    echo "<p class='error'>✗ Users table error: " . $e->getMessage() . "</p>";
}

// Test 3: List Users
echo "<h3>3. List of Users:</h3>";
try {
    $stmt = $conn->query("SELECT id, username, full_name, role FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Role</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>No users found in database.</p>";
    }
} catch(Exception $e) {
    echo "<p class='error'>✗ Error listing users: " . $e->getMessage() . "</p>";
}

// Test 4: Session Info
echo "<h3>4. Session Information:</h3>";
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<p class='success'>✓ User is logged in</p>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
} else {
    echo "<p class='info'>No active session</p>";
}

// Test 5: Environment Info
echo "<h3>5. Environment Information:</h3>";
$is_azure = getenv('WEBSITE_SITE_NAME') !== false;
echo "<p class='info'>Environment: " . ($is_azure ? "Azure" : "Local") . "</p>";
if ($is_azure) {
    echo "<p class='info'>Site Name: " . getenv('WEBSITE_SITE_NAME') . "</p>";
}

echo "</body></html>";
?>
