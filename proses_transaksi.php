<?php
include 'config.php';
header('Content-Type: application/json');

// 1. Cek Sesi Login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi berakhir, silakan login ulang.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Keranjang belanja kosong!']);
    exit;
}

try {
    // 2. Sinkronisasi Database (Otomatis Tambah Kolom jika belum ada)
    // Memastikan tabel penjualan siap menampung metode bayar dan jumlah uang
    $pdo->exec("ALTER TABLE penjualan ADD COLUMN IF NOT EXISTS metode_bayar VARCHAR(50) AFTER total_harga");
    $pdo->exec("ALTER TABLE penjualan ADD COLUMN IF NOT EXISTS bayar INT DEFAULT 0 AFTER metode_bayar");

    // 3. Validasi Stok Sebelum Mulai Transaksi
    foreach ($data['items'] as $item) {
        $stmt_cek = $pdo->prepare("SELECT stok, nama_produk FROM produk WHERE id = ?");
        $stmt_cek->execute([$item['id']]);
        $produk_db = $stmt_cek->fetch();
        
        if (!$produk_db || $produk_db['stok'] < $item['qty']) {
            echo json_encode([
                'success' => false, 
                'message' => 'Stok produk [' . ($produk_db['nama_produk'] ?? 'Unknown') . '] tidak mencukupi!'
            ]);
            exit;
        }
    }

    // 4. Mulai Transaksi (Atomic Transaction)
    $pdo->beginTransaction();

    // 5. Masukkan Data ke Tabel Penjualan (Mencatat ID Kasir dari session)
    $stmt_penjualan = $pdo->prepare("INSERT INTO penjualan (user_id, total_harga, metode_bayar, bayar, tanggal) VALUES (?, ?, ?, ?, NOW())");
    $stmt_penjualan->execute([
        $_SESSION['user_id'], 
        $data['total'], 
        $data['metode'], 
        $data['bayar']
    ]);
    
    $penjualan_id = $pdo->lastInsertId();

    // 6. Masukkan Detail Barang & Potong Stok
    $stmt_detail = $pdo->prepare("INSERT INTO detail_penjualan (penjualan_id, produk_id, qty, subtotal) VALUES (?, ?, ?, ?)");
    $stmt_update_stok = $pdo->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");

    foreach ($data['items'] as $item) {
        $subtotal = $item['qty'] * $item['harga'];
        
        // Simpan baris per item
        $stmt_detail->execute([
            $penjualan_id, 
            $item['id'], 
            $item['qty'], 
            $subtotal
        ]);

        // Eksekusi potong stok
        $stmt_update_stok->execute([
            $item['qty'], 
            $item['id']
        ]);
    }

    // 7. Jika semua lancar, simpan permanen
    $pdo->commit();

    // Respon untuk diterima oleh transaksi.php (JS)
    echo json_encode([
        'success' => true, 
        'penjualan_id' => $penjualan_id,
        'message' => 'Transaksi berhasil diproses!'
    ]);

} catch (Exception $e) {
    // 8. Jika ada satu saja yang gagal, batalkan semua agar data tidak rusak
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false, 
        'message' => 'Kesalahan Sistem: ' . $e->getMessage()
    ]);
}
