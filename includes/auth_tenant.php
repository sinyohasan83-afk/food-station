<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['tenant_logged_in']) || $_SESSION['tenant_logged_in'] !== true) {
    header('Location: tenant_login.php');
    exit;
}
