<?php
require_once __DIR__ . '/../includes/data.php';
$penyewa = $appData['penyewa'];
$today   = date('Y-m-d');

$lunas    = array_filter($penyewa, fn($p) => $p['status'] === 'Lunas');
$menunggu = array_filter($penyewa, fn($p) => $p['status'] === 'Menunggu');
$terlambat= array_filter($penyewa, fn($p) => $p['status'] === 'Terlambat' || ($p['jatuhTempo'] < $today && $p['status'] !== 'Lunas'));
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
  <div>
    <div class="flex items-center gap-2 mb-1">
      <a href="dashboard.php" class="text-white/30 hover:text-white/60 text-xs font-semibold">Dashboard</a>
      <span class="text-white/20 text-xs">›</span>
      <span class="text-white/60 text-xs font-semibold">Data Penyewa</span>
    </div>
    <h1 class="text-2xl font-extrabold text-white">Direktori Penyewa</h1>
    <p class="text-white/35 text-sm mt-0.5"><?= count($penyewa) ?> penyewa terdaftar</p>
  </div>
  <!-- Export Dropdown -->
  <div class="relative" id="exportDropdown">
    <button onclick="toggleExportMenu()" class="flex items-center gap-2 bg-indigo-600/15 hover:bg-indigo-600/25 border border-indigo-500/25 text-indigo-400 px-4 py-2.5 rounded-xl text-xs font-bold transition-all">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      Export Data
      <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>
    <!-- Dropdown Menu -->
    <div id="exportMenu" class="hidden absolute right-0 mt-2 w-52 notif-dropdown z-50">
      <p class="text-[9px] font-black uppercase tracking-widest text-white/30 mb-3">Pilih Format Export</p>
      <div class="space-y-1.5">
        <a href="export/penyewa.php?format=excel" target="_blank"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/8 transition-colors text-sm font-semibold text-white/70 hover:text-white">
          <div class="w-8 h-8 rounded-lg bg-emerald-500/15 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          </div>
          <div>
            <p class="text-xs font-bold">Export Excel</p>
            <p class="text-[10px] text-white/30">Format .xls (Microsoft Excel)</p>
          </div>
        </a>
        <a href="export/penyewa.php?format=csv" target="_blank"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/8 transition-colors text-sm font-semibold text-white/70 hover:text-white">
          <div class="w-8 h-8 rounded-lg bg-sky-500/15 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          </div>
          <div>
            <p class="text-xs font-bold">Export CSV</p>
            <p class="text-[10px] text-white/30">Format .csv (universal)</p>
          </div>
        </a>
        <div class="my-2 border-t border-white/8"></div>
        <a href="export/penyewa.php?format=print" target="_blank"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/8 transition-colors text-sm font-semibold text-white/70 hover:text-white">
          <div class="w-8 h-8 rounded-lg bg-violet-500/15 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
          </div>
          <div>
            <p class="text-xs font-bold">Cetak / PDF</p>
            <p class="text-[10px] text-white/30">Buka halaman cetak</p>
          </div>
        </a>
      </div>
    </div>
  </div>

  <script>
  function toggleExportMenu() {
    document.getElementById('exportMenu').classList.toggle('hidden');
  }
  document.addEventListener('click', function(e) {
    if (!e.target.closest('#exportDropdown')) {
      document.getElementById('exportMenu').classList.add('hidden');
    }
  });
  </script>
</div>

<!-- Summary mini-cards -->
<div class="grid grid-cols-3 gap-4 mb-6">
  <div class="glass rounded-2xl p-4 border-l-4 border-emerald-500">
    <p class="text-[9px] font-black uppercase tracking-widest text-white/30 mb-1">Lunas</p>
    <p class="text-2xl font-black text-emerald-400"><?= count($lunas) ?></p>
  </div>
  <div class="glass rounded-2xl p-4 border-l-4 border-amber-500">
    <p class="text-[9px] font-black uppercase tracking-widest text-white/30 mb-1">Menunggu</p>
    <p class="text-2xl font-black text-amber-400"><?= count($menunggu) ?></p>
  </div>
  <div class="glass rounded-2xl p-4 border-l-4 border-red-500">
    <p class="text-[9px] font-black uppercase tracking-widest text-white/30 mb-1">Terlambat</p>
    <p class="text-2xl font-black text-red-400"><?= count($terlambat) ?></p>
  </div>
