<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php"); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_toko   = $_POST['nama_toko'];
    $telepon     = $_POST['telepon'];
    $alamat      = $_POST['alamat'];
    $pesan_struk = $_POST['pesan_struk'];
    $rekening    = $_POST['rekening'];
    $ewallet     = $_POST['ewallet']; // Data baru

    try {
        // 1. AUTO MIGRATE: Tambah kolom ewallet jika belum ada
        $pdo->exec("ALTER TABLE pengaturan ADD COLUMN IF NOT EXISTS rekening TEXT");
        $pdo->exec("ALTER TABLE pengaturan ADD COLUMN IF NOT EXISTS ewallet TEXT"); // Kolom baru
        $pdo->exec("ALTER TABLE pengaturan ADD COLUMN IF NOT EXISTS qris_img VARCHAR(255)");

        // 2. PROSES UPLOAD QRIS
        $qris_name = $_POST['old_qris'] ?? '';
        if (isset($_FILES['qris_img']) && $_FILES['qris_img']['error'] === 0) {
            $ext = pathinfo($_FILES['qris_img']['name'], PATHINFO_EXTENSION);
            $qris_name = "qris_" . time() . "." . $ext;
            if (!is_dir('uploads')) mkdir('uploads', 0777, true);
            move_uploaded_file($_FILES['qris_img']['tmp_name'], "uploads/" . $qris_name);
            
            if (!empty($_POST['old_qris']) && file_exists("uploads/" . $_POST['old_qris'])) {
                unlink("uploads/" . $_POST['old_qris']);
            }
        }

        // 3. SIMPAN DATA
        $check = $pdo->query("SELECT id FROM pengaturan LIMIT 1")->fetch();

        if ($check) {
            $sql = "UPDATE pengaturan SET nama_toko=?, alamat=?, telepon=?, pesan_struk=?, rekening=?, ewallet=?, qris_img=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nama_toko, $alamat, $telepon, $pesan_struk, $rekening, $ewallet, $qris_name, $check['id']]);
        } else {
            $sql = "INSERT INTO pengaturan (nama_toko, alamat, telepon, pesan_struk, rekening, ewallet, qris_img) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nama_toko, $alamat, $telepon, $pesan_struk, $rekening, $ewallet, $qris_name]);
        }

        header("Location: profil_bisnis.php?status=success");
        exit;

    } catch (PDOException $e) {
        die("Error Sistem: " . $e->getMessage());
    }
}
