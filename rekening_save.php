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

$id            = (int)($_POST['id'] ?? 0);
$namaBank      = trim($_POST['nama_bank'] ?? '');
$nomorRekening = trim($_POST['nomor_rekening'] ?? '');
$atasNama      = trim($_POST['atas_nama'] ?? '');
$urutan        = (int)($_POST['urutan'] ?? 0);
$isActive      = isset($_POST['is_active']) ? 1 : 0;

if ($namaBank === '' || $nomorRekening === '' || $atasNama === '') {
    flash_redirect($redirectTo, 'error', 'Nama bank, nomor rekening, dan atas nama wajib diisi.');
}
if (!$pdo) {
    flash_redirect($redirectTo, 'error', 'Koneksi database gagal.');
}

try {
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE rekening_perusahaan SET nama_bank=?, nomor_rekening=?, atas_nama=?, urutan=?, is_active=? WHERE id=?");
        $stmt->execute([$namaBank, $nomorRekening, $atasNama, $urutan, $isActive, $id]);
        flash_redirect($redirectTo, 'success', 'Rekening berhasil diperbarui.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO rekening_perusahaan (nama_bank, nomor_rekening, atas_nama, urutan, is_active) VALUES (?,?,?,?,?)");
        $stmt->execute([$namaBank, $nomorRekening, $atasNama, $urutan, $isActive]);
        flash_redirect($redirectTo, 'success', 'Rekening baru berhasil ditambahkan.');
    }
} catch (PDOException $e) {
    flash_redirect($redirectTo, 'error', 'Terjadi kesalahan sistem saat menyimpan rekening.');
}
