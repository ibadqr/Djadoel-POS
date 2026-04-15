<?php
include 'config.php';

// Proteksi akses
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); exit;
}

// Ambil data profil untuk info pembayaran dan pengaturan toko
$stmt_profil = $pdo->query("SELECT * FROM pengaturan LIMIT 1");
$profil = $stmt_profil->fetch();

// Ambil data produk yang stoknya tersedia
$stmt = $pdo->query("SELECT p.*, k.nama_kategori 
                     FROM produk p 
                     LEFT JOIN kategori k ON p.kategori_id = k.id 
                     WHERE p.stok > 0 
                     ORDER BY p.nama_produk ASC");
$produk = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <link rel="icon" href="uploads/ikon.png" type="image/x-icon/png">
    <title>Kasir - Djadoel POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@600;800&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* TEMA DJADOEL POS PREMIUM */
        body { background-color: #fcfaf7; font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; margin: 0; overflow-x: hidden; }
        .aksara { font-family: 'Crimson Pro', serif; }
        /* Gradasi Kayu Djadoel */
        .wood-gradient { background: linear-gradient(135deg, #2d1b14 0%, #4a2c1d 100%); }
        /* Card Kaca Djadoel */
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-blur-md; border: 1px solid rgba(146, 64, 14, 0.1); }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        
        /* State Tombol Metode Pembayaran */
        .active-metode { background: #4a2c1d !important; color: #fcd34d !important; border-color: #4a2c1d !important; transform: scale(0.95); }
        
        /* Tata Letak untuk Laptop/Tablet */
        @media (min-width: 768px) {
            .main-content { height: 100vh; overflow-y: auto; }
            .cart-panel-fixed { height: 100vh; overflow-y: auto; position: sticky; top: 0; }
        }
    </style>
</head>
<body class="flex flex-col md:flex-row">

    <main class="flex-1 p-4 md:p-8 order-2 md:order-1 main-content">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-black text-amber-950 italic uppercase tracking-tighter"><?= htmlspecialchars($profil['nama_toko'] ?? 'Djadoel POS') ?></h1>
                <p class="text-[10px] font-bold text-amber-900/40 uppercase tracking-[0.2em] italic">ꦠꦿꦤ꧀ꦱꦏ꧀ꦱꦶꦥꦼꦚ꧀ꦗꦸꦮꦭꦤ꧀</p>
            </div>
            <div class="relative w-full sm:w-64">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-amber-900/30"></i>
                <input type="text" id="searchProduk" placeholder="Cari jajan..." class="w-full pl-11 pr-4 py-3 rounded-2xl border border-amber-100 outline-none focus:border-amber-500 text-sm bg-white shadow-sm transition-all">
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4" id="produkGrid">
            <?php foreach($produk as $p): ?>
            <div class="produk-item glass-card p-3 rounded-[2rem] flex flex-col cursor-pointer active:scale-95 transition-all hover:shadow-lg group" 
                 onclick="tambahKeKeranjang(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nama_produk']), ENT_QUOTES) ?>', <?= $p['harga_jual'] ?>)"
                 data-nama="<?= strtolower($p['nama_produk']) ?>">
                <div class="relative h-28 w-full bg-amber-50 rounded-[1.5rem] mb-3 overflow-hidden border border-amber-100/50">
                    <img src="uploads/<?= $p['gambar'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($p['nama_produk']) ?>&background=random'">
                    <div class="absolute top-2 right-2 bg-amber-900/80 backdrop-blur-md text-amber-100 text-[9px] px-2 py-1 rounded-lg font-bold">Stok: <?= $p['stok'] ?></div>
                </div>
                <h3 class="text-xs font-bold text-amber-950 truncate px-1"><?= htmlspecialchars($p['nama_produk']) ?></h3>
                <p class="text-[11px] font-black text-amber-600 mt-1 px-1"><?= rupiah($p['harga_jual']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <aside class="w-full md:w-[380px] wood-gradient text-amber-100 flex flex-col order-1 md:order-2 shadow-2xl cart-panel-fixed">
        <div class="p-6 border-b border-amber-100/10 flex justify-between items-center">
            <h2 class="aksara text-2xl tracking-tighter italic">Pesanan</h2>
            <div class="flex space-x-3 items-center">
                <a href="dashboard_kasir.php" class="p-2 bg-white/10 rounded-xl hover:bg-white/20 transition-all"><i data-lucide="home" class="w-5 h-5 text-amber-200"></i></a>
                <button onclick="kosongkanKeranjang()" class="text-[10px] font-bold uppercase tracking-widest text-amber-500 hover:text-amber-300">Reset</button>
            </div>
        </div>

        <div class="flex-1 p-6 space-y-4 overflow-y-auto hide-scrollbar" id="keranjangList">
            </div>

        <div class="p-8 bg-black/30 backdrop-blur-2xl rounded-t-[3rem] border-t border-white/5 space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-[10px] font-bold text-amber-500 uppercase tracking-[0.2em]">Total</span>
                <span class="text-3xl font-black italic tracking-tighter" id="totalBayar">Rp 0</span>
            </div>
            <button onclick="bukaModalBayar()" class="w-full bg-amber-500 text-amber-950 py-5 rounded-[2.5rem] font-black text-sm uppercase tracking-[0.2em] shadow-xl active:scale-95 transition-all">Simpan Transaksi</button>
        </div>
    </aside>

    <div id="modalBayar" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-amber-950/90 backdrop-blur-md">
        <div class="glass-card w-full max-w-md rounded-[3rem] overflow-hidden shadow-2xl animate-in zoom-in duration-300">
            <div class="wood-gradient p-6 text-amber-100 flex justify-between items-center">
                <h3 class="aksara text-xl tracking-tighter italic">Pembayaran</h3>
                <button onclick="tutupModalBayar()" class="opacity-50 hover:opacity-100"><i data-lucide="x"></i></button>
            </div>
            
            <div class="p-8 space-y-5">
                <div class="text-center">
                    <p class="text-[10px] font-black text-amber-900/40 uppercase tracking-widest">Tagihan</p>
                    <h2 class="text-4xl font-black text-amber-950 italic" id="modalTotal">Rp 0</h2>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <button onclick="setMetode('Cash', this)" class="metode-btn active-metode p-3 rounded-2xl border border-amber-100 text-[9px] font-black uppercase tracking-widest transition-all">Cash</button>
                    <button onclick="setMetode('QRIS', this)" class="metode-btn p-3 rounded-2xl border border-amber-100 text-[9px] font-black uppercase tracking-widest transition-all">QRIS</button>
                    <button onclick="setMetode('Bank', this)" class="metode-btn p-3 rounded-2xl border border-amber-100 text-[9px] font-black uppercase tracking-widest transition-all">Transfer</button>
                    <button onclick="setMetode('DANA', this)" class="metode-btn p-3 rounded-2xl border border-amber-100 text-[9px] font-black uppercase tracking-widest transition-all">DANA</button>
                    <button onclick="setMetode('OVO', this)" class="metode-btn p-3 rounded-2xl border border-amber-100 text-[9px] font-black uppercase tracking-widest transition-all">OVO</button>
                    <button onclick="setMetode('Gopay', this)" class="metode-btn p-3 rounded-2xl border border-amber-100 text-[9px] font-black uppercase tracking-widest transition-all">Gopay</button>
                </div>

                <div id="infoPembayaran" class="hidden animate-in fade-in duration-300">
                    <div class="p-4 bg-white rounded-3xl border border-amber-100 text-center shadow-inner">
                        <img id="displayQRIS" src="" class="hidden w-32 h-32 mx-auto mb-2 rounded-xl border border-amber-100 shadow-sm">
                        <p id="displayText" class="text-[10px] font-bold text-amber-950 whitespace-pre-line leading-relaxed italic"></p>
                    </div>
                </div>

                <div id="areaCash" class="space-y-4">
                    <input type="number" id="uangBayar" oninput="hitungKembalian()" class="w-full p-4 rounded-2xl border border-amber-100 bg-amber-50/30 text-2xl font-black text-amber-950 outline-none focus:border-amber-500" placeholder="0">
                    <div class="flex justify-between items-center p-4 bg-amber-50 rounded-2xl border border-amber-100 shadow-inner">
                        <span class="text-[10px] font-bold text-amber-900/40 uppercase tracking-widest">Kembali</span>
                        <span class="text-xl font-black text-amber-950" id="textKembalian">Rp 0</span>
                    </div>
                </div>

                <button onclick="simpanTransaksi()" id="btnSimpan" class="w-full wood-gradient text-amber-100 py-5 rounded-[2.5rem] font-black text-sm uppercase tracking-[0.2em] shadow-xl active:scale-95 transition-all">Selesaikan & Cetak</button>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        let keranjang = [];
        let totalBelanja = 0;
        let metodeTerpilih = 'Cash';

        const dataToko = {
            qris: '<?= $profil['qris_img'] ?? '' ?>',
            rekening: `<?= addslashes($profil['rekening'] ?? 'Belum diatur') ?>`,
            ewallet: `<?= addslashes($profil['ewallet'] ?? 'Belum diatur') ?>`
        };

        function tambahKeKeranjang(id, nama, harga) {
            const idx = keranjang.findIndex(i => i.id === id);
            if (idx > -1) keranjang[idx].qty++;
            else keranjang.push({ id, nama, harga, qty: 1 });
            renderKeranjang();
        }

        function updateQty(id, delta) {
            const idx = keranjang.findIndex(i => i.id === id);
            if (idx > -1) {
                keranjang[idx].qty += delta;
                if (keranjang[idx].qty <= 0) keranjang.splice(idx, 1);
            }
            renderKeranjang();
        }

        // PERBAIKAN: Fungsi rupiah() ditambahkan langsung di script agar tidak error jika config belum ada
        function formatRupiah(angka) {
            return 'Rp ' + angka.toLocaleString('id-ID');
        }

        function renderKeranjang() {
            const list = document.getElementById('keranjangList');
            if (keranjang.length === 0) {
                // Desain Placeholder Keranjang Kosong Premium
                list.innerHTML = `<div class="py-20 text-center opacity-20"><i data-lucide="shopping-basket" class="w-12 h-12 mx-auto mb-2 text-amber-500 stroke-[1.5]"></i><p class="text-[10px] font-bold uppercase tracking-widest italic text-amber-100">Belum ada pesanan</p></div>`;
                document.getElementById('totalBayar').innerText = 'Rp 0';
                lucide.createIcons();
                return;
            }
            totalBelanja = 0;
            list.innerHTML = '';
            keranjang.forEach(i => {
                totalBelanja += i.qty * i.harga;
                // Desain Item Keranjang Premium
                list.innerHTML += `<div class="flex justify-between items-center bg-white/5 p-4 rounded-3xl border border-white/5">
                    <div class="flex-1 truncate pr-4">
                        <h4 class="text-xs font-bold truncate text-amber-100">${i.nama}</h4>
                        <p class="text-[10px] text-amber-500 font-bold">${formatRupiah(i.harga)}</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button onclick="updateQty(${i.id}, -1)" class="w-8 h-8 flex items-center justify-center bg-amber-900 border border-amber-800 rounded-xl active:scale-90 transition-all"><i data-lucide="minus" class="w-3 h-3 text-amber-100 stroke-[3]"></i></button>
                        <span class="text-xs font-black w-4 text-center text-amber-100">${i.qty}</span>
                        <button onclick="updateQty(${i.id}, 1)" class="w-8 h-8 flex items-center justify-center bg-amber-900 border border-amber-800 rounded-xl active:scale-90 transition-all"><i data-lucide="plus" class="w-3 h-3 text-amber-100 stroke-[3]"></i></button>
                    </div>
                </div>`;
            });
            document.getElementById('totalBayar').innerText = formatRupiah(totalBelanja);
            lucide.createIcons();
        }

        function bukaModalBayar() {
            if (keranjang.length === 0) return Swal.fire({ icon: 'warning', title: 'Oopps!', text: 'Isi keranjang dulu!', confirmButtonColor: '#4a2c1d' });
            document.getElementById('modalTotal').innerText = formatRupiah(totalBelanja);
            document.getElementById('modalBayar').classList.remove('hidden');
        }

        function tutupModalBayar() { document.getElementById('modalBayar').classList.add('hidden'); }

        function setMetode(m, btn) {
            metodeTerpilih = m;
            document.querySelectorAll('.metode-btn').forEach(b => b.classList.remove('active-metode'));
            btn.classList.add('active-metode');
            
            const infoBox = document.getElementById('infoPembayaran');
            const qrisImg = document.getElementById('displayQRIS');
            const rekText = document.getElementById('displayText');
            const areaCash = document.getElementById('areaCash');
            const inputUang = document.getElementById('uangBayar');

            infoBox.classList.add('hidden');
            qrisImg.classList.add('hidden');
            rekText.classList.add('hidden');

            if(m === 'Cash') {
                areaCash.classList.remove('hidden');
                inputUang.disabled = false;
            } else {
                infoBox.classList.remove('hidden');
                inputUang.disabled = true;
                areaCash.classList.add('hidden');
                if(m === 'QRIS') {
                    if(dataToko.qris) { qrisImg.src = 'uploads/' + dataToko.qris; qrisImg.classList.remove('hidden'); }
                    else { rekText.innerText = "QRIS belum diset"; rekText.classList.remove('hidden'); }
                } else if(m === 'Bank') {
                    rekText.innerText = "TRANSFER KE:\n" + dataToko.rekening; rekText.classList.remove('hidden');
                } else {
                    rekText.innerText = "NOMOR " + m + ":\n" + dataToko.ewallet; rekText.classList.remove('hidden');
                }
            }
        }

        function hitungKembalian() {
            const bayar = parseInt(document.getElementById('uangBayar').value) || 0;
            const kembali = bayar - totalBelanja;
            document.getElementById('textKembalian').innerText = formatRupiah(kembali > 0 ? kembali : 0);
        }

        function simpanTransaksi() {
            const bayar = parseInt(document.getElementById('uangBayar').value) || 0;
            if(metodeTerpilih === 'Cash' && bayar < totalBelanja) return Swal.fire({ icon: 'error', title: 'Error', text: 'Uang tidak cukup!', confirmButtonColor: '#4a2c1d' });

            const btn = document.getElementById('btnSimpan');
            btn.disabled = true;
            btn.innerText = "MEMPROSES...";

            fetch('proses_transaksi.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    items: keranjang, 
                    total: totalBelanja, 
                    metode: metodeTerpilih, 
                    bayar: (metodeTerpilih === 'Cash' ? bayar : totalBelanja) 
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    const printUrl = 'print_struk.php?id=' + data.penjualan_id + '&autoprint=true';
                    window.open(printUrl, '_blank');
                    
                    Swal.fire({
                        icon: 'success', title: 'Berhasil!', text: 'Transaksi selesai & struk dicetak.',
                        confirmButtonColor: '#4a2c1d'
                    }).then(() => location.reload());
                } else {
                    btn.disabled = false;
                    btn.innerText = "Selesaikan & Cetak";
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message, confirmButtonColor: '#4a2c1d' });
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerText = "Selesaikan & Cetak";
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server.', confirmButtonColor: '#4a2c1d' });
            });
        }

        document.getElementById('searchProduk').addEventListener('input', function(e) {
            const v = e.target.value.toLowerCase();
            document.querySelectorAll('.produk-item').forEach(i => {
                i.style.display = i.getAttribute('data-nama').includes(v) ? 'flex' : 'none';
            });
        });

        function kosongkanKeranjang() { keranjang = []; renderKeranjang(); }
        renderKeranjang();
    </script>
</body>
</html>
