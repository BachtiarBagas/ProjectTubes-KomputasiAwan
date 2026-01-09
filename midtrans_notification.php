
<?php
require_once 'config.php';
require_once 'midtrans_config.php';

$notif = new \Midtrans\Notification();

$transaction = $notif->transaction_status;
$order_id = $notif->order_id;
$fraud = $notif->fraud_status;

// Extract order ID asli (hapus prefix ORDER- dan timestamp)
preg_match('/ORDER-(\d+)-/', $order_id, $matches);
$real_order_id = $matches[1];

error_log("Order ID $order_id: transaction status = $transaction, fraud status = $fraud");

// Update status di database
if ($transaction == 'capture') {
    if ($fraud == 'accept') {
        $stmt = $conn->prepare("UPDATE orders SET status = 'Completed' WHERE id = ?");
        $stmt->execute([$real_order_id]);
    }
} else if ($transaction == 'settlement') {
    $stmt = $conn->prepare("UPDATE orders SET status = 'Completed' WHERE id = ?");
    $stmt->execute([$real_order_id]);
} else if ($transaction == 'pending') {
    $stmt = $conn->prepare("UPDATE orders SET status = 'Pending' WHERE id = ?");
    $stmt->execute([$real_order_id]);
} else if ($transaction == 'deny' || $transaction == 'expire' || $transaction == 'cancel') {
    $stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?");
    $stmt->execute([$real_order_id]);
}

echo json_encode(['status' => 'success']);
?>
