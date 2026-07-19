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

if ($nama === '' || $telepon === '') {
    flash_redirect($redirectTo, 'error', 'Nama dan telepon wajib diisi.');
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_redirect($redirectTo, 'error', 'Format email tidak valid.');
}
if (!$pdo) {
    flash_redirect($redirectTo, 'error', 'Koneksi database gagal.');
}

$params = [
    $nama, $jenis, $nikNib ?: null, $npwp ?: null,
    $penanggungJawab ?: null, $jabatanPJ ?: null, $telepon, $email ?: null,
    $alamat ?: null, $kota ?: null, $provinsi ?: null, $kodePos ?: null,
    $kontakDaruratNama ?: null, $kontakDaruratTelepon ?: null, $catatan ?: null,
];

try {
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE penyewa SET
            nama=?, jenis=?, nik_nib=?, npwp=?, nama_penanggung_jawab=?, jabatan_penanggung_jawab=?,
            telepon=?, email=?, alamat=?, kota=?, provinsi=?, kode_pos=?,
            kontak_darurat_nama=?, kontak_darurat_telepon=?, catatan=?
            WHERE id=?");
        $stmt->execute([...$params, $id]);
        flash_redirect($redirectTo, 'success', 'Data penyewa berhasil diperbarui.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO penyewa
            (nama, jenis, nik_nib, npwp, nama_penanggung_jawab, jabatan_penanggung_jawab,
             telepon, email, alamat, kota, provinsi, kode_pos, kontak_darurat_nama, kontak_darurat_telepon, catatan)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute($params);
        flash_redirect($redirectTo, 'success', 'Penyewa baru berhasil ditambahkan.');
    }
} catch (PDOException $e) {
    flash_redirect($redirectTo, 'error', 'Terjadi kesalahan sistem saat menyimpan data penyewa.');
}