</div>

<!-- Search & Filter -->
<div class="flex flex-col sm:flex-row gap-3 mb-5">
  <div class="flex-1 flex items-center gap-2 glass border border-white/10 rounded-xl px-4 py-2.5">
    <svg class="w-4 h-4 text-white/30 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    <input type="text" id="searchInput" placeholder="Cari nama, unit, atau status…"
           class="bg-transparent text-sm text-white placeholder-white/25 outline-none flex-1"/>
  </div>
  <div class="flex flex-wrap gap-2">
    <button onclick="filterTable('all')"       data-filter="all"       class="filter-btn active px-4 py-2 rounded-xl text-xs font-bold transition-all">Semua</button>
    <button onclick="filterTable('Lunas')"     data-filter="Lunas"     class="filter-btn px-4 py-2 rounded-xl text-xs font-bold transition-all">Lunas</button>
    <button onclick="filterTable('Menunggu')"  data-filter="Menunggu"  class="filter-btn px-4 py-2 rounded-xl text-xs font-bold transition-all">Menunggu</button>
    <button onclick="filterTable('Terlambat')" data-filter="Terlambat" class="filter-btn px-4 py-2 rounded-xl text-xs font-bold transition-all">Terlambat</button>
  </div>
</div>

<!-- Table -->
<div class="glass rounded-3xl overflow-hidden">
  <div class="overflow-x-auto">
  <table class="data-table">
    <thead>
      <tr>
        <th class="text-left">Nama Penyewa</th>
        <th class="text-left">Unit</th>
        <th class="text-left">Tipe</th>
        <th class="text-center">Status</th>
        <th class="text-left">Nominal</th>
        <th class="text-left">Jatuh Tempo</th>
        <th class="text-center">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($penyewa as $p):
        $isOverdue = ($p['jatuhTempo'] < $today && $p['status'] !== 'Lunas');
        $displayStatus = $isOverdue ? 'Terlambat' : $p['status'];
        $badgeClass = match($displayStatus) {
          'Lunas'     => 'badge-green',
          'Menunggu'  => 'badge-yellow',
          'Terlambat' => 'badge-red',
          default     => 'badge-indigo',
        };
      ?>
        <tr class="table-row" data-status="<?= $displayStatus ?>">
          <td>
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-xl bg-indigo-500/20 flex items-center justify-center text-xs font-black text-indigo-400 flex-shrink-0">
                <?= strtoupper(substr($p['nama'], 0, 1)) ?>
              </div>
              <span class="font-bold text-white"><?= htmlspecialchars($p['nama']) ?></span>
            </div>
          </td>
          <td class="font-mono text-xs text-white/60"><?= htmlspecialchars($p['unit']) ?></td>
          <td>
            <span class="badge badge-sky"><?= $p['tipe'] ?></span>
          </td>
          <td class="text-center">
            <span class="badge <?= $badgeClass ?>"><?= $displayStatus ?></span>
          </td>
          <td class="font-semibold text-amber-400 text-xs"><?= htmlspecialchars($p['nominal']) ?></td>
          <td class="<?= $isOverdue ? 'text-red-400 font-bold' : 'text-white/50' ?> text-xs">
            <?= $p['jatuhTempo'] ?>
            <?php if ($isOverdue): ?>
              <span class="ml-1 badge badge-red" style="font-size:8px">Telat</span>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <button onclick="showToast('Detail penyewa: <?= htmlspecialchars($p['nama']) ?>','info')"
                    class="text-indigo-400 hover:text-indigo-300 text-xs font-bold transition-colors">Detail</button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<style>
.filter-btn { background:rgba(255,255,255,0.05); color:rgba(255,255,255,0.4); border:1px solid rgba(255,255,255,0.08); }
.filter-btn:hover { background:rgba(255,255,255,0.09); color:rgba(255,255,255,0.7); }
.filter-btn.active { background:rgba(99,102,241,0.2); color:#a5b4fc; border-color:rgba(99,102,241,0.35); }
</style>
