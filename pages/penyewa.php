<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/flash.php';

$today = date('Y-m-d');
$rows  = [];
$dbError = false;

if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT p.*,
                   k.id AS kontrak_id, k.nomor AS kontrak_nomor, k.harga_sewa, k.tanggal_selesai, k.status AS status_kontrak,
                   u.kode AS kode_unit, u.nama AS nama_unit, ku.nama AS kategori,
                   t.status AS status_tagihan, t.tanggal_jatuh_tempo, t.nominal
            FROM penyewa p
            LEFT JOIN kontrak k       ON k.penyewa_id = p.id AND k.status = 'Aktif'
            LEFT JOIN units u         ON u.id = k.unit_id
            LEFT JOIN kategori_unit ku ON ku.id = u.kategori_id
            LEFT JOIN tagihan t       ON t.kontrak_id = k.id
                                      AND t.periode_bulan = MONTH(CURDATE())
                                      AND t.periode_tahun  = YEAR(CURDATE())
            ORDER BY p.nama ASC
        ");
        $rows = $stmt->fetchAll();
    } catch (PDOException $e) {
        $dbError = true;
    }
} else {
    $dbError = true;
}

$lunas     = array_filter($rows, fn($p) => $p['status_tagihan'] === 'Lunas');
$menunggu  = array_filter($rows, fn($p) => $p['status_tagihan'] === 'Menunggu');
$terlambat = array_filter($rows, fn($p) => $p['status_tagihan'] === 'Terlambat' || (!empty($p['tanggal_jatuh_tempo']) && $p['tanggal_jatuh_tempo'] < $today && $p['status_tagihan'] !== 'Lunas'));
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
    <p class="text-white/35 text-sm mt-0.5"><?= count($rows) ?> penyewa terdaftar</p>
  </div>
  <div class="flex items-center gap-3">
    <button onclick="openPenyewaModal()" class="flex items-center gap-2 bg-emerald-600/15 hover:bg-emerald-600/25 border border-emerald-500/25 text-emerald-400 px-4 py-2.5 rounded-xl text-xs font-bold transition-all">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      Tambah Penyewa
    </button>
    <!-- Export Dropdown -->
    <div class="relative" id="exportDropdown">
      <button onclick="toggleExportMenu()" class="flex items-center gap-2 bg-indigo-600/15 hover:bg-indigo-600/25 border border-indigo-500/25 text-indigo-400 px-4 py-2.5 rounded-xl text-xs font-bold transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Export Data
        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div id="exportMenu" class="hidden absolute right-0 mt-2 w-52 notif-dropdown z-50">
        <p class="text-[9px] font-black uppercase tracking-widest text-white/30 mb-3">Pilih Format Export</p>
        <div class="space-y-1.5">
          <a href="export/penyewa.php?format=excel" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/8 transition-colors text-sm font-semibold text-white/70 hover:text-white">
            <div class="w-8 h-8 rounded-lg bg-emerald-500/15 flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div><p class="text-xs font-bold">Export Excel</p><p class="text-[10px] text-white/30">Format .xls (Microsoft Excel)</p></div>
          </a>
          <a href="export/penyewa.php?format=csv" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/8 transition-colors text-sm font-semibold text-white/70 hover:text-white">
            <div class="w-8 h-8 rounded-lg bg-sky-500/15 flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div><p class="text-xs font-bold">Export CSV</p><p class="text-[10px] text-white/30">Format .csv (universal)</p></div>
          </a>
          <div class="my-2 border-t border-white/8"></div>
          <a href="export/penyewa.php?format=print" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/8 transition-colors text-sm font-semibold text-white/70 hover:text-white">
            <div class="w-8 h-8 rounded-lg bg-violet-500/15 flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            </div>
            <div><p class="text-xs font-bold">Cetak / PDF</p><p class="text-[10px] text-white/30">Buka halaman cetak</p></div>
          </a>
        </div>
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

<?php if ($dbError): ?>
  <div class="mb-6 bg-red-500/10 border border-red-500/30 rounded-2xl px-5 py-4 text-sm text-red-400 font-semibold">
    Koneksi database gagal. Pastikan MySQL aktif di XAMPP untuk mengelola data penyewa.
  </div>
