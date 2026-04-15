<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    exit("Akses ditolak");
}

// PROSES HAPUS
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'owner'")->execute([$id]);
    header("Location: kelola_kasir.php");
    exit;
}

// PROSES SIMPAN (TAMBAH / EDIT)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $id = $_POST['id'];
    $nama = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($action == 'tambah') {
        // Enkripsi Password untuk keamanan
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (nama_lengkap, username, password, role) VALUES (?, ?, ?, 'kasir')";
        $pdo->prepare($sql)->execute([$nama, $username, $hash]);
    } else {
        // EDIT
        if (!empty($password)) {
            // Jika ganti password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET nama_lengkap=?, username=?, password=? WHERE id=?";
            $pdo->prepare($sql)->execute([$nama, $username, $hash, $id]);
        } else {
            // Jika tidak ganti password
            $sql = "UPDATE users SET nama_lengkap=?, username=? WHERE id=?";
            $pdo->prepare($sql)->execute([$nama, $username, $id]);
        }
    }
    header("Location: kelola_kasir.php");
    exit;
}
