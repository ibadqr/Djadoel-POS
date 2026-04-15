<?php
// Menghapus output lain agar tidak merusak format JSON
ob_start();
include 'config.php';
ob_clean();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama_kategori'])) {
    $nama = trim($_POST['nama_kategori']);
    
    if (!empty($nama)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
            $stmt->execute([$nama]);
            $id = $pdo->lastInsertId();

            echo json_encode([
                'success' => true, 
                'id' => $id, 
                'nama' => $nama
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Nama kategori kosong']);
    }
    exit;
}
