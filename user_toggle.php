<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/flash.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
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
if ($id === (int)($_SESSION['user_id'] ?? 0)) {
    flash_redirect($redirectTo, 'error', 'Tidak bisa menonaktifkan akun yang sedang kamu pakai sendiri.');
}
if (!$pdo) {
    flash_redirect($redirectTo, 'error', 'Koneksi database gagal.');
}

try {
    $stmt = $pdo->prepare("SELECT is_active, nama FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $u = $stmt->fetch();
    if (!$u) {
        flash_redirect($redirectTo, 'error', 'Akun tidak ditemukan.');
    }

    $newStatus = $u['is_active'] ? 0 : 1;
    $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?")->execute([$newStatus, $id]);

    $msg = $newStatus ? "Akun {$u['nama']} diaktifkan kembali." : "Akun {$u['nama']} dinonaktifkan.";
    flash_redirect($redirectTo, 'success', $msg);
} catch (PDOException $e) {
    flash_redirect($redirectTo, 'error', 'Terjadi kesalahan sistem.');
}
