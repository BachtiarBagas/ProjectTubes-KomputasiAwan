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
    
    // KURANGI STOK OTOMATIS untuk pembayaran Midtrans
    $stmt = $conn->prepare("SELECT menu_id, quantity FROM order_details WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();
    
    foreach($items as $item) {
        $stmt = $conn->prepare("UPDATE menu SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['menu_id']]);
    }
}

// Clear cart
unset($_SESSION['cart']);
unset($_SESSION['current_order_id']);

header('Location: success.php?order_id=' . $order_id);
exit();
?>
