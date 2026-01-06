<?php
if (isset($_POST['register'])) {
    $username  = trim($_POST['username']);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);

    // 1. Cek username / email sudah ada
    $check = $conn->prepare(
        "SELECT id FROM users WHERE username = :username OR email = :email"
    );
    $check->execute([
        'username' => $username,
        'email'    => $email
    ]);

    if ($check->fetch()) {
        echo '<div class="alert alert-danger">
                Username atau Email sudah terdaftar
              </div>';
    } else {
        // 2. Insert user baru
        $stmt = $conn->prepare("
            INSERT INTO users (username, password, full_name, email, role)
            VALUES (:username, :password, :full_name, :email, 'customer')
        ");

        try {
            $stmt->execute([
                'username'  => $username,
                'password'  => $password,
                'full_name' => $full_name,
                'email'     => $email
            ]);

            echo '<div class="alert alert-success">
                    Registrasi berhasil! <a href="index.php">Login di sini</a>
                  </div>';

        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">
                    Registrasi gagal: '.$e->getMessage().'
                  </div>';
        }
    }
}
?>
