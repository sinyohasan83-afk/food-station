<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $pageTitle ?? 'PT. Food Station — Management System' ?></title>

  <!-- Anti-flash: terapkan tema SEBELUM render agar tidak berkedip -->
  <script>
    (function(){
      var t = localStorage.getItem('fs_theme') || 'dark';
      document.documentElement.setAttribute('data-theme', t);
      if (t === 'light') document.documentElement.classList.add('light-preload');
    })();
  </script>
  <style>
    html            { background: #0a0f1e; }
    html.light-preload { background: #f0f4f9; }
  </style>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= $assetBase ?? '' ?>assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>"/>
</head>
