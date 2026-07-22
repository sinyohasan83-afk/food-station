<?php
require_once __DIR__ . '/../includes/db.php';

$tenantId = (int)($_SESSION['tenant_id'] ?? 0);
$messages = [];
$rekening = [];
$dbError = false;

if ($pdo) {
    try {
        $pdo->prepare("UPDATE pesan SET is_read = 1 WHERE tenant_account_id = ? AND pengirim = 'admin' AND is_read = 0")
            ->execute([$tenantId]);

        $stmt = $pdo->prepare("SELECT * FROM pesan WHERE tenant_account_id = ? ORDER BY created_at ASC");
        $stmt->execute([$tenantId]);
        $messages = $stmt->fetchAll();

        $rekening = $pdo->query("SELECT * FROM rekening_perusahaan WHERE is_active = 1 ORDER BY urutan ASC")->fetchAll();
    } catch (PDOException $e) {
        $dbError = true;
    }
} else {
    $dbError = true;
}
?>

<!-- Header -->
<div class="mb-6">
  <h1 class="text-2xl font-extrabold text-white">Pesan dengan Admin</h1>
  <p class="text-white/35 text-sm mt-0.5">Tanyakan seputar pengajuan sewa, kirim bukti pembayaran, atau info lainnya langsung ke admin.</p>
</div>

<?php if ($dbError): ?>
  <div class="mb-6 bg-red-500/10 border border-red-500/30 rounded-2xl px-5 py-4 text-sm text-red-400 font-semibold">
    Koneksi database gagal. Pastikan MySQL aktif di XAMPP.
  </div>
<?php endif; ?>

<!-- Banner Rekening Perusahaan -->
<div class="glass rounded-2xl p-5 mb-6 border border-emerald-500/20">
  <div class="flex items-center gap-2 mb-3">
    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-400">Rekening Pembayaran Sewa</p>
  </div>
  <?php if (empty($rekening)): ?>
    <p class="text-xs text-white/30">Belum ada rekening yang diatur admin.</p>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <?php foreach ($rekening as $r): ?>
        <div class="bg-white/5 border border-white/10 rounded-xl p-3">
          <p class="text-xs font-black text-white"><?= htmlspecialchars($r['nama_bank']) ?></p>
          <p class="text-sm font-mono font-bold text-emerald-400 mt-0.5"><?= htmlspecialchars($r['nomor_rekening']) ?></p>
          <p class="text-[10px] text-white/40 mt-0.5"><?= htmlspecialchars($r['atas_nama']) ?></p>
          <button onclick='openBayarModal(<?= json_encode([
            "bank" => $r["nama_bank"], "norek" => $r["nomor_rekening"], "nama" => $r["atas_nama"],
          ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                  class="mt-2 w-full bg-emerald-500/15 hover:bg-emerald-500/25 border border-emerald-500/25 text-emerald-400 text-[10px] font-bold py-1.5 rounded-lg transition-all">
            Bayar Sekarang
          </button>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="text-[10px] text-white/25 mt-3">Setelah transfer, kirim bukti pembayaran lewat kolom pesan di bawah.</p>
  <?php endif; ?>
</div>

<!-- Modal Bayar Sekarang -->
<div id="bayarRekModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);">
  <div class="glass-strong rounded-3xl p-6 w-full max-w-sm fade-up text-center">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-base font-extrabold text-white">Bayar via Transfer</h3>
      <button type="button" onclick="closeBayarModal()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/60 transition-all">✕</button>
    </div>
    <div class="bg-white p-3 rounded-2xl inline-block mb-4">
      <img id="bayarQrImg" src="" alt="QR Rekening" class="w-40 h-40" width="160" height="160"/>
    </div>
    <p class="text-xs font-black text-white" id="bayarBankName">—</p>
    <div class="flex items-center justify-center gap-2 mt-1">
      <p class="text-lg font-mono font-bold text-emerald-400" id="bayarNorek">—</p>
      <button type="button" onclick="copyNorek()" title="Salin nomor rekening" class="text-white/40 hover:text-emerald-400 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
      </button>
    </div>
    <p class="text-xs text-white/40 mt-1" id="bayarAtasNama">—</p>
    <p class="text-[10px] text-white/25 mt-4 leading-relaxed">
      QR ini berisi rangkuman detail rekening (bukan QRIS resmi) — scan dengan aplikasi kamera/QR reader untuk menyalin info transfer, atau salin manual nomor rekening di atas.
      Setelah transfer, jangan lupa kirim bukti pembayaran lewat kolom pesan di bawah.
    </p>
  </div>
