<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/flash.php';
require_once 'includes/db.php';

$isTenant = isset($_SESSION['tenant_logged_in']) && $_SESSION['tenant_logged_in'] === true;
$isAdmin  = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

if (!$isTenant && !$isAdmin) {
    header('Location: index.php');
    exit;
}

$isi = trim($_POST['isi'] ?? '');

if ($isTenant) {
    $redirectTo = 'portal.php?page=pesan';
    $tenantAccountId = (int)$_SESSION['tenant_id'];
    $pengirim  = 'tenant';
    $adminUserId = null;
} else {
    $tenantAccountId = (int)($_POST['tenant_account_id'] ?? 0);
    $redirectTo = 'dashboard.php?page=pesan&tenant_id=' . $tenantAccountId;
    $pengirim  = 'admin';
    $adminUserId = (int)$_SESSION['user_id'];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirectTo);
    exit;
}

// ── Proses lampiran (opsional) ──────────────────────────────────
$lampiranPath = null;
if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['bukti'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        flash_redirect($redirectTo, 'error', 'Gagal mengunggah file. Coba lagi.');
    }

    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        flash_redirect($redirectTo, 'error', 'Ukuran file maksimal 5MB.');
    }

    $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);

    if (!isset($allowedMime[$mime])) {
        flash_redirect($redirectTo, 'error', 'Format file tidak didukung. Hanya JPG, PNG, WEBP, atau PDF.');
    }

    $ext = $allowedMime[$mime];
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $destDir  = __DIR__ . '/uploads/bukti_pembayaran/';
    $destPath = $destDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        flash_redirect($redirectTo, 'error', 'Gagal menyimpan file yang diunggah.');
    }

    $lampiranPath = 'uploads/bukti_pembayaran/' . $filename;
    if ($isi === '') {
        $isi = '[Lampiran bukti pembayaran]';
    }
}

if ($tenantAccountId <= 0 || ($isi === '' && $lampiranPath === null)) {
    flash_redirect($redirectTo, 'error', 'Pesan tidak boleh kosong.');
}
if (!$pdo) {
    flash_redirect($redirectTo, 'error', 'Koneksi database gagal.');
}

try {
    $stmt = $pdo->prepare("INSERT INTO pesan (tenant_account_id, pengirim, admin_user_id, isi, lampiran) VALUES (?,?,?,?,?)");
    $stmt->execute([$tenantAccountId, $pengirim, $adminUserId, $isi, $lampiranPath]);
    flash_redirect($redirectTo, 'success', 'Pesan terkirim.');
} catch (PDOException $e) {
    flash_redirect($redirectTo, 'error', 'Terjadi kesalahan sistem saat mengirim pesan.');
}
