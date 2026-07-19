<?php
require_once __DIR__ . '/../includes/db.php';

$rows = [];
$dbError = false;
if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT br.*, ta.nama AS tenant_nama, ta.email AS tenant_email,
                   u.kode AS kode_unit, u.nama AS nama_unit, u.harga_per_bulan, ku.nama AS kategori
            FROM booking_requests br
            JOIN tenant_accounts ta ON ta.id = br.tenant_account_id
            JOIN units u ON u.id = br.unit_id
            JOIN kategori_unit ku ON ku.id = u.kategori_id
            ORDER BY FIELD(br.status,'Menunggu','Disetujui','Ditolak'), br.created_at DESC
        ");
        $rows = $stmt->fetchAll();
    } catch (PDOException $e) {
        $dbError = true;
    }
} else {
    $dbError = true;
}

$menunggu  = array_filter($rows, fn($r) => $r['status'] === 'Menunggu');
$disetujui = array_filter($rows, fn($r) => $r['status'] === 'Disetujui');
$ditolak   = array_filter($rows, fn($r) => $r['status'] === 'Ditolak');
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
  <div>
    <div class="flex items-center gap-2 mb-1">
      <a href="dashboard.php" class="text-white/30 hover:text-white/60 text-xs font-semibold">Dashboard</a>
      <span class="text-white/20 text-xs">›</span>
      <span class="text-white/60 text-xs font-semibold">Pengajuan Sewa</span>
    </div>
    <h1 class="text-2xl font-extrabold text-white">Pengajuan Sewa dari Penyewa</h1>
    <p class="text-white/35 text-sm mt-0.5"><?= count($menunggu) ?> menunggu persetujuan</p>
  </div>
</div>

<?php if ($dbError): ?>
  <div class="mb-6 bg-red-500/10 border border-red-500/30 rounded-2xl px-5 py-4 text-sm text-red-400 font-semibold">
    Koneksi database gagal. Pastikan MySQL aktif di XAMPP.
  </div>
<?php endif; ?>

<!-- Summary -->
<div class="grid grid-cols-3 gap-4 mb-6">
  <div class="glass rounded-2xl p-4 border-l-4 border-amber-500">
    <p class="text-[9px] font-black uppercase tracking-widest text-white/30 mb-1">Menunggu</p>
    <p class="text-2xl font-black text-amber-400"><?= count($menunggu) ?></p>
  </div>
  <div class="glass rounded-2xl p-4 border-l-4 border-emerald-500">
    <p class="text-[9px] font-black uppercase tracking-widest text-white/30 mb-1">Disetujui</p>
    <p class="text-2xl font-black text-emerald-400"><?= count($disetujui) ?></p>
  </div>
  <div class="glass rounded-2xl p-4 border-l-4 border-red-500">
    <p class="text-[9px] font-black uppercase tracking-widest text-white/30 mb-1">Ditolak</p>
    <p class="text-2xl font-black text-red-400"><?= count($ditolak) ?></p>
  </div>
</div>

<!-- Filter Tabs -->
<div class="flex flex-wrap gap-2 mb-5">
  <button onclick="filterPengajuan('Menunggu')" data-pftab="Menunggu" class="pftab pftab-active px-4 py-2 rounded-xl text-xs font-bold transition-all">Menunggu (<?= count($menunggu) ?>)</button>
  <button onclick="filterPengajuan('Disetujui')" data-pftab="Disetujui" class="pftab px-4 py-2 rounded-xl text-xs font-bold transition-all">Disetujui (<?= count($disetujui) ?>)</button>
  <button onclick="filterPengajuan('Ditolak')" data-pftab="Ditolak" class="pftab px-4 py-2 rounded-xl text-xs font-bold transition-all">Ditolak (<?= count($ditolak) ?>)</button>
  <button onclick="filterPengajuan('all')" data-pftab="all" class="pftab px-4 py-2 rounded-xl text-xs font-bold transition-all">Semua (<?= count($rows) ?>)</button>
</div>

