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
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$nama     = trim($_POST['nama'] ?? '');
$email    = trim($_POST['email'] ?? '');
$roleValid = ['superadmin', 'admin', 'staff'];
$role     = in_array($_POST['role'] ?? '', $roleValid, true) ? $_POST['role'] : 'staff';

if ($username === '' || $nama === '' || $email === '') {
    flash_redirect($redirectTo, 'error', 'Username, nama, dan email wajib diisi.');
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
    // Username harus unik lintas tabel users & tenant_accounts (supaya deteksi login tidak ambigu)
    $chk = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $chk->execute([$username, $id]);
    if ($chk->fetch()) {
        flash_redirect($redirectTo, 'error', 'Username sudah dipakai akun admin/staff lain.');
    }
    $chk2 = $pdo->prepare("SELECT id FROM tenant_accounts WHERE username = ?");
    $chk2->execute([$username]);
    if ($chk2->fetch()) {
        flash_redirect($redirectTo, 'error', 'Username sudah dipakai akun penyewa.');
    }

    if ($id > 0) {
        if ($password !== '') {
            $stmt = $pdo->prepare("UPDATE users SET username=?, nama=?, email=?, role=?, password=? WHERE id=?");
            $stmt->execute([$username, $nama, $email, $role, password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username=?, nama=?, email=?, role=? WHERE id=?");
            $stmt->execute([$username, $nama, $email, $role, $id]);
        }
        flash_redirect($redirectTo, 'success', 'Akun admin/staff berhasil diperbarui.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, nama, email, role) VALUES (?,?,?,?,?)");
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $nama, $email, $role]);
        flash_redirect($redirectTo, 'success', 'Akun admin/staff baru berhasil dibuat.');
    }
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        flash_redirect($redirectTo, 'error', 'Username atau email sudah dipakai.');
    }
    flash_redirect($redirectTo, 'error', 'Terjadi kesalahan sistem saat menyimpan akun.');
}
