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

$format = $_GET['format'] ?? 'excel';
$bulan  = (int)($_GET['bulan'] ?? date('n'));
$tahun  = (int)($_GET['tahun'] ?? date('Y'));
$filter = $_GET['status'] ?? 'all';

$namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni',
               'Juli','Agustus','September','Oktober','November','Desember'];
$periodeTeks = $namaBulan[$bulan] . ' ' . $tahun;
$namaFile    = 'Laporan_Tagihan_' . $namaBulan[$bulan] . '_' . $tahun . '_' . date('His');

// ── Ambil data ────────────────────────────────────────────────
if ($pdo) {
    $sql = "
        SELECT
            t.nomor,
            p.nama          AS nama_penyewa,
            p.jenis,
            p.telepon,
            u.kode          AS kode_unit,
            u.nama          AS nama_unit,
            k.nama          AS kategori,
            t.periode_bulan,
            t.periode_tahun,
            t.nominal,
            t.denda,
            (t.nominal + t.denda) AS total_bayar,
            t.tanggal_jatuh_tempo,
            t.status,
            pm.tanggal_bayar,
            pm.metode,
            pm.referensi
        FROM tagihan t
        JOIN penyewa       p  ON p.id = t.penyewa_id
        JOIN units         u  ON u.id = t.unit_id
        JOIN kategori_unit k  ON k.id = u.kategori_id
        LEFT JOIN pembayaran pm ON pm.tagihan_id = t.id
        WHERE t.periode_bulan = :bulan
          AND t.periode_tahun = :tahun
    ";
    if ($filter !== 'all') $sql .= " AND t.status = :status";
    $sql .= " ORDER BY t.status, p.nama ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':bulan', $bulan, PDO::PARAM_INT);
    $stmt->bindValue(':tahun', $tahun, PDO::PARAM_INT);
    if ($filter !== 'all') $stmt->bindValue(':status', $filter);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $data = array_map(fn($r) => [
        'nomor'       => $r['nomor'],
        'nama'        => $r['nama_penyewa'],
        'jenis'       => $r['jenis'],
        'telepon'     => $r['telepon'],
        'unit'        => $r['kode_unit'] . ' – ' . $r['nama_unit'],
        'kategori'    => $r['kategori'],
        'nominal'     => $r['nominal'],
        'denda'       => $r['denda'],
        'total'       => $r['total_bayar'],
        'jatuhTempo'  => $r['tanggal_jatuh_tempo'],
        'status'      => $r['status'],
        'tgl_bayar'   => $r['tanggal_bayar'] ?? '-',
        'metode'      => $r['metode'] ?? '-',
        'referensi'   => $r['referensi'] ?? '-',
    ], $rows);

} else {
    // Fallback static
    $raw = $appData['penyewa'];
    if ($filter !== 'all') {
        $raw = array_filter($raw, fn($p) => strtolower($p['status']) === strtolower($filter));
    }
    $data = array_map(fn($p) => [
        'nomor'      => 'INV/' . $tahun . '/' . str_pad($bulan,2,'0',STR_PAD_LEFT) . '/' . str_pad($p['id'],3,'0',STR_PAD_LEFT),
        'nama'       => $p['nama'],
        'jenis'      => $p['tipe'],
        'telepon'    => '-',
        'unit'       => $p['unit'],
        'kategori'   => $p['tipe'],
        'nominal'    => preg_replace('/[^0-9]/', '', $p['nominal']),
        'denda'      => 0,
        'total'      => preg_replace('/[^0-9]/', '', $p['nominal']),
        'jatuhTempo' => $p['jatuhTempo'],
        'status'     => $p['status'],
        'tgl_bayar'  => '-',
        'metode'     => '-',
        'referensi'  => '-',
    ], array_values($raw));
}

