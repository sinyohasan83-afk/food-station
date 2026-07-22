<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/flash.php';

if (!isset($_SESSION['tenant_logged_in']) || $_SESSION['tenant_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$redirectTo = 'portal.php?page=lengkapi';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirectTo);
    exit;
}

require_once 'includes/db.php';

$id       = (int)($_POST['id'] ?? 0);
$tenantId = (int)$_SESSION['tenant_id'];

$jenisValid = ['Perorangan', 'CV', 'PT', 'UD', 'Lainnya'];
$nama       = trim($_POST['nama'] ?? '');
$jenis      = in_array($_POST['jenis'] ?? '', $jenisValid, true) ? $_POST['jenis'] : 'Perorangan';
$nikNib     = trim($_POST['nik_nib'] ?? '');
$npwp       = trim($_POST['npwp'] ?? '');
$penanggungJawab = trim($_POST['nama_penanggung_jawab'] ?? '');
$jabatanPJ  = trim($_POST['jabatan_penanggung_jawab'] ?? '');
$telepon    = trim($_POST['telepon'] ?? '');
$email      = trim($_POST['email'] ?? '');
$alamat     = trim($_POST['alamat'] ?? '');
$kota       = trim($_POST['kota'] ?? '');
$provinsi   = trim($_POST['provinsi'] ?? '');
$kodePos    = trim($_POST['kode_pos'] ?? '');
$kontakDaruratNama    = trim($_POST['kontak_darurat_nama'] ?? '');
$kontakDaruratTelepon = trim($_POST['kontak_darurat_telepon'] ?? '');
$catatan    = trim($_POST['catatan'] ?? '');

if ($id <= 0 || $nama === '' || $telepon === '') {
    flash_redirect($redirectTo, 'error', 'Nama dan telepon wajib diisi.');
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_redirect($redirectTo, 'error', 'Format email tidak valid.');
}
if (!$pdo) {
    flash_redirect($redirectTo, 'error', 'Koneksi database gagal.');
}

try {
    // Pastikan data penyewa ini benar-benar milik tenant yang sedang login
    $chk = $pdo->prepare("SELECT id FROM penyewa WHERE id = ? AND tenant_account_id = ?");
    $chk->execute([$id, $tenantId]);
    if (!$chk->fetch()) {
        flash_redirect($redirectTo, 'error', 'Data penyewa tidak ditemukan atau bukan milik Anda.');
    }

    $stmt = $pdo->prepare("UPDATE penyewa SET
        nama=?, jenis=?, nik_nib=?, npwp=?, nama_penanggung_jawab=?, jabatan_penanggung_jawab=?,
        telepon=?, email=?, alamat=?, kota=?, provinsi=?, kode_pos=?,
        kontak_darurat_nama=?, kontak_darurat_telepon=?, catatan=?
        WHERE id=? AND tenant_account_id=?");
    $stmt->execute([
        $nama, $jenis, $nikNib ?: null, $npwp ?: null,
        $penanggungJawab ?: null, $jabatanPJ ?: null, $telepon, $email ?: null,
        $alamat ?: null, $kota ?: null, $provinsi ?: null, $kodePos ?: null,
        $kontakDaruratNama ?: null, $kontakDaruratTelepon ?: null, $catatan ?: null,
        $id, $tenantId,
    ]);

    flash_redirect($redirectTo, 'success', 'Data penyewa berhasil disimpan.');
} catch (PDOException $e) {
    flash_redirect($redirectTo, 'error', 'Terjadi kesalahan sistem saat menyimpan data.');
}