<?php endif; ?>

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
        <th class="text-left">Kontak</th>
        <th class="text-left">Unit</th>
        <th class="text-center">Status Tagihan</th>
        <th class="text-left">Jatuh Tempo</th>
        <th class="text-center">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="6" class="text-center text-white/30 py-8 text-sm">Belum ada data penyewa. Klik "Tambah Penyewa" untuk mulai mengisi.</td></tr>
      <?php endif; ?>
      <?php foreach ($rows as $p):
        $isOverdue = (!empty($p['tanggal_jatuh_tempo']) && $p['tanggal_jatuh_tempo'] < $today && $p['status_tagihan'] !== 'Lunas');
        $displayStatus = $isOverdue ? 'Terlambat' : ($p['status_tagihan'] ?? 'Belum Ada Tagihan');
        $badgeClass = match($displayStatus) {
          'Lunas'     => 'badge-green',
          'Menunggu'  => 'badge-yellow',
          'Terlambat' => 'badge-red',
          default     => 'badge-indigo',
        };
      ?>
        <tr class="table-row" data-status="<?= htmlspecialchars($displayStatus) ?>">
          <td>
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-xl bg-indigo-500/20 flex items-center justify-center text-xs font-black text-indigo-400 flex-shrink-0">
                <?= strtoupper(substr($p['nama'], 0, 1)) ?>
              </div>
              <div>
                <span class="font-bold text-white block"><?= htmlspecialchars($p['nama']) ?></span>
                <span class="text-[10px] text-white/30"><?= htmlspecialchars($p['jenis']) ?></span>
              </div>
            </div>
          </td>
          <td class="text-xs text-white/60">
            <?= htmlspecialchars($p['telepon']) ?><br>
            <span class="text-white/30"><?= htmlspecialchars($p['email'] ?: '-') ?></span>
          </td>
          <td class="font-mono text-xs text-white/60">
            <?= $p['kode_unit'] ? htmlspecialchars($p['kode_unit'] . ' – ' . $p['nama_unit']) : '<span class="text-white/25 font-sans">Belum ada unit</span>' ?>
          </td>
          <td class="text-center">
            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($displayStatus) ?></span>
          </td>
          <td class="<?= $isOverdue ? 'text-red-400 font-bold' : 'text-white/50' ?> text-xs">
            <?= htmlspecialchars($p['tanggal_jatuh_tempo'] ?? '-') ?>
          </td>
          <td class="text-center">
            <div class="flex items-center justify-center gap-2">
              <a href="penyewa_detail.php?id=<?= (int)$p['id'] ?>" target="_blank" class="text-sky-400 hover:text-sky-300 text-xs font-bold transition-colors">Detail</a>
              <?php if ($p['kontrak_id']): ?>
                <button onclick='openTagihanModal(<?= json_encode([
                  "kontrak_id" => $p["kontrak_id"], "nama" => $p["nama"], "kode_unit" => $p["kode_unit"],
                  "nama_unit" => $p["nama_unit"], "kategori" => $p["kategori"], "harga_sewa" => $p["harga_sewa"],
                ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                        class="text-amber-400 hover:text-amber-300 text-xs font-bold transition-colors">Kirim Tagihan</button>
              <?php endif; ?>
              <button onclick="openPenyewaModal(<?= (int)$p['id'] ?>)" class="text-indigo-400 hover:text-indigo-300 text-xs font-bold transition-colors">Edit</button>
              <form method="POST" action="penyewa_delete.php" onsubmit="return confirm('Hapus data penyewa &quot;<?= htmlspecialchars(addslashes($p['nama'])) ?>&quot;? Kontrak, tagihan, dan pembayaran terkait juga akan terhapus.');" class="inline">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>"/>
                <button type="submit" class="text-red-400 hover:text-red-300 text-xs font-bold transition-colors">Hapus</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<!-- Modal Tambah / Edit Penyewa -->
