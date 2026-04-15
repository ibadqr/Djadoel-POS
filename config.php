<?php
session_start();
$host = 'localhost:3306';
$db   = 'djadoel_pos';
$user = 'root';
$pass = 'root'; // Sesuaikan dengan password database-mu

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// FUNGSI WAJIB UNTUK HALAMAN PRODUK & LAPORAN
function rupiah($angka) {
    // Memastikan jika $angka null atau bukan angka, otomatis jadi 0
    $nilai = (float)($angka ?? 0); 
    return "Rp " . number_format($nilai, 0, ',', '.');
}
?>
