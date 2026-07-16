<?php
require_once __DIR__ . '/../includes/data.php';
$units   = $appData['units'];
$kosongGudang = count(array_filter($units['gudang'], fn($u) => $u['status'] === 'Kosong'));
$kosongToko   = count(array_filter($units['toko'],   fn($u) => $u['status'] === 'Kosong'));
$kosongKantin = count(array_filter(array_merge($units['kantin_gudang'] ?? [], $units['kantin_toko'] ?? []), fn($u) => $u['status'] === 'Kosong'));
$tenantNama   = $_SESSION['tenant_nama'] ?? 'Penyewa';
?>

<!-- Greeting -->
<div class="mb-8">
  <div class="flex items-start gap-4">
    <div class="w-12 h-12 rounded-2xl bg-emerald-600/20 border border-emerald-500/30 flex items-center justify-center flex-shrink-0">
      <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
      </svg>
    </div>
    <div>
      <p class="text-white/40 text-sm">Selamat datang,</p>
      <h1 class="text-2xl font-extrabold text-white"><?= htmlspecialchars($tenantNama) ?></h1>
      <p class="text-white/35 text-sm mt-0.5">Jelajahi unit yang tersedia dan hubungi kami untuk informasi penyewaan.</p>
    </div>
  </div>
</div>

<!-- Ketersediaan Unit -->
<div class="mb-8">
  <p class="text-[10px] font-black uppercase tracking-widest text-white/30 mb-4">Ketersediaan Unit Saat Ini</p>
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

    <!-- Gudang -->
    <a href="portal.php?page=gudang" class="glass rounded-2xl p-5 flex items-center gap-4 hover:bg-white/8 transition-all group">
      <div class="w-12 h-12 rounded-xl bg-orange-500/15 border border-orange-500/20 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
        <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
      </div>
      <div>
        <p class="text-[9px] font-black uppercase tracking-widest text-white/25 mb-0.5">Gudang</p>
        <p class="text-2xl font-black text-orange-400"><?= $kosongGudang ?></p>
        <p class="text-xs text-white/40">Unit tersedia</p>
      </div>
      <svg class="w-4 h-4 text-white/20 ml-auto group-hover:text-orange-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>

    <!-- Toko -->
    <a href="portal.php?page=toko" class="glass rounded-2xl p-5 flex items-center gap-4 hover:bg-white/8 transition-all group">
      <div class="w-12 h-12 rounded-xl bg-sky-500/15 border border-sky-500/20 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
        <svg class="w-6 h-6 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
      </div>
      <div>
        <p class="text-[9px] font-black uppercase tracking-widest text-white/25 mb-0.5">Toko</p>
        <p class="text-2xl font-black text-sky-400"><?= $kosongToko ?></p>
        <p class="text-xs text-white/40">Unit tersedia</p>
      </div>
      <svg class="w-4 h-4 text-white/20 ml-auto group-hover:text-sky-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>

    <!-- Kantin -->
    <a href="portal.php?page=kantin_gudang" class="glass rounded-2xl p-5 flex items-center gap-4 hover:bg-white/8 transition-all group">
      <div class="w-12 h-12 rounded-xl bg-emerald-500/15 border border-emerald-500/20 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
      </div>
      <div>
        <p class="text-[9px] font-black uppercase tracking-widest text-white/25 mb-0.5">Kantin</p>
        <p class="text-2xl font-black text-emerald-400"><?= $kosongKantin ?></p>
        <p class="text-xs text-white/40">Unit tersedia</p>
      </div>
      <svg class="w-4 h-4 text-white/20 ml-auto group-hover:text-emerald-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>

  </div>
</div>

