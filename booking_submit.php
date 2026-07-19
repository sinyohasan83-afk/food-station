<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/flash.php';

if (!isset($_SESSION['tenant_logged_in']) || $_SESSION['tenant_logged_in'] !== true) {
    header('Location: tenant_login.php');
    exit;
}

$redirectTo = $_POST['redirect_to'] ?? 'portal.php';
if (!str_starts_with($redirectTo, 'portal.php')) {
    $redirectTo = 'portal.php';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirectTo);
    exit;
}

require_once 'includes/db.php';

$unitId   = (int)($_POST['unit_id'] ?? 0);
$nama     = trim($_POST['nama_pemohon'] ?? '');
$telepon  = trim($_POST['telepon'] ?? '');
$tanggal  = trim($_POST['tanggal_mulai_rencana'] ?? '');
$catatan  = trim($_POST['catatan'] ?? '');
$tenantId = (int)$_SESSION['tenant_id'];

if (!$pdo) {
    flash_redirect($redirectTo, 'error', 'Koneksi database gagal. Coba lagi nanti.');
}

if ($unitId <= 0 || $nama === '' || $telepon === '' || $tanggal === '') {
    flash_redirect($redirectTo, 'error', 'Harap lengkapi semua field booking.');
}

if ($tanggal < date('Y-m-d')) {
    flash_redirect($redirectTo, 'error', 'Tanggal mulai sewa tidak boleh sebelum hari ini.');
}

try {
    $stmt = $pdo->prepare("SELECT status FROM units WHERE id = ?");
    $stmt->execute([$unitId]);
    $unit = $stmt->fetch();

    if (!$unit) {
        flash_redirect($redirectTo, 'error', 'Unit tidak ditemukan.');
    }
    if ($unit['status'] !== 'Kosong') {
        flash_redirect($redirectTo, 'error', 'Maaf, unit ini sudah tidak tersedia.');
    }

    $chk = $pdo->prepare("SELECT id FROM booking_requests WHERE tenant_account_id = ? AND unit_id = ? AND status = 'Menunggu'");
    $chk->execute([$tenantId, $unitId]);
    if ($chk->fetch()) {
        flash_redirect($redirectTo, 'info', 'Kamu sudah mengajukan booking untuk unit ini, sedang menunggu persetujuan admin.');
    }

    $ins = $pdo->prepare("INSERT INTO booking_requests (tenant_account_id, unit_id, nama_pemohon, telepon, tanggal_mulai_rencana, catatan) VALUES (?,?,?,?,?,?)");
    $ins->execute([$tenantId, $unitId, $nama, $telepon, $tanggal, $catatan]);

    flash_redirect($redirectTo, 'success', 'Permintaan booking berhasil dikirim! Menunggu persetujuan admin.');
} catch (PDOException $e) {
    flash_redirect($redirectTo, 'error', 'Terjadi kesalahan sistem saat mengirim booking.');
}
