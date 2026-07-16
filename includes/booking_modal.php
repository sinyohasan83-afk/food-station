<!-- Booking Modal -->
<div id="bookingModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);">
  <div class="glass-strong rounded-3xl p-8 w-full max-w-md fade-up">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-extrabold text-white">Booking Unit</h3>
      <button onclick="closeBooking()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/60 transition-all">✕</button>
    </div>

    <div class="glass rounded-2xl p-4 mb-6">
      <div class="flex items-center justify-between text-sm mb-2">
        <span class="text-white/40">ID Unit</span>
        <span class="font-black text-white" id="modalUnitId">—</span>
      </div>
      <div class="flex items-center justify-between text-sm mb-2">
        <span class="text-white/40">Nama Unit</span>
        <span class="font-semibold text-white" id="modalUnitName">—</span>
      </div>
      <div class="flex items-center justify-between text-sm">
        <span class="text-white/40">Harga / Bulan</span>
        <span class="font-bold text-amber-400" id="modalHarga">—</span>
      </div>
    </div>

    <form onsubmit="submitBooking(event)" class="space-y-4">
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Nama Calon Penyewa</label>
        <input type="text" placeholder="Masukkan nama lengkap" required
               class="login-input" style="font-size:13px"/>
      </div>
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">No. Telepon / WhatsApp</label>
        <input type="tel" placeholder="08xx-xxxx-xxxx" required
               class="login-input" style="font-size:13px"/>
      </div>
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Rencana Mulai Sewa</label>
        <input type="date" required class="login-input" style="font-size:13px"
               min="<?= date('Y-m-d') ?>"/>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeBooking()" class="flex-1 bg-white/5 border border-white/10 hover:bg-white/10 text-white/60 font-bold text-sm py-3 rounded-xl transition-all">Batal</button>
        <button type="submit" class="flex-1 login-btn py-3" style="font-size:12px;">Kirim Permintaan</button>
      </div>
    </form>
  </div>
</div>
