<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/flash.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$redirectTo = 'dashboard.php?page=penyewa';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirectTo);
    exit;
}

require_once 'includes/db.php';

$kontrakId    = (int)($_POST['kontrak_id'] ?? 0);
$periodeBulan = (int)($_POST['periode_bulan'] ?? 0);
$periodeTahun = (int)($_POST['periode_tahun'] ?? 0);
$biayaSewa    = (int)preg_replace('/\D/', '', $_POST['biaya_sewa'] ?? '0');
$biayaListrik = (int)preg_replace('/\D/', '', $_POST['biaya_listrik'] ?? '0');
$biayaAir     = (int)preg_replace('/\D/', '', $_POST['biaya_air'] ?? '0');
$jatuhTempo   = trim($_POST['tanggal_jatuh_tempo'] ?? '');
$catatan      = trim($_POST['catatan'] ?? '');
$adminId      = (int)$_SESSION['user_id'];

if ($kontrakId <= 0 || $periodeBulan < 1 || $periodeBulan > 12 || $periodeTahun < 2000 || $jatuhTempo === '') {
    flash_redirect($redirectTo, 'error', 'Data tagihan tidak lengkap atau tidak valid.');
}
$nominal = $biayaSewa + $biayaListrik + $biayaAir;
if ($nominal <= 0) {
    flash_redirect($redirectTo, 'error', 'Total tagihan harus lebih dari nol.');
}
if (!$pdo) {
    flash_redirect($redirectTo, 'error', 'Koneksi database gagal.');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT k.*, p.nama AS penyewa_nama, p.tenant_account_id, u.kode AS kode_unit, u.nama AS nama_unit, ku.nama AS kategori
        FROM kontrak k
        JOIN penyewa p ON p.id = k.penyewa_id
        JOIN units u ON u.id = k.unit_id
        JOIN kategori_unit ku ON ku.id = u.kategori_id
        WHERE k.id = ? FOR UPDATE
    ");
    $stmt->execute([$kontrakId]);
    $kontrak = $stmt->fetch();

    if (!$kontrak) {
        $pdo->rollBack();
        flash_redirect($redirectTo, 'error', 'Kontrak tidak ditemukan.');
    }
    if ($kontrak['status'] !== 'Aktif') {
        $pdo->rollBack();
        flash_redirect($redirectTo, 'error', 'Kontrak ini sudah tidak aktif.');
    }

    $dup = $pdo->prepare("SELECT id FROM tagihan WHERE kontrak_id = ? AND periode_bulan = ? AND periode_tahun = ?");
    $dup->execute([$kontrakId, $periodeBulan, $periodeTahun]);
    if ($dup->fetch()) {
        $pdo->rollBack();
        flash_redirect($redirectTo, 'error', 'Tagihan untuk periode ini sudah pernah dikirim ke penyewa tersebut.');
    }

    $ins = $pdo->prepare("INSERT INTO tagihan
        (nomor, kontrak_id, penyewa_id, unit_id, periode_bulan, periode_tahun, nominal, biaya_sewa, biaya_listrik, biaya_air, tanggal_jatuh_tempo, status, catatan, dikirim_oleh)
        VALUES ('TMP',?,?,?,?,?,?,?,?,?,?, 'Menunggu', ?, ?)");
    $ins->execute([
        $kontrakId, $kontrak['penyewa_id'], $kontrak['unit_id'], $periodeBulan, $periodeTahun,
        $nominal, $biayaSewa, $biayaListrik, $biayaAir, $jatuhTempo, $catatan ?: null, $adminId,
    ]);
    $tagihanId = (int)$pdo->lastInsertId();

    $nomorInvoice = sprintf('INV/%d/%02d/%03d', $periodeTahun, $periodeBulan, $tagihanId);
    $pdo->prepare("UPDATE tagihan SET nomor = ? WHERE id = ?")->execute([$nomorInvoice, $tagihanId]);

    $namaBulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'][$periodeBulan];
    $fmt = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');

    if (!empty($kontrak['tenant_account_id'])) {
        $pesanIsi = "TAGIHAN SEWA — {$nomorInvoice}\n"
            . "Periode: {$namaBulan} {$periodeTahun}\n"
            . "Unit: {$kontrak['kode_unit']} – {$kontrak['nama_unit']} ({$kontrak['kategori']})\n\n"
            . "Rincian:\n"
            . "- Sewa Properti: {$fmt($biayaSewa)}\n"
            . "- Listrik: {$fmt($biayaListrik)}\n"
            . "- Air: {$fmt($biayaAir)}\n"
            . "GRAND TOTAL: {$fmt($nominal)}\n\n"
            . "Jatuh tempo: {$jatuhTempo}\n"
            . ($catatan !== '' ? "Catatan: {$catatan}\n\n" : "\n")
            . "Mohon segera lakukan pembayaran sebelum jatuh tempo. Keterlambatan pembayaran dapat mengakibatkan penangguhan fasilitas sewa Anda.";

        $pdo->prepare("INSERT INTO pesan (tenant_account_id, pengirim, admin_user_id, isi) VALUES (?, 'admin', ?, ?)")
            ->execute([$kontrak['tenant_account_id'], $adminId, $pesanIsi]);
    }

    $pdo->prepare("INSERT INTO aktivitas_log (user_id, tipe, judul, keterangan, referensi) VALUES (?, 'lainnya', 'Tagihan Dikirim', ?, ?)")
        ->execute([$adminId, "{$kontrak['penyewa_nama']} — {$kontrak['kode_unit']} periode {$namaBulan} {$periodeTahun}", $nomorInvoice]);

    $pdo->commit();

    if (empty($kontrak['tenant_account_id'])) {
        flash_redirect($redirectTo, 'info', "Tagihan {$nomorInvoice} dibuat, namun penyewa ini belum punya akun portal sehingga notifikasi tidak terkirim ke inbox.");
    }
    flash_redirect($redirectTo, 'success', "Tagihan {$nomorInvoice} berhasil dikirim ke inbox penyewa.");
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash_redirect($redirectTo, 'error', 'Terjadi kesalahan sistem saat mengirim tagihan.');
}
