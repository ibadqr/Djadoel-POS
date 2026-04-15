<?php
include 'config.php';

// Proteksi akses owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php"); exit;
}

// Filter Tanggal
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');

// Ambil data profil
$stmt_profil = $pdo->query("SELECT * FROM pengaturan LIMIT 1");
$profil = $stmt_profil->fetch();

// --- 1. QUERY OMZET & TOTAL TRX (Murni tabel penjualan) ---
$sql_omzet = "SELECT COUNT(id) as total_trx, SUM(total_harga) as total_omzet 
              FROM penjualan 
              WHERE DATE(tanggal) BETWEEN ? AND ?";
$stmt_o = $pdo->prepare($sql_omzet);
$stmt_o->execute([$tgl_mulai, $tgl_selesai]);
$data_omzet = $stmt_o->fetch();

// --- 2. QUERY LABA BERSIH (Baru JOIN ke detail untuk ambil modal) ---
// Gunakan pr.harga_jual (dari tabel produk) atau dp.harga_jual (jika ada di detail)
// Sesuai error sebelumnya, kita gunakan pr.harga_jual - pr.harga_beli
$sql_laba = "SELECT SUM((pr.harga_jual - pr.harga_beli) * dp.qty) as total_laba
             FROM detail_penjualan dp
             JOIN penjualan p ON dp.penjualan_id = p.id
             JOIN produk pr ON dp.produk_id = pr.id
             WHERE DATE(p.tanggal) BETWEEN ? AND ?";
$stmt_l = $pdo->prepare($sql_laba);
$stmt_l->execute([$tgl_mulai, $tgl_selesai]);
$data_laba = $stmt_l->fetch();

// Gabungkan hasil ke dalam array $rekap agar tidak merusak tampilan bawah
$rekap = [
    'total_trx' => $data_omzet['total_trx'] ?? 0,
    'omzet'     => $data_omzet['total_omzet'] ?? 0,
    'total_laba'=> $data_laba['total_laba'] ?? 0
];

// --- 3. QUERY LIST TRANSAKSI (Untuk Tabel di bawah) ---
$sql_list = "SELECT p.*, u.nama_lengkap as nama_kasir 
             FROM penjualan p 
             JOIN users u ON p.user_id = u.id 
             WHERE DATE(p.tanggal) BETWEEN ? AND ? 
             ORDER BY p.tanggal DESC";
$stmt_list = $pdo->prepare($sql_list);
$stmt_list->execute([$tgl_mulai, $tgl_selesai]);
$transaksi = $stmt_list->fetchAll();

