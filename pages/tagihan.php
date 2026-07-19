<?php
require_once __DIR__ . '/../includes/db.php';

$today = date('Y-m-d');
$rows  = [];
$dbError = false;

if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT t.*, p.nama AS penyewa_nama, u.kode AS kode_unit, u.nama AS nama_unit
            FROM tagihan t
            JOIN penyewa p ON p.id = t.penyewa_id
            JOIN units u   ON u.id = t.unit_id
            WHERE t.periode_bulan = MONTH(CURDATE()) AND t.periode_tahun = YEAR(CURDATE())
            ORDER BY t.status = 'Lunas' ASC, t.tanggal_jatuh_tempo ASC
        ");
        $rows = $stmt->fetchAll();
    } catch (PDOException $e) {
        $dbError = true;
    }
} else {
    $dbError = true;
}

$totalTagihan = array_sum(array_column($rows, 'nominal'));
$lunas = array_filter($rows, fn($r) => $r['status'] === 'Lunas');
$belum = array_filter($rows, fn($r) => $r['status'] !== 'Lunas');
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
  <div>
    <div class="flex items-center gap-2 mb-1">
      <a href="dashboard.php" class="text-white/30 hover:text-white/60 text-xs font-semibold">Dashboard</a>
      <span class="text-white/20 text-xs">›</span>
      <span class="text-white/60 text-xs font-semibold">Penagihan</span>
    </div>
    <h1 class="text-2xl font-extrabold text-white">Sistem Penagihan</h1>
    <p class="text-white/35 text-sm mt-0.5">Periode: <?= date('F Y') ?></p>
  </div>
  <!-- Export Dropdown Tagihan -->
  <div class="relative" id="exportDropdownTagihan">
    <button onclick="toggleExportTagihan()" class="flex items-center gap-2 bg-violet-600/15 hover:bg-violet-600/25 border border-violet-500/25 text-violet-400 px-4 py-2.5 rounded-xl text-xs font-bold transition-all">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      Laporan Keuangan
      <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>
    <div id="exportMenuTagihan" class="hidden absolute right-0 mt-2 w-56 notif-dropdown z-50">
      <p class="text-[9px] font-black uppercase tracking-widest text-white/30 mb-3">Ekspor Laporan</p>
      <?php
        $bln = date('n'); $thn = date('Y');
        $paramBase = "?bulan={$bln}&tahun={$thn}";
      ?>
      <div class="space-y-1.5">
        <a href="export/tagihan.php<?= $paramBase ?>&format=excel" target="_blank"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/8 transition-colors">
          <div class="w-8 h-8 rounded-lg bg-emerald-500/15 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          </div>
          <div>
            <p class="text-xs font-bold text-white/80">Export Excel</p>
            <p class="text-[10px] text-white/30">Laporan lengkap .xls</p>
          </div>
        </a>
        <a href="export/tagihan.php<?= $paramBase ?>&format=csv" target="_blank"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/8 transition-colors">
          <div class="w-8 h-8 rounded-lg bg-sky-500/15 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          </div>
          <div>
            <p class="text-xs font-bold text-white/80">Export CSV</p>
            <p class="text-[10px] text-white/30">Format .csv (universal)</p>
          </div>
        </a>
        <div class="my-2 border-t border-white/8"></div>
        <a href="export/tagihan.php<?= $paramBase ?>&format=print" target="_blank"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/8 transition-colors">
          <div class="w-8 h-8 rounded-lg bg-violet-500/15 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
          </div>
          <div>
            <p class="text-xs font-bold text-white/80">Cetak / PDF</p>
            <p class="text-[10px] text-white/30">Halaman cetak landscape</p>
          </div>
        </a>
      </div>
    </div>
  </div>

  <script>
  function toggleExportTagihan() {
    document.getElementById('exportMenuTagihan').classList.toggle('hidden');
  }
  document.addEventListener('click', function(e) {
    if (!e.target.closest('#exportDropdownTagihan')) {
      const m = document.getElementById('exportMenuTagihan');
      if (m) m.classList.add('hidden');
    }
  });
  </script>

</div>

<?php if ($dbError): ?>
  <div class="mb-6 bg-red-500/10 border border-red-500/30 rounded-2xl px-5 py-4 text-sm text-red-400 font-semibold">
    Koneksi database gagal. Pastikan MySQL aktif di XAMPP.
  </div>
<?php endif; ?>

<!-- Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
  <div class="stat-card glass border-l-4 border-emerald-500">
    <p class="text-[9px] font-black uppercase tracking-widest text-white/30 mb-3">Total Tagihan Bulan Ini</p>
    <p class="text-2xl font-black text-emerald-400">Rp <?= number_format($totalTagihan,0,',','.') ?></p>
    <p class="text-xs text-white/30 mt-1"><?= count($rows) ?> tagihan aktif</p>
  </div>
  <div class="stat-card glass border-l-4 border-indigo-500">
    <p class="text-[9px] font-black uppercase tracking-widest text-white/30 mb-3">Sudah Dibayar</p>
    <p class="text-2xl font-black text-indigo-400"><?= count($lunas) ?> <span class="text-sm font-normal text-white/30">tagihan</span></p>
    <div class="progress-bar mt-3">
      <div class="progress-fill bg-indigo-500" style="width:0%" data-width="<?= count($rows) > 0 ? round(count($lunas)/count($rows)*100) : 0 ?>"></div>
    </div>
  </div>
  <div class="stat-card glass border-l-4 border-red-500">
    <p class="text-[9px] font-black uppercase tracking-widest text-white/30 mb-3">Belum / Terlambat</p>
    <p class="text-2xl font-black text-red-400"><?= count($belum) ?> <span class="text-sm font-normal text-white/30">tagihan</span></p>
    <p class="text-xs text-white/30 mt-1">Perlu tindak lanjut segera</p>
  </div>
