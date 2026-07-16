<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /food-station/index.php'); exit;
}

/** @var PDO|null $pdo */
$pdo     = null;
/** @var array $appData */
$appData = [];
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/data.php';

$format = $_GET['format'] ?? 'excel'; // excel | csv | print
$filter = $_GET['status'] ?? 'all';

// ── Ambil data dari DB atau fallback ke array statis ──────────────────────────
if ($pdo) {
    $sql = "
        SELECT
            p.id,
            p.nama,
            p.jenis,
            p.nik_nib,
            p.telepon,
            p.email,
            p.alamat,
            p.kota,
            u.kode   AS kode_unit,
            u.nama   AS nama_unit,
            k.nama   AS kategori,
            kt.harga_sewa,
            kt.tanggal_mulai,
            kt.tanggal_selesai,
            COALESCE(t.status, 'Belum Ada Tagihan') AS status_tagihan,
            t.tanggal_jatuh_tempo,
            t.nominal
        FROM kontrak kt
        JOIN penyewa       p  ON p.id  = kt.penyewa_id
        JOIN units         u  ON u.id  = kt.unit_id
        JOIN kategori_unit k  ON k.id  = u.kategori_id
        LEFT JOIN tagihan  t  ON t.kontrak_id = kt.id
                              AND t.periode_bulan = MONTH(CURDATE())
                              AND t.periode_tahun = YEAR(CURDATE())
        WHERE kt.status = 'Aktif'
    ";
    if ($filter !== 'all') {
        $sql .= " AND t.status = :status";
    }
    $sql .= " ORDER BY p.nama ASC";

    $stmt = $pdo->prepare($sql);
    if ($filter !== 'all') $stmt->bindValue(':status', $filter);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    // Normalise ke format yang sama dengan array statis
    $data = array_map(fn($r) => [
        'id'          => $r['id'],
        'nama'        => $r['nama'],
        'jenis'       => $r['jenis'],
        'nik_nib'     => $r['nik_nib'] ?? '-',
        'telepon'     => $r['telepon'],
        'email'       => $r['email'] ?? '-',
        'unit'        => $r['kode_unit'] . ' – ' . $r['nama_unit'],
        'kategori'    => $r['kategori'],
        'harga_sewa'  => 'Rp ' . number_format($r['harga_sewa'] ?? 0, 0, ',', '.'),
        'kontrak'     => ($r['tanggal_mulai'] ?? '-') . ' s/d ' . ($r['tanggal_selesai'] ?? '-'),
        'status'      => $r['status_tagihan'],
        'jatuhTempo'  => $r['tanggal_jatuh_tempo'] ?? '-',
        'nominal'     => 'Rp ' . number_format($r['nominal'] ?? 0, 0, ',', '.'),
    ], $rows);

} else {
    // Fallback: array statis
    $raw = $appData['penyewa'];
    if ($filter !== 'all') {
        $raw = array_filter($raw, fn($p) => strtolower($p['status']) === strtolower($filter));
    }
    $data = array_map(fn($p) => [
        'id'         => $p['id'],
        'nama'       => $p['nama'],
        'jenis'      => '-',
        'nik_nib'    => '-',
        'telepon'    => '-',
        'email'      => '-',
        'unit'       => $p['unit'],
        'kategori'   => $p['tipe'],
        'harga_sewa' => $p['nominal'],
        'kontrak'    => '-',
        'status'     => $p['status'],
        'jatuhTempo' => $p['jatuhTempo'],
        'nominal'    => $p['nominal'],
    ], array_values($raw));
}

$totalRows     = count($data);
$tanggalExport = date('d/m/Y H:i');
$namaFile      = 'Data_Penyewa_' . date('Ymd_His');

