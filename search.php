<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'includes/db.php';

$q = trim($_GET['q'] ?? '');
$units = [];
$penyewaList = [];
$dbError = false;

if ($q !== '' && $pdo) {
    try {
        $like = '%' . $q . '%';

        $stmt = $pdo->prepare("
            SELECT u.*, ku.nama AS kategori
            FROM units u
            JOIN kategori_unit ku ON ku.id = u.kategori_id
            WHERE u.kode LIKE ? OR u.nama LIKE ?
            ORDER BY u.kode ASC
            LIMIT 30
        ");
        $stmt->execute([$like, $like]);
        $units = $stmt->fetchAll();

        $stmt2 = $pdo->prepare("
            SELECT p.*, u.kode AS kode_unit, u.nama AS nama_unit
            FROM penyewa p
            LEFT JOIN kontrak k ON k.penyewa_id = p.id AND k.status = 'Aktif'
            LEFT JOIN units u   ON u.id = k.unit_id
            WHERE p.nama LIKE ? OR p.telepon LIKE ? OR p.email LIKE ?
            ORDER BY p.nama ASC
            LIMIT 30
        ");
        $stmt2->execute([$like, $like, $like]);
        $penyewaList = $stmt2->fetchAll();
    } catch (PDOException $e) {
        $dbError = true;
    }
} elseif (!$pdo) {
    $dbError = true;
}

$catPageMap = [1 => 'gudang', 2 => 'toko', 3 => 'kantin_gudang'];
$pageTitle = 'PT. Food Station — Pencarian';
$assetBase = '';
?>
<?php include 'includes/header.php'; ?>
<body class="bg-hero min-h-screen">
<script>
  (function(){
    if (localStorage.getItem('fs_theme') === 'light') {
      document.body.classList.add('light');
    }
  })();
</script>

<?php include 'includes/navbar.php'; ?>

<div class="flex h-[calc(100vh-65px)]">
  <?php include 'includes/sidebar.php'; ?>

  <main class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8">
    <div class="max-w-7xl mx-auto fade-up">

      <div class="mb-8">
        <div class="flex items-center gap-2 mb-1">
          <a href="dashboard.php" class="text-white/30 hover:text-white/60 text-xs font-semibold">Dashboard</a>
          <span class="text-white/20 text-xs">›</span>
          <span class="text-white/60 text-xs font-semibold">Pencarian</span>
        </div>
        <h1 class="text-2xl font-extrabold text-white">Hasil Pencarian</h1>
        <p class="text-white/35 text-sm mt-0.5">
          <?= $q !== '' ? 'Untuk "' . htmlspecialchars($q) . '"' : 'Ketik kata kunci di kotak pencarian di atas' ?>
        </p>
      </div>

      <?php if ($dbError): ?>
        <div class="mb-6 bg-red-500/10 border border-red-500/30 rounded-2xl px-5 py-4 text-sm text-red-400 font-semibold">
          Koneksi database gagal. Pastikan MySQL aktif.
        </div>
      <?php elseif ($q === ''): ?>
        <div class="glass rounded-2xl p-8 text-center text-white/30 text-sm">Masukkan nama unit, kode unit, nama penyewa, telepon, atau email untuk mencari.</div>
      <?php elseif (empty($units) && empty($penyewaList)): ?>
        <div class="glass rounded-2xl p-8 text-center text-white/30 text-sm">Tidak ada hasil untuk "<?= htmlspecialchars($q) ?>".</div>
      <?php endif; ?>

      <?php if (!empty($units)): ?>
        <div class="mb-8">
          <p class="text-[10px] font-black uppercase tracking-widest text-white/30 mb-3">Unit (<?= count($units) ?>)</p>
          <div class="glass rounded-3xl overflow-hidden">
            <div class="overflow-x-auto">
            <table class="data-table">
              <thead>
                <tr>
                  <th class="text-left">Kode</th>
                  <th class="text-left">Nama</th>
                  <th class="text-left">Kategori</th>
                  <th class="text-center">Status</th>
                  <th class="text-right">Harga/Bln</th>
                  <th class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($units as $u):
                  $badgeClass = match($u['status']) { 'Kosong' => 'badge-green', 'Terisi' => 'badge-red', default => 'badge-yellow' };
                  $page = $catPageMap[$u['kategori_id']] ?? 'gudang';
                ?>
                  <tr class="table-row">
                    <td class="font-mono text-xs text-white/70"><?= htmlspecialchars($u['kode']) ?></td>
                    <td class="font-bold text-white"><?= htmlspecialchars($u['nama']) ?></td>
                    <td class="text-xs text-white/50"><?= htmlspecialchars($u['kategori']) ?></td>
                    <td class="text-center"><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($u['status']) ?></span></td>
                    <td class="text-right text-amber-400 font-semibold text-xs">Rp <?= number_format($u['harga_per_bulan'], 0, ',', '.') ?></td>
                    <td class="text-center">
                      <a href="dashboard.php?page=<?= $page ?>" class="text-indigo-400 hover:text-indigo-300 text-xs font-bold transition-colors">Lihat</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($penyewaList)): ?>
        <div>
          <p class="text-[10px] font-black uppercase tracking-widest text-white/30 mb-3">Penyewa (<?= count($penyewaList) ?>)</p>
          <div class="glass rounded-3xl overflow-hidden">
            <div class="overflow-x-auto">
            <table class="data-table">
              <thead>
                <tr>
                  <th class="text-left">Nama</th>
                  <th class="text-left">Kontak</th>
                  <th class="text-left">Unit</th>
                  <th class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($penyewaList as $p): ?>
                  <tr class="table-row">
                    <td class="font-bold text-white"><?= htmlspecialchars($p['nama']) ?></td>
                    <td class="text-xs text-white/60"><?= htmlspecialchars($p['telepon']) ?><br><span class="text-white/30"><?= htmlspecialchars($p['email'] ?: '-') ?></span></td>
                    <td class="font-mono text-xs text-white/50"><?= $p['kode_unit'] ? htmlspecialchars($p['kode_unit'] . ' – ' . $p['nama_unit']) : '-' ?></td>
                    <td class="text-center">
                      <a href="penyewa_detail.php?id=<?= (int)$p['id'] ?>" target="_blank" class="text-sky-400 hover:text-sky-300 text-xs font-bold transition-colors">Detail</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            </div>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </main>
</div>

<div id="toastContainer" class="toast-container"></div>
<script src="assets/js/app.js?v=<?= filemtime(__DIR__ . '/assets/js/app.js') ?>"></script>
<?php require_once 'includes/flash.php'; flash_render(); ?>
</body>
</html>
