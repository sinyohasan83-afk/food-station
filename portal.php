<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/auth_tenant.php';

$page       = $_GET['page'] ?? 'home';
$validPages = ['home', 'gudang', 'toko', 'kantin_gudang', 'kantin_toko', 'pesan', 'lengkapi'];
if (!in_array($page, $validPages)) $page = 'home';

$pageTitle = 'PT. Food Station — Portal Penyewa';
$assetBase = '';
?>
<?php include 'includes/header.php'; ?>
<body class="bg-hero min-h-screen">
<script>
  (function(){
    if (localStorage.getItem('fs_theme') === 'light') {
      document.body.classList.add('light');
    }
  })();
</script>

<?php include 'includes/navbar_tenant.php'; ?>

<div class="flex h-[calc(100vh-65px)]">
  <?php include 'includes/sidebar_tenant.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8">
    <div class="max-w-7xl mx-auto fade-up">
      <?php
      switch ($page) {
        case 'home':
          include 'pages/portal_home.php';
          break;
        case 'gudang':
          include 'pages/gudang.php';
          break;
        case 'toko':
          include 'pages/toko.php';
          break;
        case 'kantin_gudang':
          $_GET['sub'] = 'gudang';
          include 'pages/kantin.php';
          break;
        case 'kantin_toko':
          $_GET['sub'] = 'toko';
          include 'pages/kantin.php';
          break;
        case 'pesan':
          include 'pages/portal_pesan.php';
          break;
        case 'lengkapi':
          include 'pages/portal_lengkapi.php';
          break;
      }
      ?>
    </div>
  </main>
</div>

<div id="toastContainer" class="toast-container"></div>

<script src="assets/js/app.js?v=<?= filemtime(__DIR__ . '/assets/js/app.js') ?>"></script>
<?php require_once 'includes/flash.php'; flash_render(); ?>
</body>
</html>
