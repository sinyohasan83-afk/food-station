<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['tenant_logged_in']) && $_SESSION['tenant_logged_in'] === true) {
    header('Location: portal.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/db.php';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, nama, username, password, status FROM tenant_accounts WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $tenant = $stmt->fetch();

            if ($tenant && $tenant['status'] === 'aktif' && password_verify($password, $tenant['password'])) {
                $_SESSION['tenant_logged_in'] = true;
                $_SESSION['tenant_id']        = $tenant['id'];
                $_SESSION['tenant_nama']      = $tenant['nama'];
                $_SESSION['tenant_username']  = $tenant['username'];
                header('Location: portal.php');
                exit;
            } else {
                $error = 'Username atau password salah, atau akun tidak aktif.';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem. Pastikan database aktif dan coba lagi.';
        }
    }
}

$pageTitle = 'PT. Food Station — Login Penyewa';
$assetBase = '';
?>
<?php include 'includes/header.php'; ?>
<body class="bg-hero">

<button onclick="toggleTheme()" data-theme-toggle class="login-theme-btn" title="Ganti tema">
  <svg class="icon-moon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
  <svg class="icon-sun w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
</button>

<div class="login-wrap fade-up">
  <div class="w-full max-w-lg">

    <!-- Card Login Penyewa -->
    <div class="login-card mb-5">

      <!-- Logo & Title -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-indigo-600/15 border border-indigo-500/25 mb-4 mx-auto">
          <img src="LOGO_FS.png" alt="Logo" class="h-14 w-auto object-contain"
               onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
          <span style="display:none;font-size:32px;font-weight:900;color:#818cf8">FS</span>
        </div>
        <h1 class="text-2xl font-extrabold tracking-widest uppercase text-white mb-1">Food Station</h1>
        <p class="text-white/40 text-sm">Portal Penyewa</p>
      </div>

      <!-- Badge Portal -->
      <div class="flex justify-center mb-6">
        <div class="flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/25 rounded-full px-4 py-1.5">
          <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
          <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-400">Akses Portal Penyewa</span>
        </div>
      </div>

      <!-- Info akses terbatas -->
      <div class="bg-white/3 border border-white/8 rounded-xl p-3 mb-6 flex items-start gap-3">
        <svg class="w-4 h-4 text-indigo-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-xs text-white/40 leading-relaxed">
          Portal ini memberikan akses untuk melihat ketersediaan <strong class="text-white/60">Gudang</strong>,
          <strong class="text-white/60">Toko</strong>, dan <strong class="text-white/60">Kantin</strong>.
          Untuk keperluan kontrak dan pembayaran, hubungi tim kami.
        </p>
      </div>

      <!-- Error -->
      <?php if ($error): ?>
        <div class="mb-5 bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 text-sm text-red-400 font-semibold flex items-center gap-2">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <!-- Form Login -->
      <form method="POST" class="space-y-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5 ml-1">Username</label>
          <input type="text" name="username" placeholder="Username Anda"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                 class="login-input" required autocomplete="username"/>
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5 ml-1">Password</label>
          <div class="relative">
            <input type="password" name="password" placeholder="Password Anda"
                   id="pwdInput" class="login-input pr-12" required autocomplete="current-password"/>
            <button type="button" onclick="togglePwd()"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition-colors">
              <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
          </div>
        </div>
        <button type="submit" class="login-btn mt-2"
                style="background:linear-gradient(135deg,#4f46e5,#7c3aed); box-shadow:0 4px 20px rgba(99,102,241,0.35);">
          Masuk ke Portal Penyewa
        </button>
      </form>

      <div class="mt-6 pt-5 border-t border-white/5 text-center space-y-2">
        <p class="text-white/30 text-xs">
          Belum punya akun?
          <a href="register.php" class="text-emerald-400 hover:text-emerald-300 font-bold transition-colors">Daftar sekarang</a>
        </p>
        <p class="text-white/15 text-xs">
          <a href="index.php" class="hover:text-white/40 transition-colors">← Login Admin</a>
        </p>
      </div>

    </div>

    <!-- Hubungi Kami -->
    <div class="login-card text-center" style="padding: 24px 32px;">
      <p class="text-[9px] font-black uppercase tracking-[0.25em] text-indigo-400 mb-4">Hubungi Layanan Kami</p>
      <div class="flex justify-center gap-5">
        <a href="https://wa.me/081290564483" target="_blank" class="flex flex-col items-center gap-2 group">
          <div class="w-12 h-12 rounded-2xl border border-emerald-500/25 bg-emerald-500/15 text-emerald-400 flex items-center justify-center transition-all group-hover:scale-110 group-hover:brightness-125">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.588-5.946 0-6.556 5.332-11.891 11.891-11.891 3.181 0 6.167 1.24 8.413 3.488 2.246 2.248 3.484 5.232 3.484 8.403 0 6.556-5.332 11.891-11.891 11.891-2.093 0-4.141-.544-5.945-1.587L0 24zm6.549-4.322l.379.225c1.43.85 3.097 1.298 4.795 1.298 4.991 0 9.051-4.06 9.051-9.052 0-2.42-.942-4.695-2.653-6.406-1.71-1.713-3.986-2.653-6.398-2.653-4.991 0-9.051 4.06-9.051 9.052 0 1.698.448 3.364 1.298 4.793l.247.417-1.001 3.657 3.733-.982z"/></svg>
          </div>
          <span class="text-[10px] font-bold text-white/50 group-hover:text-white/80 transition-colors">WhatsApp</span>
        </a>
        <a href="mailto:info@foodstation.co.id" class="flex flex-col items-center gap-2 group">
          <div class="w-12 h-12 rounded-2xl border border-sky-500/25 bg-sky-500/15 text-sky-400 flex items-center justify-center transition-all group-hover:scale-110 group-hover:brightness-125">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </div>
          <span class="text-[10px] font-bold text-white/50 group-hover:text-white/80 transition-colors">Email</span>
        </a>
        <a href="tel:+62211234567" class="flex flex-col items-center gap-2 group">
          <div class="w-12 h-12 rounded-2xl border border-indigo-500/25 bg-indigo-500/15 text-indigo-400 flex items-center justify-center transition-all group-hover:scale-110 group-hover:brightness-125">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
          </div>
          <span class="text-[10px] font-bold text-white/50 group-hover:text-white/80 transition-colors">Telepon</span>
        </a>
      </div>
    </div>

  </div>
</div>

<script>
function togglePwd() {
  const inp = document.getElementById('pwdInput');
  inp.type = inp.type === 'password' ? 'text' : 'password';
}
const THEME_KEY = 'fs_theme';
function applyTheme(t) {
  document.body.classList.toggle('light', t === 'light');
  document.documentElement.classList.toggle('light-preload', t === 'light');
  localStorage.setItem(THEME_KEY, t);
}
function toggleTheme() { applyTheme(localStorage.getItem(THEME_KEY) === 'light' ? 'dark' : 'light'); }
applyTheme(localStorage.getItem(THEME_KEY) || 'dark');
</script>
</body>
</html>
