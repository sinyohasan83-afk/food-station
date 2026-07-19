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

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    flash_redirect($redirectTo, 'error', 'Penyewa tidak valid.');
}
if (!$pdo) {
    flash_redirect($redirectTo, 'error', 'Koneksi database gagal.');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT id, nama FROM penyewa WHERE id = ? FOR UPDATE");
    $stmt->execute([$id]);
    $penyewa = $stmt->fetch();

    if (!$penyewa) {
        $pdo->rollBack();
        flash_redirect($redirectTo, 'error', 'Data penyewa tidak ditemukan.');
    }

    // Unit yang disewa penyewa ini (lewat kontrak) dikembalikan jadi Kosong
    $unitStmt = $pdo->prepare("SELECT DISTINCT unit_id FROM kontrak WHERE penyewa_id = ?");
    $unitStmt->execute([$id]);
    $unitIds = array_column($unitStmt->fetchAll(), 'unit_id');

    // Hapus berjenjang: pembayaran -> tagihan -> kontrak -> penyewa
    $pdo->prepare("DELETE p FROM pembayaran p JOIN tagihan t ON t.id = p.tagihan_id WHERE t.penyewa_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM tagihan WHERE penyewa_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM kontrak WHERE penyewa_id = ?")->execute([$id]);
    $pdo->prepare("UPDATE booking_requests SET penyewa_id = NULL WHERE penyewa_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM penyewa WHERE id = ?")->execute([$id]);

    if (!empty($unitIds)) {
        $in = implode(',', array_fill(0, count($unitIds), '?'));
        $pdo->prepare("UPDATE units SET status = 'Kosong' WHERE id IN ($in)")->execute($unitIds);
    }

    $pdo->commit();
    flash_redirect($redirectTo, 'success', 'Data penyewa "' . $penyewa['nama'] . '" beserta kontrak & tagihan terkait berhasil dihapus.');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash_redirect($redirectTo, 'error', 'Terjadi kesalahan sistem saat menghapus data penyewa.');
}
