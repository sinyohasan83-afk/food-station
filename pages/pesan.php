<?php
require_once __DIR__ . '/../includes/db.php';

$threads = [];
$messages = [];
$activeTenant = null;
$tenantId = (int)($_GET['tenant_id'] ?? 0);
$dbError = false;

if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT ta.id, ta.nama, ta.email,
                   (SELECT isi FROM pesan WHERE tenant_account_id = ta.id ORDER BY created_at DESC LIMIT 1) AS last_msg,
                   (SELECT created_at FROM pesan WHERE tenant_account_id = ta.id ORDER BY created_at DESC LIMIT 1) AS last_at,
                   (SELECT COUNT(*) FROM pesan WHERE tenant_account_id = ta.id AND pengirim='tenant' AND is_read=0) AS unread
            FROM tenant_accounts ta
            ORDER BY last_at IS NULL, last_at DESC, ta.nama ASC
        ");
        $threads = $stmt->fetchAll();

        if ($tenantId > 0) {
            $chk = $pdo->prepare("SELECT id, nama, email FROM tenant_accounts WHERE id = ?");
            $chk->execute([$tenantId]);
            $activeTenant = $chk->fetch();

            if ($activeTenant) {
                $pdo->prepare("UPDATE pesan SET is_read = 1 WHERE tenant_account_id = ? AND pengirim = 'tenant' AND is_read = 0")
                    ->execute([$tenantId]);

                $msgStmt = $pdo->prepare("SELECT * FROM pesan WHERE tenant_account_id = ? ORDER BY created_at ASC");
                $msgStmt->execute([$tenantId]);
                $messages = $msgStmt->fetchAll();
            }
        }
    } catch (PDOException $e) {
        $dbError = true;
    }
} else {
    $dbError = true;
}
?>

<!-- Header -->
<div class="mb-6">
  <div class="flex items-center gap-2 mb-1">
    <a href="dashboard.php" class="text-white/30 hover:text-white/60 text-xs font-semibold">Dashboard</a>
    <span class="text-white/20 text-xs">›</span>
    <span class="text-white/60 text-xs font-semibold">Pesan</span>
  </div>
  <h1 class="text-2xl font-extrabold text-white">Pesan dengan Penyewa</h1>
</div>

<?php if ($dbError): ?>
  <div class="mb-6 bg-red-500/10 border border-red-500/30 rounded-2xl px-5 py-4 text-sm text-red-400 font-semibold">
    Koneksi database gagal. Pastikan MySQL aktif di XAMPP.
  </div>
<?php endif; ?>

