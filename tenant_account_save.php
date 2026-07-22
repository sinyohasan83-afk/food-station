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

$id       = (int)($_POST['id'] ?? 0);
$nama     = trim($_POST['nama'] ?? '');
$email    = trim($_POST['email'] ?? '');
$telepon  = trim($_POST['telepon'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($nama === '' || $email === '' || $username === '') {
    flash_redirect($redirectTo, 'error', 'Nama, email, dan username wajib diisi.');
}
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    flash_redirect($redirectTo, 'error', 'Username hanya boleh berisi huruf, angka, dan garis bawah.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_redirect($redirectTo, 'error', 'Format email tidak valid.');
}
if ($id <= 0 && strlen($password) < 6) {
    flash_redirect($redirectTo, 'error', 'Password minimal 6 karakter untuk akun baru.');
}
if ($password !== '' && strlen($password) < 6) {
    flash_redirect($redirectTo, 'error', 'Password minimal 6 karakter.');
}
if (!$pdo) {
    flash_redirect($redirectTo, 'error', 'Koneksi database gagal.');
}

try {
    // Username harus unik lintas tabel tenant_accounts & users
    $chk = $pdo->prepare("SELECT id FROM tenant_accounts WHERE username = ? AND id != ?");
    $chk->execute([$username, $id]);
    if ($chk->fetch()) {
        flash_redirect($redirectTo, 'error', 'Username sudah dipakai akun penyewa lain.');
    }
    $chk2 = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $chk2->execute([$username]);
    if ($chk2->fetch()) {
        flash_redirect($redirectTo, 'error', 'Username sudah dipakai akun admin/staff.');
    }

    if ($id > 0) {
        if ($password !== '') {
            $stmt = $pdo->prepare("UPDATE tenant_accounts SET nama=?, email=?, telepon=?, username=?, password=? WHERE id=?");
            $stmt->execute([$nama, $email, $telepon ?: null, $username, password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE tenant_accounts SET nama=?, email=?, telepon=?, username=? WHERE id=?");
            $stmt->execute([$nama, $email, $telepon ?: null, $username, $id]);
        }
        flash_redirect($redirectTo, 'success', 'Akun penyewa berhasil diperbarui.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO tenant_accounts (nama, email, telepon, username, password) VALUES (?,?,?,?,?)");
        $stmt->execute([$nama, $email, $telepon ?: null, $username, password_hash($password, PASSWORD_DEFAULT)]);
        flash_redirect($redirectTo, 'success', 'Akun penyewa baru berhasil dibuat. Silakan berikan username & password ini ke penyewa terkait.');
    }
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        flash_redirect($redirectTo, 'error', 'Username atau email sudah dipakai.');
    }
    flash_redirect($redirectTo, 'error', 'Terjadi kesalahan sistem saat menyimpan akun.');
}
