<?php
include 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php"); exit;
}

// Fitur: Ambil kategori untuk dropdown (Sesuai kode asli)
$kategori = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();

// Fitur: Pencarian (Sesuai kode asli)
$search = $_GET['search'] ?? '';
$stmt = $pdo->prepare("SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id WHERE p.nama_produk LIKE ? ORDER BY p.id DESC");
$stmt->execute(["%$search%"]);
$produk = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <link rel="icon" href="uploads/ikon.png" type="image/x-icon/png">
    <title>Manajemen Produk - Djadoel POS</title>
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

        .img-produk { aspect-ratio: 1 / 1; width: 100%; object-fit: cover; border-radius: 0.75rem; }
    </style>
</head>
<body class="flex flex-col md:flex-row">

    <aside class="fixed md:relative bottom-0 left-0 w-full md:w-72 wood-gradient h-[75px] md:h-screen text-amber-100 flex md:flex-col justify-between md:justify-start z-50 px-2 md:p-6 pb-[env(safe-area-inset-bottom)]">
        <div class="hidden md:flex items-center space-x-3 mb-10">
            <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center shadow-lg"><i data-lucide="crown" class="text-amber-950 w-6 h-6"></i></div>
            <h2 class="aksara text-2xl tracking-tighter text-amber-100">ꦣ꧀ꦗꦣꦺꦴꦮꦺꦭ꧀</h2>
        </div>
        <nav class="flex md:flex-col flex-1 justify-around md:justify-start md:space-y-2 w-full">
            <a href="dashboard_owner.php" class="flex flex-col md:flex-row items-center justify-center md:justify-start md:space-x-4 py-2 md:p-3 text-amber-100/50 rounded-2xl">
                <i data-lucide="home" class="w-5 h-5"></i>
                <span class="text-[9px] md:text-sm font-bold mt-1 md:mt-0 uppercase">Home</span>
            </a>
            <a href="stok_produk.php" class="nav-active flex flex-col md:flex-row items-center justify-center md:justify-start md:space-x-4 py-2 md:p-3 rounded-2xl">
                <i data-lucide="package" class="w-5 h-5"></i>
                <span class="text-[9px] md:text-sm font-bold mt-1 md:mt-0 uppercase font-black">Produk</span>
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
        <div class="p-6 md:p-12 max-w-6xl mx-auto space-y-6">
            <div class="flex justify-between items-end">
                <div>
                    <h1 class="text-3xl font-black text-amber-950 italic">Stok Barang</h1>
                    <p class="text-amber-900/40 font-medium italic">ꦩꦤꦗꦺꦩꦺꦤ꧀ꦱ꧀ꦠꦺꦴꦏ꧀</p>
                </div>
                <button onclick="openModal('tambah')" class="bg-amber-900 text-amber-100 px-5 py-3 rounded-2xl font-bold flex items-center space-x-2 shadow-xl hover:bg-amber-800 transition-all active:scale-95">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    <span class="hidden sm:inline uppercase text-xs tracking-widest">Tambah</span>
                </button>
            </div>

            <form method="GET" class="flex gap-2">
                <div class="relative flex-1">
                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-amber-900/30"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Temukan produk..." class="w-full pl-12 pr-4 py-4 rounded-3xl border border-amber-100 glass-card text-sm outline-none shadow-inner focus:border-amber-500 transition-all">
                </div>
                <button type="submit" class="bg-amber-100 text-amber-950 px-8 rounded-3xl font-bold text-xs uppercase tracking-widest hover:bg-amber-200">Filter</button>
            </form>

            <div class="glass-card rounded-[2.5rem] overflow-hidden border border-amber-100 shadow-xl shadow-amber-900/5">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-amber-50/50 text-amber-900/40 text-[10px] font-bold uppercase tracking-widest border-b border-amber-50">
                            <tr>
                                <th class="py-6 px-6">Produk</th>
                                <th class="py-6 px-4">Kategori</th>
                                <th class="py-6 px-4 text-center">Stok</th>
                                <th class="py-6 px-4 text-right">Harga Jual</th>
                                <th class="py-6 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-50">
                            <?php foreach($produk as $p): ?>
                            <tr class="hover:bg-amber-50/40 transition-all group">
                                <td class="py-5 px-6">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 flex-shrink-0">
                                            <img src="uploads/<?= $p['gambar'] ?>" class="img-produk shadow-md">
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-amber-950 leading-none"><?= htmlspecialchars($p['nama_produk']) ?></p>
                                            <p class="text-[10px] text-amber-900/30 font-bold uppercase mt-1">Beli: <?= rupiah($p['harga_beli']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-5 px-4"><span class="text-[10px] font-black text-amber-800/40 uppercase bg-amber-100/30 px-2 py-1 rounded-md"><?= htmlspecialchars($p['nama_kategori'] ?: 'Umum') ?></span></td>
                                <td class="py-5 px-4 text-center">
                                    <span class="px-3 py-1 rounded-lg text-xs font-black <?= $p['stok'] <= 5 ? 'bg-red-100 text-red-600 animate-pulse' : 'bg-green-100 text-green-700' ?>">
                                        <?= $p['stok'] ?>
                                    </span>
                                </td>
                                <td class="py-5 px-4 text-right font-black text-amber-950 text-sm"><?= rupiah($p['harga_jual']) ?></td>
                                <td class="py-5 px-6 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <button onclick='openEdit(<?= json_encode($p) ?>)' class="p-2.5 bg-amber-100 text-amber-700 rounded-xl hover:bg-amber-200 transition-colors"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                                        <button onclick="konfirmasiHapus(<?= $p['id'] ?>)" class="p-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <div id="modalProduk" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-amber-950/60 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="glass-card w-full max-w-lg rounded-[2.5rem] relative z-10 shadow-2xl overflow-hidden animate-in zoom-in duration-200">
            <div class="wood-gradient p-6 text-amber-100 flex justify-between items-center">
                <h3 id="modalTitle" class="aksara text-2xl tracking-tighter">Form Produk</h3>
                <button onclick="closeModal()" class="opacity-50 hover:opacity-100"><i data-lucide="x"></i></button>
            </div>
            <form action="proses_produk.php" method="POST" enctype="multipart/form-data" class="p-8 space-y-4 max-h-[75vh] overflow-y-auto">
                <input type="hidden" name="action" id="formAction" value="tambah">
                <input type="hidden" name="id" id="formId">
                <div>
                    <label class="text-[10px] font-bold text-amber-900/40 uppercase ml-2 tracking-widest">Nama Barang</label>
                    <input type="text" name="nama_produk" id="formNama" required class="w-full p-4 rounded-2xl border border-amber-100 outline-none focus:border-amber-500 text-sm bg-amber-50/30">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="text-[10px] font-bold text-amber-900/40 uppercase ml-2 tracking-widest">Kategori</label>
                        <div class="flex gap-2">
                            <select name="kategori_id" id="formKategori" class="flex-1 p-4 rounded-2xl border border-amber-100 text-sm bg-white outline-none">
                                <?php foreach($kategori as $k): ?><option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option><?php endforeach; ?>
                            </select>
                            <button type="button" onclick="openModalKategori()" class="bg-amber-100 text-amber-900 px-3 rounded-2xl hover:bg-amber-200 transition-all"><i data-lucide="plus" class="w-4 h-4"></i></button>
                        </div>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="text-[10px] font-bold text-amber-900/40 uppercase ml-2 tracking-widest">Stok</label>
                        <input type="number" name="stok" id="formStok" required class="w-full p-4 rounded-2xl border border-amber-100 text-sm outline-none bg-amber-50/30">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-amber-900/40 uppercase ml-2 tracking-widest">Harga Beli</label>
                        <input type="number" name="harga_beli" id="formBeli" required class="w-full p-4 rounded-2xl border border-amber-100 text-sm outline-none bg-amber-50/30">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-amber-900/40 uppercase ml-2 tracking-widest">Harga Jual</label>
                        <input type="number" name="harga_jual" id="formJual" required class="w-full p-4 rounded-2xl border border-amber-100 text-sm outline-none bg-amber-50/30">
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-amber-900/40 uppercase ml-2 tracking-widest">Ganti Foto (Opsional)</label>
                    <input type="file" name="gambar" class="w-full text-xs text-amber-900/40 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-amber-100 file:text-amber-900">
                </div>
                <button type="submit" class="w-full wood-gradient text-amber-100 py-5 rounded-3xl font-bold shadow-xl active:scale-95 transition-all mt-4 tracking-widest text-xs uppercase">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <div id="modalKategori" class="fixed inset-0 z-[110] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-amber-950/60 backdrop-blur-sm" onclick="closeModalKategori()"></div>
        <div class="glass-card w-full max-w-xs rounded-[2rem] relative z-10 p-8 shadow-2xl animate-in zoom-in duration-200">
            <h3 class="aksara text-2xl mb-4 text-amber-950 italic">Kategori Anyar</h3>
            <input type="text" id="nama_kategori_baru" class="w-full p-4 rounded-2xl border border-amber-100 outline-none mb-6 text-sm" placeholder="Nama kategori...">
            <div class="flex gap-2">
                <button id="btnSimpanKategori" class="flex-1 bg-amber-900 text-white py-3 rounded-2xl text-[10px] font-bold uppercase tracking-widest shadow-lg active:scale-95 transition-all">Simpan</button>
                <button onclick="closeModalKategori()" class="px-4 text-[10px] font-bold text-amber-900/40 uppercase tracking-widest">Batal</button>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // JS Logic (Sesuai kode asli Juragan)
        function openModal(type) {
            document.getElementById('modalProduk').classList.remove('hidden');
            document.getElementById('formAction').value = type;
            document.getElementById('modalTitle').innerText = type === 'tambah' ? 'Tambah Produk' : 'Edit Produk';
            if(type === 'tambah') { 
                document.getElementById('formId').value = ""; 
                document.getElementById('formNama').value = "";
                document.getElementById('formStok').value = "";
                document.getElementById('formBeli').value = "";
                document.getElementById('formJual').value = "";
            }
        }
        function closeModal() { document.getElementById('modalProduk').classList.add('hidden'); }

        function openEdit(data) {
            openModal('edit');
            document.getElementById('formId').value = data.id;
            document.getElementById('formNama').value = data.nama_produk;
            document.getElementById('formKategori').value = data.kategori_id;
            document.getElementById('formStok').value = data.stok;
            document.getElementById('formBeli').value = data.harga_beli;
            document.getElementById('formJual').value = data.harga_jual;
        }

        function openModalKategori() { document.getElementById('modalKategori').classList.remove('hidden'); }
        function closeModalKategori() { document.getElementById('modalKategori').classList.add('hidden'); document.getElementById('nama_kategori_baru').value = ""; }

        document.getElementById('btnSimpanKategori').onclick = function() {
            const nama = document.getElementById('nama_kategori_baru').value;
            if(!nama) return;
            fetch('proses_kategori.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'nama_kategori=' + encodeURIComponent(nama)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    const select = document.getElementById('formKategori');
                    const opt = document.createElement('option');
                    opt.value = data.id; opt.text = data.nama;
                    select.add(opt); select.value = data.id;
                    closeModalKategori();
                    Swal.fire({ icon: 'success', title: 'Ditambahkan!', showConfirmButton: false, timer: 1000 });
                }
            });
        };

        function konfirmasiHapus(id) {
            Swal.fire({
                title: 'Hapus Barang?', text: "Data bakal ilang selamanya!", icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#4a2c1d', cancelButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
            }).then((result) => { if (result.isConfirmed) window.location.href = "proses_produk.php?hapus=" + id; });
        }
    </script>
</body>
</html>
