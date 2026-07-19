<?php
require_once __DIR__ . '/../includes/db.php';

$rows = [];
$dbError = false;
if ($pdo) {
    try {
        $rows = $pdo->query("SELECT * FROM rekening_perusahaan ORDER BY urutan ASC, id ASC")->fetchAll();
    } catch (PDOException $e) {
        $dbError = true;
    }
} else {
    $dbError = true;
}
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
  <div>
    <div class="flex items-center gap-2 mb-1">
      <a href="dashboard.php" class="text-white/30 hover:text-white/60 text-xs font-semibold">Dashboard</a>
      <span class="text-white/20 text-xs">›</span>
      <span class="text-white/60 text-xs font-semibold">Rekening Perusahaan</span>
    </div>
    <h1 class="text-2xl font-extrabold text-white">Rekening Perusahaan</h1>
    <p class="text-white/35 text-sm mt-0.5">Ditampilkan di pesan approval penyewa dan lembar kontrak kerjasama.</p>
  </div>
  <button onclick="openRekModal()" class="flex items-center gap-2 bg-emerald-600/15 hover:bg-emerald-600/25 border border-emerald-500/25 text-emerald-400 px-4 py-2.5 rounded-xl text-xs font-bold transition-all">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Tambah Rekening
  </button>
</div>

<?php if ($dbError): ?>
  <div class="mb-6 bg-red-500/10 border border-red-500/30 rounded-2xl px-5 py-4 text-sm text-red-400 font-semibold">
    Koneksi database gagal. Pastikan MySQL aktif di XAMPP.
  </div>
<?php endif; ?>

<div class="mb-6 bg-amber-500/8 border border-amber-500/20 rounded-2xl px-5 py-4 text-xs text-amber-300/80">
  ⚠️ Rekening dengan atas nama "GANTI DENGAN REKENING ASLI" adalah data contoh. Segera edit dengan nomor rekening asli perusahaan sebelum digunakan.
</div>

<div class="glass rounded-3xl overflow-hidden">
  <div class="overflow-x-auto">
  <table class="data-table">
    <thead>
      <tr>
        <th class="text-left">Bank</th>
        <th class="text-left">No. Rekening</th>
        <th class="text-left">Atas Nama</th>
        <th class="text-center">Status</th>
        <th class="text-center">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="5" class="text-center text-white/30 py-8 text-sm">Belum ada rekening perusahaan.</td></tr>
      <?php endif; ?>
      <?php foreach ($rows as $r): ?>
        <tr class="table-row">
          <td class="font-bold text-white"><?= htmlspecialchars($r['nama_bank']) ?></td>
          <td class="font-mono text-xs text-white/60"><?= htmlspecialchars($r['nomor_rekening']) ?></td>
          <td class="text-xs text-white/60"><?= htmlspecialchars($r['atas_nama']) ?></td>
          <td class="text-center">
            <span class="badge <?= $r['is_active'] ? 'badge-green' : 'badge-red' ?>"><?= $r['is_active'] ? 'Aktif' : 'Nonaktif' ?></span>
          </td>
          <td class="text-center">
            <div class="flex items-center justify-center gap-2">
              <button onclick='openRekModal(<?= json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="text-indigo-400 hover:text-indigo-300 text-xs font-bold transition-colors">Edit</button>
              <form method="POST" action="rekening_delete.php" onsubmit="return confirm('Hapus rekening <?= htmlspecialchars(addslashes($r['nama_bank'])) ?>?');" class="inline">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>"/>
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

<!-- Modal -->
<div id="rekModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);">
  <div class="glass-strong rounded-3xl p-8 w-full max-w-md fade-up">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-extrabold text-white" id="rekModalTitle">Tambah Rekening</h3>
      <button type="button" onclick="closeRekModal()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/60 transition-all">✕</button>
    </div>
    <form method="POST" action="rekening_save.php" class="space-y-4">
      <input type="hidden" name="id" id="rek_id" value=""/>
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Nama Bank <span class="text-red-400">*</span></label>
        <input type="text" name="nama_bank" id="rek_nama_bank" required class="login-input" style="font-size:13px" placeholder="Contoh: BCA"/>
      </div>
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Nomor Rekening <span class="text-red-400">*</span></label>
        <input type="text" name="nomor_rekening" id="rek_nomor" required class="login-input" style="font-size:13px"/>
      </div>
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Atas Nama <span class="text-red-400">*</span></label>
        <input type="text" name="atas_nama" id="rek_atas_nama" required class="login-input" style="font-size:13px"/>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Urutan Tampil</label>
          <input type="number" name="urutan" id="rek_urutan" value="0" class="login-input" style="font-size:13px"/>
        </div>
        <div class="flex items-end pb-2.5">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_active" id="rek_active" checked class="w-4 h-4 rounded accent-emerald-500"/>
            <span class="text-xs font-semibold text-white/60">Aktif (tampil)</span>
          </label>
        </div>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeRekModal()" class="flex-1 bg-white/5 border border-white/10 hover:bg-white/10 text-white/60 font-bold text-sm py-3 rounded-xl transition-all">Batal</button>
        <button type="submit" class="flex-1 login-btn py-3" style="font-size:12px;">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function openRekModal(data) {
  const modal = document.getElementById('rekModal');
  modal.querySelector('form').reset();
  if (data && data.id) {
    document.getElementById('rekModalTitle').textContent = 'Edit Rekening';
    document.getElementById('rek_id').value = data.id;
    document.getElementById('rek_nama_bank').value = data.nama_bank;
    document.getElementById('rek_nomor').value = data.nomor_rekening;
    document.getElementById('rek_atas_nama').value = data.atas_nama;
    document.getElementById('rek_urutan').value = data.urutan;
    document.getElementById('rek_active').checked = data.is_active == 1;
  } else {
    document.getElementById('rekModalTitle').textContent = 'Tambah Rekening';
    document.getElementById('rek_id').value = '';
    document.getElementById('rek_active').checked = true;
  }
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}
function closeRekModal() {
  const modal = document.getElementById('rekModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}
</script>
