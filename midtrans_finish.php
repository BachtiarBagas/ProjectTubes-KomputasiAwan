<?php
require_once 'config.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header('Location: dashboard.php');
    exit();
}

$order_id = $_GET['order_id'];
$status = isset($_GET['status']) ? $_GET['status'] : 'pending';

// Update status order
if($status == 'success') {
    $stmt = $conn->prepare("UPDATE orders SET status = 'Completed' WHERE id = ?");
    $stmt->execute([$order_id]);
}

// Clear cart
unset($_SESSION['cart']);
unset($_SESSION['current_order_id']);

header('Location: success.php?order_id=' . $order_id);
exit();
?>