<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/flash.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}
if (($_SESSION['user_role'] ?? '') !== 'superadmin') {
    flash_redirect('dashboard.php?page=home', 'error', 'Akses ditolak. Menu Pengaturan hanya untuk Superadmin.');
}

$redirectTo = 'dashboard.php?page=pengaturan';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirectTo);
    exit;
}

require_once 'includes/db.php';

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    flash_redirect($redirectTo, 'error', 'Akun tidak valid.');
}
if (!$pdo) {
    flash_redirect($redirectTo, 'error', 'Koneksi database gagal.');
}

try {
    $stmt = $pdo->prepare("SELECT status, nama FROM tenant_accounts WHERE id = ?");
    $stmt->execute([$id]);
    $t = $stmt->fetch();
    if (!$t) {
        flash_redirect($redirectTo, 'error', 'Akun tidak ditemukan.');
    }

    $newStatus = $t['status'] === 'aktif' ? 'nonaktif' : 'aktif';
    $pdo->prepare("UPDATE tenant_accounts SET status = ? WHERE id = ?")->execute([$newStatus, $id]);

    $msg = $newStatus === 'aktif' ? "Akun {$t['nama']} diaktifkan kembali." : "Akun {$t['nama']} dinonaktifkan.";
    flash_redirect($redirectTo, 'success', $msg);
} catch (PDOException $e) {
    flash_redirect($redirectTo, 'error', 'Terjadi kesalahan sistem.');
}
