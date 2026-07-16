<?php
require_once __DIR__ . '/../includes/data.php';
$units   = $appData['units']['gudang'];
$kosong  = array_filter($units, fn($u) => $u['status'] === 'Kosong');
$terisi  = array_filter($units, fn($u) => $u['status'] === 'Terisi');
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
  <div>
    <div class="flex items-center gap-2 mb-1">
      <a href="dashboard.php" class="text-white/30 hover:text-white/60 text-xs font-semibold transition-colors">Dashboard</a>
      <span class="text-white/20 text-xs">›</span>
      <span class="text-white/60 text-xs font-semibold">Gudang</span>
    </div>
    <h1 class="text-2xl font-extrabold text-white">Manajemen Gudang</h1>
    <p class="text-white/35 text-sm mt-0.5">Total <?= count($units) ?> unit — <?= count($kosong) ?> tersedia</p>
  </div>
  <div class="flex items-center gap-3">
    <div class="flex items-center gap-2 bg-white/5 border border-white/10 rounded-xl px-3 py-2">
      <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
      <span class="text-xs font-bold text-white/60"><?= count($kosong) ?> Kosong</span>
    </div>
    <div class="flex items-center gap-2 bg-white/5 border border-white/10 rounded-xl px-3 py-2">
      <div class="w-2 h-2 rounded-full bg-red-500"></div>
      <span class="text-xs font-bold text-white/60"><?= count($terisi) ?> Terisi</span>
    </div>
  </div>
</div>

<!-- Filter Tabs -->
<div class="flex flex-wrap gap-2 mb-6">
  <button onclick="filterUnits('all')"    data-ftab="all"    class="ftab ftab-active px-4 py-2 rounded-xl text-xs font-bold transition-all">Semua (<?= count($units) ?>)</button>
  <button onclick="filterUnits('Kosong')" data-ftab="Kosong" class="ftab px-4 py-2 rounded-xl text-xs font-bold transition-all">Kosong (<?= count($kosong) ?>)</button>
  <button onclick="filterUnits('Terisi')" data-ftab="Terisi" class="ftab px-4 py-2 rounded-xl text-xs font-bold transition-all">Terisi (<?= count($terisi) ?>)</button>
</div>

<!-- Unit Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="unitGrid">
  <?php foreach ($units as $u):
    $isKosong = $u['status'] === 'Kosong';
  ?>
    <div class="unit-card glass <?= strtolower($u['status']) ?>" data-status="<?= $u['status'] ?>">
      <!-- Header row -->
      <div class="flex items-start justify-between mb-4">
        <div>
          <p class="text-[9px] font-black uppercase tracking-widest text-white/25 mb-1">ID Unit</p>
          <p class="text-xl font-black text-white"><?= htmlspecialchars($u['id']) ?></p>
        </div>
        <span class="badge <?= $isKosong ? 'badge-green' : 'badge-red' ?>">
          <?= $u['status'] ?>
        </span>
      </div>

      <!-- Details -->
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
            <span class="font-semibold text-white/60 text-right max-w-[120px] truncate"><?= htmlspecialchars($u['penyewa']) ?></span>
          </div>
        <?php endif; ?>
      </div>

      <!-- Action -->
      <?php if ($isKosong): ?>
        <button onclick="openBooking('<?= htmlspecialchars($u['id']) ?>','<?= htmlspecialchars($u['nama']) ?>','<?= $u['harga'] ?>')"
                class="w-full bg-emerald-500/15 hover:bg-emerald-500/30 border border-emerald-500/30 text-emerald-400 text-xs font-bold py-2.5 rounded-xl transition-all">
          + Booking Unit Ini
        </button>
      <?php else: ?>
        <div class="w-full bg-white/3 border border-white/8 rounded-xl py-2.5 text-center text-xs text-white/25 font-semibold">
          Unit Tidak Tersedia
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/booking_modal.php'; ?>

<style>
.ftab { background:rgba(255,255,255,0.05); color:rgba(255,255,255,0.4); border:1px solid rgba(255,255,255,0.08); }
.ftab:hover { background:rgba(255,255,255,0.09); color:rgba(255,255,255,0.7); }
.ftab-active { background:rgba(99,102,241,0.2)!important; color:#a5b4fc!important; border-color:rgba(99,102,241,0.35)!important; }
</style>
<script>
function filterUnits(status) {
  document.querySelectorAll('.ftab').forEach(b => b.classList.remove('ftab-active'));
  document.querySelector(`[data-ftab="${status}"]`).classList.add('ftab-active');
  document.querySelectorAll('#unitGrid .unit-card').forEach(card => {
    const s = card.getAttribute('data-status');
    card.style.display = (status === 'all' || s === status) ? '' : 'none';
  });
}
</script>
