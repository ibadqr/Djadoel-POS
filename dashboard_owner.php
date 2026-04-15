<?php
include 'config.php';

// Proteksi akses owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php"); exit;
}

// 1. Ambil Data Statistik
$total_produk = $pdo->query("SELECT COUNT(*) FROM produk")->fetchColumn();
$total_kasir = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'kasir'")->fetchColumn();

// AMBIL OMZET HARI INI
$tgl_sekarang = date('Y-m-d');
$stmt_omzet = $pdo->prepare("SELECT SUM(total_harga) as omzet_hari_ini FROM penjualan WHERE DATE(tanggal) = ?");
$stmt_omzet->execute([$tgl_sekarang]);
$omzet_data = $stmt_omzet->fetch();
$omzet_hari_ini = $omzet_data['omzet_hari_ini'] ?? 0;

// 2. Ambil 5 Produk Terlaris (Gunakan qty sebagai standar jumlah)
$sql_terlaris = "SELECT p.*, k.nama_kategori, SUM(dp.qty) as total_terjual 
                 FROM detail_penjualan dp
                 JOIN produk p ON dp.produk_id = p.id
                 LEFT JOIN kategori k ON p.kategori_id = k.id
                 GROUP BY p.id
                 ORDER BY total_terjual DESC
                 LIMIT 5";