$nama_pencetak = $_SESSION['nama_lengkap'] ?? ($_SESSION['username'] ?? 'Owner');
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <link rel="icon" href="uploads/ikon.png" type="image/x-icon/png">
    <title>Laporan Bisnis - Djadoel POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@600;800&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --nav-height: 75px; }
        body { background-color: #fcfaf7; font-family: 'Plus Jakarta Sans', sans-serif; overflow: hidden; height: 100vh; }
        .aksara { font-family: 'Crimson Pro', serif; }
        .wood-gradient { background: linear-gradient(135deg, #2d1b14 0%, #4a2c1d 100%); }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-blur-md; border: 1px solid rgba(146, 64, 14, 0.1); }
        .content-scroll { height: calc(100vh - var(--nav-height)); overflow-y: auto; padding-bottom: 80px; }
        @media (min-width: 768px) { .content-scroll { height: 100vh; padding-bottom: 20px; } }
        
        .nav-active { color: #f59e0b !important; background: rgba(245, 158, 11, 0.1); border-top: 3px solid #f59e0b; }
        @media (min-width: 768px) { .nav-active { border-top: 0; border-left: 4px solid #f59e0b; background: rgba(255, 255, 255, 0.05); } }

        @media print {
            aside, .no-print, form, button { display: none !important; }
            body { background: white !important; overflow: visible !important; height: auto !important; }
            .content-scroll { height: auto !important; overflow: visible !important; padding: 0 !important; }
            .glass-card { border: none !important; box-shadow: none !important; background: white !important; padding: 0 !important; border-radius: 0 !important; }
            .print-header { display: block !important; text-align: center; border-bottom: 2px solid black; padding-bottom: 15px; margin-bottom: 25px; }
            .grid-stats { display: flex !important; gap: 15px; margin-bottom: 20px; }
            .grid-stats > div { flex: 1; border: 1px solid #000 !important; border-radius: 0 !important; padding: 10px !important; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #000; }
            th { background: #eee !important; border: 1px solid #000 !important; padding: 10px !important; color: black !important; font-size: 10px; }
            td { border: 1px solid #000 !important; padding: 10px !important; font-size: 11px !important; color: black !important; }
            .print-only { display: block !important; }
        }
        .print-header, .print-only { display: none; }
    </style>
</head>
<body class="flex flex-col md:flex-row">

    <aside class="fixed md:relative bottom-0 left-0 w-full md:w-72 wood-gradient h-[75px] md:h-screen text-amber-100 flex md:flex-col justify-between md:justify-start z-50 px-2 md:p-6 pb-[env(safe-area-inset-bottom)]">
        <div class="hidden md:flex items-center space-x-3 mb-10">
            <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center shadow-lg"><i data-lucide="crown" class="text-amber-950 w-6 h-6"></i></div>
            <h2 class="aksara text-2xl tracking-tighter">ꦣ꧀ꦗꦣꦺꦴꦮꦺꦭ꧀</h2>
        </div>
        <nav class="flex md:flex-col flex-1 justify-around md:justify-start md:space-y-2 w-full">
            <a href="dashboard_owner.php" class="flex flex-col md:flex-row items-center justify-center md:justify-start md:space-x-4 py-2 md:p-3 text-amber-100/50 rounded-2xl">
                <i data-lucide="home" class="w-5 h-5"></i>
                <span class="text-[9px] md:text-sm font-bold mt-1 md:mt-0 uppercase">Home</span>
            </a>
            <a href="stok_produk.php" class="flex flex-col md:flex-row items-center justify-center md:justify-start md:space-x-4 py-2 md:p-3 text-amber-100/50 rounded-2xl">
                <i data-lucide="package" class="w-5 h-5"></i>
                <span class="text-[9px] md:text-sm font-bold mt-1 md:mt-0 uppercase">Produk</span>
            </a>
            <a href="kelola_kasir.php" class="flex flex-col md:flex-row items-center justify-center md:justify-start md:space-x-4 py-2 md:p-3 text-amber-100/50 rounded-2xl">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span class="text-[9px] md:text-sm font-bold mt-1 md:mt-0 uppercase">Kasir</span>
            </a>
            <a href="laporan_bisnis.php" class="nav-active flex flex-col md:flex-row items-center justify-center md:justify-start md:space-x-4 py-2 md:p-3 rounded-2xl">
                <i data-lucide="file-bar-chart" class="w-5 h-5"></i>
                <span class="text-[9px] md:text-sm font-bold mt-1 md:mt-0 uppercase font-black">Laporan</span>
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
    <div class="pt-2 md:pt-6 px-6 md:px-12 max-w-6xl mx-auto space-y-6">
            
            <div class="print-header">
                <h1 class="font-bold text-2xl uppercase"><?= htmlspecialchars($profil['nama_toko'] ?? 'Djadoel POS') ?></h1>
                <p class="text-xs italic">Laporan Laba Rugi Resmi</p>
                <p class="text-[10px] mt-2 border-t pt-2">Periode: <?= date('d/m/Y', strtotime($tgl_mulai)) ?> s/d <?= date('d/m/Y', strtotime($tgl_selesai)) ?></p>
            </div>

            <div class="flex justify-between items-end no-print">
                <div>
                    <h1 class="text-3xl font-black text-amber-950 italic">Laporan Bisnis</h1>
                    <p class="text-amber-900/40 font-medium italic">ꦥꦼꦤ꧀ꦝꦥꦠꦤ꧀ꦭꦤ꧀ꦧꦠꦶ</p>
                </div>
                <button onclick="window.print()" class="bg-amber-100 text-amber-950 px-5 py-3 rounded-2xl font-bold flex items-center space-x-2 shadow-md hover:bg-amber-200 transition-all uppercase text-[10px]">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="glass-card p-6 rounded-[2.5rem] border border-amber-100 shadow-sm no-print">
                <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-amber-900/40 uppercase ml-2">Mulai</label>
                        <input type="date" name="tgl_mulai" value="<?= $tgl_mulai ?>" class="w-full mt-1 p-4 rounded-2xl border border-amber-50 bg-white text-sm outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-amber-900/40 uppercase ml-2">Selesai</label>
                        <input type="date" name="tgl_selesai" value="<?= $tgl_selesai ?>" class="w-full mt-1 p-4 rounded-2xl border border-amber-50 bg-white text-sm outline-none">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full wood-gradient text-amber-100 py-4 rounded-2xl font-bold text-xs uppercase tracking-widest shadow-lg">Analisa Data</button>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 grid-stats">
                <div class="glass-card p-6 rounded-[2.5rem] border-l-4 border-l-amber-500">
                    <p class="text-[10px] font-black text-amber-900/30 uppercase tracking-widest">Total Omzet (Kotor)</p>
                    <h2 class="text-xl font-black text-amber-950 mt-1"><?= rupiah($rekap['omzet'] ?? 0) ?></h2>
                </div>
                <div class="glass-card p-6 rounded-[2.5rem] border-l-4 border-l-emerald-600 bg-emerald-50/30">
                    <p class="text-[10px] font-black text-emerald-900/40 uppercase tracking-widest">Keuntungan Bersih (Laba)</p>
                    <h2 class="text-xl font-black text-emerald-700 mt-1"><?= rupiah($rekap['total_laba'] ?? 0) ?></h2>
                </div>
                <div class="glass-card p-6 rounded-[2.5rem] border-l-4 border-l-blue-500">
                    <p class="text-[10px] font-black text-amber-900/30 uppercase tracking-widest">Total Transaksi</p>
                    <h2 class="text-xl font-black text-amber-950 mt-1"><?= $rekap['total_trx'] ?? 0 ?> Kali</h2>
                </div>
            </div>

            <div class="glass-card rounded-[2.5rem] overflow-hidden border border-amber-100 shadow-xl shadow-amber-900/5">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-amber-50/50 text-amber-900/40 text-[10px] font-bold uppercase tracking-[0.2em]">
                            <tr>
                                <th class="p-6">Waktu</th>
                                <th class="p-6">No. Nota</th>
                                <th class="p-6">Kasir</th>
                                <th class="p-6 text-right">Total Transaksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-50">
                            <?php foreach($transaksi as $t): ?>
                            <tr class="hover:bg-amber-50/40 transition-all">
                                <td class="p-6 text-sm font-bold text-amber-950">
                                    <?= date('d M Y', strtotime($t['tanggal'])) ?> <span class="text-[10px] text-amber-900/30 ml-2 font-normal"><?= date('H:i', strtotime($t['tanggal'])) ?></span>
                                </td>
                                <td class="p-6"><span class="text-[10px] font-black text-amber-800/60 uppercase">#<?= $t['id'] ?></span></td>
                                <td class="p-6 text-xs font-bold text-amber-900"><?= htmlspecialchars($t['nama_kasir']) ?></td>
                                <td class="p-6 text-right font-black text-amber-950 text-sm"><?= rupiah($t['total_harga']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="print-only" style="margin-top: 50px;">
                <div style="display: flex; justify-content: space-between; padding: 0 40px;">
                    <div style="text-align: center;">
                        <p style="font-size: 11px;">Mengetahui,</p>
                        <div style="height: 70px;"></div>
                        <p style="font-size: 11px; font-weight: bold; border-top: 1px solid black; min-width: 150px; display: inline-block; padding-top: 5px;">MANAJER</p>
                    </div>
                    <div style="text-align: center;">
                        <p style="font-size: 11px;">Dicetak pada, <?= date('d/m/Y H:i') ?></p>
                        <div style="height: 70px;"></div>
                        <p style="font-size: 11px; font-weight: bold; border-top: 1px solid black; min-width: 150px; display: inline-block; padding-top: 5px;"><?= htmlspecialchars($nama_pencetak) ?></p>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>lucide.createIcons();</script>
</body>
</html>
