<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 1. Cari user di database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // 2. Validasi: User harus ada DAN password harus cocok
    if ($user && password_verify($password, $user['password'])) {
        // Login Berhasil
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['nama']    = $user['username'];
        
        $target = ($user['role'] === 'owner') ? 'dashboard_owner.php' : 'dashboard_kasir.php';
        header("Location: " . $target);
        exit;
    } else {
        // Login Gagal: Lempar balik ke index dengan parameter error
        header("Location: index.php?error=1");
        exit;
    }
}
?>