try {
    $produk_terlaris = $pdo->query($sql_terlaris)->fetchAll();
} catch (Exception $e) {
    $produk_terlaris = []; // Supaya tidak crash kalau nama kolom beda
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <link rel="icon" href="uploads/ikon.png" type="image/x-icon/png">
    <title>Owner Dashboard - Djadoel POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@600;800&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --nav-height: 75px; }
        body { background-color: #fcfaf7; font-family: 'Plus Jakarta Sans', sans-serif; overflow: hidden; height: 100vh; }
        .aksara { font-family: 'Crimson Pro', serif; }
        .wood-gradient { background: linear-gradient(135deg, #2d1b14 0%, #4a2c1d 100%); }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-blur-md; border: 1px solid rgba(146, 64, 14, 0.1); }
        .content-scroll { height: calc(100vh - var(--nav-height)); overflow-y: auto; padding-bottom: 40px; }
        @media (min-width: 768px) { .content-scroll { height: 100vh; } }
        .img-preview { aspect-ratio: 1/1; object-fit: cover; border-radius: 0.75rem; }
        
        .nav-active { color: #f59e0b !important; background: rgba(245, 158, 11, 0.1); border-top: 3px solid #f59e0b; }
        @media (min-width: 768px) { .nav-active { border-top: 0; border-left: 4px solid #f59e0b; background: rgba(255, 255, 255, 0.05); } }
    </style>
</head>
<body class="flex flex-col md:flex-row">

    <aside class="fixed md:relative bottom-0 left-0 w-full md:w-72 wood-gradient h-[75px] md:h-screen text-amber-100 flex md:flex-col justify-between md:justify-start z-50 px-2 md:p-6 pb-[env(safe-area-inset-bottom)]">
        <div class="hidden md:flex items-center space-x-3 mb-10">
            <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center shadow-lg"><i data-lucide="crown" class="text-amber-950 w-6 h-6"></i></div>
            <h2 class="aksara text-2xl tracking-tighter">ꦣ꧀ꦗꦣꦺꦴꦮꦺꦭ꧀</h2>
        </div>
        <nav class="flex md:flex-col flex-1 justify-around md:justify-start md:space-y-2 w-full">
            <a href="dashboard_owner.php" class="nav-active flex flex-col md:flex-row items-center justify-center md:justify-start md:space-x-4 py-2 md:p-3 rounded-2xl">
                <i data-lucide="home" class="w-5 h-5"></i>
                <span class="text-[9px] md:text-sm font-bold mt-1 md:mt-0 uppercase font-black">Home</span>
            </a>
            <a href="stok_produk.php" class="flex flex-col md:flex-row items-center justify-center md:justify-start md:space-x-4 py-2 md:p-3 text-amber-100/50 rounded-2xl">
                <i data-lucide="package" class="w-5 h-5"></i>
                <span class="text-[9px] md:text-sm font-bold mt-1 md:mt-0 uppercase">Produk</span>
            </a>
            <a href="kelola_kasir.php" class="flex flex-col md:flex-row items-center justify-center md:justify-start md:space-x-4 py-2 md:p-3 text-amber-100/50 rounded-2xl">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span class="text-[9px] md:text-sm font-bold mt-1 md:mt-0 uppercase">Kasir</span>
            </a>
            <a href="laporan_bisnis.php" class="flex flex-col md:flex-row items-center justify-center md:justify-start md:space-x-4 py-2 md:p-3 text-amber-100/50 rounded-2xl">
                <i data-lucide="file-bar-chart" class="w-5 h-5"></i>
                <span class="text-[9px] md:text-sm font-bold mt-1 md:mt-0 uppercase">Laporan</span>
            </a>
            <a href="profil_bisnis.php" class="flex flex-col md:flex-row items-center justify-center md:justify-start md:space-x-4 py-2 md:p-3 text-amber-100/50 rounded-2xl">
                <i data-lucide="store" class="w-5 h-5"></i>
                <span class="text-[9px] md:text-sm font-bold mt-1 md:mt-0 uppercase">Profil</span>
            </a>
            <a href="logout.php" class="flex flex-col md:flex-row items-center justify-center md:justify-start md:space-x-4 py-2 md:p-3 text-red-400/60">
                <i data-lucide="log-out" class="w-5 h-5"></i>
                <span class="text-[9px] md:text-sm font-bold mt-1 md:mt-0 uppercase">Keluar</span>
            </a>
        </nav>
    </aside>

    <main class="flex-1 content-scroll order-1 md:order-2">
        <div class="pt-4 md:pt-6 px-6 md:px-12 max-w-6xl mx-auto space-y-8">
            
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-black text-amber-950 italic tracking-tight leading-none">Dashboard Bisnis</h1>
                    <p class="text-amber-900/40 font-medium italic mt-1">ꦱꦸꦒꦼꦁꦫꦮꦸꦃꦧꦺꦴꦱ꧀</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="glass-card p-6 rounded-[2.5rem] border-l-8 border-amber-500 shadow-xl shadow-amber-900/5">
                    <div class="p-3 bg-amber-100 w-fit rounded-2xl text-amber-900 mb-4"><i data-lucide="package" class="w-6 h-6"></i></div>
                    <h3 class="text-4xl font-black text-amber-950"><?= $total_produk ?></h3>
                    <p class="text-[10px] font-bold text-amber-900/40 uppercase tracking-widest mt-1">Jenis Produk</p>
                </div>

                <div class="glass-card p-6 rounded-[2.5rem] border-l-8 border-emerald-500 shadow-xl shadow-amber-900/5">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 bg-emerald-100 rounded-2xl text-emerald-600"><i data-lucide="trending-up" class="w-6 h-6"></i></div>
                        <span class="bg-emerald-600 text-white text-[9px] px-2 py-1 rounded-full font-black">LIVE</span>
                    </div>
                    <h3 class="text-2xl font-black text-emerald-600"><?= rupiah($omzet_hari_ini) ?></h3>
                    <p class="text-[10px] font-bold text-amber-900/40 uppercase tracking-widest mt-1">Omzet Hari Ini</p>
                </div>

                <div class="glass-card p-6 rounded-[2.5rem] border-l-8 border-amber-950 shadow-xl shadow-amber-900/5">
                    <div class="p-3 bg-amber-50 w-fit rounded-2xl text-amber-950 mb-4"><i data-lucide="users" class="w-6 h-6"></i></div>
                    <h3 class="text-4xl font-black text-amber-950"><?= $total_kasir ?></h3>
                    <p class="text-[10px] font-bold text-amber-900/40 uppercase tracking-widest mt-1">Total Kasir</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <h2 class="aksara text-2xl text-amber-950">Akses Cepat</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <a href="stok_produk.php" class="wood-gradient p-8 rounded-[2.5rem] text-amber-100 text-center space-y-3 hover:scale-[1.02] transition-all">
                            <i data-lucide="plus-square" class="w-10 h-10 mx-auto"></i>
                            <p class="text-xs font-bold uppercase tracking-widest">Input Stok</p>
                        </a>
                        <a href="laporan_bisnis.php" class="glass-card p-8 rounded-[2.5rem] text-amber-950 text-center space-y-3 hover:scale-[1.02] transition-all">
                            <i data-lucide="trending-up" class="w-10 h-10 mx-auto"></i>
                            <p class="text-xs font-bold uppercase tracking-widest">Cek Laporan</p>
                        </a>
                    </div>
                </div>

                <div class="glass-card p-8 rounded-[2.5rem] border border-amber-100">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="aksara text-2xl text-amber-950">Produk Terlaris</h2>
                        <span class="text-[10px] font-black bg-amber-100 text-amber-800 px-3 py-1 rounded-full uppercase">Top 5</span>
                    </div>
                    <div class="space-y-4">
                        <?php foreach($produk_terlaris as $p): ?>
                        <div class="flex items-center justify-between group">
                            <div class="flex items-center space-x-4">
                                <img src="uploads/<?= $p['gambar'] ?>" class="w-12 h-12 img-preview border border-amber-50 group-hover:scale-105 transition-transform">
                                <div>
                                    <p class="text-sm font-bold text-amber-950 leading-none"><?= htmlspecialchars($p['nama_produk'] ?? '') ?></p>
                                    <p class="text-[10px] text-amber-900/30 font-bold uppercase mt-1"><?= htmlspecialchars($p['nama_kategori'] ?? 'Umum') ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-black text-amber-950"><?= (int)$p['total_terjual'] ?> Pcs</p>
                                <span class="text-[9px] font-bold text-amber-500 uppercase">Terjual</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($produk_terlaris)): ?>
                            <div class="text-center py-10 opacity-20 font-bold italic">Belum ada data...</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>lucide.createIcons();</script>
</body>
</html>
