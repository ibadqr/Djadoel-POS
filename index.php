<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" href="uploads/ikon.png" type="image/x-icon/png">
    <title>Djadoel POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@600;800&family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #1a0f0a;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow: hidden;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bg-ornament {
            background-image: url('https://www.transparenttextures.com/patterns/black-linen.png');
            position: fixed;
            inset: 0;
            opacity: 0.4;
            z-index: -1;
        }
        .aksara { font-family: 'Crimson Pro', serif; }
        .safe-area {
            width: 100%;
            max-width: 400px;
            max-height: 95vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        /* Custom SweetAlert Style */
        .swal2-popup {
            background: #2d1b14 !important;
            color: #ffedca !important;
            border: 1px solid #78350f !important;
            border-radius: 2rem !important;
        }
    </style>
</head>
<body class="p-6">
    <div class="bg-ornament"></div>
    
    <div class="absolute top-[-10%] left-[-10%] w-[60%] h-[40%] rounded-full bg-amber-900/20 blur-[100px]"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[60%] h-[40%] rounded-full bg-amber-600/10 blur-[100px]"></div>

    <main class="relative z-10 safe-area">
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-[#2d1b14] border-2 border-amber-600/50 rounded-full flex items-center justify-center mx-auto shadow-[0_0_30px_rgba(197,160,89,0.2)]">
                <span class="aksara text-4xl text-amber-500">ꦝ</span>
            </div>
            <h1 class="aksara text-4xl text-amber-100 mt-4 tracking-tighter">ꦣ꧀ꦗꦣꦺꦴꦮꦺꦭ꧀</h1>
            <p class="text-amber-700 text-[10px] tracking-[0.3em] uppercase mt-1 font-semibold">Sistem Kasir UMKM</p>
        </div>

        <div class="bg-[#2d1b14]/90 backdrop-blur-2xl border border-amber-900/40 rounded-[2rem] p-8 shadow-2xl">
            <form action="auth.php" method="POST" class="space-y-5">
                <div class="space-y-1.5">
                    <label class="block text-amber-500/60 text-[10px] font-bold uppercase tracking-[0.2em] ml-1">Username</label>
                    <input type="text" name="username" required autocomplete="off"
                        class="w-full bg-black/40 border border-amber-900/50 rounded-xl px-5 py-3.5 text-amber-50 text-sm focus:outline-none focus:border-amber-500/50 transition-all placeholder-amber-900/30"
                        placeholder="username...">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-amber-500/60 text-[10px] font-bold uppercase tracking-[0.2em] ml-1">Password</label>
                    <input type="password" name="password" required
                        class="w-full bg-black/40 border border-amber-900/50 rounded-xl px-5 py-3.5 text-amber-50 text-sm focus:outline-none focus:border-amber-500/50 transition-all placeholder-amber-900/30"
                        placeholder="••••••••">
                </div>

                <button type="submit" 
                    class="w-full bg-gradient-to-r from-amber-800 to-amber-900 hover:from-amber-700 hover:to-amber-800 text-amber-100 font-bold py-4 rounded-xl shadow-xl transition-all active:scale-[0.97] flex items-center justify-center space-x-3 mt-4">
                    <span class="tracking-widest uppercase text-xs">Masuk Sistem</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>
        </div>

        <footer class="mt-8 text-center">
            <p class="text-amber-900/40 text-[9px] tracking-[0.4em] uppercase font-bold">
               Djadoel POS v1.4 ©2026
            </p>
        </footer>
    </main>

    <?php if(isset($_GET['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Maaf!',
            text: 'Username atau password Anda salah!',
            confirmButtonColor: '#92400e',
        });
    </script>
    <?php endif; ?>
</body>
</html>
