<?php
include 'config.php';

// Proteksi: Hanya Owner yang bisa akses halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php"); exit;
}

// Ambil data semua kasir (Bukan owner)
$stmt = $pdo->prepare("SELECT id, username, nama_lengkap, created_at FROM users WHERE role = 'kasir' ORDER BY id DESC");
$stmt->execute();
$kasir = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <link rel="icon" href="uploads/ikon.png" type="image/x-icon/png">
    <title>Kelola Kasir - Djadoel POS</title>
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
            <a href="kelola_kasir.php" class="nav-active flex flex-col md:flex-row items-center justify-center md:justify-start md:space-x-4 py-2 md:p-3 rounded-2xl">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span class="text-[9px] md:text-sm font-bold mt-1 md:mt-0 uppercase font-black">Kasir</span>
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
        <div class="p-6 md:p-12 max-w-5xl mx-auto space-y-8">
            <div class="flex justify-between items-end">
                <div>
                    <h1 class="text-3xl font-black text-amber-950 italic">Kelola Kasir</h1>
                    <p class="text-amber-900/40 font-medium italic">ꦏꦼꦭꦺꦴꦭꦏꦱꦶꦂ</p>
                </div>
                <button onclick="openModal('tambah')" class="bg-amber-900 text-amber-100 px-6 py-3 rounded-2xl font-bold flex items-center space-x-2 shadow-xl hover:bg-amber-800 transition-all active:scale-95">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                    <span class="hidden sm:inline uppercase text-[10px] tracking-widest">Tambah Kasir</span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($kasir as $k): ?>
                <div class="glass-card p-6 rounded-[2.5rem] border border-amber-100 flex flex-col items-center text-center group">
                    <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="user" class="w-10 h-10 text-amber-900"></i>
                    </div>
                    <h3 class="font-bold text-lg text-amber-950"><?= htmlspecialchars($k['nama_lengkap']) ?></h3>
                    <p class="text-[10px] font-black text-amber-900/30 uppercase tracking-[0.2em] mb-4">@<?= htmlspecialchars($k['username']) ?></p>
                    
                    <div class="w-full flex space-x-2">
                        <button onclick='openEdit(<?= json_encode($k) ?>)' class="flex-1 py-3 bg-amber-50 text-amber-700 rounded-xl text-[10px] font-bold uppercase hover:bg-amber-100 transition-colors">Edit</button>
                        <button onclick="konfirmasiHapus(<?= $k['id'] ?>)" class="px-4 py-3 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if(empty($kasir)): ?>
                <div class="col-span-full py-20 text-center opacity-20 font-bold italic uppercase tracking-widest">Belum ada kasir yang terdaftar</div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <div id="modalKasir" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-amber-950/60 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="glass-card w-full max-w-md rounded-[2.5rem] relative z-10 shadow-2xl overflow-hidden animate-in zoom-in duration-200">
            <div class="wood-gradient p-6 text-amber-100 flex justify-between items-center">
                <h3 id="modalTitle" class="aksara text-2xl tracking-tighter">Data Kasir</h3>
                <button onclick="closeModal()" class="opacity-50 hover:opacity-100"><i data-lucide="x"></i></button>
            </div>
            <form action="proses_kasir.php" method="POST" class="p-8 space-y-4">
                <input type="hidden" name="action" id="formAction" value="tambah">
                <input type="hidden" name="id" id="formId">
                
                <div>
                    <label class="text-[10px] font-bold text-amber-900/40 uppercase ml-2 tracking-widest">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="formNama" required class="w-full p-4 rounded-2xl border border-amber-100 outline-none focus:border-amber-500 text-sm bg-amber-50/30">
                </div>
                
                <div>
                    <label class="text-[10px] font-bold text-amber-900/40 uppercase ml-2 tracking-widest">Username</label>
                    <input type="text" name="username" id="formUser" required class="w-full p-4 rounded-2xl border border-amber-100 outline-none focus:border-amber-500 text-sm bg-amber-50/30">
                </div>

                <div>
                    <label class="text-[10px] font-bold text-amber-900/40 uppercase ml-2 tracking-widest">Password</label>
                    <input type="password" name="password" id="formPass" placeholder="Isi jika ingin ganti..." class="w-full p-4 rounded-2xl border border-amber-100 outline-none focus:border-amber-500 text-sm bg-amber-50/30">
                </div>

                <button type="submit" class="w-full wood-gradient text-amber-100 py-5 rounded-3xl font-bold shadow-xl active:scale-95 transition-all mt-4 tracking-widest text-xs uppercase">Simpan Data Kasir</button>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function openModal(type) {
            document.getElementById('modalKasir').classList.remove('hidden');
            document.getElementById('formAction').value = type;
            document.getElementById('modalTitle').innerText = type === 'tambah' ? 'Kasir Baru' : 'Edit Kasir';
            if(type === 'tambah') {
                document.getElementById('formId').value = "";
                document.getElementById('formNama').value = "";
                document.getElementById('formUser').value = "";
                document.getElementById('formPass').required = true;
            } else {
                document.getElementById('formPass').required = false;
            }
        }

        function closeModal() { document.getElementById('modalKasir').classList.add('hidden'); }

        function openEdit(data) {
            openModal('edit');
            document.getElementById('formId').value = data.id;
            document.getElementById('formNama').value = data.nama_lengkap;
            document.getElementById('formUser').value = data.username;
            document.getElementById('formPass').value = ""; // Kosongkan password saat edit
        }

        function konfirmasiHapus(id) {
            Swal.fire({
                title: 'Hapus Kasir?', text: "Kasir ini tidak akan bisa login lagi!", icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#4a2c1d', confirmButtonText: 'Ya, Hapus!'
            }).then((result) => { if (result.isConfirmed) window.location.href = "proses_kasir.php?hapus=" + id; });
        }
    </script>
</body>
</html>