// ══════════════════════════════════════════════════════════════
//  FORMAT: CSV
// ══════════════════════════════════════════════════════════════
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $namaFile . '.csv"');
    header('Cache-Control: no-cache, no-store, must-revalidate');

    $out = fopen('php://output', 'w');
    // BOM agar Excel baca UTF-8 dengan benar
    fputs($out, "\xEF\xBB\xBF");

    // Header kolom
    fputcsv($out, ['NO','NAMA PENYEWA','JENIS','NIK/NIB','TELEPON','EMAIL',
                   'UNIT','KATEGORI','HARGA SEWA','MASA KONTRAK',
                   'STATUS TAGIHAN','JATUH TEMPO','NOMINAL TAGIHAN'], ';');

    foreach ($data as $i => $r) {
        fputcsv($out, [
            $i + 1,
            $r['nama'], $r['jenis'], $r['nik_nib'], $r['telepon'], $r['email'],
            $r['unit'], $r['kategori'], $r['harga_sewa'], $r['kontrak'],
            $r['status'], $r['jatuhTempo'], $r['nominal'],
        ], ';');
    }
    fclose($out);
    exit;
}

// ══════════════════════════════════════════════════════════════
//  FORMAT: EXCEL (SpreadsheetML — .xls tanpa library)
// ══════════════════════════════════════════════════════════════
if ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $namaFile . '.xls"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
         xmlns:o="urn:schemas-microsoft-com:office:office"
         xmlns:x="urn:schemas-microsoft-com:office:excel"
         xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
         xmlns:html="http://www.w3.org/TR/REC-html40">';
    ?>

    <Styles>
      <Style ss:ID="title">
        <Font ss:Bold="1" ss:Size="14" ss:Color="#1e3a5f"/>
      </Style>
      <Style ss:ID="subtitle">
        <Font ss:Size="10" ss:Color="#666666"/>
      </Style>
      <Style ss:ID="header">
        <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#1e3a5f"/>
          <Border ss:Position="Top"    ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#1e3a5f"/>
          <Border ss:Position="Left"   ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#1e3a5f"/>
          <Border ss:Position="Right"  ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#1e3a5f"/>
        </Borders>
        <Font ss:Bold="1" ss:Size="10" ss:Color="#FFFFFF"/>
        <Interior ss:Color="#1e3a5f" ss:Pattern="Solid"/>
      </Style>
      <Style ss:ID="cell_even">
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/>
          <Border ss:Position="Left"   ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/>
          <Border ss:Position="Right"  ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/>
        </Borders>
        <Interior ss:Color="#f0f4f8" ss:Pattern="Solid"/>
        <Font ss:Size="10"/>
        <Alignment ss:Vertical="Center"/>
      </Style>
      <Style ss:ID="cell_odd">
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/>
          <Border ss:Position="Left"   ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/>
          <Border ss:Position="Right"  ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/>
        </Borders>
        <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
        <Font ss:Size="10"/>
        <Alignment ss:Vertical="Center"/>
      </Style>
      <Style ss:ID="lunas">
        <Interior ss:Color="#d1fae5" ss:Pattern="Solid"/>
        <Font ss:Bold="1" ss:Color="#065f46" ss:Size="10"/>
        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/>
          <Border ss:Position="Left"   ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/>
          <Border ss:Position="Right"  ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/>
        </Borders>
      </Style>
      <Style ss:ID="menunggu">
        <Interior ss:Color="#fef3c7" ss:Pattern="Solid"/>
        <Font ss:Bold="1" ss:Color="#92400e" ss:Size="10"/>
        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/>
          <Border ss:Position="Left"   ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/>
          <Border ss:Position="Right"  ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/>
        </Borders>
      </Style>
      <Style ss:ID="terlambat">
        <Interior ss:Color="#fee2e2" ss:Pattern="Solid"/>
        <Font ss:Bold="1" ss:Color="#991b1b" ss:Size="10"/>
        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/>
          <Border ss:Position="Left"   ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/>
          <Border ss:Position="Right"  ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/>
        </Borders>
      </Style>
      <Style ss:ID="footer">
        <Font ss:Bold="1" ss:Size="10" ss:Color="#1e3a5f"/>
        <Interior ss:Color="#dbeafe" ss:Pattern="Solid"/>
        <Borders>
          <Border ss:Position="Top"    ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#1e3a5f"/>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#1e3a5f"/>
          <Border ss:Position="Left"   ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#1e3a5f"/>
          <Border ss:Position="Right"  ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#1e3a5f"/>
        </Borders>
      </Style>
      <Style ss:ID="number">
        <NumberFormat ss:Format="#,##0"/>
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/>
          <Border ss:Position="Left"   ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/>
          <Border ss:Position="Right"  ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/>
        </Borders>
        <Font ss:Size="10"/>
        <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
      </Style>
    </Styles>

    <Worksheet ss:Name="Data Penyewa">
      <Table ss:DefaultColumnWidth="100">
        <!-- Lebar kolom -->
        <Column ss:Width="35"/>   <!-- No -->
        <Column ss:Width="160"/>  <!-- Nama -->
        <Column ss:Width="80"/>   <!-- Jenis -->
        <Column ss:Width="120"/>  <!-- NIK/NIB -->
        <Column ss:Width="110"/>  <!-- Telepon -->
        <Column ss:Width="150"/>  <!-- Email -->
        <Column ss:Width="130"/>  <!-- Unit -->
        <Column ss:Width="80"/>   <!-- Kategori -->
        <Column ss:Width="120"/>  <!-- Harga Sewa -->
        <Column ss:Width="180"/>  <!-- Masa Kontrak -->
        <Column ss:Width="100"/>  <!-- Status -->
        <Column ss:Width="100"/>  <!-- Jatuh Tempo -->
        <Column ss:Width="130"/>  <!-- Nominal -->

        <!-- Baris Judul -->
        <Row ss:Height="30">
          <Cell ss:MergeAcross="12" ss:StyleID="title">
            <Data ss:Type="String">PT. FOOD STATION — DATA PENYEWA AKTIF</Data>
          </Cell>
        </Row>
        <Row ss:Height="18">
          <Cell ss:MergeAcross="12" ss:StyleID="subtitle">
            <Data ss:Type="String">Diekspor pada: <?= $tanggalExport ?> | Total: <?= $totalRows ?> penyewa</Data>
          </Cell>
        </Row>
        <Row ss:Height="6"><Cell><Data ss:Type="String"></Data></Cell></Row>

        <!-- Header -->
        <Row ss:Height="30">
          <?php foreach (['NO','NAMA PENYEWA','JENIS','NIK / NIB','TELEPON','EMAIL',
                          'UNIT','KATEGORI','HARGA SEWA','MASA KONTRAK',
                          'STATUS TAGIHAN','JATUH TEMPO','NOMINAL TAGIHAN'] as $h): ?>
          <Cell ss:StyleID="header"><Data ss:Type="String"><?= $h ?></Data></Cell>
          <?php endforeach; ?>
        </Row>

        <!-- Baris Data -->
        <?php foreach ($data as $i => $r):
          $rowStyle = ($i % 2 === 0) ? 'cell_even' : 'cell_odd';
          $statusStyle = match(strtolower($r['status'])) {
            'lunas'     => 'lunas',
            'terlambat' => 'terlambat',
            'menunggu'  => 'menunggu',
            default     => $rowStyle,
          };
        ?>
        <Row ss:Height="22">
          <Cell ss:StyleID="<?= $rowStyle ?>"><Data ss:Type="Number"><?= $i+1 ?></Data></Cell>
          <Cell ss:StyleID="<?= $rowStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['nama']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $rowStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['jenis']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $rowStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['nik_nib']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $rowStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['telepon']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $rowStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['email']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $rowStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['unit']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $rowStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['kategori']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $rowStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['harga_sewa']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $rowStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['kontrak']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $statusStyle ?>"><Data ss:Type="String"><?= strtoupper($r['status']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $rowStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['jatuhTempo']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $rowStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['nominal']) ?></Data></Cell>
        </Row>
        <?php endforeach; ?>

        <!-- Footer Total -->
        <Row ss:Height="24">
          <Cell ss:MergeAcross="11" ss:StyleID="footer">
            <Data ss:Type="String">TOTAL: <?= $totalRows ?> DATA PENYEWA AKTIF</Data>
          </Cell>
          <Cell ss:StyleID="footer"><Data ss:Type="String"></Data></Cell>
        </Row>

      </Table>
    </Worksheet>
    </Workbook>
    <?php
    exit;
}