<div id="penyewaModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);">
  <div class="glass-strong rounded-3xl p-8 w-full max-w-2xl fade-up" style="max-height:90vh;overflow-y:auto;">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-extrabold text-white" id="penyewaModalTitle">Tambah Penyewa</h3>
      <button type="button" onclick="closePenyewaModal()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/60 transition-all">✕</button>
    </div>

    <form method="POST" action="penyewa_save.php" class="space-y-4">
      <input type="hidden" name="id" id="f_id" value=""/>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Nama / Badan Usaha <span class="text-red-400">*</span></label>
          <input type="text" name="nama" id="f_nama" required class="login-input" style="font-size:13px"/>
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Jenis</label>
          <select name="jenis" id="f_jenis" class="login-input" style="font-size:13px">
            <option value="Perorangan">Perorangan</option>
            <option value="CV">CV</option>
            <option value="PT">PT</option>
            <option value="UD">UD</option>
            <option value="Lainnya">Lainnya</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">NIK / NIB</label>
          <input type="text" name="nik_nib" id="f_nik_nib" class="login-input" style="font-size:13px"/>
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">NPWP</label>
          <input type="text" name="npwp" id="f_npwp" class="login-input" style="font-size:13px"/>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Nama Penanggung Jawab</label>
          <input type="text" name="nama_penanggung_jawab" id="f_pj_nama" class="login-input" style="font-size:13px"/>
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Jabatan Penanggung Jawab</label>
          <input type="text" name="jabatan_penanggung_jawab" id="f_pj_jabatan" class="login-input" style="font-size:13px"/>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Telepon <span class="text-red-400">*</span></label>
          <input type="tel" name="telepon" id="f_telepon" required class="login-input" style="font-size:13px"/>
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Email</label>
          <input type="email" name="email" id="f_email" class="login-input" style="font-size:13px"/>
        </div>
      </div>

      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Alamat</label>
        <textarea name="alamat" id="f_alamat" rows="2" class="login-input resize-none" style="height:auto;font-size:13px"></textarea>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Kota</label>
          <input type="text" name="kota" id="f_kota" class="login-input" style="font-size:13px"/>
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Provinsi</label>
          <input type="text" name="provinsi" id="f_provinsi" class="login-input" style="font-size:13px"/>
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Kode Pos</label>
          <input type="text" name="kode_pos" id="f_kode_pos" class="login-input" style="font-size:13px"/>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Kontak Darurat — Nama</label>
          <input type="text" name="kontak_darurat_nama" id="f_kd_nama" class="login-input" style="font-size:13px"/>
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Kontak Darurat — Telepon</label>
          <input type="tel" name="kontak_darurat_telepon" id="f_kd_telepon" class="login-input" style="font-size:13px"/>
        </div>
      </div>

      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Catatan</label>
        <textarea name="catatan" id="f_catatan" rows="2" class="login-input resize-none" style="height:auto;font-size:13px"></textarea>
      </div>

      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closePenyewaModal()" class="flex-1 bg-white/5 border border-white/10 hover:bg-white/10 text-white/60 font-bold text-sm py-3 rounded-xl transition-all">Batal</button>
        <button type="submit" class="flex-1 login-btn py-3" style="font-size:12px;">Simpan</button>
      </div>
    </form>
  </div>
</div>