<!-- List -->
<div class="space-y-3" id="pengajuanList">
  <?php if (empty($rows)): ?>
    <div class="glass rounded-2xl p-8 text-center text-white/30 text-sm">Belum ada pengajuan sewa masuk.</div>
  <?php endif; ?>
  <?php foreach ($rows as $r):
    $badgeClass = match($r['status']) {
      'Menunggu'  => 'badge-yellow',
      'Disetujui' => 'badge-green',
      'Ditolak'   => 'badge-red',
      default     => 'badge-indigo',
    };
    $hargaFmt = 'Rp ' . number_format($r['harga_per_bulan'], 0, ',', '.');
  ?>
    <div class="glass rounded-2xl p-5 pengajuan-row" data-pstatus="<?= htmlspecialchars($r['status']) ?>">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1.5 flex-wrap">
            <span class="font-bold text-white"><?= htmlspecialchars($r['nama_pemohon']) ?></span>
            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($r['status']) ?></span>
            <span class="badge badge-sky"><?= htmlspecialchars($r['kategori']) ?></span>
          </div>
          <p class="text-xs text-white/40">
            Unit <strong class="text-white/60"><?= htmlspecialchars($r['kode_unit'] . ' – ' . $r['nama_unit']) ?></strong>
            · <?= $hargaFmt ?>/bln · Rencana mulai <?= htmlspecialchars($r['tanggal_mulai_rencana']) ?>
          </p>
          <p class="text-xs text-white/30 mt-1">
            Akun: <?= htmlspecialchars($r['tenant_nama']) ?> (<?= htmlspecialchars($r['tenant_email']) ?>) · Telp <?= htmlspecialchars($r['telepon']) ?>
          </p>
          <?php if (!empty($r['catatan'])): ?>
            <p class="text-xs text-white/35 mt-1.5 italic">"<?= htmlspecialchars($r['catatan']) ?>"</p>
          <?php endif; ?>
          <?php if ($r['status'] === 'Ditolak' && !empty($r['alasan_tolak'])): ?>
            <p class="text-xs text-red-400/70 mt-1.5">Alasan ditolak: <?= htmlspecialchars($r['alasan_tolak']) ?></p>
          <?php endif; ?>
        </div>
        <?php if ($r['status'] === 'Menunggu'): ?>
          <div class="flex items-center gap-2 flex-shrink-0">
            <button onclick='openApproveModal(<?= json_encode([
              "id" => $r["id"], "nama" => $r["nama_pemohon"], "telepon" => $r["telepon"],
              "tanggal_mulai" => $r["tanggal_mulai_rencana"], "harga_sewa" => $r["harga_per_bulan"],
            ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                    class="bg-emerald-500/15 hover:bg-emerald-500/30 border border-emerald-500/30 text-emerald-400 text-xs font-bold px-4 py-2 rounded-xl transition-all">
              Setujui
            </button>
            <button onclick="openRejectModal(<?= (int)$r['id'] ?>)"
                    class="bg-red-500/15 hover:bg-red-500/30 border border-red-500/30 text-red-400 text-xs font-bold px-4 py-2 rounded-xl transition-all">
              Tolak
            </button>
          </div>
        <?php elseif ($r['status'] === 'Disetujui'): ?>
          <div class="flex items-center gap-2 flex-shrink-0">
            <a href="penyewa_detail.php?id=<?= (int)$r['penyewa_id'] ?>" target="_blank"
               class="bg-sky-500/15 hover:bg-sky-500/30 border border-sky-500/30 text-sky-400 text-xs font-bold px-4 py-2 rounded-xl transition-all">
              Lihat Penyewa
            </a>
            <a href="kontrak_cetak.php?id=<?= (int)$r['kontrak_id'] ?>" target="_blank"
               class="bg-indigo-500/15 hover:bg-indigo-500/30 border border-indigo-500/30 text-indigo-400 text-xs font-bold px-4 py-2 rounded-xl transition-all">
              Lihat Kontrak
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Modal: Setujui (buat kontrak; data pribadi diisi sendiri oleh penyewa) -->
<div id="approveModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);">
  <div class="glass-strong rounded-3xl p-8 w-full max-w-md fade-up">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-extrabold text-white">Setujui &amp; Tentukan Syarat Kontrak</h3>
      <button type="button" onclick="closeApproveModal()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/60 transition-all">✕</button>
    </div>
    <p class="text-xs text-white/40 mb-5 leading-relaxed">
      Data pribadi/perusahaan (NIK, NPWP, alamat, dll) akan diisi sendiri oleh penyewa setelah disetujui, lewat portal mereka.
      Di sini Anda hanya menentukan syarat sewa.
    </p>
    <form method="POST" action="booking_approve.php" class="space-y-4">
      <input type="hidden" name="booking_id" id="ap_booking_id" value=""/>

      <div id="ap_pemohon_info" class="glass rounded-2xl p-3 mb-1">
        <p class="text-sm font-bold text-white" id="ap_nama_display">—</p>
        <p class="text-xs text-white/40" id="ap_telepon_display">—</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Tanggal Mulai <span class="text-red-400">*</span></label>
          <input type="date" name="tanggal_mulai" id="ap_tanggal_mulai" required class="login-input" style="font-size:13px"/>
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Tanggal Selesai <span class="text-red-400">*</span></label>
          <input type="date" name="tanggal_selesai" id="ap_tanggal_selesai" required class="login-input" style="font-size:13px"/>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Harga Sewa / Bulan <span class="text-red-400">*</span></label>
          <input type="text" name="harga_sewa" id="ap_harga_sewa" required class="login-input" style="font-size:13px"/>
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Deposit</label>
          <input type="text" name="deposit" id="ap_deposit" value="0" class="login-input" style="font-size:13px"/>
        </div>
      </div>

      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Catatan</label>
        <textarea name="catatan" rows="2" class="login-input resize-none" style="height:auto;font-size:13px"></textarea>
      </div>

      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeApproveModal()" class="flex-1 bg-white/5 border border-white/10 hover:bg-white/10 text-white/60 font-bold text-sm py-3 rounded-xl transition-all">Batal</button>
        <button type="submit" class="flex-1 login-btn py-3" style="background:linear-gradient(135deg,#059669,#10b981);font-size:12px;">Setujui &amp; Buat Kontrak</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Tolak -->
