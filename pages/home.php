<?php
require_once __DIR__ . '/../includes/data.php';
$s = $appData['stats'];
$totalUnit = $s['gudang']['total'] + $s['toko']['total'] + $s['kantin']['total'];
$totalTerisi = $s['gudang']['terisi'] + $s['toko']['terisi'] + $s['kantin']['terisi'];
?>

<!-- Page Header -->
<div class="mb-8">
  <p class="text-white/30 text-xs font-semibold uppercase tracking-widest mb-1">Dashboard</p>
  <h1 class="text-2xl sm:text-3xl font-extrabold text-white">Ringkasan Operasional</h1>
  <p class="text-white/35 text-sm mt-1">Selamat datang kembali, Administrator. Berikut kondisi unit per hari ini.</p>
</div>

<!-- Stat Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
  <!-- Total Penyewa -->
  <div class="stat-card glass">
    <div class="flex items-start justify-between mb-4">
      <div class="w-11 h-11 rounded-2xl bg-indigo-500/20 flex items-center justify-center">
        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      </div>
      <span class="badge badge-indigo">+4 bln ini</span>
    </div>
    <p class="text-3xl font-black text-white" data-count="128">0</p>
    <p class="text-xs text-white/35 mt-1 font-semibold uppercase tracking-wide">Total Penyewa Aktif</p>
  </div>

  <!-- Revenue -->
  <div class="stat-card glass">
    <div class="flex items-start justify-between mb-4">
      <div class="w-11 h-11 rounded-2xl bg-emerald-500/20 flex items-center justify-center">
        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <span class="badge badge-green">↑ 6%</span>
    </div>
    <p class="text-3xl font-black text-emerald-400">450<span class="text-lg text-white/50 font-semibold">jt</span></p>
    <p class="text-xs text-white/35 mt-1 font-semibold uppercase tracking-wide">Estimasi Revenue / Bln</p>
  </div>

  <!-- Unit Tersedia -->
  <div class="stat-card glass">
    <div class="flex items-start justify-between mb-4">
      <div class="w-11 h-11 rounded-2xl bg-amber-500/20 flex items-center justify-center">
        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
      </div>
      <span class="badge badge-yellow">dari <?= $totalUnit ?></span>
    </div>
    <p class="text-3xl font-black text-amber-400" data-count="20">0</p>
    <p class="text-xs text-white/35 mt-1 font-semibold uppercase tracking-wide">Unit Tersedia</p>
  </div>

  <!-- Kontrak Aktif -->
  <div class="stat-card glass">
    <div class="flex items-start justify-between mb-4">
      <div class="w-11 h-11 rounded-2xl bg-pink-500/20 flex items-center justify-center">
        <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      </div>
      <span class="badge" style="background:rgba(236,72,153,0.15);color:#f472b6;">Aktif</span>
    </div>
    <p class="text-3xl font-black text-pink-400" data-count="55">0</p>
    <p class="text-xs text-white/35 mt-1 font-semibold uppercase tracking-wide">Kontrak Berjalan</p>
  </div>
</div>

