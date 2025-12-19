<?php 
require_once 'config.php';
require_once 'midtrans_config.php'; // Pastikan file ini ada

if(!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header('Location: dashboard.php');
    exit();
}

// Hitung total dan siapkan item details untuk Midtrans
$total = 0;
$item_details = array();

foreach($_SESSION['cart'] as $menu_id => $qty) {
    $stmt = $conn->prepare("SELECT * FROM menu WHERE id = ?");
    $stmt->execute([$menu_id]);
    $item = $stmt->fetch();
    
    $subtotal = $item['price'] * $qty;
    $total += $subtotal;
    
    // Format item untuk Midtrans
    $item_details[] = array(
        'id' => $item['id'],
        'price' => (int)$item['price'],
        'quantity' => $qty,
        'name' => $item['name']
    );
}

// Jika tombol pembayaran diklik
if(isset($_POST['process_payment'])) {
    $payment_method = $_POST['payment_method'];
    $order_type = $_SESSION['order_type'];
    
    // Insert order ke database dulu
    $stmt = $conn->prepare("INSERT INTO orders (user_id, order_type, payment_method, total_amount, status) VALUES (?, ?, ?, ?, 'Pending')");
    $stmt->execute([$_SESSION['user_id'], $order_type, $payment_method, $total]);
    $order_id = $conn->lastInsertId();
    
    // Insert order details
    foreach($_SESSION['cart'] as $menu_id => $qty) {
        $stmt = $conn->prepare("SELECT price FROM menu WHERE id = ?");
        $stmt->execute([$menu_id]);
        $item = $stmt->fetch();
        
        $stmt = $conn->prepare("INSERT INTO order_details (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $menu_id, $qty, $item['price']]);
    }
    
    // Jika pilih Midtrans (Online Payment)
    if($payment_method == 'Midtrans') {
        // Parameter transaksi untuk Midtrans Snap
        $transaction_details = array(
            'order_id' => 'ORDER-' . $order_id . '-' . time(),
            'gross_amount' => (int)$total
        );
        
        $customer_details = array(
            'first_name' => $_SESSION['full_name'],
            'email' => 'customer@example.com',
            'phone' => '08123456789'
        );
        
        $transaction = array(
            'transaction_details' => $transaction_details,
            'customer_details' => $customer_details,
            'item_details' => $item_details
        );
        
        try {
            // Dapatkan Snap Token
            $snapToken = \Midtrans\Snap::getSnapToken($transaction);
            
            // Simpan snap token ke session untuk digunakan di frontend
            $_SESSION['snap_token'] = $snapToken;
            $_SESSION['current_order_id'] = $order_id;
            
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            exit();
        }
    } else {
        // Jika Cash, kurangi stok langsung lalu redirect
        foreach($_SESSION['cart'] as $menu_id => $qty) {
            $stmt = $conn->prepare("UPDATE menu SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$qty, $menu_id]);
        }
        
        unset($_SESSION['cart']);
        header('Location: success.php?order_id=' . $order_id);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Midtrans Snap JS -->
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="GANTI_DENGAN_CLIENT_KEY_ANDA"></script>
    
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 40px 0; }
        .payment-card { background: white; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .payment-option {
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        .payment-option:hover { transform: scale(1.05); border-color: #667eea; }
        .payment-option.active { border-color: #667eea; background: #f0f3ff; }
        .payment-icon { font-size: 60px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="payment-card p-5">
                    <h2 class="text-center mb-4"><i class="fas fa-credit-card"></i> Pilih Metode Pembayaran</h2>
                    
                    <!-- Detail Pesanan -->
                    <div class="card mb-4 bg-light">
                        <div class="card-body">
                            <h5 class="border-bottom pb-2">Detail Pesanan</h5>
                            <?php foreach($_SESSION['cart'] as $menu_id => $qty): 
                                $stmt = $conn->prepare("SELECT * FROM menu WHERE id = ?");
                                $stmt->execute([$menu_id]);
                                $item = $stmt->fetch();
                            ?>
                            <div class="d-flex justify-content-between py-1">
                                <span><?php echo $item['name']; ?> (<?php echo $qty; ?>x)</span>
                                <span>Rp <?php echo number_format($item['price'] * $qty, 0, ',', '.'); ?></span>
                            </div>
                            <?php endforeach; ?>
                            <hr>
                            <h5 class="d-flex justify-content-between mb-0">
                                <span>Total:</span>
                                <span class="text-success">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                            </h5>
                        </div>
                    </div>
                    
                    <!-- Form Pembayaran -->
                    <form method="POST" id="payment-form">
                        <div class="row">
                            <!-- Cash -->
                            <div class="col-md-6">
                                <div class="payment-option" onclick="selectPayment('Cash')">
                                    <input type="radio" name="payment_method" value="Cash" id="cash" required hidden>
                                    <div class="payment-icon text-success"><i class="fas fa-money-bill-wave"></i></div>
                                    <h4>Cash</h4>
                                    <p class="text-muted mb-0">Bayar tunai di tempat</p>
                                </div>
                            </div>
                            
                            <!-- Midtrans (Online) -->
                            <div class="col-md-6">
                                <div class="payment-option" onclick="selectPayment('Midtrans')">
                                    <input type="radio" name="payment_method" value="Midtrans" id="midtrans" required hidden>
                                    <div class="payment-icon text-primary"><i class="fas fa-wallet"></i></div>
                                    <h4>Online Payment</h4>
                                    <p class="text-muted mb-0">Kartu, E-wallet, Transfer</p>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="process_payment" class="btn btn-success btn-lg w-100 mt-4">
                            <i class="fas fa-check-circle"></i> Proses Pembayaran
                        </button>
                        <a href="menu.php?type=<?php echo $_SESSION['order_type']; ?>" class="btn btn-secondary btn-lg w-100 mt-2">
                            <i class="fas fa-arrow-left"></i> Kembali ke Menu
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Fungsi pilih payment method
        function selectPayment(method) {
            document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('active'));
            event.currentTarget.classList.add('active');
            document.getElementById(method.toLowerCase()).checked = true;
        }
        
        <?php if(isset($_SESSION['snap_token'])): ?>
        // Trigger Midtrans Snap popup
        snap.pay('<?php echo $_SESSION['snap_token']; ?>', {
            onSuccess: function(result) {
                window.location.href = 'midtrans_finish.php?order_id=<?php echo $_SESSION['current_order_id']; ?>&status=success';
            },
            onPending: function(result) {
                window.location.href = 'midtrans_finish.php?order_id=<?php echo $_SESSION['current_order_id']; ?>&status=pending';
            },
            onError: function(result) {
                alert('Pembayaran gagal! Silakan coba lagi.');
                window.location.href = 'payment.php';
            },
            onClose: function() {
                alert('Anda menutup halaman pembayaran sebelum menyelesaikan transaksi.');
                window.location.href = 'payment.php';
            }
        });
        <?php 
            unset($_SESSION['snap_token']); 
        ?>
        <?php endif; ?>
    </script>
</body>
</html>
