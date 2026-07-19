<?php
require_once __DIR__ . '/db.php';
$currentPage = $_GET['page'] ?? 'home';

$countGudang = $countToko = $countKantin = 0;
$countUnreadPesan = 0;
$countBelumLengkap = 0;
$tenantId = (int)($_SESSION['tenant_id'] ?? 0);

if ($pdo) {
    try {
        $countGudang = (int)$pdo->query("SELECT COUNT(*) c FROM units WHERE kategori_id = 1")->fetch()['c'];
        $countToko   = (int)$pdo->query("SELECT COUNT(*) c FROM units WHERE kategori_id = 2")->fetch()['c'];
        $countKantin = (int)$pdo->query("SELECT COUNT(*) c FROM units WHERE kategori_id = 3")->fetch()['c'];
        if ($tenantId > 0) {
            $stmt = $pdo->prepare("SELECT COUNT(*) c FROM pesan WHERE tenant_account_id = ? AND pengirim = 'admin' AND is_read = 0");
            $stmt->execute([$tenantId]);
            $countUnreadPesan = (int)$stmt->fetch()['c'];

            $stmt2 = $pdo->prepare("SELECT COUNT(*) c FROM penyewa WHERE tenant_account_id = ? AND (nik_nib IS NULL OR alamat IS NULL)");
            $stmt2->execute([$tenantId]);
            $countBelumLengkap = (int)$stmt2->fetch()['c'];
        }
    } catch (PDOException $e) {
        // biarkan default 0 jika query gagal
    }
}
?>
<div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>
<aside class="sidebar w-64 flex-shrink-0 flex flex-col gap-1 overflow-y-auto p-4" id="sidebar">

  <div class="px-3 pt-2 pb-1">
    <p class="sidebar-label">Katalog Unit</p>
  </div>

  <a href="portal.php?page=gudang"
     class="sidebar-link <?= $currentPage === 'gudang' ? 'active' : '' ?>">
    <span class="sidebar-icon bg-orange-500/20 text-orange-400">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
    </span>
    <span>Gudang</span>
    <span class="ml-auto sidebar-count bg-orange-500/20 text-orange-400"><?= $countGudang ?></span>
  </a>

  <a href="portal.php?page=toko"
     class="sidebar-link <?= $currentPage === 'toko' ? 'active' : '' ?>">
    <span class="sidebar-icon bg-sky-500/20 text-sky-400">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
    </span>
    <span>Toko</span>
    <span class="ml-auto sidebar-count bg-sky-500/20 text-sky-400"><?= $countToko ?></span>
  </a>

  <!-- Kantin dengan submenu -->
  <div class="kantin-group">
    <button onclick="toggleKantin()"
            class="sidebar-link w-full <?= in_array($currentPage, ['kantin_gudang','kantin_toko']) ? 'active' : '' ?>">
      <span class="sidebar-icon bg-emerald-500/20 text-emerald-400">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
      </span>
      <span>Kantin</span>
      <span class="ml-auto sidebar-count bg-emerald-500/20 text-emerald-400"><?= $countKantin ?></span>
      <svg id="kantinArrow"
           class="w-4 h-4 ml-1 text-white/30 transition-transform <?= in_array($currentPage, ['kantin_gudang','kantin_toko']) ? 'rotate-180' : '' ?>"
           fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>
    <div id="kantinSub" class="submenu <?= in_array($currentPage, ['kantin_gudang','kantin_toko']) ? 'open' : '' ?>">
      <a href="portal.php?page=kantin_gudang"
         class="submenu-link <?= $currentPage === 'kantin_gudang' ? 'active' : '' ?>">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
        Area Pergudangan
      </a>
      <a href="portal.php?page=kantin_toko"
         class="submenu-link <?= $currentPage === 'kantin_toko' ? 'active' : '' ?>">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
        Area Pertokoan
      </a>
    </div>
  </div>

  <div class="mx-3 my-3 border-t border-white/5"></div>

  <div class="px-3 pb-1">
    <p class="sidebar-label">Komunikasi</p>
  </div>

  <a href="portal.php?page=pesan" class="sidebar-link <?= $currentPage === 'pesan' ? 'active' : '' ?>">
    <span class="sidebar-icon bg-teal-500/20 text-teal-400">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    </span>
    <span>Pesan Admin</span>
    <?php if ($countUnreadPesan > 0): ?>
      <span class="ml-auto sidebar-count bg-teal-500/20 text-teal-400"><?= $countUnreadPesan ?></span>
    <?php endif; ?>
  </a>

  <a href="portal.php?page=lengkapi" class="sidebar-link <?= $currentPage === 'lengkapi' ? 'active' : '' ?>">
    <span class="sidebar-icon bg-pink-500/20 text-pink-400">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
    </span>
    <span>Lengkapi Data</span>
    <?php if ($countBelumLengkap > 0): ?>
      <span class="ml-auto sidebar-count bg-pink-500/20 text-pink-400"><?= $countBelumLengkap ?></span>
    <?php endif; ?>
  </a>

  <!-- Spacer -->
  <div class="mx-3 my-3 border-t border-white/5"></div>

  <!-- Info akses terbatas -->
  <div class="px-3 mb-2">
    <div class="bg-amber-500/8 border border-amber-500/20 rounded-xl p-3">
      <div class="flex items-center gap-2 mb-1.5">
        <svg class="w-3.5 h-3.5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <p class="text-[9px] font-black uppercase tracking-widest text-amber-400">Akses Terbatas</p>
      </div>
      <p class="text-[10px] text-white/35 leading-relaxed">
        Menu Penagihan dan Data Penyewa hanya tersedia untuk Administrator.
      </p>
    </div>
  </div>

  <!-- Hubungi Admin -->
  <div class="px-3">
    <a href="https://wa.me/081290564483" target="_blank"
       class="flex items-center gap-2.5 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 rounded-xl p-3 transition-all group">
      <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.588-5.946 0-6.556 5.332-11.891 11.891-11.891 3.181 0 6.167 1.24 8.413 3.488 2.246 2.248 3.484 5.232 3.484 8.403 0 6.556-5.332 11.891-11.891 11.891-2.093 0-4.141-.544-5.945-1.587L0 24zm6.549-4.322l.379.225c1.43.85 3.097 1.298 4.795 1.298 4.991 0 9.051-4.06 9.051-9.052 0-2.42-.942-4.695-2.653-6.406-1.71-1.713-3.986-2.653-6.398-2.653-4.991 0-9.051 4.06-9.051 9.052 0 1.698.448 3.364 1.298 4.793l.247.417-1.001 3.657 3.733-.982z"/>
      </svg>
      <div>
        <p class="text-[9px] font-black uppercase tracking-widest text-emerald-400">Hubungi Kami</p>
        <p class="text-[10px] text-white/40">WhatsApp 081290564483</p>
      </div>
    </a>
  </div>

  <!-- Footer -->
  <div class="mt-auto pt-4 border-t border-white/5 px-2">
    <div class="bg-indigo-600/10 border border-indigo-500/20 rounded-2xl p-3 text-center">
      <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-1">Portal Penyewa</p>
      <p class="text-xs text-white/50">v2.3 — 2026</p>
    </div>
  </div>

</aside>
