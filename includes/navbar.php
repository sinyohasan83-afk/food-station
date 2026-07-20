<?php
require_once __DIR__ . '/db.php';

$overdue = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT p.nama, u.kode AS kode_unit, u.nama AS nama_unit, t.tanggal_jatuh_tempo
            FROM tagihan t
            JOIN penyewa p ON p.id = t.penyewa_id
            JOIN units u   ON u.id = t.unit_id
            WHERE t.status != 'Lunas' AND t.tanggal_jatuh_tempo < CURDATE()
            ORDER BY t.tanggal_jatuh_tempo ASC
            LIMIT 10
        ");
        $overdue = array_map(fn($r) => [
            'nama' => $r['nama'],
            'unit' => $r['kode_unit'] . ' – ' . $r['nama_unit'],
            'jatuhTempo' => $r['tanggal_jatuh_tempo'],
        ], $stmt->fetchAll());
    } catch (PDOException $e) {
        $overdue = [];
    }
}
?>
<nav class="navbar sticky top-0 z-50 px-3 sm:px-6 py-3 flex items-center justify-between">
  <div class="flex items-center gap-2 sm:gap-3 min-w-0">
    <!-- Mobile sidebar toggle -->
    <button onclick="toggleSidebar()" class="md:hidden flex items-center justify-center w-10 h-10 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 transition-all flex-shrink-0" title="Menu" aria-label="Buka menu">
      <svg class="w-5 h-5 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <!-- Brand -->
    <a href="dashboard.php" class="flex items-center gap-3 group min-w-0">
      <div class="logo-ring flex-shrink-0">
        <img src="<?= $assetBase ?? '' ?>LOGO_FS.png" alt="Logo Food Station" class="h-9 w-auto object-contain" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
        <div class="hidden w-9 h-9 rounded-full bg-indigo-600 items-center justify-center font-black text-sm">FS</div>
      </div>
      <div class="leading-tight hidden sm:block truncate">
        <p class="font-extrabold text-base text-white tracking-wide whitespace-nowrap">PT. Food Station</p>
        <p class="text-[10px] text-indigo-400 font-bold tracking-[0.2em] uppercase whitespace-nowrap">Management System</p>
      </div>
    </a>
  </div>

  <!-- Right Actions -->
  <div class="flex items-center gap-2 sm:gap-3">
    <!-- Search bar (desktop) -->
    <form action="search.php" method="GET" class="hidden md:flex items-center gap-2 bg-white/5 border border-white/10 rounded-xl px-3 py-2">
      <svg class="w-4 h-4 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      <input type="text" name="q" placeholder="Cari unit atau penyewa…"
             value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
             class="bg-transparent text-sm text-white placeholder-white/30 outline-none w-44"/>
    </form>

    <!-- Theme Toggle -->
    <button onclick="toggleTheme()" data-theme-toggle
            class="theme-toggle" title="Ganti ke Mode Terang">
      <!-- Ikon Bulan (dark mode aktif) -->
      <svg class="icon-moon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
      </svg>
      <!-- Ikon Matahari (light mode aktif) -->
      <svg class="icon-sun w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
      </svg>
    </button>

    <!-- Notifications -->
    <div class="relative" id="notifToggle">
      <button onclick="toggleNotif()" class="relative w-10 h-10 flex items-center justify-center rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 transition-all">
        <svg class="w-5 h-5 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <?php if (count($overdue) > 0): ?>
          <span class="notif-badge absolute -top-1 -right-1"><?= count($overdue) ?></span>
        <?php endif; ?>
      </button>

      <!-- Dropdown -->
      <div id="notifBox" class="notif-dropdown hidden absolute right-0 mt-2 w-80">
        <div class="flex items-center justify-between mb-4">
          <h4 class="text-xs font-bold text-white/70 uppercase tracking-widest">Notifikasi</h4>
          <?php if (count($overdue) > 0): ?>
            <span class="text-[10px] font-bold bg-red-500/20 text-red-400 px-2 py-0.5 rounded-full"><?= count($overdue) ?> belum dibayar</span>
          <?php endif; ?>
        </div>
        <div class="space-y-2">
          <?php if (empty($overdue)): ?>
            <p class="text-center text-white/30 text-xs py-4">Tidak ada notifikasi</p>
          <?php else: foreach ($overdue as $p): ?>
            <div class="notif-item">
              <div class="notif-icon-warn">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs font-bold text-white truncate"><?= htmlspecialchars($p['nama']) ?></p>
                <p class="text-[10px] text-white/40"><?= htmlspecialchars($p['unit']) ?> — Jatuh tempo <?= $p['jatuhTempo'] ?></p>
              </div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>

    <!-- User avatar + logout -->
    <div class="relative" id="userToggle">
      <button onclick="toggleUser()" class="flex items-center gap-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl px-3 py-2 transition-all">
        <div class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center text-xs font-black">A</div>
        <span class="hidden md:block text-sm font-semibold">Admin</span>
        <svg class="w-4 h-4 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div id="userBox" class="notif-dropdown hidden absolute right-0 mt-2 w-48">
        <div class="pb-3 mb-3 border-b border-white/10">
          <p class="text-sm font-bold">Administrator</p>
          <p class="text-[10px] text-white/40">admin@foodstation.id</p>
        </div>
        <a href="<?= $assetBase ?? '' ?>logout.php" class="flex items-center gap-2 text-sm text-red-400 hover:text-red-300 font-semibold transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          Logout
        </a>
      </div>
    </div>
  </div>
</nav>
