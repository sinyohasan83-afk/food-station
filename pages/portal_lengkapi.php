<?php
require_once __DIR__ . '/../includes/db.php';

$tenantId = (int)($_SESSION['tenant_id'] ?? 0);
$rows = [];
$dbError = false;

if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, k.id AS kontrak_id, k.nomor AS kontrak_nomor, u.kode AS kode_unit, u.nama AS nama_unit
            FROM penyewa p
            LEFT JOIN kontrak k ON k.penyewa_id = p.id AND k.status = 'Aktif'
            LEFT JOIN units u   ON u.id = k.unit_id
            WHERE p.tenant_account_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$tenantId]);
        $rows = $stmt->fetchAll();
    } catch (PDOException $e) {
        $dbError = true;
    }
} else {
    $dbError = true;
}
?>

<div class="mb-6">
  <h1 class="text-2xl font-extrabold text-white">Lengkapi Data Penyewa</h1>
  <p class="text-white/35 text-sm mt-0.5">Lengkapi data pribadi/perusahaan untuk setiap kontrak sewa yang sudah disetujui admin.</p>
</div>

<?php if ($dbError): ?>
  <div class="mb-6 bg-red-500/10 border border-red-500/30 rounded-2xl px-5 py-4 text-sm text-red-400 font-semibold">
    Koneksi database gagal. Pastikan MySQL aktif di XAMPP.
  </div>
<?php endif; ?>

<?php if (empty($rows)): ?>
  <div class="glass rounded-2xl p-8 text-center text-white/30 text-sm">
    Belum ada pengajuan sewa yang disetujui. Ajukan booking unit terlebih dahulu di menu Katalog Unit.
  </div>
<?php endif; ?>

<div class="space-y-4">
  <?php foreach ($rows as $p):
    $kodePenyewa = 'PYW-' . str_pad($p['id'], 4, '0', STR_PAD_LEFT);
    $isLengkap = !empty($p['nik_nib']) && !empty($p['alamat']);
  ?>
    <div class="glass rounded-2xl p-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div>
          <div class="flex items-center gap-2 flex-wrap mb-1">
            <span class="font-bold text-white">ID Penyewa: <?= $kodePenyewa ?></span>
            <?php if ($isLengkap): ?>
              <span class="badge badge-green">Data Lengkap</span>
            <?php else: ?>
              <span class="badge badge-yellow">Belum Lengkap</span>
            <?php endif; ?>
          </div>
          <p class="text-xs text-white/40">
            <?= $p['kode_unit'] ? htmlspecialchars($p['kode_unit'] . ' – ' . $p['nama_unit']) : '-' ?>
            <?= $p['kontrak_nomor'] ? ' · Kontrak ' . htmlspecialchars($p['kontrak_nomor']) : '' ?>
          </p>
        </div>
        <?php if ($p['kontrak_nomor']): ?>
          <a href="kontrak_cetak.php?id=<?= (int)$p['kontrak_id'] ?>" target="_blank"
             class="text-xs font-bold bg-indigo-600/15 hover:bg-indigo-600/25 border border-indigo-500/25 text-indigo-400 px-4 py-2 rounded-xl transition-all text-center">
            Lihat Lembar Kontrak
          </a>
        <?php endif; ?>
      </div>

      <form method="POST" action="penyewa_lengkapi_submit.php" class="space-y-4">
        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>"/>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Nama / Badan Usaha <span class="text-red-400">*</span></label>
            <input type="text" name="nama" required value="<?= htmlspecialchars($p['nama']) ?>" class="login-input" style="font-size:13px"/>
          </div>
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Jenis</label>
            <select name="jenis" class="login-input" style="font-size:13px">
              <?php foreach (['Perorangan','CV','PT','UD','Lainnya'] as $j): ?>
                <option value="<?= $j ?>" <?= $p['jenis'] === $j ? 'selected' : '' ?>><?= $j ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">NIK / NIB</label>
            <input type="text" name="nik_nib" value="<?= htmlspecialchars($p['nik_nib'] ?? '') ?>" class="login-input" style="font-size:13px"/>
          </div>
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">NPWP</label>
            <input type="text" name="npwp" value="<?= htmlspecialchars($p['npwp'] ?? '') ?>" class="login-input" style="font-size:13px"/>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Nama Penanggung Jawab</label>
            <input type="text" name="nama_penanggung_jawab" value="<?= htmlspecialchars($p['nama_penanggung_jawab'] ?? '') ?>" class="login-input" style="font-size:13px"/>
          </div>
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Jabatan Penanggung Jawab</label>
            <input type="text" name="jabatan_penanggung_jawab" value="<?= htmlspecialchars($p['jabatan_penanggung_jawab'] ?? '') ?>" class="login-input" style="font-size:13px"/>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Telepon <span class="text-red-400">*</span></label>
            <input type="tel" name="telepon" required value="<?= htmlspecialchars($p['telepon']) ?>" class="login-input" style="font-size:13px"/>
          </div>
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($p['email'] ?? '') ?>" class="login-input" style="font-size:13px"/>
          </div>
        </div>

        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Alamat</label>
          <textarea name="alamat" rows="2" class="login-input resize-none" style="height:auto;font-size:13px"><?= htmlspecialchars($p['alamat'] ?? '') ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Kota</label>
            <input type="text" name="kota" value="<?= htmlspecialchars($p['kota'] ?? '') ?>" class="login-input" style="font-size:13px"/>
          </div>
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Provinsi</label>
            <input type="text" name="provinsi" value="<?= htmlspecialchars($p['provinsi'] ?? '') ?>" class="login-input" style="font-size:13px"/>
          </div>
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Kode Pos</label>
            <input type="text" name="kode_pos" value="<?= htmlspecialchars($p['kode_pos'] ?? '') ?>" class="login-input" style="font-size:13px"/>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Kontak Darurat — Nama</label>
            <input type="text" name="kontak_darurat_nama" value="<?= htmlspecialchars($p['kontak_darurat_nama'] ?? '') ?>" class="login-input" style="font-size:13px"/>
          </div>
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Kontak Darurat — Telepon</label>
            <input type="tel" name="kontak_darurat_telepon" value="<?= htmlspecialchars($p['kontak_darurat_telepon'] ?? '') ?>" class="login-input" style="font-size:13px"/>
          </div>
        </div>

        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Catatan</label>
          <textarea name="catatan" rows="2" class="login-input resize-none" style="height:auto;font-size:13px"><?= htmlspecialchars($p['catatan'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="login-btn py-3" style="font-size:12px;">Simpan Data</button>
      </form>
    </div>
  <?php endforeach; ?>
</div>