<div class="glass rounded-3xl overflow-hidden flex flex-col md:flex-row" style="height:calc(100vh - 260px); min-height:420px;">

  <!-- Daftar thread -->
  <div class="w-full md:w-72 flex-shrink-0 border-b md:border-b-0 md:border-r border-white/8 overflow-y-auto">
    <?php if (empty($threads)): ?>
      <p class="text-center text-white/25 text-xs py-8 px-4">Belum ada akun penyewa terdaftar.</p>
    <?php endif; ?>
    <?php foreach ($threads as $t): ?>
      <a href="dashboard.php?page=pesan&tenant_id=<?= (int)$t['id'] ?>"
         class="flex items-start gap-3 px-4 py-3.5 border-b border-white/5 hover:bg-white/5 transition-colors <?= $tenantId === (int)$t['id'] ? 'bg-white/8' : '' ?>">
        <div class="w-9 h-9 rounded-full bg-emerald-600/20 flex items-center justify-center text-xs font-black text-emerald-400 flex-shrink-0">
          <?= strtoupper(substr($t['nama'], 0, 1)) ?>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between gap-2">
            <p class="text-sm font-bold text-white truncate"><?= htmlspecialchars($t['nama']) ?></p>
            <?php if ($t['unread'] > 0): ?>
              <span class="bg-emerald-500 text-white text-[9px] font-black rounded-full w-4 h-4 flex items-center justify-center flex-shrink-0"><?= $t['unread'] ?></span>
            <?php endif; ?>
          </div>
          <p class="text-[11px] text-white/35 truncate"><?= $t['last_msg'] ? htmlspecialchars($t['last_msg']) : 'Belum ada pesan' ?></p>
        </div>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Percakapan -->
  <div class="flex-1 flex flex-col min-w-0">
    <?php if (!$activeTenant): ?>
      <div class="flex-1 flex items-center justify-center text-white/25 text-sm px-6 text-center">
        Pilih penyewa di sebelah kiri untuk melihat / membalas pesan.
      </div>
    <?php else: ?>
      <div class="px-5 py-3.5 border-b border-white/8 flex items-center gap-3 flex-shrink-0">
        <div class="w-9 h-9 rounded-full bg-emerald-600/20 flex items-center justify-center text-xs font-black text-emerald-400">
          <?= strtoupper(substr($activeTenant['nama'], 0, 1)) ?>
        </div>
        <div>
          <p class="text-sm font-bold text-white"><?= htmlspecialchars($activeTenant['nama']) ?></p>
          <p class="text-[11px] text-white/35"><?= htmlspecialchars($activeTenant['email']) ?></p>
        </div>
      </div>

      <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3">
        <?php if (empty($messages)): ?>
          <p class="text-center text-white/25 text-xs py-8">Belum ada percakapan. Mulai kirim pesan.</p>
        <?php endif; ?>
        <?php foreach ($messages as $m):
          $isAdmin = $m['pengirim'] === 'admin';
          $ext = $m['lampiran'] ? strtolower(pathinfo($m['lampiran'], PATHINFO_EXTENSION)) : null;
        ?>
          <div class="flex <?= $isAdmin ? 'justify-end' : 'justify-start' ?>">
            <div class="<?= $isAdmin ? 'bg-emerald-600/20 border-emerald-500/25' : 'bg-white/8 border-white/10' ?> border rounded-2xl px-4 py-2.5 max-w-[75%]">
              <p class="text-sm text-white/90 whitespace-pre-wrap"><?= htmlspecialchars($m['isi']) ?></p>
              <?php if ($m['lampiran']): ?>
                <?php if (in_array($ext, ['jpg','jpeg','png','webp'])): ?>
                  <a href="<?= htmlspecialchars($m['lampiran']) ?>" target="_blank" class="block mt-2">
                    <img src="<?= htmlspecialchars($m['lampiran']) ?>" alt="Lampiran" class="rounded-xl max-h-48 border border-white/10"/>
                  </a>
                <?php else: ?>
                  <a href="<?= htmlspecialchars($m['lampiran']) ?>" target="_blank" class="flex items-center gap-2 mt-2 bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-xs font-bold text-sky-400 hover:text-sky-300 transition-colors w-fit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Lihat Lampiran (PDF)
                  </a>
                <?php endif; ?>
              <?php endif; ?>
              <p class="text-[10px] text-white/30 mt-1"><?= date('d M Y, H:i', strtotime($m['created_at'])) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <form method="POST" action="pesan_send.php" enctype="multipart/form-data" class="p-4 border-t border-white/8 flex-shrink-0">
        <input type="hidden" name="tenant_account_id" value="<?= (int)$activeTenant['id'] ?>"/>
        <div class="flex items-center gap-2">
          <label class="cursor-pointer flex-shrink-0 w-10 h-10 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 flex items-center justify-center transition-all" title="Lampirkan file (JPG/PNG/WEBP/PDF, maks 5MB)">
            <svg class="w-4 h-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
            <input type="file" name="bukti" accept=".jpg,.jpeg,.png,.webp,.pdf" class="hidden"/>
          </label>
          <input type="text" name="isi" placeholder="Tulis pesan…"
                 class="login-input flex-1" style="font-size:13px"/>
          <button type="submit" class="login-btn px-5 flex-shrink-0" style="width:auto;font-size:12px;">Kirim</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>