</div>

<!-- Tagihan Table -->
<div class="glass rounded-3xl overflow-hidden mb-6">
  <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
    <h3 class="font-bold text-sm text-white">Daftar Tagihan Aktif</h3>
    <span class="badge badge-indigo"><?= date('M Y') ?></span>
  </div>
  <div class="overflow-x-auto">
  <table class="data-table">
    <thead>
      <tr>
        <th class="text-left">Penyewa</th>
        <th class="text-left">Unit</th>
        <th class="text-right">Nominal</th>
        <th class="text-left">Jatuh Tempo</th>
        <th class="text-center">Status</th>
        <th class="text-center">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="6" class="text-center text-white/30 py-8 text-sm">Belum ada tagihan periode ini. Kirim tagihan dari halaman Data Penyewa.</td></tr>
      <?php endif; ?>
      <?php foreach ($rows as $r):
        $isOverdue = ($r['tanggal_jatuh_tempo'] < $today && $r['status'] !== 'Lunas');
        $status = $isOverdue ? 'Terlambat' : $r['status'];
        $badgeClass = match($status) {
          'Lunas'     => 'badge-green',
          'Menunggu'  => 'badge-yellow',
          'Terlambat' => 'badge-red',
          default     => 'badge-indigo',
        };
      ?>
        <tr>
          <td class="font-bold text-white"><?= htmlspecialchars($r['penyewa_nama']) ?></td>
          <td class="font-mono text-xs text-white/50"><?= htmlspecialchars($r['kode_unit'] . ' – ' . $r['nama_unit']) ?></td>
          <td class="text-right font-bold text-amber-400">Rp <?= number_format($r['nominal'],0,',','.') ?></td>
          <td class="text-xs <?= $isOverdue ? 'text-red-400 font-bold' : 'text-white/50' ?>"><?= htmlspecialchars($r['tanggal_jatuh_tempo']) ?></td>
          <td class="text-center"><span class="badge <?= $badgeClass ?>"><?= $status ?></span></td>
          <td class="text-center">
            <?php if ($status !== 'Lunas'): ?>
              <div class="flex items-center justify-center gap-2">
                <form method="POST" action="tagihan_reminder.php" class="inline">
                  <input type="hidden" name="tagihan_id" value="<?= (int)$r['id'] ?>"/>
                  <button type="submit" class="text-xs font-bold bg-emerald-500/15 hover:bg-emerald-500/30 text-emerald-400 border border-emerald-500/30 px-3 py-1.5 rounded-lg transition-all">
                    Kirim Reminder
                  </button>
                </form>
                <button onclick="openBayarModal(<?= (int)$r['id'] ?>, '<?= htmlspecialchars(addslashes($r['nomor'])) ?>')"
                        class="text-xs font-bold bg-indigo-500/15 hover:bg-indigo-500/30 text-indigo-400 border border-indigo-500/30 px-3 py-1.5 rounded-lg transition-all">
                  Konfirmasi Bayar
                </button>
              </div>
            <?php else: ?>
              <span class="text-xs text-white/20 font-semibold">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<!-- Modal Konfirmasi Pembayaran -->
<div id="bayarModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);">
  <div class="glass-strong rounded-3xl p-8 w-full max-w-sm fade-up">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-extrabold text-white">Konfirmasi Pembayaran</h3>
      <button type="button" onclick="closeBayarModal()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/60 transition-all">✕</button>
    </div>
    <p class="text-xs text-white/40 mb-5">Tagihan <span id="bayar_nomor" class="font-bold text-white/70">—</span></p>
    <form method="POST" action="tagihan_bayar.php" class="space-y-4">
      <input type="hidden" name="tagihan_id" id="bayar_tagihan_id" value=""/>
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Metode Pembayaran</label>
        <select name="metode" class="login-input" style="font-size:13px">
          <option>Transfer Bank</option>
          <option>Tunai</option>
          <option>QRIS</option>
          <option>Cek</option>
          <option>Giro</option>
          <option>Lainnya</option>
        </select>
      </div>
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">No. Referensi (opsional)</label>
        <input type="text" name="referensi" class="login-input" style="font-size:13px" placeholder="No. transaksi / bukti transfer"/>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeBayarModal()" class="flex-1 bg-white/5 border border-white/10 hover:bg-white/10 text-white/60 font-bold text-sm py-3 rounded-xl transition-all">Batal</button>
        <button type="submit" class="flex-1 login-btn py-3" style="background:linear-gradient(135deg,#4f46e5,#6366f1);font-size:12px;">Konfirmasi Lunas</button>
      </div>
    </form>
  </div>
</div>

<!-- Info Banner -->
<div class="glass rounded-2xl p-5 flex items-center gap-4 border border-indigo-500/20">
  <div class="w-12 h-12 rounded-2xl bg-indigo-500/15 flex items-center justify-center flex-shrink-0">
    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
  </div>
  <div>
    <p class="text-sm font-bold text-white mb-0.5">Konfirmasi Pembayaran Manual</p>
    <p class="text-xs text-white/40">Klik "Konfirmasi Bayar" setelah menerima transfer/pembayaran dari penyewa. Status tagihan otomatis berubah Lunas dan penyewa dapat notifikasi konfirmasi.</p>
  </div>
</div>

<script>
function openBayarModal(id, nomor) {
  document.getElementById('bayar_tagihan_id').value = id;
  document.getElementById('bayar_nomor').textContent = nomor;
  const modal = document.getElementById('bayarModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}
function closeBayarModal() {
  const modal = document.getElementById('bayarModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}
</script>