<!-- Charts + Occupancy Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">

  <!-- Revenue Chart -->
  <div class="lg:col-span-2 glass rounded-3xl p-6">
    <div class="flex items-center justify-between mb-5">
      <div>
        <h3 class="font-bold text-sm text-white">Grafik Pendapatan</h3>
        <p class="text-xs text-white/30 mt-0.5">6 bulan terakhir (juta rupiah)</p>
      </div>
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
        <span class="text-xs text-white/40">Revenue</span>
      </div>
    </div>
    <div style="height:200px;">
      <canvas id="revenueChart"></canvas>
    </div>
  </div>

  <!-- Occupancy Donut -->
  <div class="glass rounded-3xl p-6 flex flex-col">
    <h3 class="font-bold text-sm text-white mb-1">Tingkat Hunian</h3>
    <p class="text-xs text-white/30 mb-4">Total <?= $totalTerisi ?>/<?= $totalUnit ?> unit terisi</p>
    <div class="flex-1 flex items-center justify-center" style="min-height:140px">
      <canvas id="occupancyChart"></canvas>
    </div>
    <div class="mt-4 space-y-2">
      <?php
      $legends = [
        ['Gudang Terisi', '#6366f1', $s['gudang']['terisi'], $s['gudang']['total']],
        ['Toko Terisi',   '#f59e0b', $s['toko']['terisi'],   $s['toko']['total']],
        ['Kantin Terisi', '#10b981', $s['kantin']['terisi'],  $s['kantin']['total']],
      ];
      foreach ($legends as [$label, $color, $terisi, $total]):
        $pct = round($terisi/$total*100);
      ?>
        <div>
          <div class="flex items-center justify-between text-xs mb-1">
            <div class="flex items-center gap-1.5">
              <span class="legend-dot" style="background:<?= $color ?>"></span>
              <span class="text-white/50 font-medium"><?= $label ?></span>
            </div>
            <span class="font-bold text-white"><?= $terisi ?>/<?= $total ?></span>
          </div>
          <div class="progress-bar">
            <div class="progress-fill" style="background:<?= $color ?>;width:0%" data-width="<?= $pct ?>"></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Log Aktivitas + Penyewa Jatuh Tempo -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

  <!-- Log Aktivitas -->
  <div class="glass rounded-3xl p-6">
    <div class="flex items-center gap-2 mb-5">
      <span class="w-2 h-2 rounded-full bg-indigo-500 animate-ping"></span>
      <h3 class="font-bold text-sm text-white uppercase tracking-wide">Log Aktivitas</h3>
    </div>
    <div class="space-y-3">
      <?php
      $iconMap = [
        'check' => ['bg'=>'bg-emerald-500/15','text'=>'text-emerald-400','svg'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>'],
        'box'   => ['bg'=>'bg-indigo-500/15',  'text'=>'text-indigo-400', 'svg'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'],
        'warn'  => ['bg'=>'bg-amber-500/15',   'text'=>'text-amber-400',  'svg'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>'],
        'renew' => ['bg'=>'bg-sky-500/15',     'text'=>'text-sky-400',    'svg'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>'],
      ];
      foreach ($appData['aktivitas'] as $a):
        $ic = $iconMap[$a['icon']] ?? $iconMap['check'];
      ?>
        <div class="flex items-center gap-3 p-3 rounded-2xl hover:bg-white/3 transition-colors">
          <div class="w-10 h-10 rounded-xl <?= $ic['bg'] ?> <?= $ic['text'] ?> flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $ic['svg'] ?></svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-white"><?= htmlspecialchars($a['judul']) ?></p>
            <p class="text-xs text-white/35 truncate"><?= htmlspecialchars($a['sub']) ?></p>
          </div>
          <span class="text-[10px] text-white/25 font-mono whitespace-nowrap"><?= $a['waktu'] ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Unit Availability per Kategori -->
  <div class="glass rounded-3xl p-6">
    <h3 class="font-bold text-sm text-white uppercase tracking-wide mb-5">Ketersediaan Unit</h3>
    <div class="space-y-5">
      <?php
      $cats = [
        ['label'=>'Gudang','icon'=>'bg-orange-500/20 text-orange-400','data'=>$s['gudang'],'link'=>'gudang','color'=>'bg-orange-500'],
        ['label'=>'Toko',  'icon'=>'bg-sky-500/20 text-sky-400',      'data'=>$s['toko'],  'link'=>'toko',  'color'=>'bg-sky-500'],
        ['label'=>'Kantin','icon'=>'bg-emerald-500/20 text-emerald-400','data'=>$s['kantin'],'link'=>'kantin_gudang','color'=>'bg-emerald-500'],
      ];
      foreach ($cats as $cat):
        $d = $cat['data'];
        $pct = round($d['terisi']/$d['total']*100);
      ?>
        <div>
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
              <span class="text-xs font-bold text-white"><?= $cat['label'] ?></span>
            </div>
            <div class="flex items-center gap-3 text-xs">
              <span class="text-white/35"><span class="text-white font-bold"><?= $d['terisi'] ?></span> terisi</span>
              <span class="text-white/35"><span class="text-emerald-400 font-bold"><?= $d['tersedia'] ?></span> kosong</span>
              <a href="dashboard.php?page=<?= $cat['link'] ?>" class="text-indigo-400 hover:text-indigo-300 font-semibold transition-colors">Lihat →</a>
            </div>
          </div>
          <div class="progress-bar">
            <div class="progress-fill <?= $cat['color'] ?>" style="width:0%" data-width="<?= $pct ?>"></div>
          </div>
          <p class="text-[10px] text-white/20 mt-1"><?= $pct ?>% terisi dari <?= $d['total'] ?> unit</p>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mt-6 pt-5 border-t border-white/5">
      <div class="grid grid-cols-2 gap-3">
        <a href="dashboard.php?page=penyewa" class="flex items-center justify-center gap-2 bg-indigo-600/15 hover:bg-indigo-600/25 border border-indigo-500/25 text-indigo-400 rounded-xl py-3 text-xs font-bold transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          Data Penyewa
        </a>
        <a href="dashboard.php?page=tagihan" class="flex items-center justify-center gap-2 bg-emerald-600/15 hover:bg-emerald-600/25 border border-emerald-500/25 text-emerald-400 rounded-xl py-3 text-xs font-bold transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          Penagihan
        </a>
      </div>
    </div>
  </div>
</div>