<style>
.filter-btn { background:rgba(255,255,255,0.05); color:rgba(255,255,255,0.4); border:1px solid rgba(255,255,255,0.08); }
.filter-btn:hover { background:rgba(255,255,255,0.09); color:rgba(255,255,255,0.7); }
.filter-btn.active { background:rgba(99,102,241,0.2); color:#a5b4fc; border-color:rgba(99,102,241,0.35); }
</style>

<script>
const penyewaData = <?= json_encode(array_reduce($rows, function ($carry, $p) {
    $carry[$p['id']] = [
        'nama' => $p['nama'], 'jenis' => $p['jenis'], 'nik_nib' => $p['nik_nib'], 'npwp' => $p['npwp'],
        'nama_penanggung_jawab' => $p['nama_penanggung_jawab'], 'jabatan_penanggung_jawab' => $p['jabatan_penanggung_jawab'],
        'telepon' => $p['telepon'], 'email' => $p['email'], 'alamat' => $p['alamat'],
        'kota' => $p['kota'], 'provinsi' => $p['provinsi'], 'kode_pos' => $p['kode_pos'],
        'kontak_darurat_nama' => $p['kontak_darurat_nama'], 'kontak_darurat_telepon' => $p['kontak_darurat_telepon'],
        'catatan' => $p['catatan'],
    ];
    return $carry;
}, []), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) ?>;

function openPenyewaModal(id) {
  const modal = document.getElementById('penyewaModal');
  const form  = modal.querySelector('form');
  form.reset();
  if (id && penyewaData[id]) {
    document.getElementById('penyewaModalTitle').textContent = 'Edit Penyewa';
    document.getElementById('f_id').value = id;
    const d = penyewaData[id];
    document.getElementById('f_nama').value        = d.nama || '';
    document.getElementById('f_jenis').value       = d.jenis || 'Perorangan';
    document.getElementById('f_nik_nib').value     = d.nik_nib || '';
    document.getElementById('f_npwp').value        = d.npwp || '';
    document.getElementById('f_pj_nama').value     = d.nama_penanggung_jawab || '';
    document.getElementById('f_pj_jabatan').value  = d.jabatan_penanggung_jawab || '';
    document.getElementById('f_telepon').value     = d.telepon || '';
    document.getElementById('f_email').value       = d.email || '';
    document.getElementById('f_alamat').value      = d.alamat || '';
    document.getElementById('f_kota').value        = d.kota || '';
    document.getElementById('f_provinsi').value    = d.provinsi || '';
    document.getElementById('f_kode_pos').value    = d.kode_pos || '';
    document.getElementById('f_kd_nama').value     = d.kontak_darurat_nama || '';
    document.getElementById('f_kd_telepon').value  = d.kontak_darurat_telepon || '';
    document.getElementById('f_catatan').value     = d.catatan || '';
  } else {
    document.getElementById('penyewaModalTitle').textContent = 'Tambah Penyewa';
    document.getElementById('f_id').value = '';
  }
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}
function closePenyewaModal() {
  const modal = document.getElementById('penyewaModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

/* ===== Modal Kirim Tagihan ===== */
function formatRupiah(n) {
  return 'Rp ' + (n || 0).toLocaleString('id-ID');
}
function recalcGrandTotal() {
  const sewa    = parseInt(document.getElementById('tg_biaya_sewa').value.replace(/\D/g,'')) || 0;
  const listrik = parseInt(document.getElementById('tg_biaya_listrik').value.replace(/\D/g,'')) || 0;
  const air     = parseInt(document.getElementById('tg_biaya_air').value.replace(/\D/g,'')) || 0;
  document.getElementById('tg_grand_total').textContent = formatRupiah(sewa + listrik + air);
}
function openTagihanModal(data) {
  document.getElementById('tg_kontrak_id').value = data.kontrak_id;
  document.getElementById('tg_info').textContent = data.nama + ' — ' + data.kode_unit + ' – ' + data.nama_unit + ' (' + data.kategori + ')';
  document.getElementById('tg_biaya_sewa').value = data.harga_sewa;
  document.getElementById('tg_biaya_listrik').value = 0;
  document.getElementById('tg_biaya_air').value = 0;
  document.getElementById('tg_catatan').value = '';
  const now = new Date();
  document.getElementById('tg_periode_bulan').value = now.getMonth() + 1;
  document.getElementById('tg_periode_tahun').value = now.getFullYear();
  const jatuhTempo = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 10);
  document.getElementById('tg_jatuh_tempo').value = jatuhTempo.toISOString().slice(0,10);
  recalcGrandTotal();
  const modal = document.getElementById('tagihanModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}
function closeTagihanModal() {
  const modal = document.getElementById('tagihanModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}
</script>

<!-- Modal Kirim Tagihan -->
<div id="tagihanModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);">
  <div class="glass-strong rounded-3xl p-8 w-full max-w-md fade-up">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-extrabold text-white">Kirim Tagihan</h3>
      <button type="button" onclick="closeTagihanModal()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/60 transition-all">✕</button>
    </div>
    <p class="text-xs text-white/40 mb-5" id="tg_info">—</p>

    <form method="POST" action="tagihan_kirim.php" class="space-y-4" onsubmit="return true;">
      <input type="hidden" name="kontrak_id" id="tg_kontrak_id" value=""/>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Periode Bulan</label>
          <select name="periode_bulan" id="tg_periode_bulan" class="login-input" style="font-size:13px">
            <?php foreach (['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $bln): ?>
              <option value="<?= $i+1 ?>"><?= $bln ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Tahun</label>
          <input type="number" name="periode_tahun" id="tg_periode_tahun" class="login-input" style="font-size:13px"/>
        </div>
      </div>

      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Sewa Properti (Rp)</label>
        <input type="text" name="biaya_sewa" id="tg_biaya_sewa" oninput="recalcGrandTotal()" required class="login-input" style="font-size:13px"/>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Listrik (Rp)</label>
          <input type="text" name="biaya_listrik" id="tg_biaya_listrik" oninput="recalcGrandTotal()" class="login-input" style="font-size:13px"/>
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Air (Rp)</label>
          <input type="text" name="biaya_air" id="tg_biaya_air" oninput="recalcGrandTotal()" class="login-input" style="font-size:13px"/>
        </div>
      </div>

      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Jatuh Tempo</label>
        <input type="date" name="tanggal_jatuh_tempo" id="tg_jatuh_tempo" required class="login-input" style="font-size:13px"/>
      </div>

      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Catatan (opsional)</label>
        <textarea name="catatan" id="tg_catatan" rows="2" class="login-input resize-none" style="height:auto;font-size:13px"></textarea>
      </div>

      <div class="glass rounded-2xl p-4 flex items-center justify-between">
        <span class="text-xs font-bold text-white/50 uppercase tracking-widest">Grand Total</span>
        <span class="text-xl font-black text-amber-400" id="tg_grand_total">Rp 0</span>
      </div>

      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeTagihanModal()" class="flex-1 bg-white/5 border border-white/10 hover:bg-white/10 text-white/60 font-bold text-sm py-3 rounded-xl transition-all">Batal</button>
        <button type="submit" class="flex-1 login-btn py-3" style="background:linear-gradient(135deg,#d97706,#f59e0b);font-size:12px;">Kirim Tagihan</button>
      </div>
    </form>
  </div>
</div>
