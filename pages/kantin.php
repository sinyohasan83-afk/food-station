<?php
require_once __DIR__ . '/../includes/db.php';

$isTenant = isset($_SESSION['tenant_logged_in']) && $_SESSION['tenant_logged_in'] === true;
$isAdmin  = !$isTenant;
$subtype  = $_GET['sub'] ?? 'gudang'; // gudang or toko
$subKategoriId = $subtype === 'toko' ? 2 : 1; // 1=Area Pergudangan, 2=Area Pertokoan
$subLabel = $subtype === 'toko' ? 'Area Pertokoan' : 'Area Pergudangan';
$pageParam = $subtype === 'toko' ? 'kantin_toko' : 'kantin_gudang';
$bookingRedirectUrl = ($isTenant ? 'portal.php' : 'dashboard.php') . '?page=' . $pageParam;

$units = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT u.*, p.nama AS penyewa_nama
            FROM units u
            LEFT JOIN kontrak k ON k.unit_id = u.id AND k.status = 'Aktif'
            LEFT JOIN penyewa p ON p.id = k.penyewa_id
            WHERE u.kategori_id = 3 AND u.subkategori_id = ?
            ORDER BY u.kode ASC
        ");
        $stmt->execute([$subKategoriId]);
        $units = $stmt->fetchAll();
    } catch (PDOException $e) {
        $units = [];
    }
}
$kosong = array_filter($units, fn($u) => $u['status'] === 'Kosong');
$terisi = array_filter($units, fn($u) => $u['status'] === 'Terisi');
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
  <div>
    <div class="flex items-center gap-2 mb-1">
      <a href="<?= $isTenant ? 'portal.php' : 'dashboard.php' ?>" class="text-white/30 hover:text-white/60 text-xs font-semibold transition-colors">Dashboard</a>
      <span class="text-white/20 text-xs">›</span>
      <span class="text-white/60 text-xs font-semibold">Kantin — <?= $subLabel ?></span>
    </div>
    <h1 class="text-2xl font-extrabold text-white">Kantin <?= $subLabel ?></h1>
    <p class="text-white/35 text-sm mt-0.5">Total <?= count($units) ?> unit — <?= count($kosong) ?> tersedia</p>
  </div>
  <div class="flex items-center gap-3">
    <?php $base = $isTenant ? 'portal.php' : 'dashboard.php'; ?>
    <a href="<?= $base ?>?page=kantin_gudang" class="text-xs font-bold px-4 py-2 rounded-xl transition-all <?= $subtype !== 'toko' ? 'bg-emerald-500/20 border border-emerald-500/30 text-emerald-400' : 'bg-white/5 border border-white/10 text-white/40 hover:text-white/70' ?>">Pergudangan</a>
    <a href="<?= $base ?>?page=kantin_toko"   class="text-xs font-bold px-4 py-2 rounded-xl transition-all <?= $subtype === 'toko' ? 'bg-emerald-500/20 border border-emerald-500/30 text-emerald-400' : 'bg-white/5 border border-white/10 text-white/40 hover:text-white/70' ?>">Pertokoan</a>
    <?php if ($isAdmin): ?>
      <button onclick="openUnitModal()" class="flex items-center gap-2 bg-emerald-600/15 hover:bg-emerald-600/25 border border-emerald-500/25 text-emerald-400 px-4 py-2.5 rounded-xl text-xs font-bold transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Unit
      </button>
    <?php endif; ?>
  </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
  <?php if (empty($units)): ?>
    <p class="col-span-full text-center text-white/30 text-sm py-8">Belum ada data unit kantin di area ini, atau koneksi database gagal.</p>
  <?php endif; ?>
  <?php foreach ($units as $u):
    $isKosong = $u['status'] === 'Kosong';
    $luasFmt  = rtrim(rtrim(number_format((float)$u['luas'], 2, '.', ''), '0'), '.') . ' m²';
    $hargaFmt = 'Rp ' . number_format($u['harga_per_bulan'], 0, ',', '.');
  ?>
    <div class="unit-card glass <?= strtolower($u['status']) ?>">
      <div class="flex items-start justify-between mb-4">
        <div>
          <p class="text-[9px] font-black uppercase tracking-widest text-white/25 mb-1">ID Unit</p>
          <p class="text-xl font-black text-white"><?= htmlspecialchars($u['kode']) ?></p>
        </div>
        <span class="badge <?= $isKosong ? 'badge-green' : 'badge-red' ?>"><?= $u['status'] ?></span>
      </div>
      <p class="text-sm font-semibold text-white/70 mb-3"><?= htmlspecialchars($u['nama']) ?></p>
      <div class="space-y-1.5 mb-4">
        <div class="flex items-center justify-between text-xs">
          <span class="text-white/30">Luas</span>
          <span class="font-semibold text-white/60"><?= $luasFmt ?></span>
        </div>
        <div class="flex items-center justify-between text-xs">
          <span class="text-white/30">Harga/Bln</span>
          <span class="font-bold text-amber-400"><?= $hargaFmt ?></span>
        </div>
        <?php if (!$isKosong): ?>
          <div class="flex items-center justify-between text-xs">
            <span class="text-white/30">Penyewa</span>
            <span class="font-semibold text-white/60"><?= htmlspecialchars($u['penyewa_nama'] ?? '-') ?></span>
          </div>
        <?php endif; ?>
      </div>
      <?php if ($isKosong && $isTenant): ?>
        <button onclick="openBooking(<?= (int)$u['id'] ?>,'<?= htmlspecialchars($u['kode']) ?>','<?= htmlspecialchars($u['nama']) ?>','<?= $hargaFmt ?>')"
                class="w-full bg-emerald-500/15 hover:bg-emerald-500/30 border border-emerald-500/30 text-emerald-400 text-xs font-bold py-2.5 rounded-xl transition-all">
          + Booking Unit Ini
        </button>
      <?php elseif ($isAdmin): ?>
        <div class="flex gap-2">
          <button onclick='openUnitModal(<?= json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                  class="flex-1 bg-indigo-500/15 hover:bg-indigo-500/30 border border-indigo-500/30 text-indigo-400 text-xs font-bold py-2 rounded-xl transition-all">
            Edit
          </button>
          <?php if ($isKosong): ?>
            <form method="POST" action="unit_delete.php" onsubmit="return confirm('Hapus unit <?= htmlspecialchars(addslashes($u['kode'])) ?>?');" class="flex-1">
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>"/>
              <input type="hidden" name="redirect_to" value="dashboard.php?page=<?= $pageParam ?>"/>
              <button type="submit" class="w-full bg-red-500/15 hover:bg-red-500/30 border border-red-500/30 text-red-400 text-xs font-bold py-2 rounded-xl transition-all">Hapus</button>
            </form>
          <?php endif; ?>
        </div>
        <?php if (!$isKosong): ?>
          <p class="text-[10px] text-white/20 mt-1.5 text-center">Terisi — kelola lewat Data Penyewa</p>
        <?php endif; ?>
      <?php elseif ($isKosong): ?>
        <div class="w-full bg-white/3 border border-white/8 rounded-xl py-2.5 text-center text-xs text-white/30 font-semibold">
          Tersedia — kelola lewat Pengajuan Sewa
        </div>
      <?php else: ?>
        <div class="w-full bg-white/3 border border-white/8 rounded-xl py-2.5 text-center text-xs text-white/25 font-semibold">Unit Tidak Tersedia</div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<?php if ($isTenant): include __DIR__ . '/../includes/booking_modal.php'; endif; ?>
<?php if ($isAdmin):
    $unitModalKategoriId    = 3;
    $unitModalSubkategoriId = $subKategoriId;
    $unitModalRedirect      = 'dashboard.php?page=' . $pageParam;
    include __DIR__ . '/../includes/unit_modal.php';
endif; ?>
