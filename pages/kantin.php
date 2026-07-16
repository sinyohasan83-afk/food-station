<?php
require_once __DIR__ . '/../includes/data.php';
$subtype = $_GET['sub'] ?? 'gudang'; // gudang or toko
$units   = $appData['units']['kantin'];
$kosong  = array_filter($units, fn($u) => $u['status'] === 'Kosong');
$terisi  = array_filter($units, fn($u) => $u['status'] === 'Terisi');
$subLabel = $subtype === 'toko' ? 'Area Pertokoan' : 'Area Pergudangan';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
  <div>
    <div class="flex items-center gap-2 mb-1">
      <a href="dashboard.php" class="text-white/30 hover:text-white/60 text-xs font-semibold transition-colors">Dashboard</a>
      <span class="text-white/20 text-xs">›</span>
      <span class="text-white/60 text-xs font-semibold">Kantin — <?= $subLabel ?></span>
    </div>
    <h1 class="text-2xl font-extrabold text-white">Kantin <?= $subLabel ?></h1>
    <p class="text-white/35 text-sm mt-0.5">Total <?= count($units) ?> unit — <?= count($kosong) ?> tersedia</p>
  </div>
  <div class="flex items-center gap-3">
    <a href="dashboard.php?page=kantin_gudang" class="text-xs font-bold px-4 py-2 rounded-xl transition-all <?= $subtype !== 'toko' ? 'bg-emerald-500/20 border border-emerald-500/30 text-emerald-400' : 'bg-white/5 border border-white/10 text-white/40 hover:text-white/70' ?>">Pergudangan</a>
    <a href="dashboard.php?page=kantin_toko"   class="text-xs font-bold px-4 py-2 rounded-xl transition-all <?= $subtype === 'toko' ? 'bg-emerald-500/20 border border-emerald-500/30 text-emerald-400' : 'bg-white/5 border border-white/10 text-white/40 hover:text-white/70' ?>">Pertokoan</a>
  </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
  <?php foreach ($units as $u):
    $isKosong = $u['status'] === 'Kosong';
  ?>
    <div class="unit-card glass <?= strtolower($u['status']) ?>">
      <div class="flex items-start justify-between mb-4">
        <div>
          <p class="text-[9px] font-black uppercase tracking-widest text-white/25 mb-1">ID Unit</p>
          <p class="text-xl font-black text-white"><?= htmlspecialchars($u['id']) ?></p>
        </div>
        <span class="badge <?= $isKosong ? 'badge-green' : 'badge-red' ?>"><?= $u['status'] ?></span>
      </div>
      <p class="text-sm font-semibold text-white/70 mb-3"><?= htmlspecialchars($u['nama']) ?></p>
      <div class="space-y-1.5 mb-4">
        <div class="flex items-center justify-between text-xs">
          <span class="text-white/30">Luas</span>
          <span class="font-semibold text-white/60"><?= $u['luas'] ?></span>
        </div>
        <div class="flex items-center justify-between text-xs">
          <span class="text-white/30">Harga/Bln</span>
          <span class="font-bold text-amber-400"><?= $u['harga'] ?></span>
        </div>
        <?php if (!$isKosong): ?>
          <div class="flex items-center justify-between text-xs">
            <span class="text-white/30">Penyewa</span>
            <span class="font-semibold text-white/60"><?= htmlspecialchars($u['penyewa']) ?></span>
          </div>
        <?php endif; ?>
      </div>
      <?php if ($isKosong): ?>
        <button onclick="openBooking('<?= htmlspecialchars($u['id']) ?>','<?= htmlspecialchars($u['nama']) ?>','<?= $u['harga'] ?>')"
                class="w-full bg-emerald-500/15 hover:bg-emerald-500/30 border border-emerald-500/30 text-emerald-400 text-xs font-bold py-2.5 rounded-xl transition-all">
          + Booking Unit Ini
        </button>
      <?php else: ?>
        <div class="w-full bg-white/3 border border-white/8 rounded-xl py-2.5 text-center text-xs text-white/25 font-semibold">Unit Tidak Tersedia</div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/booking_modal.php'; ?>
