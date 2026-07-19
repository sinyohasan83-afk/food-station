<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/flash.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'includes/db.php';

$id           = (int)($_POST['id'] ?? 0);
$kategoriId   = (int)($_POST['kategori_id'] ?? 0);
$subkategoriId = !empty($_POST['subkategori_id']) ? (int)$_POST['subkategori_id'] : null;
$kode         = trim($_POST['kode'] ?? '');
$nama         = trim($_POST['nama'] ?? '');
$luas         = (float)str_replace(',', '.', $_POST['luas'] ?? '0');
$harga        = (int)preg_replace('/\D/', '', $_POST['harga_per_bulan'] ?? '0');
$lantai       = trim($_POST['lantai'] ?? '');
$deskripsi    = trim($_POST['deskripsi'] ?? '');
$status       = ($_POST['status'] ?? 'Kosong') === 'Maintenance' ? 'Maintenance' : 'Kosong';
$redirectMap  = [1 => 'dashboard.php?page=gudang', 2 => 'dashboard.php?page=toko', 3 => 'dashboard.php?page=kantin_gudang'];
$redirectTo   = $redirectMap[$kategoriId] ?? 'dashboard.php';
if ($kategoriId === 3 && $subkategoriId === 2) {
    $redirectTo = 'dashboard.php?page=kantin_toko';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirectTo);
    exit;
}

if (!in_array($kategoriId, [1, 2, 3], true) || $kode === '' || $nama === '' || $luas <= 0 || $harga <= 0) {
    flash_redirect($redirectTo, 'error', 'Harap lengkapi semua field wajib dengan benar.');
}
if (!$pdo) {
    flash_redirect($redirectTo, 'error', 'Koneksi database gagal.');
}

try {
    if ($id > 0) {
        // Status Terisi tidak boleh diubah manual lewat form ini (hanya lewat alur approval kontrak)
        $cur = $pdo->prepare("SELECT status FROM units WHERE id = ?");
        $cur->execute([$id]);
        $existing = $cur->fetch();
        if ($existing && $existing['status'] === 'Terisi') {
            $status = 'Terisi';
        }

        $stmt = $pdo->prepare("UPDATE units SET kode=?, nama=?, kategori_id=?, subkategori_id=?, luas=?, harga_per_bulan=?, lantai=?, deskripsi=?, status=? WHERE id=?");
        $stmt->execute([$kode, $nama, $kategoriId, $subkategoriId, $luas, $harga, $lantai ?: null, $deskripsi ?: null, $status, $id]);
        flash_redirect($redirectTo, 'success', 'Unit berhasil diperbarui.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO units (kode, nama, kategori_id, subkategori_id, luas, harga_per_bulan, lantai, deskripsi, status) VALUES (?,?,?,?,?,?,?,?, 'Kosong')");
        $stmt->execute([$kode, $nama, $kategoriId, $subkategoriId, $luas, $harga, $lantai ?: null, $deskripsi ?: null]);
        flash_redirect($redirectTo, 'success', 'Unit baru berhasil ditambahkan.');
    }
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        flash_redirect($redirectTo, 'error', 'Kode unit sudah dipakai, gunakan kode lain.');
    }
    flash_redirect($redirectTo, 'error', 'Terjadi kesalahan sistem saat menyimpan unit.');
}
