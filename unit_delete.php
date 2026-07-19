<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/flash.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'includes/db.php';

$id         = (int)($_POST['id'] ?? 0);
$redirectTo = trim($_POST['redirect_to'] ?? 'dashboard.php?page=gudang');
if (!str_starts_with($redirectTo, 'dashboard.php')) {
    $redirectTo = 'dashboard.php?page=gudang';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirectTo);
    exit;
}

if ($id <= 0) {
    flash_redirect($redirectTo, 'error', 'Unit tidak valid.');
}
if (!$pdo) {
    flash_redirect($redirectTo, 'error', 'Koneksi database gagal.');
}

try {
    $stmt = $pdo->prepare("SELECT status FROM units WHERE id = ?");
    $stmt->execute([$id]);
    $unit = $stmt->fetch();

    if (!$unit) {
        flash_redirect($redirectTo, 'error', 'Unit tidak ditemukan.');
    }
    if ($unit['status'] === 'Terisi') {
        flash_redirect($redirectTo, 'error', 'Unit ini sedang disewa (Terisi), tidak bisa dihapus.');
    }

    $pdo->prepare("DELETE FROM units WHERE id = ?")->execute([$id]);
    flash_redirect($redirectTo, 'success', 'Unit berhasil dihapus.');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        flash_redirect($redirectTo, 'error', 'Unit ini punya riwayat kontrak/pengajuan sewa, tidak bisa dihapus.');
    }
    flash_redirect($redirectTo, 'error', 'Terjadi kesalahan sistem saat menghapus unit.');
}
