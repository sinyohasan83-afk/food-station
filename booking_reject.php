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
$alasan    = trim($_POST['alasan_tolak'] ?? '');
$adminId   = (int)$_SESSION['user_id'];

if ($bookingId <= 0) {
    flash_redirect($redirectTo, 'error', 'Pengajuan tidak valid.');
}
if (!$pdo) {
    flash_redirect($redirectTo, 'error', 'Koneksi database gagal.');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM booking_requests WHERE id = ? FOR UPDATE");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    if (!$booking) {
        $pdo->rollBack();
        flash_redirect($redirectTo, 'error', 'Pengajuan tidak ditemukan.');
    }
    if ($booking['status'] !== 'Menunggu') {
        $pdo->rollBack();
        flash_redirect($redirectTo, 'error', 'Pengajuan ini sudah diproses sebelumnya.');
    }

    $pdo->prepare("UPDATE booking_requests SET status='Ditolak', alasan_tolak=?, processed_by=?, processed_at=NOW() WHERE id=?")
        ->execute([$alasan ?: null, $adminId, $bookingId]);

    $pesanIsi = 'Mohon maaf, pengajuan sewa Anda belum dapat kami setujui.'
        . ($alasan !== '' ? (' Alasan: ' . $alasan) : '')
        . ' Silakan hubungi kami jika ada pertanyaan.';
    $pdo->prepare("INSERT INTO pesan (tenant_account_id, pengirim, admin_user_id, isi) VALUES (?, 'admin', ?, ?)")
        ->execute([$booking['tenant_account_id'], $adminId, $pesanIsi]);

    $pdo->commit();
    flash_redirect($redirectTo, 'success', 'Pengajuan telah ditolak dan penyewa sudah diberi tahu.');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash_redirect($redirectTo, 'error', 'Terjadi kesalahan sistem saat memproses penolakan.');
}
