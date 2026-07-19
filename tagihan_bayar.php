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
$metodeValid = ['Transfer Bank', 'Tunai', 'Cek', 'Giro', 'QRIS', 'Lainnya'];
$metode    = in_array($_POST['metode'] ?? '', $metodeValid, true) ? $_POST['metode'] : 'Transfer Bank';
$referensi = trim($_POST['referensi'] ?? '');
$adminId   = (int)$_SESSION['user_id'];

if ($tagihanId <= 0) {
    flash_redirect($redirectTo, 'error', 'Tagihan tidak valid.');
}
if (!$pdo) {
    flash_redirect($redirectTo, 'error', 'Koneksi database gagal.');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT t.*, p.nama AS penyewa_nama, p.tenant_account_id, u.kode AS kode_unit FROM tagihan t
        JOIN penyewa p ON p.id = t.penyewa_id JOIN units u ON u.id = t.unit_id WHERE t.id = ? FOR UPDATE");
    $stmt->execute([$tagihanId]);
    $tagihan = $stmt->fetch();

    if (!$tagihan) {
        $pdo->rollBack();
        flash_redirect($redirectTo, 'error', 'Tagihan tidak ditemukan.');
    }
    if ($tagihan['status'] === 'Lunas') {
        $pdo->rollBack();
        flash_redirect($redirectTo, 'error', 'Tagihan ini sudah lunas.');
    }

    $totalBayar = $tagihan['nominal'] + $tagihan['denda'];

    $pdo->prepare("INSERT INTO pembayaran (tagihan_id, nominal, tanggal_bayar, metode, referensi, dicatat_oleh) VALUES (?,?,CURDATE(),?,?,?)")
        ->execute([$tagihanId, $totalBayar, $metode, $referensi ?: null, $adminId]);

    $pdo->prepare("UPDATE tagihan SET status = 'Lunas' WHERE id = ?")->execute([$tagihanId]);

    if (!empty($tagihan['tenant_account_id'])) {
        $fmt = 'Rp ' . number_format($totalBayar, 0, ',', '.');
        $pesanIsi = "Pembayaran tagihan {$tagihan['nomor']} sebesar {$fmt} ({$metode}) telah kami terima dan konfirmasi. Terima kasih!";
        $pdo->prepare("INSERT INTO pesan (tenant_account_id, pengirim, admin_user_id, isi) VALUES (?, 'admin', ?, ?)")
            ->execute([$tagihan['tenant_account_id'], $adminId, $pesanIsi]);
    }

    $pdo->prepare("INSERT INTO aktivitas_log (user_id, tipe, judul, keterangan, referensi) VALUES (?, 'pembayaran', 'Pembayaran Diterima', ?, ?)")
        ->execute([$adminId, "{$tagihan['penyewa_nama']} — {$tagihan['kode_unit']}", $tagihan['nomor']]);

    $pdo->commit();
    flash_redirect($redirectTo, 'success', "Pembayaran tagihan {$tagihan['nomor']} berhasil dikonfirmasi.");
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash_redirect($redirectTo, 'error', 'Terjadi kesalahan sistem saat mengonfirmasi pembayaran.');
}
