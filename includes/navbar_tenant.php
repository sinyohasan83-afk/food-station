<?php
$tenantNama     = $_SESSION['tenant_nama']     ?? 'Penyewa';
$tenantUsername = $_SESSION['tenant_username'] ?? '';
$initials = mb_strtoupper(mb_substr($tenantNama, 0, 1));
?>
<nav class="navbar sticky top-0 z-50 px-3 sm:px-6 py-3 flex items-center justify-between">
  <div class="flex items-center gap-2 sm:gap-3 min-w-0">
    <!-- Mobile sidebar toggle -->
    <button onclick="toggleSidebar()" class="md:hidden flex items-center justify-center w-10 h-10 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 transition-all flex-shrink-0" title="Menu" aria-label="Buka menu">
      <svg class="w-5 h-5 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <!-- Brand -->
    <a href="portal.php" class="flex items-center gap-3 group min-w-0">
      <div class="logo-ring flex-shrink-0">
        <img src="<?= $assetBase ?? '' ?>LOGO_FS.png" alt="Logo Food Station" class="h-9 w-auto object-contain"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
        <div class="hidden w-9 h-9 rounded-full bg-indigo-600 items-center justify-center font-black text-sm">FS</div>
      </div>
      <div class="leading-tight hidden sm:block truncate">
        <p class="font-extrabold text-base text-white tracking-wide whitespace-nowrap">PT. Food Station</p>
        <p class="text-[10px] text-emerald-400 font-bold tracking-[0.2em] uppercase whitespace-nowrap">Portal Penyewa</p>
      </div>
    </a>
  </div>

  <!-- Right Actions -->
  <div class="flex items-center gap-2 sm:gap-3">

    <!-- Badge Portal Penyewa -->
    <div class="hidden md:flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-3 py-1.5">
      <div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div>
      <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-400">Akses Terbatas</span>
    </div>

    <!-- Theme Toggle -->
    <button onclick="toggleTheme()" data-theme-toggle class="theme-toggle" title="Ganti ke Mode Terang">
      <svg class="icon-moon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
      </svg>
      <svg class="icon-sun w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
      </svg>
    </button>

    <!-- User avatar + dropdown -->
    <div class="relative" id="userToggle">
      <button onclick="toggleUser()" class="flex items-center gap-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl px-3 py-2 transition-all">
        <div class="w-7 h-7 rounded-full bg-emerald-600 flex items-center justify-center text-xs font-black text-white">
          <?= htmlspecialchars($initials) ?>
        </div>
        <span class="hidden md:block text-sm font-semibold truncate max-w-[120px]"><?= htmlspecialchars($tenantNama) ?></span>
        <svg class="w-4 h-4 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>

      <div id="userBox" class="notif-dropdown hidden absolute right-0 mt-2 w-52">
        <div class="pb-3 mb-3 border-b border-white/10">
          <p class="text-sm font-bold truncate"><?= htmlspecialchars($tenantNama) ?></p>
          <p class="text-[10px] text-white/40">@<?= htmlspecialchars($tenantUsername) ?></p>
          <div class="mt-1.5 inline-flex items-center gap-1.5 bg-emerald-500/10 border border-emerald-500/20 rounded-full px-2 py-0.5">
            <div class="w-1 h-1 rounded-full bg-emerald-400"></div>
            <span class="text-[9px] font-bold text-emerald-400 uppercase tracking-widest">Penyewa</span>
          </div>
        </div>
        <a href="<?= $assetBase ?? '' ?>logout_tenant.php"
           class="flex items-center gap-2 text-sm text-red-400 hover:text-red-300 font-semibold transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          Keluar
        </a>
      </div>
    </div>

  </div>
</nav>
