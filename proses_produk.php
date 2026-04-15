<?php
include 'config.php';

/**
 * PROTEKSI AKSES
 * Memastikan hanya Owner yang bisa mengolah data produk
 */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php");
    exit;
}

// --- 1. PROSES HAPUS PRODUK ---
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    try {
        // Cari info gambar dulu agar bisa dihapus dari folder uploads
        $stmt = $pdo->prepare("SELECT gambar FROM produk WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row) {
            // Hapus file fisik jika bukan gambar default
            if ($row['gambar'] != 'default.png' && file_exists("uploads/" . $row['gambar'])) {
                unlink("uploads/" . $row['gambar']);
            }

            // Hapus data dari database
            $delete = $pdo->prepare("DELETE FROM produk WHERE id = ?");
            $delete->execute([$id]);
        }
        
        header("Location: stok_produk.php?status=deleted");
    } catch (PDOException $e) {
        die("Error Hapus: " . $e->getMessage());
    }
    exit;
}

// --- 2. PROSES TAMBAH & EDIT PRODUK ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action      = $_POST['action']; // 'tambah' atau 'edit'
    $id          = $_POST['id'] ?? null;
    $nama_produk = $_POST['nama_produk'];
    $kategori_id = $_POST['kategori_id'];
    $stok        = $_POST['stok'];
    $harga_beli  = $_POST['harga_beli'];
    $harga_jual  = $_POST['harga_jual'];
    
    // Inisialisasi variabel gambar
    $nama_file = $_FILES['gambar']['name'];
    $tmp_name  = $_FILES['gambar']['tmp_name'];
    $gambar_final = ""; 

    try {
        // Jika ada file yang diunggah
        if ($nama_file != "") {
            $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
            $allowed  = ['jpg', 'jpeg', 'png', 'webp'];

            // Validasi format
            if (!in_array($ekstensi, $allowed)) {
                die("Format gambar tidak didukung! Gunakan JPG/PNG/WebP.");
            }

            // Beri nama unik agar tidak bentrok (produk_171265xxxx.jpg)
            $gambar_final = "prod_" . time() . "_" . rand(100, 999) . "." . $ekstensi;
            
            // Pastikan folder uploads ada
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }

            move_uploaded_file($tmp_name, "uploads/" . $gambar_final);
            
            // Jika proses EDIT dan ganti gambar, hapus gambar yang lama
            if ($action == 'edit' && $id) {
                $stmt_img = $pdo->prepare("SELECT gambar FROM produk WHERE id = ?");
                $stmt_img->execute([$id]);
                $old_img = $stmt_img->fetchColumn();
                
                if ($old_img && $old_img != 'default.png' && file_exists("uploads/" . $old_img)) {
                    unlink("uploads/" . $old_img);
                }
            }
        }

        if ($action == 'tambah') {
            // Gunakan default.png jika tidak upload gambar saat tambah baru
            $final_img_to_db = ($gambar_final == "") ? "default.png" : $gambar_final;
            
            $sql = "INSERT INTO produk (nama_produk, kategori_id, stok, harga_beli, harga_jual, gambar) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$nama_produk, $kategori_id, $stok, $harga_beli, $harga_jual, $final_img_to_db]);

        } elseif ($action == 'edit') {
            if ($gambar_final != "") {
                // Update beserta gambar baru
                $sql = "UPDATE produk SET nama_produk=?, kategori_id=?, stok=?, harga_beli=?, harga_jual=?, gambar=? WHERE id=?";
                $pdo->prepare($sql)->execute([$nama_produk, $kategori_id, $stok, $harga_beli, $harga_jual, $gambar_final, $id]);
            } else {
                // Update tanpa mengganti gambar lama
                $sql = "UPDATE produk SET nama_produk=?, kategori_id=?, stok=?, harga_beli=?, harga_jual=? WHERE id=?";
                $pdo->prepare($sql)->execute([$nama_produk, $kategori_id, $stok, $harga_beli, $harga_jual, $id]);
            }
        }

        header("Location: stok_produk.php?status=success");
    } catch (PDOException $e) {
        die("Error Database: " . $e->getMessage());
    }
    exit;
}