<!-- Grid: Cara Sewa + Kontak -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

  <!-- Cara Menyewa -->
  <div class="glass rounded-2xl p-6">
    <div class="flex items-center gap-3 mb-5">
      <div class="w-9 h-9 rounded-xl bg-indigo-500/15 border border-indigo-500/20 flex items-center justify-center">
        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 3h6m-6 4h6m-6 4h4"/></svg>
      </div>
      <h3 class="font-extrabold text-white text-sm">Cara Menyewa Unit</h3>
    </div>
    <div class="space-y-4">
      <?php
      $steps = [
        ['num'=>'1','title'=>'Pilih Unit','desc'=>'Jelajahi katalog Gudang, Toko, atau Kantin dan temukan unit yang sesuai.','color'=>'orange'],
        ['num'=>'2','title'=>'Hubungi Kami','desc'=>'Kontak tim kami via WhatsApp atau email untuk konfirmasi ketersediaan.','color'=>'sky'],
        ['num'=>'3','title'=>'Survei Unit','desc'=>'Tim kami akan mengatur jadwal survei langsung ke lokasi unit.','color'=>'emerald'],
        ['num'=>'4','title'=>'Tanda Tangan Kontrak','desc'=>'Proses administrasi dan penandatanganan kontrak penyewaan.','color'=>'violet'],
      ];
      foreach ($steps as $s): ?>
      <div class="flex items-start gap-3">
        <div class="w-7 h-7 rounded-lg bg-<?= $s['color'] ?>-500/15 border border-<?= $s['color'] ?>-500/25 flex items-center justify-center flex-shrink-0">
          <span class="text-xs font-black text-<?= $s['color'] ?>-400"><?= $s['num'] ?></span>
        </div>
        <div>
          <p class="text-sm font-bold text-white"><?= $s['title'] ?></p>
          <p class="text-xs text-white/40 mt-0.5 leading-relaxed"><?= $s['desc'] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Kontak & Info -->
  <div class="flex flex-col gap-4">

    <!-- Kontak -->
    <div class="glass rounded-2xl p-6">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-9 h-9 rounded-xl bg-emerald-500/15 border border-emerald-500/20 flex items-center justify-center">
          <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        </div>
        <h3 class="font-extrabold text-white text-sm">Hubungi Kami</h3>
      </div>
      <div class="space-y-3">
        <a href="https://wa.me/081290564483" target="_blank"
           class="flex items-center gap-3 bg-emerald-500/8 hover:bg-emerald-500/15 border border-emerald-500/15 rounded-xl p-3 transition-all group">
          <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center">
            <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.588-5.946 0-6.556 5.332-11.891 11.891-11.891 3.181 0 6.167 1.24 8.413 3.488 2.246 2.248 3.484 5.232 3.484 8.403 0 6.556-5.332 11.891-11.891 11.891-2.093 0-4.141-.544-5.945-1.587L0 24zm6.549-4.322l.379.225c1.43.85 3.097 1.298 4.795 1.298 4.991 0 9.051-4.06 9.051-9.052 0-2.42-.942-4.695-2.653-6.406-1.71-1.713-3.986-2.653-6.398-2.653-4.991 0-9.051 4.06-9.051 9.052 0 1.698.448 3.364 1.298 4.793l.247.417-1.001 3.657 3.733-.982z"/></svg>
          </div>
          <div>
            <p class="text-xs font-bold text-emerald-400">WhatsApp</p>
            <p class="text-[10px] text-white/40">0812-9056-4483</p>
          </div>
          <svg class="w-3.5 h-3.5 text-white/20 ml-auto group-hover:text-emerald-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
        <a href="mailto:info@foodstation.co.id"
           class="flex items-center gap-3 bg-sky-500/8 hover:bg-sky-500/15 border border-sky-500/15 rounded-xl p-3 transition-all group">
          <div class="w-8 h-8 rounded-lg bg-sky-500/20 flex items-center justify-center">
            <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </div>
          <div>
            <p class="text-xs font-bold text-sky-400">Email</p>
            <p class="text-[10px] text-white/40">info@foodstation.co.id</p>
          </div>
        </a>
        <a href="tel:+62211234567"
           class="flex items-center gap-3 bg-indigo-500/8 hover:bg-indigo-500/15 border border-indigo-500/15 rounded-xl p-3 transition-all group">
          <div class="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center">
            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
          </div>
          <div>
            <p class="text-xs font-bold text-indigo-400">Telepon</p>
            <p class="text-[10px] text-white/40">(021) 123-4567</p>
          </div>
        </a>
      </div>
    </div>

    <!-- Jam Operasional -->
    <div class="glass rounded-2xl p-5">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-8 h-8 rounded-xl bg-violet-500/15 border border-violet-500/20 flex items-center justify-center">
          <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h3 class="font-extrabold text-white text-sm">Jam Operasional</h3>
      </div>
      <div class="space-y-2 text-xs">
        <div class="flex justify-between">
          <span class="text-white/40">Senin – Jumat</span>
          <span class="font-bold text-white/70">08:00 – 17:00 WIB</span>
        </div>
        <div class="flex justify-between">
          <span class="text-white/40">Sabtu</span>
          <span class="font-bold text-white/70">08:00 – 13:00 WIB</span>
        </div>
        <div class="flex justify-between">
          <span class="text-white/40">Minggu & Libur</span>
          <span class="font-semibold text-red-400/70">Tutup</span>
        </div>
      </div>
    </div>

  </div>
</div>
