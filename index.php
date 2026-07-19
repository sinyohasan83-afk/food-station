<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Already logged in → redirect
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (empty($user) || empty($pass)) {
        $error = 'Username dan password harus diisi.';
    } else {
        require_once 'includes/db.php';
        try {
            $stmt = $pdo->prepare("SELECT id, username, password, nama, role, is_active FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$user]);
            $row = $stmt->fetch();

            $valid = false;
            if ($row && $row['is_active']) {
                if (str_starts_with($row['password'], '$2y$') || str_starts_with($row['password'], '$2a$')) {
                    // bcrypt
                    $valid = password_verify($pass, $row['password']);
                } else {
                    // hash lama (MD5) — otomatis upgrade ke bcrypt setelah login berhasil
                    $valid = ($row['password'] === md5($pass));
                    if ($valid) {
                        $upgrade = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $upgrade->execute([password_hash($pass, PASSWORD_DEFAULT), $row['id']]);
                    }
                }
            }

            if ($valid) {
                $_SESSION['logged_in']  = true;
                $_SESSION['username']   = $row['nama'];
                $_SESSION['user_role']  = $row['role'];
                $_SESSION['user_id']    = $row['id'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Username atau password salah, atau akun tidak aktif.';
            }
        } catch (PDOException $e) {
            $error = 'Koneksi database gagal. Coba lagi nanti.';
        }
    }
}

$pageTitle  = 'PT. Food Station — Login';
$assetBase  = '';
?>
<?php include 'includes/header.php'; ?>
<body class="bg-hero">

<!-- Tombol ganti tema di halaman login -->
<button onclick="toggleTheme()" data-theme-toggle
        class="login-theme-btn" title="Ganti tema">
  <svg class="icon-moon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
  </svg>
  <svg class="icon-sun w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
  </svg>
</button>

<div class="login-wrap fade-up">
  <div class="w-full max-w-lg">

    <!-- Card Login -->
    <div class="login-card mb-5">
      <!-- Logo -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-indigo-600/15 border border-indigo-500/25 mb-4 mx-auto">
          <img src="LOGO_FS.png" alt="Logo" class="h-14 w-auto object-contain"
               onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
          <span style="display:none;font-size:32px;font-weight:900;color:#818cf8">FS</span>
        </div>
        <h1 class="text-2xl font-extrabold tracking-widest uppercase text-white mb-1">Food Station</h1>
        <p class="text-white/40 text-sm">Management & Leasing System v2.1</p>
      </div>

      <!-- Stats Row -->
      <div class="grid grid-cols-3 gap-3 mb-8">
        <?php
        $stats = [
          ['label'=>'Gudang','val'=>'12','color'=>'text-orange-400','dot'=>'bg-orange-500'],
          ['label'=>'Toko',  'val'=>'5', 'color'=>'text-sky-400',   'dot'=>'bg-sky-500'],
          ['label'=>'Kantin','val'=>'3', 'color'=>'text-emerald-400','dot'=>'bg-emerald-500'],
        ];
        foreach ($stats as $s): ?>
          <div class="glass rounded-2xl p-3 text-center">
            <div class="flex items-center justify-center gap-1.5 mb-1">
              <span class="w-1.5 h-1.5 rounded-full <?= $s['dot'] ?>"></span>
              <span class="text-[9px] font-black uppercase tracking-widest text-white/35"><?= $s['label'] ?></span>
            </div>
            <p class="text-xl font-black <?= $s['color'] ?>"><?= $s['val'] ?></p>
            <p class="text-[9px] text-white/30 mt-0.5">Unit Tersedia</p>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Error -->
      <?php if ($error): ?>
        <div class="mb-4 bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 text-sm text-red-400 font-semibold flex items-center gap-2">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <!-- Form -->
      <form method="POST" class="space-y-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5 ml-1">Username</label>
          <input type="text" name="username" placeholder="Masukkan username"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                 class="login-input" required autocomplete="username"/>
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5 ml-1">Password</label>
          <div class="relative">
            <input type="password" name="password" placeholder="Masukkan password"
                   id="pwdInput" class="login-input pr-12" required autocomplete="current-password"/>
            <button type="button" onclick="togglePwd()" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition-colors">
              <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
          </div>
        </div>
        <button type="submit" class="login-btn mt-2">Masuk ke Panel Admin</button>
      </form>

      <p class="text-center text-white/20 text-xs mt-6">
        Akses terbatas untuk administrator PT. Food Station
      </p>

      <!-- Divider -->
      <div class="flex items-center gap-3 my-5">
        <div class="flex-1 border-t border-white/8"></div>
        <span class="text-[10px] text-white/20 font-semibold uppercase tracking-widest">atau</span>
        <div class="flex-1 border-t border-white/8"></div>
      </div>

      <!-- Akses Portal Penyewa -->
      <div class="bg-emerald-500/5 border border-emerald-500/15 rounded-2xl p-4 text-center">
        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-emerald-400/70 mb-3">Portal Penyewa</p>
        <p class="text-xs text-white/30 mb-4 leading-relaxed">
          Ingin melihat ketersediaan Gudang, Toko, atau Kantin?<br>
          Daftar atau login sebagai penyewa.
        </p>
        <div class="flex gap-2">
          <a href="register.php"
             class="flex-1 bg-emerald-500/15 hover:bg-emerald-500/25 border border-emerald-500/25 text-emerald-400 text-xs font-bold py-2.5 rounded-xl transition-all text-center">
            Daftar Penyewa
          </a>
          <a href="tenant_login.php"
             class="flex-1 bg-white/5 hover:bg-white/10 border border-white/10 text-white/60 hover:text-white/90 text-xs font-bold py-2.5 rounded-xl transition-all text-center">
            Login Penyewa
          </a>
        </div>
      </div>

    </div>

    <!-- Hubungi Kami -->
    <div class="login-card text-center" style="padding: 28px 32px;">
      <p class="text-[9px] font-black uppercase tracking-[0.25em] text-indigo-400 mb-5">Hubungi Layanan Kami</p>
      <div class="flex justify-center gap-5">
        <?php
        $contacts = [
          ['href'=>'https://wa.me/081290564483', 'target'=>'_blank',
           'label'=>'WhatsApp','color'=>'bg-emerald-500/15 border-emerald-500/25 text-emerald-400',
           'svg'=>'<path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.588-5.946 0-6.556 5.332-11.891 11.891-11.891 3.181 0 6.167 1.24 8.413 3.488 2.246 2.248 3.484 5.232 3.484 8.403 0 6.556-5.332 11.891-11.891 11.891-2.093 0-4.141-.544-5.945-1.587L0 24zm6.549-4.322l.379.225c1.43.85 3.097 1.298 4.795 1.298 4.991 0 9.051-4.06 9.051-9.052 0-2.42-.942-4.695-2.653-6.406-1.71-1.713-3.986-2.653-6.398-2.653-4.991 0-9.051 4.06-9.051 9.052 0 1.698.448 3.364 1.298 4.793l.247.417-1.001 3.657 3.733-.982z"/>',
           'fill'=>true],
          ['href'=>'mailto:info@foodstation.co.id','target'=>'',
           'label'=>'Email','color'=>'bg-sky-500/15 border-sky-500/25 text-sky-400',
           'svg'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
           'fill'=>false],
          ['href'=>'tel:+62211234567','target'=>'',
           'label'=>'Telepon','color'=>'bg-indigo-500/15 border-indigo-500/25 text-indigo-400',
           'svg'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>',
           'fill'=>false],
          ['href'=>'https://www.foodstation.id/','target'=>'_blank',
           'label'=>'Website','color'=>'bg-orange-500/15 border-orange-500/25 text-orange-400',
           'svg'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9h18"/>',
           'fill'=>false],
        ];
        foreach ($contacts as $c):
          $svgAttr = $c['fill'] ? 'fill="currentColor" viewBox="0 0 24 24"' : 'fill="none" stroke="currentColor" viewBox="0 0 24 24"';
        ?>
          <a href="<?= $c['href'] ?>" <?= $c['target'] ? 'target="'.$c['target'].'"' : '' ?>
             class="flex flex-col items-center gap-2.5 group">
            <div class="w-13 h-13 rounded-2xl border flex items-center justify-center <?= $c['color'] ?> transition-all group-hover:scale-110 group-hover:brightness-125" style="width:52px;height:52px;border-radius:14px;">
              <svg class="w-5 h-5" <?= $svgAttr ?>><?= $c['svg'] ?></svg>
            </div>
            <span class="text-[10px] font-bold text-white/50 group-hover:text-white/80 transition-colors"><?= $c['label'] ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

<script>
function togglePwd() {
  const inp = document.getElementById('pwdInput');
  inp.type = inp.type === 'password' ? 'text' : 'password';
}

// Theme engine (mini version untuk halaman login)
const THEME_KEY = 'fs_theme';
function applyTheme(t) {
  document.body.classList.toggle('light', t === 'light');
  document.documentElement.classList.toggle('light-preload', t === 'light');
  localStorage.setItem(THEME_KEY, t);
  document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
    btn.title = t === 'dark' ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap';
  });
}
function toggleTheme() {
  applyTheme(localStorage.getItem(THEME_KEY) === 'light' ? 'dark' : 'light');
}
// Terapkan tema yang tersimpan saat halaman dimuat
applyTheme(localStorage.getItem(THEME_KEY) || 'dark');
</script>
</body>
</html>