// Hitung summary
$totalTagihan  = array_sum(array_column($data, 'nominal'));
$totalDenda    = array_sum(array_column($data, 'denda'));
$totalBayar    = array_sum(array_column($data, 'total'));
$sudahLunas    = array_filter($data, fn($r) => strtolower($r['status']) === 'lunas');
$belumBayar    = array_filter($data, fn($r) => strtolower($r['status']) !== 'lunas');
$nominalLunas  = array_sum(array_map(fn($r) => $r['total'], $sudahLunas));
$nominalBelum  = array_sum(array_map(fn($r) => $r['total'], $belumBayar));
$totalRows     = count($data);
$tanggalExport = date('d/m/Y H:i');

// ══════════════════════════════════════════════════════════════
//  FORMAT: CSV
// ══════════════════════════════════════════════════════════════
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $namaFile . '.csv"');
    header('Cache-Control: no-cache, no-store, must-revalidate');

    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF");

    fputcsv($out, ['PT. FOOD STATION - LAPORAN KEUANGAN PERIODE: ' . strtoupper($periodeTeks)], ';');
    fputcsv($out, ['Diekspor pada: ' . $tanggalExport], ';');
    fputcsv($out, [], ';');
    fputcsv($out, ['NO','NO. INVOICE','NAMA PENYEWA','JENIS','TELEPON','UNIT',
                   'KATEGORI','NOMINAL (Rp)','DENDA (Rp)','TOTAL (Rp)',
                   'JATUH TEMPO','STATUS','TGL BAYAR','METODE','REFERENSI'], ';');

    foreach ($data as $i => $r) {
        fputcsv($out, [
            $i+1,
            $r['nomor'], $r['nama'], $r['jenis'], $r['telepon'], $r['unit'], $r['kategori'],
            $r['nominal'], $r['denda'], $r['total'],
            $r['jatuhTempo'], strtoupper($r['status']),
            $r['tgl_bayar'], $r['metode'], $r['referensi'],
        ], ';');
    }
    fputcsv($out, [], ';');
    fputcsv($out, ['','','','','','','TOTAL TAGIHAN:', $totalTagihan, $totalDenda, $totalBayar,'','','','',''], ';');
    fputcsv($out, ['','','','','','','SUDAH LUNAS:',  '',            '',           $nominalLunas,'','','','',''], ';');
    fputcsv($out, ['','','','','','','BELUM BAYAR:',  '',            '',           $nominalBelum,'','','','',''], ';');
    fclose($out);
    exit;
}

