<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$page = $_GET['page'] ?? 'home';
$validPages = ['home','gudang','toko','kantin_gudang','kantin_toko','penyewa','tagihan','pengajuan','pesan','rekening','pengaturan'];
if (!in_array($page, $validPages)) $page = 'home';

$pageTitle = 'PT. Food Station — Dashboard';
$assetBase = '';
?>
<?php include 'includes/header.php'; ?>
<body class="bg-hero min-h-screen">
<script>
  // Terapkan class 'light' ke body sebelum konten render (anti-flash lapis 2)
  (function(){
    if (localStorage.getItem('fs_theme') === 'light') {
      document.body.classList.add('light');
    }
  })();
</script>

<?php include 'includes/navbar.php'; ?>

<div class="flex h-[calc(100vh-65px)]">
  <?php include 'includes/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8">
    <div class="max-w-7xl mx-auto fade-up">
      <?php
      switch ($page) {
        case 'home':          include 'pages/home.php';   break;
        case 'gudang':        include 'pages/gudang.php'; break;
        case 'toko':          include 'pages/toko.php';   break;
        case 'kantin_gudang': $_GET['sub'] = 'gudang'; include 'pages/kantin.php'; break;
        case 'kantin_toko':   $_GET['sub'] = 'toko';   include 'pages/kantin.php'; break;
        case 'penyewa':       include 'pages/penyewa.php'; break;
        case 'tagihan':       include 'pages/tagihan.php'; break;
        case 'pengajuan':     include 'pages/pengajuan.php'; break;
        case 'pesan':         include 'pages/pesan.php'; break;
        case 'rekening':      include 'pages/rekening.php'; break;
        case 'pengaturan':    include 'pages/pengaturan.php'; break;
      }
      ?>
    </div>
  </main>
</div>

<!-- Toast Container (rendered by JS) -->
<div id="toastContainer" class="toast-container"></div>

<script src="assets/js/app.js?v=<?= filemtime(__DIR__ . '/assets/js/app.js') ?>"></script>
<?php require_once 'includes/flash.php'; flash_render(); ?>
</body>
</html>
