<?php
include 'config.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); exit;
}

$user_id_login = $_SESSION['user_id'];
$nama_user_login = $_SESSION['nama_lengkap'] ?? 'Kasir';
$hari_ini = date('Y-m-d');

// Query Khusus Kasir Login & Hari Ini
$query = "SELECT * FROM penjualan 
          WHERE user_id = ? 
          AND DATE(tanggal) = ? 
          ORDER BY tanggal DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id_login, $hari_ini]);
$riwayat = $stmt->fetchAll();

// Hitung Omzet Pribadi Hari Ini
$total_saya = 0;
foreach($riwayat as $r) { $total_saya += $r['total_harga']; }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="uploads/ikon.png" type="image/x-icon/png">
    <title>Riwayat Saya - Djadoel POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fcfaf7; }
        /* Gradasi Kayu Khas Djadoel */
        .wood-gradient { background: linear-gradient(135deg, #2d1b14 0%, #4a2c1d 100%); }
        .text-shadow-gold { text-shadow: 0 2px 10px rgba(245, 158, 11, 0.3); }
    </style>
</head>
<body class="pb-28">

    <header class="wood-gradient pt-10 pb-20 px-6 rounded-b-[3.5rem] shadow-2xl relative">
        <div class="flex justify-between items-center mb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/10 rounded-2xl backdrop-blur-md border border-white/20 flex items-center justify-center">
                    <i data-lucide="history" class="text-amber-400"></i>
                </div>
                <div>
                    <h1 class="text-white font-extrabold text-lg tracking-tight">Riwayat Saya</h1>
                    <p class="text-amber-200/50 text-[10px] font-bold uppercase tracking-[0.2em] leading-none mt-1">Laporan Jualan Harian</p>
                </div>
            </div>
            <a href="dashboard_kasir.php" class="text-amber-100/30 hover:text-white transition-colors">
                <i data-lucide="x-circle" class="w-8 h-8"></i>
            </a>
        </div>

        <div class="bg-white p-6 rounded-[2.5rem] shadow-xl flex items-center justify-between">
            <div>
                <p class="text-amber-900/40 text-[9px] font-black uppercase tracking-widest mb-1">Hasil Anda Hari Ini</p>
                <h2 class="text-3xl font-black text-amber-950 italic tracking-tighter text-shadow-gold">
                    Rp <?= number_format($total_saya, 0, ',', '.') ?>
                </h2>
            </div>
            <div class="bg-amber-100 p-4 rounded-[1.5rem]">
                <i data-lucide="wallet" class="text-amber-600 w-7 h-7"></i>
            </div>
        </div>
    </header>

    <main class="px-6 -mt-8">
        <div class="flex justify-between items-center mb-4 px-2">
            <h3 class="font-black text-amber-950 text-[11px] uppercase tracking-[0.2em]">Data Nota</h3>
            <span class="text-[10px] font-extrabold text-amber-600 italic uppercase tracking-tighter"><?= count($riwayat) ?> Transaksi Terkunci</span>
        </div>

        <div class="space-y-4">
            <?php if (empty($riwayat)): ?>
                <div class="bg-white/50 border-2 border-dashed border-amber-200 rounded-[2.5rem] py-16 text-center">
                    <i data-lucide="package-search" class="w-12 h-12 text-amber-200 mx-auto mb-4"></i>
                    <p class="text-amber-900/30 font-bold uppercase text-[10px] tracking-widest italic">Belum ada aktivitas jualan</p>
                </div>
            <?php endif; ?>

            <?php foreach($riwayat as $r): ?>
            <div class="bg-white p-5 rounded-[2.5rem] shadow-sm border border-amber-50 flex items-center justify-between active:scale-95 transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-[1.5rem] bg-amber-50 flex items-center justify-center border border-amber-100">
                        <i data-lucide="<?= $r['metode_bayar'] == 'Cash' ? 'banknote' : 'qr-code' ?>" class="text-amber-800 w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-black text-amber-950">#<?= $r['id'] ?></h4>
                            <span class="text-[8px] px-2 py-0.5 bg-amber-950 text-amber-100 rounded-full font-bold uppercase tracking-widest leading-none">
                                <?= $r['metode_bayar'] ?>
                            </span>
                        </div>
                        <p class="text-[10px] text-amber-900/40 font-bold uppercase tracking-widest mt-1">
                            Pukul <?= date('H:i', strtotime($r['tanggal'])) ?>
                        </p>
                    </div>
                </div>
                
                <div class="text-right">
                    <p class="font-black text-amber-600 text-lg tracking-tighter">Rp <?= number_format($r['total_harga'], 0, ',', '.') ?></p>
                    <a href="print_struk.php?id=<?= $r['id'] ?>" class="flex items-center justify-end gap-1 text-[9px] font-black text-amber-400 uppercase tracking-[0.1em] mt-1 hover:text-amber-600">
                        Detail <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <nav class="fixed bottom-8 left-6 right-6 wood-gradient rounded-[2.5rem] p-4 flex justify-around items-center shadow-2xl border border-white/10 z-50">
        <a href="dashboard_kasir.php" class="text-amber-100/30 hover:text-amber-400 transition-all p-2 rounded-2xl">
            <i data-lucide="layout-dashboard"></i>
        </a>
        
        <a href="transaksi.php" class="bg-amber-500 p-4 rounded-full text-white -mt-16 shadow-2xl shadow-amber-500/50 border-[6px] border-[#fcfaf7] active:scale-90 transition-all">
            <i data-lucide="plus" class="w-8 h-8 stroke-[3]"></i>
        </a>

        <a href="riwayat_kasir.php" class="text-amber-400 bg-white/10 p-2 rounded-2xl border border-white/10">
            <i data-lucide="history"></i>
        </a>
    </nav>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
