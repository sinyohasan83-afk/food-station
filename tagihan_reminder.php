<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/flash.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$redirectTo = 'dashboard.php?page=tagihan';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirectTo);
    exit;
}

require_once 'includes/db.php';

$tagihanId = (int)($_POST['tagihan_id'] ?? 0);
$adminId   = (int)$_SESSION['user_id'];

if ($tagihanId <= 0) {
    flash_redirect($redirectTo, 'error', 'Tagihan tidak valid.');
}
if (!$pdo) {
    flash_redirect($redirectTo, 'error', 'Koneksi database gagal.');
}

try {
    $stmt = $pdo->prepare("SELECT t.*, p.tenant_account_id, u.kode AS kode_unit, u.nama AS nama_unit FROM tagihan t
        JOIN penyewa p ON p.id = t.penyewa_id JOIN units u ON u.id = t.unit_id WHERE t.id = ?");
    $stmt->execute([$tagihanId]);
    $tagihan = $stmt->fetch();

    if (!$tagihan) {
        flash_redirect($redirectTo, 'error', 'Tagihan tidak ditemukan.');
    }
    if ($tagihan['status'] === 'Lunas') {
        flash_redirect($redirectTo, 'error', 'Tagihan ini sudah lunas, tidak perlu diingatkan.');
    }
    if (empty($tagihan['tenant_account_id'])) {
        flash_redirect($redirectTo, 'error', 'Penyewa ini belum punya akun portal, reminder tidak bisa dikirim.');
    }

    $fmt = 'Rp ' . number_format($tagihan['nominal'] + $tagihan['denda'], 0, ',', '.');
    $pesanIsi = "PENGINGAT PEMBAYARAN\n\n"
        . "Tagihan {$tagihan['nomor']} untuk unit {$tagihan['kode_unit']} – {$tagihan['nama_unit']} sebesar {$fmt} "
        . "jatuh tempo pada {$tagihan['tanggal_jatuh_tempo']} masih belum kami terima pembayarannya.\n\n"
        . "Mohon segera lakukan pembayaran. Keterlambatan pembayaran dapat mengakibatkan penangguhan fasilitas sewa Anda.";

    $pdo->prepare("INSERT INTO pesan (tenant_account_id, pengirim, admin_user_id, isi) VALUES (?, 'admin', ?, ?)")
        ->execute([$tagihan['tenant_account_id'], $adminId, $pesanIsi]);

    flash_redirect($redirectTo, 'success', "Pengingat untuk tagihan {$tagihan['nomor']} berhasil dikirim ke inbox penyewa.");
} catch (PDOException $e) {
    flash_redirect($redirectTo, 'error', 'Terjadi kesalahan sistem saat mengirim pengingat.');
}
