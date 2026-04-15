<?php
include 'config.php';

// Proteksi akses owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php"); exit;
}

// AMBIL DATA PROFIL DENGAN PENGAMAN
try {
    $stmt = $pdo->query("SELECT * FROM pengaturan LIMIT 1");
    $profil = $stmt->fetch();
} catch (PDOException $e) {
    $profil = false;
}

if (!$profil) {
    $profil = [
        'nama_toko'   => 'Djadoel POS',
        'alamat'      => 'Jl. Raya Jatirogo No. 12, Tuban, Jawa Timur',
        'telepon'     => '0812-3456-7890',
        'pesan_struk' => 'Matur Nuwun Sampun Belonjo!',
        'rekening'    => '',
        'qris_img'    => ''
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <link rel="icon" href="uploads/ikon.png" type="image/x-icon/png">
    <title>Profil Bisnis - Djadoel POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            <a href="laporan_bisnis.php" class="flex flex-col md:flex-row items-center justify-center md:justify-start md:space-x-4 py-2 md:p-3 text-amber-100/50 rounded-2xl">
                <i data-lucide="file-bar-chart" class="w-5 h-5"></i>
                <span class="text-[9px] md:text-sm font-bold mt-1 md:mt-0 uppercase">Laporan</span>
            </a>
            <a href="profil_bisnis.php" class="nav-active flex flex-col md:flex-row items-center justify-center md:justify-start md:space-x-4 py-2 md:p-3 rounded-2xl">
                <i data-lucide="store" class="w-5 h-5"></i>
                <span class="text-[9px] md:text-sm font-bold mt-1 md:mt-0 uppercase font-black">Profil</span>
            </a>
            <a href="logout.php" class="flex flex-col md:flex-row items-center justify-center md:justify-start md:space-x-4 py-2 md:p-3 text-red-400/60">
                <i data-lucide="log-out" class="w-5 h-5"></i>
                <span class="text-[9px] md:text-sm font-bold mt-1 md:mt-0 uppercase">Keluar</span>
            </a>
        </nav>
    </aside>

    <main class="flex-1 content-scroll order-1 md:order-2">
        <div class="p-6 md:p-12 max-w-5xl mx-auto space-y-8">
            <div>
                <h1 class="text-3xl font-black text-amber-950 italic">Profil Bisnis</h1>
                <p class="text-amber-900/40 font-medium italic">ꦥꦿꦺꦴꦥ꦳ꦶꦭ꧀ꦧꦶꦱ꧀ꦤꦶꦱ꧀</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1">
                    <div class="glass-card p-6 rounded-[2.5rem] border-2 border-dashed border-amber-200 relative overflow-hidden shadow-inner">
                        <div class="absolute top-0 left-0 w-full h-2 wood-gradient"></div>
                        <div class="text-center mt-4">
                            <i data-lucide="printer" class="w-8 h-8 mx-auto text-amber-900/10 mb-2"></i>
                            <h4 class="font-bold text-amber-950 uppercase tracking-tighter" id="preview-nama"><?= htmlspecialchars($profil['nama_toko']) ?></h4>
                            <p class="text-[9px] text-amber-900/50 px-4 mt-1 leading-tight" id="preview-alamat"><?= htmlspecialchars($profil['alamat']) ?></p>
                            <div class="border-t border-dotted border-amber-200 my-4"></div>
                            <div class="space-y-1">
                                <div class="flex justify-between text-[9px] font-mono"><span>Item x1</span><span>Rp 5.000</span></div>
                                <div class="flex justify-between text-[9px] font-mono font-bold"><span>TOTAL</span><span>Rp 5.000</span></div>
                            </div>
                            <div class="border-t border-dotted border-amber-200 my-4"></div>
                            <p class="text-[9px] italic text-amber-900/40" id="preview-pesan">"<?= htmlspecialchars($profil['pesan_struk']) ?>"</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="glass-card p-8 rounded-[2.5rem] shadow-xl shadow-amber-900/5">
                        <form action="proses_profil.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="col-span-2 md:col-span-1">
                                    <label class="text-[10px] font-bold text-amber-900/40 uppercase ml-2 tracking-widest">Nama Toko</label>
                                    <input type="text" name="nama_toko" value="<?= htmlspecialchars($profil['nama_toko']) ?>" oninput="updatePreview('preview-nama', this.value)" class="w-full mt-1 p-4 rounded-2xl border border-amber-50 bg-amber-50/30 text-sm outline-none focus:border-amber-500 transition-all">
                                </div>
                                <div class="col-span-2 md:col-span-1">
                                    <label class="text-[10px] font-bold text-amber-900/40 uppercase ml-2 tracking-widest">Nomor Telepon</label>
                                    <input type="text" name="telepon" value="<?= htmlspecialchars($profil['telepon']) ?>" class="w-full mt-1 p-4 rounded-2xl border border-amber-50 bg-amber-50/30 text-sm outline-none focus:border-amber-500 transition-all">
                                </div>
                                <div class="col-span-2">
                                    <label class="text-[10px] font-bold text-amber-900/40 uppercase ml-2 tracking-widest">Alamat Lengkap</label>
                                    <textarea name="alamat" oninput="updatePreview('preview-alamat', this.value)" rows="3" class="w-full mt-1 p-4 rounded-2xl border border-amber-50 bg-amber-50/30 text-sm outline-none focus:border-amber-500 transition-all"><?= htmlspecialchars($profil['alamat']) ?></textarea>
                                </div>
                                
                                <div class="col-span-2 md:col-span-1">
    <label class="text-[10px] font-bold text-amber-900/40 uppercase ml-2 tracking-widest">Daftar Rekening Bank</label>
    <textarea name="rekening" rows="3" placeholder="Contoh: BRI 0021-xxx a/n Budi" class="w-full mt-1 p-4 rounded-2xl border border-amber-50 bg-amber-50/30 text-[11px] outline-none focus:border-amber-500 transition-all"><?= htmlspecialchars($profil['rekening'] ?? '') ?></textarea>
</div>

<div class="col-span-2 md:col-span-1">
    <label class="text-[10px] font-bold text-amber-900/40 uppercase ml-2 tracking-widest">Nomor E-Wallet</label>
    <textarea name="ewallet" rows="3" placeholder="Contoh: DANA 0812xxx a/n Budi" class="w-full mt-1 p-4 rounded-2xl border border-amber-50 bg-amber-50/30 text-[11px] outline-none focus:border-amber-500 transition-all"><?= htmlspecialchars($profil['ewallet'] ?? '') ?></textarea>
</div>

<div class="col-span-2">
    <label class="text-[10px] font-bold text-amber-900/40 uppercase ml-2 tracking-widest">Upload QRIS Pembayaran</label>
    <input type="hidden" name="old_qris" value="<?= $profil['qris_img'] ?? '' ?>">
    <input type="file" name="qris_img" class="w-full mt-1 p-4 rounded-2xl border border-dashed border-amber-200 bg-amber-50/30 text-[10px] outline-none">
    <?php if(!empty($profil['qris_img'])): ?>
        <p class="text-[9px] mt-2 text-amber-600 font-bold italic">File QRIS Aktif: <?= $profil['qris_img'] ?></p>
    <?php endif; ?>
</div>

                                <div class="col-span-2">
                                    <label class="text-[10px] font-bold text-amber-900/40 uppercase ml-2 tracking-widest">Pesan di Bawah Struk</label>
                                    <input type="text" name="pesan_struk" value="<?= htmlspecialchars($profil['pesan_struk']) ?>" oninput="updatePreview('preview-pesan', '“' + this.value + '”')" class="w-full mt-1 p-4 rounded-2xl border border-amber-50 bg-amber-50/30 text-sm outline-none focus:border-amber-500 transition-all">
                                </div>
                            </div>
                            
                            <button type="submit" class="w-full wood-gradient text-amber-100 py-5 rounded-3xl font-bold shadow-xl active:scale-95 transition-all mt-4 tracking-widest text-xs uppercase">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();
        function updatePreview(id, val) {
            document.getElementById(id).innerText = val;
        }
    </script>
</body>
</html>
