<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/flash.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$redirectTo = 'dashboard.php?page=pengajuan';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirectTo);
    exit;
}

require_once 'includes/db.php';

$bookingId = (int)($_POST['booking_id'] ?? 0);
$adminId   = (int)$_SESSION['user_id'];

// --- Data kontrak (admin yang menentukan syarat sewa) ---
$tanggalMulai   = trim($_POST['tanggal_mulai'] ?? '');
$tanggalSelesai = trim($_POST['tanggal_selesai'] ?? '');
$hargaSewa      = (int)preg_replace('/\D/', '', $_POST['harga_sewa'] ?? '0');
$deposit        = (int)preg_replace('/\D/', '', $_POST['deposit'] ?? '0');
$catatan        = trim($_POST['catatan'] ?? '');

if ($bookingId <= 0 || $tanggalMulai === '' || $tanggalSelesai === '' || $hargaSewa <= 0) {
    flash_redirect($redirectTo, 'error', 'Harap lengkapi tanggal sewa dan harga sewa.');
}
if ($tanggalSelesai <= $tanggalMulai) {
    flash_redirect($redirectTo, 'error', 'Tanggal selesai kontrak harus setelah tanggal mulai.');
}
if (!$pdo) {
    flash_redirect($redirectTo, 'error', 'Koneksi database gagal.');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT br.*, u.status AS unit_status FROM booking_requests br JOIN units u ON u.id = br.unit_id WHERE br.id = ? FOR UPDATE");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    if (!$booking) {
        $pdo->rollBack();
        flash_redirect($redirectTo, 'error', 'Pengajuan booking tidak ditemukan.');
    }
    if ($booking['status'] !== 'Menunggu') {
        $pdo->rollBack();
        flash_redirect($redirectTo, 'error', 'Pengajuan ini sudah diproses sebelumnya.');
    }
    if ($booking['unit_status'] !== 'Kosong') {
        $pdo->rollBack();
        flash_redirect($redirectTo, 'error', 'Unit ini sudah tidak tersedia.');
    }

    // 1) Buat data penyewa minimal — detail lengkap (NIK/NPWP/alamat/dll) diisi sendiri oleh penyewa nanti
    $insPenyewa = $pdo->prepare("INSERT INTO penyewa (tenant_account_id, nama, telepon) VALUES (?,?,?)");
    $insPenyewa->execute([$booking['tenant_account_id'], $booking['nama_pemohon'], $booking['telepon']]);
    $penyewaId = (int)$pdo->lastInsertId();
    $kodePenyewa = 'PYW-' . str_pad($penyewaId, 4, '0', STR_PAD_LEFT);

    // 2) Buat kontrak (nomor sementara, diupdate setelah dapat id)
    $insKontrak = $pdo->prepare("INSERT INTO kontrak
        (nomor, penyewa_id, unit_id, tanggal_mulai, tanggal_selesai, harga_sewa, deposit, status, catatan, dibuat_oleh)
        VALUES (?,?,?,?,?,?,?, 'Aktif', ?, ?)");
    $insKontrak->execute(['TMP', $penyewaId, $booking['unit_id'], $tanggalMulai, $tanggalSelesai, $hargaSewa, $deposit, $catatan ?: null, $adminId]);
    $kontrakId = (int)$pdo->lastInsertId();

    $nomorKontrak = sprintf('KTR/%s/%03d', date('Y'), $kontrakId);
    $pdo->prepare("UPDATE kontrak SET nomor = ? WHERE id = ?")->execute([$nomorKontrak, $kontrakId]);

    // 3) Unit jadi Terisi
    $pdo->prepare("UPDATE units SET status = 'Terisi' WHERE id = ?")->execute([$booking['unit_id']]);

    // 4) Tandai booking request disetujui
    $pdo->prepare("UPDATE booking_requests SET status='Disetujui', penyewa_id=?, kontrak_id=?, processed_by=?, processed_at=NOW() WHERE id=?")
        ->execute([$penyewaId, $kontrakId, $adminId, $bookingId]);

    // 5) Ambil rekening perusahaan aktif untuk dicantumkan di pesan
    $rekStmt = $pdo->query("SELECT nama_bank, nomor_rekening, atas_nama FROM rekening_perusahaan WHERE is_active = 1 ORDER BY urutan ASC");
    $rekening = $rekStmt->fetchAll();
    $rekeningText = '';
    foreach ($rekening as $r) {
        $rekeningText .= "\n- {$r['nama_bank']}: {$r['nomor_rekening']} a.n. {$r['atas_nama']}";
    }

    // 6) Kirim pesan notifikasi lengkap ke penyewa
    $pesanIsi = "Selamat! Pengajuan sewa Anda telah DISETUJUI.\n\n"
        . "ID Penyewa Anda: {$kodePenyewa}\n"
        . "Nomor Kontrak: {$nomorKontrak}\n\n"
        . "Langkah selanjutnya: silakan lengkapi data penyewa Anda (NIK/NIB, NPWP, alamat, dll) di menu \"Lengkapi Data Penyewa\" pada portal ini.\n\n"
        . "Lembar Kontrak Kerjasama bisa dilihat/dicetak di: kontrak_cetak.php?id={$kontrakId}\n\n"
        . "Rekening pembayaran sewa PT. Food Station:" . $rekeningText;

    $pdo->prepare("INSERT INTO pesan (tenant_account_id, pengirim, admin_user_id, isi) VALUES (?, 'admin', ?, ?)")
        ->execute([$booking['tenant_account_id'], $adminId, $pesanIsi]);

    // 7) Log aktivitas
    $pdo->prepare("INSERT INTO aktivitas_log (user_id, tipe, judul, keterangan, referensi) VALUES (?, 'kontrak_baru', 'Kontrak Sewa Baru', ?, ?)")
        ->execute([$adminId, "{$booking['nama_pemohon']} — kontrak baru dari persetujuan pengajuan sewa", $nomorKontrak]);

    $pdo->commit();
    flash_redirect($redirectTo, 'success', "Pengajuan disetujui. Kontrak {$nomorKontrak} dibuat, penyewa diminta melengkapi datanya sendiri.");
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash_redirect($redirectTo, 'error', 'Terjadi kesalahan sistem saat memproses persetujuan.');
}
