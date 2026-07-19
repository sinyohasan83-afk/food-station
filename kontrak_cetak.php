<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$isAdmin  = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$isTenant = isset($_SESSION['tenant_logged_in']) && $_SESSION['tenant_logged_in'] === true;

if (!$isAdmin && !$isTenant) {
    header('Location: index.php');
    exit;
}

require_once 'includes/db.php';

$kontrakId = (int)($_GET['id'] ?? 0);
$kontrak = null;

if ($pdo && $kontrakId > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT k.*, p.nama AS penyewa_nama, p.jenis, p.nik_nib, p.npwp, p.nama_penanggung_jawab,
                   p.jabatan_penanggung_jawab, p.telepon AS penyewa_telepon, p.email AS penyewa_email,
                   p.alamat AS penyewa_alamat, p.kota, p.provinsi, p.tenant_account_id,
                   u.kode AS kode_unit, u.nama AS nama_unit, u.luas, ku.nama AS kategori
            FROM kontrak k
            JOIN penyewa p ON p.id = k.penyewa_id
            JOIN units u   ON u.id = k.unit_id
            JOIN kategori_unit ku ON ku.id = u.kategori_id
            WHERE k.id = ?
        ");
        $stmt->execute([$kontrakId]);
        $kontrak = $stmt->fetch();
    } catch (PDOException $e) {
        $kontrak = null;
    }
}

if (!$kontrak) {
    die('Kontrak tidak ditemukan.');
}

// Tenant hanya boleh melihat kontrak miliknya sendiri
if (!$isAdmin && (int)$kontrak['tenant_account_id'] !== (int)($_SESSION['tenant_id'] ?? 0)) {
    die('Akses ditolak. Anda tidak memiliki izin untuk melihat kontrak ini.');
}

$pengaturan = [];
if ($pdo) {
    try {
        foreach ($pdo->query("SELECT kunci, nilai FROM pengaturan") as $row) {
            $pengaturan[$row['kunci']] = $row['nilai'];
        }
    } catch (PDOException $e) {
        $pengaturan = [];
    }
}
$namaPerusahaan = $pengaturan['nama_perusahaan'] ?? 'PT. Food Station';
$alamatPerusahaan = $pengaturan['alamat'] ?? '-';
$teleponPerusahaan = $pengaturan['telepon'] ?? '-';

$rekening = [];
if ($pdo) {
    try {
        $rekening = $pdo->query("SELECT * FROM rekening_perusahaan WHERE is_active = 1 ORDER BY urutan ASC")->fetchAll();
    } catch (PDOException $e) {
        $rekening = [];
    }
}

