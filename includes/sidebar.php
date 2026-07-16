<?php $currentPage = $_GET['page'] ?? 'home'; ?>
<div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>
<aside class="sidebar w-64 flex-shrink-0 flex flex-col gap-1 overflow-y-auto p-4" id="sidebar">
  <div class="px-3 pt-2 pb-1">
    <p class="sidebar-label">Katalog Unit</p>
  </div>

  <a href="dashboard.php?page=gudang" class="sidebar-link <?= $currentPage === 'gudang' ? 'active' : '' ?>">
    <span class="sidebar-icon bg-orange-500/20 text-orange-400">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
    </span>
    <span>Gudang</span>
    <span class="ml-auto sidebar-count bg-orange-500/20 text-orange-400">40</span>
  </a>

  <a href="dashboard.php?page=toko" class="sidebar-link <?= $currentPage === 'toko' ? 'active' : '' ?>">
    <span class="sidebar-icon bg-sky-500/20 text-sky-400">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
    </span>
    <span>Toko</span>
    <span class="ml-auto sidebar-count bg-sky-500/20 text-sky-400">25</span>
  </a>

  <!-- Kantin with submenu -->
  <div class="kantin-group">
    <button onclick="toggleKantin()" class="sidebar-link w-full <?= in_array($currentPage, ['kantin_gudang','kantin_toko']) ? 'active' : '' ?>">
      <span class="sidebar-icon bg-emerald-500/20 text-emerald-400">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
      </span>
      <span>Kantin</span>
      <span class="ml-auto sidebar-count bg-emerald-500/20 text-emerald-400">10</span>
      <svg id="kantinArrow" class="w-4 h-4 ml-1 text-white/30 transition-transform <?= in_array($currentPage, ['kantin_gudang','kantin_toko']) ? 'rotate-180' : '' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>
    <div id="kantinSub" class="submenu <?= in_array($currentPage, ['kantin_gudang','kantin_toko']) ? 'open' : '' ?>">
      <a href="dashboard.php?page=kantin_gudang" class="submenu-link <?= $currentPage === 'kantin_gudang' ? 'active' : '' ?>">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
        Area Pergudangan
      </a>
      <a href="dashboard.php?page=kantin_toko" class="submenu-link <?= $currentPage === 'kantin_toko' ? 'active' : '' ?>">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
        Area Pertokoan
      </a>
    </div>
  </div>

  <div class="mx-3 my-3 border-t border-white/5"></div>

  <div class="px-3 pb-1">
    <p class="sidebar-label">Manajemen</p>
  </div>

  <a href="dashboard.php?page=tagihan" class="sidebar-link <?= $currentPage === 'tagihan' ? 'active' : '' ?>">
    <span class="sidebar-icon bg-violet-500/20 text-violet-400">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
    </span>
    <span>Penagihan</span>
  </a>

  <a href="dashboard.php?page=penyewa" class="sidebar-link <?= $currentPage === 'penyewa' ? 'active' : '' ?>">
    <span class="sidebar-icon bg-pink-500/20 text-pink-400">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    </span>
    <span>Data Penyewa</span>
  </a>

  <!-- Footer info -->
  <div class="mt-auto pt-4 border-t border-white/5 px-2">
    <div class="bg-indigo-600/10 border border-indigo-500/20 rounded-2xl p-3 text-center">
      <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-1">Versi Sistem</p>
      <p class="text-xs text-white/50">v2.3 — 2026</p>
    </div>
  </div>
</aside>