// ══════════════════════════════════════════════════════════════
//  FORMAT: EXCEL (SpreadsheetML)
// ══════════════════════════════════════════════════════════════
if ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $namaFile . '.xls"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
         xmlns:o="urn:schemas-microsoft-com:office:office"
         xmlns:x="urn:schemas-microsoft-com:office:excel"
         xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
    ?>
    <Styles>
      <Style ss:ID="title"><Font ss:Bold="1" ss:Size="14" ss:Color="#1e3a5f"/></Style>
      <Style ss:ID="sub"><Font ss:Size="10" ss:Color="#666666"/></Style>
      <Style ss:ID="hdr">
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
      <Style ss:ID="ce">
        <Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/></Borders>
        <Interior ss:Color="#f0f4f8" ss:Pattern="Solid"/>
        <Font ss:Size="10"/><Alignment ss:Vertical="Center"/>
      </Style>
      <Style ss:ID="co">
        <Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/></Borders>
        <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
        <Font ss:Size="10"/><Alignment ss:Vertical="Center"/>
      </Style>
      <Style ss:ID="num_e">
        <Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/></Borders>
        <Interior ss:Color="#f0f4f8" ss:Pattern="Solid"/>
        <NumberFormat ss:Format="#,##0"/>
        <Font ss:Size="10"/><Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
      </Style>
      <Style ss:ID="num_o">
        <Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/></Borders>
        <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
        <NumberFormat ss:Format="#,##0"/>
        <Font ss:Size="10"/><Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
      </Style>
      <Style ss:ID="lunas">
        <Interior ss:Color="#d1fae5" ss:Pattern="Solid"/>
        <Font ss:Bold="1" ss:Color="#065f46" ss:Size="10"/>
        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
        <Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/></Borders>
      </Style>
      <Style ss:ID="menunggu">
        <Interior ss:Color="#fef3c7" ss:Pattern="Solid"/>
        <Font ss:Bold="1" ss:Color="#92400e" ss:Size="10"/>
        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
        <Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/></Borders>
      </Style>
      <Style ss:ID="terlambat">
        <Interior ss:Color="#fee2e2" ss:Pattern="Solid"/>
        <Font ss:Bold="1" ss:Color="#991b1b" ss:Size="10"/>
        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
        <Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cccccc"/></Borders>
      </Style>
      <Style ss:ID="sum_lbl">
        <Interior ss:Color="#dbeafe" ss:Pattern="Solid"/>
        <Font ss:Bold="1" ss:Color="#1e3a5f" ss:Size="10"/>
        <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
        <Borders><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#1e3a5f"/><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#1e3a5f"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#1e3a5f"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#1e3a5f"/></Borders>
      </Style>
      <Style ss:ID="sum_val">
        <Interior ss:Color="#dbeafe" ss:Pattern="Solid"/>
        <Font ss:Bold="1" ss:Color="#1e3a5f" ss:Size="11"/>
        <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
        <NumberFormat ss:Format="#,##0"/>
        <Borders><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#1e3a5f"/><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#1e3a5f"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#1e3a5f"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#1e3a5f"/></Borders>
      </Style>
      <Style ss:ID="sum_val_green">
        <Interior ss:Color="#d1fae5" ss:Pattern="Solid"/>
        <Font ss:Bold="1" ss:Color="#065f46" ss:Size="11"/>
        <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
        <NumberFormat ss:Format="#,##0"/>
        <Borders><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#065f46"/><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#065f46"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#065f46"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#065f46"/></Borders>
      </Style>
      <Style ss:ID="sum_val_red">
        <Interior ss:Color="#fee2e2" ss:Pattern="Solid"/>
        <Font ss:Bold="1" ss:Color="#991b1b" ss:Size="11"/>
        <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
        <NumberFormat ss:Format="#,##0"/>
        <Borders><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#991b1b"/><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#991b1b"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#991b1b"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#991b1b"/></Borders>
      </Style>
    </Styles>

    <Worksheet ss:Name="Laporan Tagihan">
      <Table>
        <Column ss:Width="35"/>  <!-- No -->
        <Column ss:Width="140"/> <!-- No. Invoice -->
        <Column ss:Width="155"/> <!-- Nama -->
        <Column ss:Width="80"/>  <!-- Jenis -->
        <Column ss:Width="110"/> <!-- Telepon -->
        <Column ss:Width="110"/> <!-- Unit -->
        <Column ss:Width="80"/>  <!-- Kategori -->
        <Column ss:Width="120"/> <!-- Nominal -->
        <Column ss:Width="90"/>  <!-- Denda -->
        <Column ss:Width="125"/> <!-- Total -->
        <Column ss:Width="100"/> <!-- Jatuh Tempo -->
        <Column ss:Width="95"/>  <!-- Status -->
        <Column ss:Width="95"/>  <!-- Tgl Bayar -->
        <Column ss:Width="100"/> <!-- Metode -->
        <Column ss:Width="130"/> <!-- Referensi -->

        <Row ss:Height="30">
          <Cell ss:MergeAcross="14" ss:StyleID="title">
            <Data ss:Type="String">PT. FOOD STATION — LAPORAN KEUANGAN PERIODE <?= strtoupper($periodeTeks) ?></Data>
          </Cell>
        </Row>
        <Row ss:Height="18">
          <Cell ss:MergeAcross="14" ss:StyleID="sub">
            <Data ss:Type="String">Diekspor pada: <?= $tanggalExport ?> | Total: <?= $totalRows ?> tagihan | Lunas: <?= count($sudahLunas) ?> | Belum: <?= count($belumBayar) ?></Data>
          </Cell>
        </Row>
        <Row ss:Height="6"><Cell><Data ss:Type="String"></Data></Cell></Row>

        <!-- Header -->
        <Row ss:Height="30">
          <?php foreach (['NO','NO. INVOICE','NAMA PENYEWA','JENIS','TELEPON','UNIT',
                          'KATEGORI','NOMINAL (Rp)','DENDA (Rp)','TOTAL (Rp)',
                          'JATUH TEMPO','STATUS','TGL BAYAR','METODE BAYAR','NO. REFERENSI'] as $h): ?>
          <Cell ss:StyleID="hdr"><Data ss:Type="String"><?= $h ?></Data></Cell>
          <?php endforeach; ?>
        </Row>

        <!-- Data rows -->
        <?php foreach ($data as $i => $r):
          $isEven     = $i % 2 === 0;
          $cellStyle  = $isEven ? 'ce' : 'co';
          $numStyle   = $isEven ? 'num_e' : 'num_o';
          $statusStyle = match(strtolower($r['status'])) {
            'lunas'     => 'lunas',
            'terlambat' => 'terlambat',
            'menunggu'  => 'menunggu',
            default     => $cellStyle,
          };
        ?>
        <Row ss:Height="22">
          <Cell ss:StyleID="<?= $cellStyle ?>"><Data ss:Type="Number"><?= $i+1 ?></Data></Cell>
          <Cell ss:StyleID="<?= $cellStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['nomor']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $cellStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['nama']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $cellStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['jenis']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $cellStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['telepon']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $cellStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['unit']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $cellStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['kategori']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $numStyle ?>"><Data ss:Type="Number"><?= (int)$r['nominal'] ?></Data></Cell>
          <Cell ss:StyleID="<?= $numStyle ?>"><Data ss:Type="Number"><?= (int)$r['denda'] ?></Data></Cell>
          <Cell ss:StyleID="<?= $numStyle ?>"><Data ss:Type="Number"><?= (int)$r['total'] ?></Data></Cell>
          <Cell ss:StyleID="<?= $cellStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['jatuhTempo']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $statusStyle ?>"><Data ss:Type="String"><?= strtoupper($r['status']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $cellStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['tgl_bayar']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $cellStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['metode']) ?></Data></Cell>
          <Cell ss:StyleID="<?= $cellStyle ?>"><Data ss:Type="String"><?= htmlspecialchars($r['referensi']) ?></Data></Cell>
        </Row>
        <?php endforeach; ?>

        <Row ss:Height="6"><Cell><Data ss:Type="String"></Data></Cell></Row>

        <!-- Summary rows -->
        <Row ss:Height="24">
          <Cell ss:MergeAcross="9" ss:StyleID="sum_lbl"><Data ss:Type="String">TOTAL KESELURUHAN</Data></Cell>
          <Cell ss:MergeAcross="4" ss:StyleID="sum_val"><Data ss:Type="Number"><?= $totalBayar ?></Data></Cell>
        </Row>
        <Row ss:Height="24">
          <Cell ss:MergeAcross="9" ss:StyleID="sum_lbl"><Data ss:Type="String">SUDAH LUNAS (<?= count($sudahLunas) ?> tagihan)</Data></Cell>
          <Cell ss:MergeAcross="4" ss:StyleID="sum_val_green"><Data ss:Type="Number"><?= $nominalLunas ?></Data></Cell>
        </Row>
        <Row ss:Height="24">
          <Cell ss:MergeAcross="9" ss:StyleID="sum_lbl"><Data ss:Type="String">BELUM / TERLAMBAT BAYAR (<?= count($belumBayar) ?> tagihan)</Data></Cell>
          <Cell ss:MergeAcross="4" ss:StyleID="sum_val_red"><Data ss:Type="Number"><?= $nominalBelum ?></Data></Cell>
        </Row>

      </Table>
    </Worksheet>
    </Workbook>
    <?php
    exit;
}