$kodePenyewa = 'PYW-' . str_pad($kontrak['penyewa_id'], 4, '0', STR_PAD_LEFT);
$luasFmt  = rtrim(rtrim(number_format((float)$kontrak['luas'], 2, '.', ''), '0'), '.') . ' m²';
$hargaFmt = 'Rp ' . number_format($kontrak['harga_sewa'], 0, ',', '.') . ' / bulan';
$depositFmt = 'Rp ' . number_format($kontrak['deposit'], 0, ',', '.');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <title>Lembar Kontrak Kerjasama — <?= htmlspecialchars($kontrak['nomor']) ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #1a1a2e; background: #fff; }
    .page { max-width: 800px; margin: 0 auto; padding: 30px 36px; }

    .report-header { display: flex; align-items: center; gap: 16px; border-bottom: 3px solid #1e3a5f; padding-bottom: 14px; margin-bottom: 20px; }
    .logo-box { width: 60px; height: 60px; border: 2px solid #e5b800; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; }
    .logo-box img { width: 100%; height: 100%; object-fit: contain; }
    .logo-fallback { font-size: 22px; font-weight: 900; color: #1e3a5f; }
    .report-title h1 { font-size: 16px; font-weight: 800; color: #1e3a5f; letter-spacing: 0.04em; text-transform: uppercase; }
    .report-title p  { font-size: 11px; color: #555; margin-top: 2px; }
    .report-meta { margin-left: auto; text-align: right; font-size: 10px; color: #666; line-height: 1.6; }

    h2.doc-title { text-align: center; font-size: 15px; text-transform: uppercase; letter-spacing: 0.05em; margin: 10px 0 4px; color: #1e3a5f; }
    p.doc-sub { text-align: center; font-size: 11px; color: #666; margin-bottom: 20px; }

    .pihak-grid { display: flex; gap: 16px; margin-bottom: 20px; }
    .pihak-card { flex: 1; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; }
    .pihak-card h3 { font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; color: #1e3a5f; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 8px; }
    .pihak-card .row { display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px; gap: 8px; }
    .pihak-card .row span:first-child { color: #888; flex-shrink: 0; }
    .pihak-card .row span:last-child { text-align: right; font-weight: 600; }

    table.detail-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; }
    table.detail-table td { padding: 7px 10px; border: 1px solid #e2e8f0; }
    table.detail-table td:first-child { width: 35%; color: #666; background: #f8fafc; }
    table.detail-table td:last-child { font-weight: 600; }

    .pasal { margin-bottom: 16px; }
    .pasal h4 { font-size: 11px; text-transform: uppercase; color: #1e3a5f; margin-bottom: 6px; }
    .pasal p { font-size: 11px; line-height: 1.6; color: #333; text-align: justify; }

    .ttd-grid { display: flex; justify-content: space-between; margin-top: 40px; gap: 30px; }
    .ttd-box { flex: 1; text-align: center; }
    .ttd-box p.label { font-size: 10px; color: #888; margin-bottom: 60px; }
    .ttd-box p.nama { font-size: 11px; font-weight: 700; border-top: 1px solid #333; display: inline-block; padding-top: 6px; min-width: 160px; }

    .rekening-box { margin-top: 24px; border: 2px solid #1e3a5f; border-radius: 8px; padding: 16px 18px; background: #f8fafc; }
    .rekening-box h4 { font-size: 11px; text-transform: uppercase; color: #1e3a5f; margin-bottom: 10px; }
    table.rek-table { width: 100%; border-collapse: collapse; font-size: 11px; }
    table.rek-table th { background: #1e3a5f; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
    table.rek-table td { padding: 6px 8px; border: 1px solid #e2e8f0; }
    .rekening-warning { font-size: 9px; color: #b45309; margin-top: 8px; font-style: italic; }

    .print-controls { position: fixed; top: 16px; right: 16px; display: flex; gap: 8px; z-index: 999; }
    .btn-print  { background: #1e3a5f; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; }
    .btn-close  { background: #ef4444; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; }

    @media print {
      .print-controls { display: none; }
      @page { size: A4; margin: 12mm; }
    }
  </style>
</head>
<body>

<div class="print-controls">
  <button class="btn-print" onclick="window.print()">Cetak / PDF</button>
  <button class="btn-close" onclick="window.close()">✕ Tutup</button>
</div>

<div class="page">
  <div class="report-header">
    <div class="logo-box">
      <img src="LOGO_FS.png" alt="Logo" onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
      <span class="logo-fallback" style="display:none">FS</span>
    </div>
    <div class="report-title">
      <h1><?= htmlspecialchars($namaPerusahaan) ?></h1>
      <p><?= htmlspecialchars($alamatPerusahaan) ?> · Telp <?= htmlspecialchars($teleponPerusahaan) ?></p>
    </div>
    <div class="report-meta">
      <strong>No. Kontrak:</strong> <?= htmlspecialchars($kontrak['nomor']) ?><br>
      <strong>Tanggal Cetak:</strong> <?= date('d F Y') ?>
    </div>
  </div>

  <h2 class="doc-title">Lembar Kontrak Kerjasama Sewa Menyewa</h2>
  <p class="doc-sub">Nomor: <?= htmlspecialchars($kontrak['nomor']) ?></p>

  <div class="pihak-grid">
    <div class="pihak-card">
      <h3>Pihak Pertama (Pemberi Sewa)</h3>
      <div class="row"><span>Nama</span><span><?= htmlspecialchars($namaPerusahaan) ?></span></div>
      <div class="row"><span>Alamat</span><span><?= htmlspecialchars($alamatPerusahaan) ?></span></div>
      <div class="row"><span>Telepon</span><span><?= htmlspecialchars($teleponPerusahaan) ?></span></div>
    </div>
    <div class="pihak-card">
      <h3>Pihak Kedua (Penyewa)</h3>
      <div class="row"><span>ID Penyewa</span><span><?= $kodePenyewa ?></span></div>
      <div class="row"><span>Nama</span><span><?= htmlspecialchars($kontrak['penyewa_nama']) ?></span></div>
      <div class="row"><span>Telepon</span><span><?= htmlspecialchars($kontrak['penyewa_telepon']) ?></span></div>
      <?php if (!empty($kontrak['nik_nib'])): ?>
        <div class="row"><span>NIK/NIB</span><span><?= htmlspecialchars($kontrak['nik_nib']) ?></span></div>
      <?php endif; ?>
      <?php if (!empty($kontrak['penyewa_alamat'])): ?>
        <div class="row"><span>Alamat</span><span><?= htmlspecialchars($kontrak['penyewa_alamat']) ?></span></div>
      <?php endif; ?>
    </div>
  </div>

  <table class="detail-table">
    <tr><td>Objek Sewa</td><td><?= htmlspecialchars($kontrak['kode_unit'] . ' – ' . $kontrak['nama_unit']) ?> (<?= htmlspecialchars($kontrak['kategori']) ?>, <?= $luasFmt ?>)</td></tr>
    <tr><td>Masa Sewa</td><td><?= htmlspecialchars($kontrak['tanggal_mulai']) ?> s/d <?= htmlspecialchars($kontrak['tanggal_selesai']) ?></td></tr>
    <tr><td>Harga Sewa</td><td><?= $hargaFmt ?></td></tr>
    <tr><td>Deposit</td><td><?= $depositFmt ?></td></tr>
    <tr><td>Status Kontrak</td><td><?= htmlspecialchars($kontrak['status']) ?></td></tr>
  </table>

  <div class="pasal">
    <h4>Pasal 1 — Objek &amp; Jangka Waktu</h4>
    <p>Pihak Pertama menyewakan unit sebagaimana disebutkan di atas kepada Pihak Kedua untuk jangka waktu sebagaimana tercantum pada Masa Sewa, terhitung sejak tanggal mulai sampai dengan tanggal berakhirnya sewa.</p>
  </div>
  <div class="pasal">
    <h4>Pasal 2 — Pembayaran</h4>
    <p>Pihak Kedua wajib membayar biaya sewa setiap bulan sesuai nominal Harga Sewa melalui rekening resmi Pihak Pertama sebagaimana tercantum di bagian bawah dokumen ini, paling lambat tanggal jatuh tempo yang ditentukan oleh Pihak Pertama.</p>
  </div>
  <div class="pasal">
    <h4>Pasal 3 — Deposit</h4>
    <p>Pihak Kedua telah/akan menyerahkan deposit sebagai jaminan sebagaimana tercantum di atas, yang akan dikembalikan pada akhir masa sewa setelah dikurangi kewajiban yang belum diselesaikan (jika ada).</p>
  </div>

  <div class="ttd-grid">
    <div class="ttd-box">
      <p class="label">Pihak Pertama,</p>
      <p class="nama"><?= htmlspecialchars($namaPerusahaan) ?></p>
    </div>
    <div class="ttd-box">
      <p class="label">Pihak Kedua,</p>
      <p class="nama"><?= htmlspecialchars($kontrak['penyewa_nama']) ?></p>
    </div>
  </div>

  <div class="rekening-box">
    <h4>Informasi Rekening Pembayaran Sewa</h4>
    <?php if (empty($rekening)): ?>
      <p style="font-size:11px;color:#888;">Belum ada rekening perusahaan yang diatur. Hubungi admin.</p>
    <?php else: ?>
      <table class="rek-table">
        <thead><tr><th>Bank</th><th>No. Rekening</th><th>Atas Nama</th></tr></thead>
        <tbody>
          <?php foreach ($rekening as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['nama_bank']) ?></td>
              <td style="font-family:monospace"><?= htmlspecialchars($r['nomor_rekening']) ?></td>
              <td><?= htmlspecialchars($r['atas_nama']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="rekening-warning">Selalu konfirmasi ke admin sebelum melakukan transfer untuk menghindari kesalahan rekening.</p>
    <?php endif; ?>
  </div>
</div>

<script>
if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
  window.onload = () => setTimeout(() => window.print(), 500);
}
</script>
</body>
</html>
