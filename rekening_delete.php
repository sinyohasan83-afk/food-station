<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/flash.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$redirectTo = 'dashboard.php?page=rekening';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirectTo);
    exit;
}

require_once 'includes/db.php';

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    flash_redirect($redirectTo, 'error', 'Rekening tidak valid.');
}
if (!$pdo) {
    flash_redirect($redirectTo, 'error', 'Koneksi database gagal.');
}

try {
    $pdo->prepare("DELETE FROM rekening_perusahaan WHERE id = ?")->execute([$id]);
    flash_redirect($redirectTo, 'success', 'Rekening berhasil dihapus.');
} catch (PDOException $e) {
    flash_redirect($redirectTo, 'error', 'Terjadi kesalahan sistem saat menghapus rekening.');
}