// ══════════════════════════════════════════════════════════════
//  FORMAT: PRINT
// ══════════════════════════════════════════════════════════════
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <title>Laporan Tagihan <?= $periodeTeks ?> — PT. Food Station</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; }
    .page { padding: 20px 28px; }

    .report-header { display: flex; align-items: center; gap: 16px; border-bottom: 3px solid #1e3a5f; padding-bottom: 12px; margin-bottom: 16px; }
    .logo-box { width: 56px; height: 56px; border: 2px solid #e5b800; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; }
    .logo-box img { width: 100%; height: 100%; object-fit: contain; }
    .report-title h1 { font-size: 16px; font-weight: 800; color: #1e3a5f; text-transform: uppercase; }
    .report-title p  { font-size: 10px; color: #555; margin-top: 2px; }
    .report-meta { margin-left: auto; text-align: right; font-size: 10px; color: #666; line-height: 1.7; }

    .summary { display: flex; gap: 10px; margin-bottom: 14px; }
    .sc { flex: 1; border-radius: 8px; padding: 10px 14px; border: 1px solid #e2e8f0; }
    .sc .val { font-size: 17px; font-weight: 800; }
    .sc .lbl { font-size: 9px; text-transform: uppercase; letter-spacing: 0.08em; color: #888; margin-top: 1px; }
    .sc-blue   { border-top: 3px solid #1e3a5f; } .sc-blue   .val { color: #1e3a5f; }
    .sc-green  { border-top: 3px solid #10b981; } .sc-green  .val { color: #065f46; }
    .sc-red    { border-top: 3px solid #ef4444; } .sc-red    .val { color: #991b1b; }
    .sc-yellow { border-top: 3px solid #f59e0b; } .sc-yellow .val { color: #92400e; }

    table { width: 100%; border-collapse: collapse; font-size: 10px; }
    thead tr th { background: #1e3a5f; color: #fff; padding: 7px 7px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid #163060; }
    tbody tr td { padding: 6px 7px; border: 1px solid #e2e8f0; vertical-align: middle; }
    tbody tr:nth-child(even) td { background: #f8fafc; }

    .badge { display: inline-block; padding: 2px 7px; border-radius: 20px; font-size: 9px; font-weight: 700; text-transform: uppercase; }
    .bg   { background: #d1fae5; color: #065f46; }
    .by   { background: #fef3c7; color: #92400e; }
    .br   { background: #fee2e2; color: #991b1b; }

    .tfoot-row td { background: #1e3a5f !important; color: #fff; font-weight: 800; font-size: 11px; border: none; }
    .tfoot-green td { background: #d1fae5 !important; color: #065f46; font-weight: 800; }
    .tfoot-red   td { background: #fee2e2 !important; color: #991b1b; font-weight: 800; }

    .footer-note { margin-top: 16px; border-top: 1px solid #e2e8f0; padding-top: 8px; font-size: 9px; color: #999; display: flex; justify-content: space-between; }

    .print-controls { position: fixed; top: 16px; right: 16px; display: flex; gap: 8px; z-index: 999; }
    .btn-print { background: #1e3a5f; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px; }
    .btn-close { background: #ef4444; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; }
    @media print {
      .print-controls { display: none; }
      @page { size: A4 landscape; margin: 8mm; }
    }
  </style>
</head>
<body>

<div class="print-controls">
  <button class="btn-print" onclick="window.print()">
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
    Cetak / PDF
  </button>
  <button class="btn-close" onclick="window.close()">✕ Tutup</button>
</div>

<div class="page">
  <div class="report-header">
    <div class="logo-box">
      <img src="/food-station/LOGO_FS.png" alt="Logo" onerror="this.style.display='none'">
    </div>
    <div class="report-title">
      <h1>PT. Food Station</h1>
      <p>Laporan Keuangan & Penagihan — Periode <?= $periodeTeks ?></p>
    </div>
    <div class="report-meta">
      <strong>Tanggal Cetak:</strong> <?= date('d F Y, H:i') ?> WIB<br>
      <strong>Total Tagihan:</strong> <?= $totalRows ?> dokumen<br>
      <strong>Lunas:</strong> <?= count($sudahLunas) ?> | <strong>Belum:</strong> <?= count($belumBayar) ?><br>
      <strong>Dicetak oleh:</strong> Administrator
    </div>
  </div>

  <div class="summary">
    <div class="sc sc-blue">
      <div class="val">Rp <?= number_format($totalBayar,0,',','.') ?></div>
      <div class="lbl">Total Tagihan</div>
    </div>
    <div class="sc sc-green">
      <div class="val">Rp <?= number_format($nominalLunas,0,',','.') ?></div>
      <div class="lbl">Sudah Lunas (<?= count($sudahLunas) ?>)</div>
    </div>
    <div class="sc sc-red">
      <div class="val">Rp <?= number_format($nominalBelum,0,',','.') ?></div>
      <div class="lbl">Belum Bayar (<?= count($belumBayar) ?>)</div>
    </div>
    <div class="sc sc-yellow">
      <div class="val"><?= $totalRows > 0 ? round($nominalLunas/$totalBayar*100) : 0 ?>%</div>
      <div class="lbl">Tingkat Pelunasan</div>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>No. Invoice</th>
        <th>Nama Penyewa</th>
        <th>Unit</th>
        <th>Kategori</th>
        <th style="text-align:right">Nominal (Rp)</th>
        <th style="text-align:right">Denda (Rp)</th>
        <th style="text-align:right">Total (Rp)</th>
        <th>Jatuh Tempo</th>
        <th>Status</th>
        <th>Tgl Bayar</th>
        <th>Metode</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($data as $i => $r):
        $bc = match(strtolower($r['status'])) { 'lunas'=>'bg','terlambat'=>'br','menunggu'=>'by',default=>'by' };
      ?>
        <tr>
          <td style="text-align:center;color:#999"><?= $i+1 ?></td>
          <td style="font-family:monospace;font-size:9px"><?= htmlspecialchars($r['nomor']) ?></td>
          <td><strong><?= htmlspecialchars($r['nama']) ?></strong></td>
          <td style="font-family:monospace"><?= htmlspecialchars($r['unit']) ?></td>
          <td><?= htmlspecialchars($r['kategori']) ?></td>
          <td style="text-align:right"><?= number_format((int)$r['nominal'],0,',','.') ?></td>
          <td style="text-align:right;color:<?= (int)$r['denda']>0?'#991b1b':'#999' ?>"><?= number_format((int)$r['denda'],0,',','.') ?></td>
          <td style="text-align:right;font-weight:700"><?= number_format((int)$r['total'],0,',','.') ?></td>
          <td style="text-align:center"><?= htmlspecialchars($r['jatuhTempo']) ?></td>
          <td style="text-align:center"><span class="badge <?= $bc ?>"><?= strtoupper($r['status']) ?></span></td>
          <td style="text-align:center"><?= htmlspecialchars($r['tgl_bayar']) ?></td>
          <td><?= htmlspecialchars($r['metode']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr class="tfoot-row">
        <td colspan="5" style="text-align:right">TOTAL KESELURUHAN:</td>
        <td style="text-align:right"><?= number_format($totalTagihan,0,',','.') ?></td>
        <td style="text-align:right"><?= number_format($totalDenda,0,',','.') ?></td>
        <td style="text-align:right"><?= number_format($totalBayar,0,',','.') ?></td>
        <td colspan="4"></td>
      </tr>
      <tr class="tfoot-green">
        <td colspan="7" style="text-align:right;padding:6px 7px">SUDAH LUNAS (<?= count($sudahLunas) ?> tagihan):</td>
        <td style="text-align:right;padding:6px 7px;font-weight:800">Rp <?= number_format($nominalLunas,0,',','.') ?></td>
        <td colspan="4"></td>
      </tr>
      <tr class="tfoot-red">
        <td colspan="7" style="text-align:right;padding:6px 7px">BELUM / TERLAMBAT (<?= count($belumBayar) ?> tagihan):</td>
        <td style="text-align:right;padding:6px 7px;font-weight:800">Rp <?= number_format($nominalBelum,0,',','.') ?></td>
        <td colspan="4"></td>
      </tr>
    </tfoot>
  </table>

  <div class="footer-note">
    <span>PT. Food Station — Management & Leasing System v2.1</span>
    <span>Dokumen dicetak otomatis | <?= date('d/m/Y H:i') ?> WIB</span>
  </div>
</div>
</body>
</html>