<div id="rejectModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);">
  <div class="glass-strong rounded-3xl p-8 w-full max-w-md fade-up">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-extrabold text-white">Tolak Pengajuan</h3>
      <button type="button" onclick="closeRejectModal()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/60 transition-all">✕</button>
    </div>
    <form method="POST" action="booking_reject.php" class="space-y-4">
      <input type="hidden" name="booking_id" id="rj_booking_id" value=""/>
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Alasan Penolakan (opsional)</label>
        <textarea name="alasan_tolak" rows="3" placeholder="Contoh: unit sudah dinegosiasikan penyewa lain"
                  class="login-input resize-none" style="height:auto;font-size:13px"></textarea>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeRejectModal()" class="flex-1 bg-white/5 border border-white/10 hover:bg-white/10 text-white/60 font-bold text-sm py-3 rounded-xl transition-all">Batal</button>
        <button type="submit" class="flex-1 login-btn py-3" style="background:linear-gradient(135deg,#dc2626,#ef4444);font-size:12px;">Tolak Pengajuan</button>
      </div>
    </form>
  </div>
</div>

<style>
.pftab { background:rgba(255,255,255,0.05); color:rgba(255,255,255,0.4); border:1px solid rgba(255,255,255,0.08); }
.pftab:hover { background:rgba(255,255,255,0.09); color:rgba(255,255,255,0.7); }
.pftab-active { background:rgba(99,102,241,0.2)!important; color:#a5b4fc!important; border-color:rgba(99,102,241,0.35)!important; }
</style>
<script>
function filterPengajuan(status) {
  document.querySelectorAll('.pftab').forEach(b => b.classList.remove('pftab-active'));
  document.querySelector(`[data-pftab="${status}"]`).classList.add('pftab-active');
  document.querySelectorAll('.pengajuan-row').forEach(row => {
    const s = row.getAttribute('data-pstatus');
    row.style.display = (status === 'all' || s === status) ? '' : 'none';
  });
}
function openApproveModal(data) {
  document.getElementById('ap_booking_id').value       = data.id;
  document.getElementById('ap_nama_display').textContent    = data.nama;
  document.getElementById('ap_telepon_display').textContent = data.telepon;
  document.getElementById('ap_tanggal_mulai').value    = data.tanggal_mulai;
  document.getElementById('ap_harga_sewa').value       = data.harga_sewa;
  const d = new Date(data.tanggal_mulai);
  d.setFullYear(d.getFullYear() + 1);
  d.setDate(d.getDate() - 1);
  document.getElementById('ap_tanggal_selesai').value  = d.toISOString().slice(0,10);
  const modal = document.getElementById('approveModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}
function closeApproveModal() {
  const modal = document.getElementById('approveModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}
function openRejectModal(id) {
  document.getElementById('rj_booking_id').value = id;
  const modal = document.getElementById('rejectModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}
function closeRejectModal() {
  const modal = document.getElementById('rejectModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}
</script>