</div>

<div class="glass rounded-3xl overflow-hidden flex flex-col" style="height:calc(100vh - 430px); min-height:340px;">

  <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3">
    <?php if (empty($messages)): ?>
      <p class="text-center text-white/25 text-xs py-8">Belum ada percakapan. Kirim pesan pertama ke admin di bawah ini.</p>
    <?php endif; ?>
    <?php foreach ($messages as $m):
      $isTenantMsg = $m['pengirim'] === 'tenant';
      $ext = $m['lampiran'] ? strtolower(pathinfo($m['lampiran'], PATHINFO_EXTENSION)) : null;
    ?>
      <div class="flex <?= $isTenantMsg ? 'justify-end' : 'justify-start' ?>">
        <div class="<?= $isTenantMsg ? 'bg-indigo-600/20 border-indigo-500/25' : 'bg-white/8 border-white/10' ?> border rounded-2xl px-4 py-2.5 max-w-[75%]">
          <?php if (!$isTenantMsg): ?>
            <p class="text-[10px] font-bold text-emerald-400 mb-0.5 uppercase tracking-widest">Admin</p>
          <?php endif; ?>
          <p class="text-sm text-white/90 whitespace-pre-wrap"><?= htmlspecialchars($m['isi']) ?></p>
          <?php if ($m['lampiran']): ?>
            <?php if (in_array($ext, ['jpg','jpeg','png','webp'])): ?>
              <a href="<?= htmlspecialchars($m['lampiran']) ?>" target="_blank" class="block mt-2">
                <img src="<?= htmlspecialchars($m['lampiran']) ?>" alt="Bukti pembayaran" class="rounded-xl max-h-48 border border-white/10"/>
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

  <form method="POST" action="pesan_send.php" enctype="multipart/form-data" class="p-4 border-t border-white/8 flex-shrink-0" id="pesanForm">
    <div id="filePreview" class="hidden mb-2 flex items-center gap-2 bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-xs text-white/60 w-fit">
      <span id="filePreviewName">—</span>
      <button type="button" onclick="clearFile()" class="text-red-400 hover:text-red-300 font-bold">✕</button>
    </div>
    <div class="flex items-center gap-2">
      <label class="cursor-pointer flex-shrink-0 w-10 h-10 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 flex items-center justify-center transition-all" title="Lampirkan bukti pembayaran (JPG/PNG/WEBP/PDF, maks 5MB)">
        <svg class="w-4 h-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
        <input type="file" name="bukti" id="fileInput" accept=".jpg,.jpeg,.png,.webp,.pdf" class="hidden" onchange="showFilePreview()"/>
      </label>
      <input type="text" name="isi" placeholder="Tulis pesan ke admin…"
             class="login-input flex-1" style="font-size:13px"/>
      <button type="submit" class="login-btn px-5 flex-shrink-0" style="width:auto;font-size:12px;">Kirim</button>
    </div>
  </form>
</div>

<script>
function showFilePreview() {
  const input = document.getElementById('fileInput');
  const preview = document.getElementById('filePreview');
  if (input.files && input.files[0]) {
    document.getElementById('filePreviewName').textContent = '📎 ' + input.files[0].name;
    preview.classList.remove('hidden');
    preview.classList.add('flex');
  }
}
function clearFile() {
  document.getElementById('fileInput').value = '';
  const preview = document.getElementById('filePreview');
  preview.classList.add('hidden');
  preview.classList.remove('flex');
}

function openBayarModal(data) {
  const qrText = `Transfer ke ${data.bank}\nNo. Rek: ${data.norek}\na.n. ${data.nama}`;
  document.getElementById('bayarQrImg').src = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(qrText);
  document.getElementById('bayarBankName').textContent = data.bank;
  document.getElementById('bayarNorek').textContent = data.norek;
  document.getElementById('bayarAtasNama').textContent = data.nama;
  const modal = document.getElementById('bayarRekModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}
function closeBayarModal() {
  const modal = document.getElementById('bayarRekModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}
function copyNorek() {
  const norek = document.getElementById('bayarNorek').textContent;
  navigator.clipboard.writeText(norek).then(() => {
    if (typeof showToast === 'function') showToast('Nomor rekening disalin!', 'success');
  });
}
</script>
