<?php
// Helper flash message: simpan pesan singkat di session, tampilkan sekali via showToast() lalu hilang.

function flash_redirect(string $url, string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    header('Location: ' . $url);
    exit;
}

function flash_render(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['flash'])) return;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $type = in_array($f['type'], ['success', 'error', 'info', 'warn'], true) ? $f['type'] : 'success';
    echo '<script>showToast(' . json_encode($f['message'], JSON_UNESCAPED_UNICODE) . ',' . json_encode($type) . ');</script>';
}