// ══════════════════════════════════════════════════════════════
//  FORMAT: PRINT (halaman cetak / PDF via browser)
// ══════════════════════════════════════════════════════════════
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <title>Data Penyewa — PT. Food Station</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; }

    .page { padding: 24px 30px; }

    /* Header */
    .report-header { display: flex; align-items: center; gap: 16px; border-bottom: 3px solid #1e3a5f; padding-bottom: 14px; margin-bottom: 18px; }
    .logo-box { width: 60px; height: 60px; border: 2px solid #e5b800; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; }
    .logo-box img { width: 100%; height: 100%; object-fit: contain; }
    .logo-fallback { font-size: 22px; font-weight: 900; color: #1e3a5f; }
    .report-title h1 { font-size: 18px; font-weight: 800; color: #1e3a5f; letter-spacing: 0.05em; text-transform: uppercase; }
    .report-title p  { font-size: 11px; color: #555; margin-top: 2px; }
    .report-meta { margin-left: auto; text-align: right; font-size: 10px; color: #666; line-height: 1.6; }

    /* Summary */
    .summary { display: flex; gap: 10px; margin-bottom: 16px; }
    .summary-card { flex: 1; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; }
    .summary-card .val { font-size: 20px; font-weight: 800; }
    .summary-card .lbl { font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #888; margin-top: 2px; }
    .sc-blue   .val { color: #1e3a5f; } .sc-blue   { border-top: 3px solid #1e3a5f; }
    .sc-green  .val { color: #065f46; } .sc-green  { border-top: 3px solid #10b981; }
    .sc-yellow .val { color: #92400e; } .sc-yellow { border-top: 3px solid #f59e0b; }
    .sc-red    .val { color: #991b1b; } .sc-red    { border-top: 3px solid #ef4444; }

    /* Table */
    table  { width: 100%; border-collapse: collapse; font-size: 10px; }
    thead tr th { background: #1e3a5f; color: #fff; padding: 8px 8px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.06em; border: 1px solid #163060; }
    tbody tr td { padding: 7px 8px; border: 1px solid #e2e8f0; vertical-align: middle; }
    tbody tr:nth-child(even) td { background: #f8fafc; }
    tbody tr:hover td { background: #eff6ff; }

    .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
    .badge-green  { background: #d1fae5; color: #065f46; }
    .badge-yellow { background: #fef3c7; color: #92400e; }
    .badge-red    { background: #fee2e2; color: #991b1b; }
    .badge-gray   { background: #f1f5f9; color: #475569; }

    .footer-note { margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 10px; font-size: 9px; color: #999; display: flex; justify-content: space-between; }
    .total-row td { background: #dbeafe !important; font-weight: 800; color: #1e3a5f; border-top: 2px solid #1e3a5f; }

    /* Print controls */
    .print-controls { position: fixed; top: 16px; right: 16px; display: flex; gap: 8px; z-index: 999; }
    .btn-print  { background: #1e3a5f; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px; }
    .btn-close  { background: #ef4444; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; }
    .btn-print:hover { background: #163060; }

    @media print {
      .print-controls { display: none; }
      body { font-size: 10px; }
      @page { size: A4 landscape; margin: 10mm; }
    }
  </style>
</head>
<body>

<!-- Tombol cetak -->
<div class="print-controls">
  <button class="btn-print" onclick="window.print()">
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
    Cetak / PDF
  </button>
  <button class="btn-close" onclick="window.close()">✕ Tutup</button>
</div>

<div class="page">
  <!-- Header -->
  <div class="report-header">
    <div class="logo-box">
      <img src="/food-station/LOGO_FS.png" alt="Logo" onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
      <span class="logo-fallback" style="display:none">FS</span>
    </div>
    <div class="report-title">
      <h1>PT. Food Station</h1>
      <p>Laporan Data Penyewa Aktif<?= $filter !== 'all' ? ' — Status: ' . strtoupper($filter) : '' ?></p>
    </div>
    <div class="report-meta">
      <strong>Tanggal Cetak:</strong> <?= date('d F Y, H:i') ?> WIB<br>
      <strong>Periode:</strong> <?= date('F Y') ?><br>
      <strong>Total Data:</strong> <?= $totalRows ?> penyewa<br>
      <strong>Dicetak oleh:</strong> Administrator
    </div>
  </div>

  <!-- Summary Cards -->
  <?php
  $jml_lunas    = count(array_filter($data, fn($r) => strtolower($r['status']) === 'lunas'));
  $jml_menunggu = count(array_filter($data, fn($r) => strtolower($r['status']) === 'menunggu'));
  $jml_telat    = count(array_filter($data, fn($r) => strtolower($r['status']) === 'terlambat'));
  ?>
  <div class="summary">
    <div class="summary-card sc-blue">
      <div class="val"><?= $totalRows ?></div>
      <div class="lbl">Total Penyewa</div>
    </div>
    <div class="summary-card sc-green">
      <div class="val"><?= $jml_lunas ?></div>
      <div class="lbl">Sudah Lunas</div>
    </div>
    <div class="summary-card sc-yellow">
      <div class="val"><?= $jml_menunggu ?></div>
      <div class="lbl">Menunggu Bayar</div>
    </div>
    <div class="summary-card sc-red">
      <div class="val"><?= $jml_telat ?></div>
      <div class="lbl">Terlambat</div>
    </div>
  </div>

  <!-- Tabel -->
  <table>
    <thead>
      <tr>
        <th style="width:30px">No</th>
        <th>Nama Penyewa</th>
        <th>Jenis</th>
        <th>Telepon</th>
        <th>Unit</th>
        <th>Kategori</th>
        <th>Harga Sewa/Bln</th>
        <th>Masa Kontrak</th>
        <th>Status Tagihan</th>
        <th>Jatuh Tempo</th>
        <th>Nominal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($data as $i => $r):
        $badgeCls = match(strtolower($r['status'])) {
          'lunas'     => 'badge-green',
          'terlambat' => 'badge-red',
          'menunggu'  => 'badge-yellow',
          default     => 'badge-gray',
        };
      ?>
        <tr>
          <td style="text-align:center;color:#999"><?= $i+1 ?></td>
          <td><strong><?= htmlspecialchars($r['nama']) ?></strong></td>
          <td><?= htmlspecialchars($r['jenis']) ?></td>
          <td><?= htmlspecialchars($r['telepon']) ?></td>
          <td style="font-family:monospace"><?= htmlspecialchars($r['unit']) ?></td>
          <td><?= htmlspecialchars($r['kategori']) ?></td>
          <td style="text-align:right"><?= htmlspecialchars($r['harga_sewa']) ?></td>
          <td style="font-size:9px"><?= htmlspecialchars($r['kontrak']) ?></td>
          <td style="text-align:center"><span class="badge <?= $badgeCls ?>"><?= strtoupper($r['status']) ?></span></td>
          <td style="text-align:center;<?= strtolower($r['status']) === 'terlambat' ? 'color:#991b1b;font-weight:700' : '' ?>"><?= htmlspecialchars($r['jatuhTempo']) ?></td>
          <td style="text-align:right;font-weight:600"><?= htmlspecialchars($r['nominal']) ?></td>
        </tr>
      <?php endforeach; ?>
      <tr class="total-row">
        <td colspan="10" style="text-align:right">TOTAL DATA:</td>
        <td style="text-align:center"><?= $totalRows ?> Penyewa</td>
      </tr>
    </tbody>
  </table>

  <!-- Footer -->
  <div class="footer-note">
    <span>PT. Food Station — Management & Leasing System v2.1</span>
    <span>Dokumen ini dicetak otomatis oleh sistem | <?= date('d/m/Y H:i') ?> WIB</span>
  </div>
</div>

<script>
// Auto print jika parameter ?autoprint=1
if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
  window.onload = () => setTimeout(() => window.print(), 500);
}
</script>
</body>
</html>
