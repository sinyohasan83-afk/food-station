<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['tenant_logged_in']) && $_SESSION['tenant_logged_in'] === true) {
    header('Location: portal.php');
    exit;
}

$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/db.php';
    $nama     = trim($_POST['nama']             ?? '');
    $email    = trim($_POST['email']            ?? '');
    $telepon  = trim($_POST['telepon']          ?? '');
    $username = trim($_POST['username']         ?? '');
    $password = $_POST['password']             ?? '';
    $confirm  = $_POST['confirm_password']     ?? '';
    $minat    = isset($_POST['minat']) ? implode(',', array_map('trim', (array)$_POST['minat'])) : '';
    $catatan  = trim($_POST['catatan']          ?? '');

    if (empty($nama) || empty($email) || empty($username) || empty($password)) {
        $error = 'Harap isi semua field yang wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Username hanya boleh berisi huruf, angka, dan garis bawah.';
    } else {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS tenant_accounts (
                id          INT AUTO_INCREMENT PRIMARY KEY,
                nama        VARCHAR(100)  NOT NULL,
                email       VARCHAR(100)  NOT NULL UNIQUE,
                telepon     VARCHAR(20),
                username    VARCHAR(50)   NOT NULL UNIQUE,
                password    VARCHAR(255)  NOT NULL,
                minat_sewa  VARCHAR(100),
                catatan     TEXT,
                status      ENUM('aktif','nonaktif') DEFAULT 'aktif',
                created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $chk = $pdo->prepare("SELECT id FROM tenant_accounts WHERE username = ? OR email = ?");
            $chk->execute([$username, $email]);
            if ($chk->fetch()) {
                $error = 'Username atau email sudah terdaftar.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $ins = $pdo->prepare("INSERT INTO tenant_accounts (nama, email, telepon, username, password, minat_sewa, catatan) VALUES (?,?,?,?,?,?,?)");
                $ins->execute([$nama, $email, $telepon, $username, $hashed, $minat, $catatan]);
                $success = true;
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem. Pastikan database aktif dan coba lagi.';
        }
    }
}

$pageTitle = 'PT. Food Station — Daftar Penyewa';
$assetBase = '';
?>
<?php include 'includes/header.php'; ?>
<body class="bg-hero">

<button onclick="toggleTheme()" data-theme-toggle class="login-theme-btn" title="Ganti tema">
  <svg class="icon-moon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
  <svg class="icon-sun w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
</button>

