<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'includes/db.php';

$id = (int)($_GET['id'] ?? 0);
$penyewa = null;
$kontrakList = [];

if ($pdo && $id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM penyewa WHERE id = ?");
        $stmt->execute([$id]);
        $penyewa = $stmt->fetch();

        if ($penyewa) {
            $kStmt = $pdo->prepare("
                SELECT k.*, u.kode AS kode_unit, u.nama AS nama_unit, ku.nama AS kategori
                FROM kontrak k
                JOIN units u ON u.id = k.unit_id
                JOIN kategori_unit ku ON ku.id = u.kategori_id
                WHERE k.penyewa_id = ?
                ORDER BY k.created_at DESC
            ");
            $kStmt->execute([$id]);
            $kontrakList = $kStmt->fetchAll();
        }
    } catch (PDOException $e) {
        $penyewa = null;
    }
}

if (!$penyewa) {
    die('Data penyewa tidak ditemukan.');
}

$kodePenyewa = 'PYW-' . str_pad($penyewa['id'], 4, '0', STR_PAD_LEFT);
$isLengkap = !empty($penyewa['nik_nib']) && !empty($penyewa['alamat']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <title>Detail Penyewa — <?= htmlspecialchars($penyewa['nama']) ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #1a1a2e; background: #f0f4f9; }
    .page { max-width: 760px; margin: 30px auto; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }

    .cv-header { background: linear-gradient(135deg,#1e3a5f,#2d5a8c); color: #fff; padding: 28px 32px; display: flex; align-items: center; gap: 18px; }
    .cv-avatar { width: 64px; height: 64px; border-radius: 16px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 900; flex-shrink: 0; }
    .cv-header h1 { font-size: 20px; font-weight: 800; }
    .cv-header p { font-size: 12px; opacity: 0.75; margin-top: 3px; }
    .cv-badge { margin-left: auto; text-align: right; }
    .cv-badge .id { font-size: 11px; font-weight: 700; background: rgba(255,255,255,0.15); padding: 4px 12px; border-radius: 20px; display: inline-block; margin-bottom: 6px; }
    .cv-badge .status { font-size: 10px; padding: 3px 10px; border-radius: 20px; font-weight: 700; text-transform: uppercase; }
    .status-lengkap { background: #d1fae5; color: #065f46; }
    .status-belum   { background: #fef3c7; color: #92400e; }

    .cv-body { padding: 28px 32px; }
    .section { margin-bottom: 24px; }
    .section h3 { font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #1e3a5f; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 12px; }
    .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 24px; }
    .field .lbl { font-size: 9px; text-transform: uppercase; color: #999; letter-spacing: 0.06em; margin-bottom: 2px; }
    .field .val { font-size: 12px; font-weight: 600; color: #1a1a2e; }
    .field .val.empty { color: #bbb; font-weight: 400; font-style: italic; }

    table.kontrak-table { width: 100%; border-collapse: collapse; font-size: 11px; }
    table.kontrak-table th { background: #1e3a5f; color: #fff; padding: 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
    table.kontrak-table td { padding: 8px; border: 1px solid #e2e8f0; }
    .badge { padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }
    .badge-aktif { background: #d1fae5; color: #065f46; }
    .badge-berakhir { background: #f1f5f9; color: #475569; }
    .badge-dibatalkan { background: #fee2e2; color: #991b1b; }

    .print-controls { position: fixed; top: 16px; right: 16px; display: flex; gap: 8px; z-index: 999; }
    .btn-print  { background: #1e3a5f; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; }
    .btn-close  { background: #ef4444; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; }
    @media print { .print-controls { display: none; } body { background: #fff; } .page { box-shadow: none; margin: 0; } }
  </style>
</head>
<body>

<div class="print-controls">
  <button class="btn-print" onclick="window.print()">Cetak</button>
  <button class="btn-close" onclick="window.close()">✕ Tutup</button>
</div>

<div class="page">
  <div class="cv-header">
    <div class="cv-avatar"><?= strtoupper(substr($penyewa['nama'], 0, 1)) ?></div>
    <div>
      <h1><?= htmlspecialchars($penyewa['nama']) ?></h1>
      <p><?= htmlspecialchars($penyewa['jenis']) ?><?= $penyewa['jabatan_penanggung_jawab'] ? ' · ' . htmlspecialchars($penyewa['jabatan_penanggung_jawab']) : '' ?></p>
    </div>
    <div class="cv-badge">
      <div class="id"><?= $kodePenyewa ?></div><br>
      <span class="status <?= $isLengkap ? 'status-lengkap' : 'status-belum' ?>"><?= $isLengkap ? 'Data Lengkap' : 'Belum Lengkap' ?></span>
    </div>
  </div>

  <div class="cv-body">
    <div class="section">
      <h3>Data Kontak</h3>
      <div class="grid2">
        <div class="field"><p class="lbl">Telepon</p><p class="val"><?= htmlspecialchars($penyewa['telepon']) ?></p></div>
        <div class="field"><p class="lbl">Email</p><p class="val <?= empty($penyewa['email']) ? 'empty' : '' ?>"><?= htmlspecialchars($penyewa['email'] ?: 'Belum diisi') ?></p></div>
        <div class="field"><p class="lbl">NIK / NIB</p><p class="val <?= empty($penyewa['nik_nib']) ? 'empty' : '' ?>"><?= htmlspecialchars($penyewa['nik_nib'] ?: 'Belum diisi') ?></p></div>
        <div class="field"><p class="lbl">NPWP</p><p class="val <?= empty($penyewa['npwp']) ? 'empty' : '' ?>"><?= htmlspecialchars($penyewa['npwp'] ?: 'Belum diisi') ?></p></div>
      </div>
    </div>

    <div class="section">
      <h3>Penanggung Jawab &amp; Alamat</h3>
      <div class="grid2">
        <div class="field"><p class="lbl">Nama Penanggung Jawab</p><p class="val <?= empty($penyewa['nama_penanggung_jawab']) ? 'empty' : '' ?>"><?= htmlspecialchars($penyewa['nama_penanggung_jawab'] ?: 'Belum diisi') ?></p></div>
        <div class="field"><p class="lbl">Jabatan</p><p class="val <?= empty($penyewa['jabatan_penanggung_jawab']) ? 'empty' : '' ?>"><?= htmlspecialchars($penyewa['jabatan_penanggung_jawab'] ?: 'Belum diisi') ?></p></div>
        <div class="field" style="grid-column:1/3;"><p class="lbl">Alamat</p><p class="val <?= empty($penyewa['alamat']) ? 'empty' : '' ?>"><?= htmlspecialchars($penyewa['alamat'] ?: 'Belum diisi') ?><?= $penyewa['kota'] ? ', ' . htmlspecialchars($penyewa['kota']) : '' ?><?= $penyewa['provinsi'] ? ', ' . htmlspecialchars($penyewa['provinsi']) : '' ?><?= $penyewa['kode_pos'] ? ' ' . htmlspecialchars($penyewa['kode_pos']) : '' ?></p></div>
        <div class="field"><p class="lbl">Kontak Darurat</p><p class="val <?= empty($penyewa['kontak_darurat_nama']) ? 'empty' : '' ?>"><?= htmlspecialchars($penyewa['kontak_darurat_nama'] ?: 'Belum diisi') ?></p></div>
        <div class="field"><p class="lbl">Telepon Darurat</p><p class="val <?= empty($penyewa['kontak_darurat_telepon']) ? 'empty' : '' ?>"><?= htmlspecialchars($penyewa['kontak_darurat_telepon'] ?: '-') ?></p></div>
      </div>
      <?php if (!empty($penyewa['catatan'])): ?>
        <div class="field" style="margin-top:12px;"><p class="lbl">Catatan</p><p class="val" style="font-weight:400;"><?= nl2br(htmlspecialchars($penyewa['catatan'])) ?></p></div>
      <?php endif; ?>
    </div>

    <div class="section">
      <h3>Riwayat Kontrak</h3>
      <?php if (empty($kontrakList)): ?>
        <p style="color:#999;font-size:11px;">Belum ada kontrak untuk penyewa ini.</p>
      <?php else: ?>
        <table class="kontrak-table">
          <thead><tr><th>No. Kontrak</th><th>Unit</th><th>Masa Sewa</th><th>Harga/Bln</th><th>Status</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($kontrakList as $k):
              $badgeCls = match($k['status']) { 'Aktif' => 'badge-aktif', 'Berakhir' => 'badge-berakhir', default => 'badge-dibatalkan' };
            ?>
              <tr>
                <td><?= htmlspecialchars($k['nomor']) ?></td>
                <td><?= htmlspecialchars($k['kode_unit'] . ' – ' . $k['nama_unit']) ?></td>
                <td><?= htmlspecialchars($k['tanggal_mulai']) ?> s/d <?= htmlspecialchars($k['tanggal_selesai']) ?></td>
                <td>Rp <?= number_format($k['harga_sewa'], 0, ',', '.') ?></td>
                <td><span class="badge <?= $badgeCls ?>"><?= htmlspecialchars($k['status']) ?></span></td>
                <td><a href="kontrak_cetak.php?id=<?= (int)$k['id'] ?>" target="_blank" style="color:#4f46e5;font-weight:700;text-decoration:none;font-size:10px;">Lihat Kontrak</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
