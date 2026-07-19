<?php
// Variabel yang harus disediakan oleh halaman yang meng-include:
// $unitModalKategoriId (1=Gudang, 2=Toko, 3=Kantin)
// $unitModalSubkategoriId (opsional, khusus Kantin: 1=Pergudangan, 2=Pertokoan)
// $unitModalRedirect (URL redirect setelah simpan/hapus)
$unitModalKategoriId    = $unitModalKategoriId ?? 0;
$unitModalSubkategoriId = $unitModalSubkategoriId ?? null;
$unitModalRedirect      = $unitModalRedirect ?? 'dashboard.php';
?>
<!-- Modal Tambah / Edit Unit -->
<div id="unitModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);">
  <div class="glass-strong rounded-3xl p-8 w-full max-w-lg fade-up" style="max-height:90vh;overflow-y:auto;">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-extrabold text-white" id="unitModalTitle">Tambah Unit</h3>
      <button type="button" onclick="closeUnitModal()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/60 transition-all">✕</button>
    </div>

    <form method="POST" action="unit_save.php" class="space-y-4">
      <input type="hidden" name="id" id="u_id" value=""/>
      <input type="hidden" name="kategori_id" value="<?= (int)$unitModalKategoriId ?>"/>
      <?php if ($unitModalSubkategoriId): ?>
        <input type="hidden" name="subkategori_id" value="<?= (int)$unitModalSubkategoriId ?>"/>
      <?php endif; ?>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Kode Unit <span class="text-red-400">*</span></label>
          <input type="text" name="kode" id="u_kode" required placeholder="Contoh: G-111" class="login-input" style="font-size:13px"/>
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Nama Unit <span class="text-red-400">*</span></label>
          <input type="text" name="nama" id="u_nama" required class="login-input" style="font-size:13px"/>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Luas (m²) <span class="text-red-400">*</span></label>
          <input type="text" name="luas" id="u_luas" required class="login-input" style="font-size:13px"/>
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Harga / Bulan (Rp) <span class="text-red-400">*</span></label>
          <input type="text" name="harga_per_bulan" id="u_harga" required class="login-input" style="font-size:13px"/>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Lantai</label>
          <input type="text" name="lantai" id="u_lantai" placeholder="Contoh: Lantai 1" class="login-input" style="font-size:13px"/>
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Status</label>
          <select name="status" id="u_status" class="login-input" style="font-size:13px">
            <option value="Kosong">Kosong</option>
            <option value="Maintenance">Maintenance</option>
          </select>
          <p id="u_status_locked_note" class="hidden text-[10px] text-amber-400/70 mt-1">Unit sedang Terisi — status hanya berubah lewat alur kontrak/Data Penyewa.</p>
        </div>
      </div>

      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Deskripsi</label>
        <textarea name="deskripsi" id="u_deskripsi" rows="2" class="login-input resize-none" style="height:auto;font-size:13px"></textarea>
      </div>

      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeUnitModal()" class="flex-1 bg-white/5 border border-white/10 hover:bg-white/10 text-white/60 font-bold text-sm py-3 rounded-xl transition-all">Batal</button>
        <button type="submit" class="flex-1 login-btn py-3" style="font-size:12px;">Simpan Unit</button>
      </div>
    </form>
  </div>
</div>

<script>
function openUnitModal(data) {
  const modal = document.getElementById('unitModal');
  const form  = modal.querySelector('form');
  form.reset();
  const statusField = document.getElementById('u_status');
  const lockedNote   = document.getElementById('u_status_locked_note');
  if (data && data.id) {
    document.getElementById('unitModalTitle').textContent = 'Edit Unit';
    document.getElementById('u_id').value = data.id;
    document.getElementById('u_kode').value = data.kode || '';
    document.getElementById('u_nama').value = data.nama || '';
    document.getElementById('u_luas').value = data.luas || '';
    document.getElementById('u_harga').value = data.harga_per_bulan || '';
    document.getElementById('u_lantai').value = data.lantai || '';
    document.getElementById('u_deskripsi').value = data.deskripsi || '';
    if (data.status === 'Terisi') {
      statusField.value = 'Kosong';
      statusField.disabled = true;
      lockedNote.classList.remove('hidden');
    } else {
      statusField.value = data.status || 'Kosong';
      statusField.disabled = false;
      lockedNote.classList.add('hidden');
    }
  } else {
    document.getElementById('unitModalTitle').textContent = 'Tambah Unit';
    document.getElementById('u_id').value = '';
    statusField.disabled = false;
    lockedNote.classList.add('hidden');
  }
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}
function closeUnitModal() {
  const modal = document.getElementById('unitModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}
</script>