<div class="login-wrap fade-up" style="padding: 2rem 1rem; align-items: flex-start; min-height: 100vh;">
  <div class="w-full max-w-lg" style="padding-top: 3rem; padding-bottom: 3rem;">

    <div class="login-card">

      <!-- Header -->
      <div class="text-center mb-7">
        <a href="index.php" class="inline-flex items-center justify-center mb-4">
          <img src="LOGO_FS.png" alt="Logo" class="h-12 w-auto object-contain" onerror="this.style.display='none'">
        </a>
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-600/15 border border-emerald-500/25 mb-3 mx-auto">
          <svg class="w-7 h-7 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
          </svg>
        </div>
        <h1 class="text-xl font-extrabold text-white mb-1">Daftar sebagai Penyewa</h1>
        <p class="text-white/40 text-xs">Buat akun untuk melihat ketersediaan dan informasi unit</p>
      </div>

      <!-- Pilihan unit -->
      <div class="grid grid-cols-3 gap-2 mb-6">
        <?php foreach ([
          ['label'=>'Gudang','dot'=>'bg-orange-500','color'=>'text-orange-400'],
          ['label'=>'Toko',  'dot'=>'bg-sky-500',   'color'=>'text-sky-400'],
          ['label'=>'Kantin','dot'=>'bg-emerald-500','color'=>'text-emerald-400'],
        ] as $u): ?>
        <div class="glass rounded-xl p-2.5 text-center">
          <div class="flex items-center justify-center gap-1 mb-1">
            <span class="w-1.5 h-1.5 rounded-full <?= $u['dot'] ?>"></span>
            <span class="text-[9px] font-black uppercase tracking-widest text-white/35"><?= $u['label'] ?></span>
          </div>
          <p class="text-[10px] text-white/30">Tersedia</p>
        </div>
        <?php endforeach; ?>
      </div>

      <?php if ($success): ?>
        <!-- Success State -->
        <div class="text-center py-8">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-500/15 border border-emerald-500/30 mb-4">
            <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <h2 class="text-lg font-extrabold text-white mb-2">Pendaftaran Berhasil!</h2>
          <p class="text-white/40 text-sm mb-6">Akun Anda telah dibuat. Silakan login untuk mengakses portal penyewa.</p>
          <a href="tenant_login.php" class="login-btn inline-block" style="background:linear-gradient(135deg,#059669,#10b981);">
            Login Sekarang
          </a>
        </div>

      <?php else: ?>

        <?php if ($error): ?>
          <div class="mb-5 bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 text-sm text-red-400 font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">

          <!-- Nama Lengkap -->
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5 ml-1">
              Nama Lengkap <span class="text-red-400">*</span>
            </label>
            <input type="text" name="nama" placeholder="Nama lengkap Anda"
                   value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                   class="login-input" required/>
          </div>

          <!-- Email & Telepon -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5 ml-1">
                Email <span class="text-red-400">*</span>
              </label>
              <input type="email" name="email" placeholder="email@anda.com"
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                     class="login-input" required/>
            </div>
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5 ml-1">No. Telepon</label>
              <input type="tel" name="telepon" placeholder="08xxxxxxxxxx"
                     value="<?= htmlspecialchars($_POST['telepon'] ?? '') ?>"
                     class="login-input"/>
            </div>
          </div>

          <!-- Username -->
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5 ml-1">
              Username <span class="text-red-400">*</span>
            </label>
            <input type="text" name="username" placeholder="Huruf, angka, garis bawah"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                   class="login-input" required autocomplete="username"/>
          </div>

          <!-- Password & Konfirmasi -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5 ml-1">
                Password <span class="text-red-400">*</span>
              </label>
              <input type="password" name="password" placeholder="Min. 6 karakter"
                     class="login-input" required autocomplete="new-password"/>
            </div>
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5 ml-1">
                Konfirmasi <span class="text-red-400">*</span>
              </label>
              <input type="password" name="confirm_password" placeholder="Ulangi password"
                     class="login-input" required autocomplete="new-password"/>
            </div>
          </div>

          <!-- Minat Sewa -->
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-2 ml-1">Minat Unit yang Disewa</label>
            <div class="flex gap-4 flex-wrap">
              <?php
              $selectedMinat = (array)($_POST['minat'] ?? []);
              $minatList = [
                ['value'=>'gudang','label'=>'Gudang','color'=>'orange'],
                ['value'=>'toko',  'label'=>'Toko',  'color'=>'sky'],
                ['value'=>'kantin','label'=>'Kantin', 'color'=>'emerald'],
              ];
              foreach ($minatList as $m):
              ?>
              <label class="flex items-center gap-2 cursor-pointer group">
                <input type="checkbox" name="minat[]" value="<?= $m['value'] ?>"
                       <?= in_array($m['value'], $selectedMinat) ? 'checked' : '' ?>
                       class="w-4 h-4 rounded cursor-pointer accent-indigo-500"/>
                <span class="text-sm text-white/60 font-semibold group-hover:text-white/90 transition-colors">
                  <?= $m['label'] ?>
                </span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Catatan -->
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5 ml-1">Catatan / Pertanyaan</label>
            <textarea name="catatan" rows="2"
                      placeholder="Tuliskan kebutuhan atau pertanyaan Anda (opsional)"
                      class="login-input resize-none" style="height:auto;"><?= htmlspecialchars($_POST['catatan'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="login-btn mt-1"
                  style="background:linear-gradient(135deg,#059669,#10b981); box-shadow:0 4px 20px rgba(16,185,129,0.3);">
            Daftar Sekarang
          </button>

        </form>

      <?php endif; ?>

      <div class="mt-5 pt-5 border-t border-white/5 text-center space-y-2">
        <p class="text-white/30 text-xs">
          Sudah punya akun?
          <a href="tenant_login.php" class="text-indigo-400 hover:text-indigo-300 font-bold transition-colors">Login di sini</a>
        </p>
        <p class="text-white/15 text-xs">
          <a href="index.php" class="hover:text-white/40 transition-colors">← Login Admin</a>
        </p>
      </div>

    </div>
  </div>
</div>

<script>
const THEME_KEY = 'fs_theme';
function applyTheme(t) {
  document.body.classList.toggle('light', t === 'light');
  document.documentElement.classList.toggle('light-preload', t === 'light');
  localStorage.setItem(THEME_KEY, t);
  document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
    btn.title = t === 'dark' ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap';
  });
}
function toggleTheme() { applyTheme(localStorage.getItem(THEME_KEY) === 'light' ? 'dark' : 'light'); }
applyTheme(localStorage.getItem(THEME_KEY) || 'dark');
</script>
</body>
</html>
