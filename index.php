<?php
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

session_start();
session_regenerate_id(true);
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Food Ordering System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
    </style>
</head>

<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="login-card">

                <div class="login-header">
                    <i class="fas fa-utensils fa-3x mb-3"></i>
                    <h2>Food Ordering System</h2>
                    <p>Silakan login untuk melanjutkan</p>
                </div>

                <div class="p-5">

                    <?php
                    if (isset($_POST['login'])) {
                        $username = trim($_POST['username']);
                        $password = $_POST['password'];

                        $stmt = $conn->prepare(
                            "SELECT * FROM users WHERE username = :username LIMIT 1"
                        );
                        $stmt->execute(['username' => $username]);
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($user === false) {
                            echo '<div class="alert alert-danger">Username tidak ditemukan</div>';
                        } elseif (!password_verify($password, $user['password'])) {
                            echo '<div class="alert alert-danger">Password salah</div>';
                        } else {
                            $_SESSION['user_id']   = $user['id'];
                            $_SESSION['username']  = $user['username'];
                            $_SESSION['full_name'] = $user['full_name'];
                            $_SESSION['role']      = $user['role'] ?? 'customer';

                            if ($_SESSION['role'] === 'admin') {
                                header('Location: admin_dashboard.php');
                            } else {
                                header('Location: dashboard.php');
                            }
                            exit;
                        }
                    }
                    ?>

                    <h3 class="text-center mb-4">Login</h3>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button type="submit" name="login" class="btn btn-primary w-100 mb-3">
                            Login
                        </button>
                    </form>

                    <div class="position-relative mb-3">
                        <hr>
                        <div class="position-absolute top-50 start-50 translate-middle bg-white px-3">
                            <small class="text-muted">atau</small>
                        </div>
                    </div>

                    <a href="google_login.php"
                       class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center">
                        <i class="fab fa-google me-2"></i>
                        Login dengan Google
                    </a>

                    <p class="text-center mt-4">
                        Belum punya akun? <a href="register.php">Daftar</a>
                    </p>

                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
