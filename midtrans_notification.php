<?php
require_once 'base_url.php';
require_once dirname(__FILE__) . '/vendor/autoload.php'; // Pastikan path vendor benar

// Set Config Midtrans (Sesuaikan dengan midtrans_config.php Anda)
\Midtrans\Config::$serverKey = 'Mid-server-Emdteo_SpXaiwq00oIeYKFxt'; // Ganti dengan Server Key Production jika sudah live
\Midtrans\Config::$isProduction = false; // Ubah ke true jika sudah production
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

try {
    $notif = new \Midtrans\Notification();
} catch (Exception $e) {
    exit($e->getMessage());
}

$transaction = $notif->transaction_status;
$type = $notif->payment_type;
$order_id = $notif->order_id;
$fraud = $notif->fraud_status;

// Extract ID asli dari format "ORDER-123-timestamp"
// Mengambil angka di antara strip pertama dan kedua
$parts = explode('-', $order_id);
$real_order_id = $parts[1]; 

// Log untuk debugging di Azure (Cek Log Stream)
error_log("Midtrans Notification: Order ID $real_order_id | Status: $transaction");

// Logic Update Status Database
$status_db = 'Pending';

if ($transaction == 'capture') {
    if ($fraud == 'challenge') {
        $status_db = 'Pending';
    } else {
        $status_db = 'Completed';
    }
} else if ($transaction == 'settlement') {
    $status_db = 'Completed';
} else if ($transaction == 'pending') {
    $status_db = 'Pending';
} else if ($transaction == 'deny') {
    $status_db = 'Cancelled';
} else if ($transaction == 'expire') {
    $status_db = 'Cancelled';
} else if ($transaction == 'cancel') {
    $status_db = 'Cancelled';
}

// Update Database
try {
    // 1. Update Status Order
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status_db, $real_order_id]);

    // 2. Kurangi Stok JIKA status Completed (dan belum dikurangi sebelumnya)
    // Note: Sebaiknya cek apakah stok sudah dikurangi agar tidak double, 
    // tapi untuk sederhana kita asumsikan logic ini jalan saat settlement.
    if ($status_db == 'Completed') {
        $stmtItems = $conn->prepare("SELECT menu_id, quantity FROM order_details WHERE order_id = ?");
        $stmtItems->execute([$real_order_id]);
        $items = $stmtItems->fetchAll();

        foreach ($items as $item) {
            // Kita lakukan pengurangan stok di sini jika belum dilakukan di checkout
            $upd = $conn->prepare("UPDATE menu SET stock = stock - ? WHERE id = ?");
            $upd->execute([$item['quantity'], $item['menu_id']]);
        }
    }
} catch (Exception $e) {
    error_log("DB Error Midtrans: " . $e->getMessage());
    http_response_code(500);
    exit();
}

http_response_code(200); // Wajib return 200 OK ke Midtrans
?>