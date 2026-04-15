<?php
include 'config.php';
$id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM penjualan WHERE id = ?");
    $stmt->execute([$id]);
    $transaksi = $stmt->fetch();
    if (!$transaksi) die("Transaksi tidak ditemukan.");

    $nama_kasir = "Kasir Djadoel"; 
    try {
        $stmt_user = $pdo->prepare("SELECT nama_lengkap FROM users WHERE id = ?");
        $stmt_user->execute([$transaksi['user_id']]);
        $u = $stmt_user->fetch();
        if ($u) $nama_kasir = $u['nama_lengkap'];
    } catch (PDOException $e) {}

    $items = $pdo->query("SELECT dp.*, pr.nama_produk FROM detail_penjualan dp JOIN produk pr ON dp.produk_id = pr.id WHERE dp.penjualan_id = $id")->fetchAll();
    $profil = $pdo->query("SELECT * FROM pengaturan LIMIT 1")->fetch() ?: [
        'nama_toko' => 'DJADOEL POS', 'alamat' => 'Tuban, Indonesia', 'telepon' => '', 'pesan_struk' => 'Matur Nuwun!'
    ];
} catch (Exception $e) { die("Error: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="uploads/ikon.png" type="image/x-icon/png">
    <title>Struk #<?= $id ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        /* Menggunakan font sistem yang bersih untuk cetak struk */
        body { background: #121212; padding: 40px 10px; font-family: 'Courier New', Courier, monospace; letter-spacing: -0.5px; }
        
        #struk-asli { 
            background: white; 
            width: 340px; 
            margin: 0 auto; 
            padding: 30px 20px; 
            color: #000; 
            border-radius: 4px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); 
        }

        .line-divider { border-top: 1px dashed #333; margin: 12px 0; }
        .text-header { letter-spacing: 2px; }
        
        @media print { 
            body { background: white; padding: 0; }
            body > *:not(#struk-asli) { display: none !important; }
            #struk-asli { box-shadow: none !important; width: 100% !important; margin: 0 !important; padding: 20px !important; } 
        }
    </style>
</head>
<body onload="window.print()">

    <div id="struk-asli">
        <div class="text-center">
            <h1 class="font-black text-xl uppercase text-header mb-1"><?= htmlspecialchars($profil['nama_toko']) ?></h1>
            <p class="text-[10px] leading-tight opacity-80"><?= htmlspecialchars($profil['alamat']) ?></p>
            <p class="text-[10px] mt-1 font-bold">WA: <?= htmlspecialchars($profil['telepon']) ?></p>
        </div>

        <div class="line-divider"></div>

        <div class="text-[11px] space-y-1">
            <div class="flex justify-between">
                <span>WAKTU : <?= date('d/m/y H:i', strtotime($transaksi['tanggal'])) ?></span>
                <span class="font-bold">#<?= $id ?></span>
            </div>
            <div class="flex justify-between">
                <span>KASIR : <?= htmlspecialchars($nama_kasir) ?></span>
                <span class="uppercase"><?= $transaksi['metode_bayar'] ?></span>
            </div>
        </div>

        <div class="line-divider"></div>

        <div class="space-y-3">
            <?php foreach($items as $item): ?>
            <div class="text-[12px] leading-tight">
                <div class="flex justify-between items-start uppercase">
                    <span class="flex-1 pr-6 font-bold leading-none"><?= $item['nama_produk'] ?></span>
                    <span class="font-bold whitespace-nowrap"><?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                </div>
                <div class="text-[10px] mt-1 text-gray-600 italic">
                    <?= $item['qty'] ?> x <?= number_format($item['subtotal']/$item['qty'], 0, ',', '.') ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="line-divider"></div>

        <div class="text-[12px] space-y-1">
            <div class="flex justify-between font-black text-lg py-1">
                <span>TOTAL</span>
                <span>Rp <?= number_format($transaksi['total_harga'], 0, ',', '.') ?></span>
            </div>
            
            <?php if($transaksi['metode_bayar'] == 'Cash'): ?>
            <div class="flex justify-between opacity-80">
                <span>TUNAI</span>
                <span><?= number_format($transaksi['bayar'], 0, ',', '.') ?></span>
            </div>
            <div class="flex justify-between border-t border-gray-200 mt-1 pt-1 font-bold">
                <span>KEMBALI</span>
                <span><?= number_format($transaksi['bayar'] - $transaksi['total_harga'], 0, ',', '.') ?></span>
            </div>
            <?php else: ?>
                <div class="text-center bg-gray-100 py-1 text-[10px] font-bold tracking-widest mt-1">LUNAS / NON-TUNAI</div>
            <?php endif; ?>
        </div>

        <div class="line-divider"></div>

        <div class="text-center mt-4">
            <p class="text-[11px] font-bold italic">"<?= htmlspecialchars($profil['pesan_struk']) ?>"</p>
            <div class="mt-6 flex justify-center items-center gap-2 opacity-20">
                <span class="h-[1px] w-8 bg-black"></span>
                <span class="text-[8px] font-bold tracking-[0.3em] uppercase">Djadoel POS</span>
                <span class="h-[1px] w-8 bg-black"></span>
            </div>
        </div>
    </div>

    <div class="mt-10 flex flex-col gap-3 w-[340px] mx-auto no-print">
        <button onclick="window.print()" class="w-full bg-white text-black py-4 rounded-2xl font-black text-xs uppercase shadow-2xl border-2 border-white active:scale-95 transition-all">
            Cetak Struk / Simpan PDF
        </button>
        <button onclick="simpanGambar()" class="w-full bg-amber-500 text-amber-950 py-4 rounded-2xl font-black text-xs uppercase shadow-2xl active:scale-95 transition-all">
            Bagikan ke WhatsApp (PNG)
        </button>
        <a href="transaksi.php" class="text-center text-[10px] text-white/40 font-bold uppercase tracking-[0.2em] mt-4 hover:text-white transition-colors italic">
            ← Kembali ke Kasir
        </a>
    </div>

    <script>
        function simpanGambar() {
            const area = document.getElementById('struk-asli');
            html2canvas(area, { 
                scale: 3,
                backgroundColor: "#ffffff",
                logging: false
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'Djadoel-Struk-<?= $id ?>.png';
                link.href = canvas.toDataURL("image/png");
                link.click();
            });
        }
    </script>
</body>
</html>
