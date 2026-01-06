<?php
require_once 'config.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil riwayat pesanan user dari database
try {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute(['user_id' => $user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Gagal mengambil data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - FoodSite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background-color: #e67e22; }
        .card-order { border-radius: 15px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: 0.3s; }
        .card-order:hover { transform: translateY(-5px); }
        .status-badge { border-radius: 20px; padding: 5px 15px; font-size: 0.8rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php fw-bold">FoodSite</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="dashboard.php">Menu</a>
            <a class="nav-link active" href="order_history.php">Riwayat</a>
            <a class="nav-link text-warning" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <h3 class="mb-4">Riwayat Pesanan Saya</h3>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="text-center py-5">
            <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
            <p class="text-muted">Anda belum pernah melakukan pemesanan.</p>
            <a href="dashboard.php" class="btn btn-primary">Pesan Sekarang</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($orders as $order): ?>
                <div class="col-md-6 mb-3">
                    <div class="card card-order p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 text-muted">ID Pesanan: #<?php echo $order['id']; ?></h6>
                                <p class="small text-muted mb-2"><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></p>
                            </div>
                            <span class="badge status-badge <?php 
                                echo ($order['status'] == 'settlement') ? 'bg-success' : 
                                     (($order['status'] == 'pending') ? 'bg-warning' : 'bg-danger'); 
                            ?>">
                                <?php echo strtoupper($order['status']); ?>
                            </span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <span>Total Pembayaran:</span>
                            <span class="fw-bold text-success">Rp <?php echo number_filter($order['total_price']); ?></span>
                        </div>
                        <div class="mt-3">
                            <a href="payment.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
