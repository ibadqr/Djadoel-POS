<?php
include 'config.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); exit;
}

// AMBIL NAMA LENGKAP (Cek session dulu, kalau tidak ada ambil dari DB)
$user_id = $_SESSION['user_id'];
if (!isset($_SESSION['nama_lengkap'])) {
    $stmt_user = $pdo->prepare("SELECT nama_lengkap FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);
    $user_data = $stmt_user->fetch();
    $nama_kasir = $user_data['nama_lengkap'] ?? 'Kasir Djadoel';
    $_SESSION['nama_lengkap'] = $nama_kasir; // Simpan ke session biar gak query terus
} else {
    $nama_kasir = $_SESSION['nama_lengkap'];
}

$hari_ini = date('Y-m-d');

// 1. Hitung Omzet Kasir Ini (Hari Ini)
$stmt = $pdo->prepare("SELECT SUM(total_harga) as total FROM penjualan WHERE user_id = ? AND DATE(tanggal) = ?");
$stmt->execute([$_SESSION['user_id'], $hari_ini]);
$omzet_saya = $stmt->fetch()['total'] ?? 0;

// 2. Hitung Jumlah Transaksi Kasir Ini (Hari Ini)
$stmt2 = $pdo->prepare("SELECT COUNT(id) as jml FROM penjualan WHERE user_id = ? AND DATE(tanggal) = ?");
$stmt2->execute([$_SESSION['user_id'], $hari_ini]);
$transaksi_saya = $stmt2->fetch()['jml'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="uploads/ikon.png" type="image/x-icon/png">
    <title>Dashboard Kasir - Djadoel POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fcfaf7; }
        .wood-gradient { background: linear-gradient(135deg, #2d1b14 0%, #4a2c1d 100%); }
        .card-shadow { box-shadow: 0 20px 40px -15px rgba(45, 27, 20, 0.1); }
    </style>
</head>
<body class="pb-28">

    <header class="wood-gradient pt-12 pb-24 px-8 rounded-b-[4rem] shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-amber-500/10 rounded-full -mr-20 -mt-20 blur-3xl"></div>
        
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <p class="text-amber-200/60 text-xs font-bold uppercase tracking-[0.3em] mb-1">Semangat Kerjanya,</p>
                <h1 class="text-white text-3xl font-black tracking-tighter italic italic"><?= htmlspecialchars($nama_kasir) ?>!</h1>
            </div>
            <a href="logout.php" class="bg-white/10 p-3 rounded-2xl backdrop-blur-md border border-white/10 text-amber-200">
                <i data-lucide="log-out" class="w-6 h-6"></i>
            </a>
        </div>
    </header>

    <main class="px-6 -mt-12 relative z-20">
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white p-6 rounded-[2.5rem] card-shadow border border-amber-50">
                <div class="w-10 h-10 bg-amber-100 rounded-2xl flex items-center justify-center mb-4">
                    <i data-lucide="banknote" class="text-amber-600 w-5 h-5"></i>
                </div>
                <p class="text-[9px] font-black text-amber-900/40 uppercase tracking-widest mb-1">Omzet Anda</p>
                <h3 class="text-lg font-black text-amber-950 tracking-tighter">Rp <?= number_format($omzet_saya, 0, ',', '.') ?></h3>
            </div>
            
            <div class="bg-white p-6 rounded-[2.5rem] card-shadow border border-amber-50">
                <div class="w-10 h-10 bg-orange-100 rounded-2xl flex items-center justify-center mb-4">
                    <i data-lucide="shopping-bag" class="text-orange-600 w-5 h-5"></i>
                </div>
                <p class="text-[9px] font-black text-amber-900/40 uppercase tracking-widest mb-1">Transaksi</p>
                <h3 class="text-lg font-black text-amber-950 tracking-tighter"><?= $transaksi_saya ?> <span class="text-[10px] text-gray-400">Nota</span></h3>
            </div>
        </div>

        <div class="mt-8">
            <h3 class="font-black text-amber-950 text-[11px] uppercase tracking-[0.2em] mb-4 ml-2">Menu Utama</h3>
            
            <div class="space-y-4">
                <a href="transaksi.php" class="wood-gradient p-2 rounded-[2.5rem] flex items-center shadow-xl group active:scale-95 transition-all">
                    <div class="bg-amber-500 w-16 h-16 rounded-[2rem] flex items-center justify-center shadow-lg">
                        <i data-lucide="plus" class="text-white w-8 h-8 stroke-[3]"></i>
                    </div>
                    <div class="ml-5">
                        <h4 class="text-white font-black text-lg tracking-tight">Transaksi Baru</h4>
                        <p class="text-amber-200/50 text-[10px] font-bold uppercase tracking-wider">Mulai Jualan Sekarang</p>
                    </div>
                </a>

                <a href="riwayat_kasir.php" class="bg-white p-2 rounded-[2.5rem] flex items-center card-shadow border border-amber-50 active:scale-95 transition-all">
                    <div class="bg-amber-50 w-16 h-16 rounded-[2rem] flex items-center justify-center">
                        <i data-lucide="history" class="text-amber-700 w-8 h-8"></i>
                    </div>
                    <div class="ml-5">
                        <h4 class="text-amber-950 font-black text-lg tracking-tight">Riwayat Saya</h4>
                        <p class="text-amber-900/30 text-[10px] font-bold uppercase tracking-wider">Cek Nota Hari Ini</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="mt-10 bg-amber-100/50 p-6 rounded-[2.5rem] border border-amber-200/50 flex items-center gap-4">
            <div class="text-amber-600">
                <i data-lucide="info" class="w-6 h-6"></i>
            </div>
            <p class="text-[11px] text-amber-900/70 font-bold leading-relaxed">
                Pastikan printer thermal sudah terhubung sebelum mencetak struk belanja pelanggan.
            </p>
        </div>
    </main>

    <nav class="fixed bottom-8 left-6 right-6 wood-gradient rounded-[2.5rem] p-4 flex justify-around items-center shadow-2xl border border-white/10 z-50">
        <a href="dashboard_kasir.php" class="text-amber-400 bg-white/10 p-2 rounded-2xl border border-white/10">
            <i data-lucide="layout-dashboard"></i>
        </a>
        
        <a href="transaksi.php" class="bg-amber-500 p-4 rounded-full text-white -mt-16 shadow-2xl shadow-amber-500/50 border-[6px] border-[#fcfaf7] active:scale-90 transition-all">
            <i data-lucide="plus" class="w-8 h-8 stroke-[3]"></i>
        </a>

        <a href="riwayat_kasir.php" class="text-amber-100/30 hover:text-amber-400 transition-all p-2 rounded-2xl">
            <i data-lucide="history"></i>
        </a>
    </nav>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
