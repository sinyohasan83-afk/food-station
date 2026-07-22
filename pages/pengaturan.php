<?php
require_once __DIR__ . '/../includes/db.php';

$adminUsers = [];
$tenantAccounts = [];
$dbError = false;

if ($pdo) {
    try {
        $adminUsers = $pdo->query("SELECT * FROM users ORDER BY id ASC")->fetchAll();
        $tenantAccounts = $pdo->query("SELECT * FROM tenant_accounts ORDER BY id ASC")->fetchAll();
    } catch (PDOException $e) {
        $dbError = true;
    }
} else {
    $dbError = true;
}

$myUserId = (int)($_SESSION['user_id'] ?? 0);
?>

<!-- Header -->
<div class="mb-8">
  <div class="flex items-center gap-2 mb-1">
    <a href="dashboard.php" class="text-white/30 hover:text-white/60 text-xs font-semibold">Dashboard</a>
    <span class="text-white/20 text-xs">›</span>
    <span class="text-white/60 text-xs font-semibold">Pengaturan</span>
  </div>
  <h1 class="text-2xl font-extrabold text-white">Pengaturan &amp; Kelola Akun</h1>
  <p class="text-white/35 text-sm mt-0.5">Pendaftaran mandiri sudah dinonaktifkan — hanya admin yang bisa membuat akun baru di sini.</p>
</div>

<?php if ($dbError): ?>
  <div class="mb-6 bg-red-500/10 border border-red-500/30 rounded-2xl px-5 py-4 text-sm text-red-400 font-semibold">
    Koneksi database gagal. Pastikan MySQL aktif di XAMPP.
  </div>
<?php endif; ?>

<!-- ===================== Akun Admin / Staff ===================== -->
<div class="mb-10">
  <div class="flex items-center justify-between mb-4">
    <p class="text-[10px] font-black uppercase tracking-widest text-white/30">Akun Admin &amp; Staff (<?= count($adminUsers) ?>)</p>
    <button onclick="openUserModal()" class="flex items-center gap-2 bg-emerald-600/15 hover:bg-emerald-600/25 border border-emerald-500/25 text-emerald-400 px-4 py-2 rounded-xl text-xs font-bold transition-all">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      Tambah Akun Admin
    </button>
  </div>
  <div class="glass rounded-3xl overflow-hidden">
    <div class="overflow-x-auto">
    <table class="data-table">
      <thead>
        <tr>
          <th class="text-left">Nama</th>
          <th class="text-left">Username</th>
          <th class="text-left">Email</th>
          <th class="text-center">Role</th>
          <th class="text-center">Status</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($adminUsers)): ?>
          <tr><td colspan="6" class="text-center text-white/30 py-6 text-sm">Belum ada akun admin/staff.</td></tr>
        <?php endif; ?>
        <?php foreach ($adminUsers as $u): ?>
          <tr class="table-row">
            <td class="font-bold text-white"><?= htmlspecialchars($u['nama']) ?><?= $u['id'] === $myUserId ? ' <span class="text-white/25 text-[10px] font-normal">(kamu)</span>' : '' ?></td>
            <td class="font-mono text-xs text-white/60"><?= htmlspecialchars($u['username']) ?></td>
            <td class="text-xs text-white/50"><?= htmlspecialchars($u['email']) ?></td>
            <td class="text-center"><span class="badge badge-indigo"><?= htmlspecialchars($u['role']) ?></span></td>
            <td class="text-center"><span class="badge <?= $u['is_active'] ? 'badge-green' : 'badge-red' ?>"><?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
            <td class="text-center">
              <div class="flex items-center justify-center gap-2">
                <button onclick='openUserModal(<?= json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="text-indigo-400 hover:text-indigo-300 text-xs font-bold transition-colors">Edit</button>
                <?php if ($u['id'] !== $myUserId): ?>
                  <form method="POST" action="user_toggle.php" onsubmit="return confirm('<?= $u['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?> akun <?= htmlspecialchars(addslashes($u['nama'])) ?>?');" class="inline">
                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>"/>
                    <button type="submit" class="<?= $u['is_active'] ? 'text-red-400 hover:text-red-300' : 'text-emerald-400 hover:text-emerald-300' ?> text-xs font-bold transition-colors">
                      <?= $u['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<!-- ===================== Akun Penyewa ===================== -->
<div>
  <div class="flex items-center justify-between mb-4">
    <p class="text-[10px] font-black uppercase tracking-widest text-white/30">Akun Penyewa (<?= count($tenantAccounts) ?>)</p>
    <button onclick="openTenantAccModal()" class="flex items-center gap-2 bg-emerald-600/15 hover:bg-emerald-600/25 border border-emerald-500/25 text-emerald-400 px-4 py-2 rounded-xl text-xs font-bold transition-all">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      Tambah Akun Penyewa
    </button>
  </div>
  <div class="glass rounded-3xl overflow-hidden">
    <div class="overflow-x-auto">
    <table class="data-table">
      <thead>
        <tr>
          <th class="text-left">Nama</th>
          <th class="text-left">Username</th>
          <th class="text-left">Kontak</th>
          <th class="text-center">Status</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($tenantAccounts)): ?>
          <tr><td colspan="5" class="text-center text-white/30 py-6 text-sm">Belum ada akun penyewa.</td></tr>
        <?php endif; ?>
        <?php foreach ($tenantAccounts as $t): ?>
          <tr class="table-row">
            <td class="font-bold text-white"><?= htmlspecialchars($t['nama']) ?></td>
            <td class="font-mono text-xs text-white/60"><?= htmlspecialchars($t['username']) ?></td>
            <td class="text-xs text-white/50"><?= htmlspecialchars($t['email']) ?><?= $t['telepon'] ? ' · ' . htmlspecialchars($t['telepon']) : '' ?></td>
            <td class="text-center"><span class="badge <?= $t['status'] === 'aktif' ? 'badge-green' : 'badge-red' ?>"><?= $t['status'] === 'aktif' ? 'Aktif' : 'Nonaktif' ?></span></td>
            <td class="text-center">
              <div class="flex items-center justify-center gap-2">
                <button onclick='openTenantAccModal(<?= json_encode($t, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="text-indigo-400 hover:text-indigo-300 text-xs font-bold transition-colors">Edit</button>
                <form method="POST" action="tenant_account_toggle.php" onsubmit="return confirm('<?= $t['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?> akun <?= htmlspecialchars(addslashes($t['nama'])) ?>?');" class="inline">
                  <input type="hidden" name="id" value="<?= (int)$t['id'] ?>"/>
                  <button type="submit" class="<?= $t['status'] === 'aktif' ? 'text-red-400 hover:text-red-300' : 'text-emerald-400 hover:text-emerald-300' ?> text-xs font-bold transition-colors">
                    <?= $t['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<!-- Modal Tambah/Edit Akun Admin -->
<div id="userModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);">
  <div class="glass-strong rounded-3xl p-8 w-full max-w-md fade-up">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-extrabold text-white" id="userModalTitle">Tambah Akun Admin</h3>
      <button type="button" onclick="closeUserModal()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/60 transition-all">✕</button>
    </div>
    <form method="POST" action="user_save.php" class="space-y-4">
      <input type="hidden" name="id" id="u_id" value=""/>
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
        <input type="text" name="nama" id="u_nama" required class="login-input" style="font-size:13px"/>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Username <span class="text-red-400">*</span></label>
          <input type="text" name="username" id="u_username" required class="login-input" style="font-size:13px"/>
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Role</label>
          <select name="role" id="u_role" class="login-input" style="font-size:13px">
            <option value="staff">Staff</option>
            <option value="admin">Admin</option>
            <option value="superadmin">Superadmin</option>
          </select>
        </div>
      </div>
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Email <span class="text-red-400">*</span></label>
        <input type="email" name="email" id="u_email" required class="login-input" style="font-size:13px"/>
      </div>
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Password <span id="u_pass_required" class="text-red-400">*</span></label>
        <input type="password" name="password" id="u_password" class="login-input" style="font-size:13px" placeholder="Min. 6 karakter"/>
        <p id="u_pass_hint" class="hidden text-[10px] text-white/30 mt-1">Kosongkan jika tidak ingin mengubah password.</p>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeUserModal()" class="flex-1 bg-white/5 border border-white/10 hover:bg-white/10 text-white/60 font-bold text-sm py-3 rounded-xl transition-all">Batal</button>
        <button type="submit" class="flex-1 login-btn py-3" style="font-size:12px;">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Tambah/Edit Akun Penyewa -->
<div id="tenantAccModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);">
  <div class="glass-strong rounded-3xl p-8 w-full max-w-md fade-up">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-extrabold text-white" id="tenantAccModalTitle">Tambah Akun Penyewa</h3>
      <button type="button" onclick="closeTenantAccModal()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/60 transition-all">✕</button>
    </div>
    <form method="POST" action="tenant_account_save.php" class="space-y-4">
      <input type="hidden" name="id" id="t_id" value=""/>
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
        <input type="text" name="nama" id="t_nama" required class="login-input" style="font-size:13px"/>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Email <span class="text-red-400">*</span></label>
          <input type="email" name="email" id="t_email" required class="login-input" style="font-size:13px"/>
        </div>
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Telepon</label>
          <input type="tel" name="telepon" id="t_telepon" class="login-input" style="font-size:13px"/>
        </div>
      </div>
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Username <span class="text-red-400">*</span></label>
        <input type="text" name="username" id="t_username" required class="login-input" style="font-size:13px"/>
      </div>
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1.5">Password <span id="t_pass_required" class="text-red-400">*</span></label>
        <input type="password" name="password" id="t_password" class="login-input" style="font-size:13px" placeholder="Min. 6 karakter"/>
        <p id="t_pass_hint" class="hidden text-[10px] text-white/30 mt-1">Kosongkan jika tidak ingin mengubah password.</p>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeTenantAccModal()" class="flex-1 bg-white/5 border border-white/10 hover:bg-white/10 text-white/60 font-bold text-sm py-3 rounded-xl transition-all">Batal</button>
        <button type="submit" class="flex-1 login-btn py-3" style="font-size:12px;">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function openUserModal(data) {
  const modal = document.getElementById('userModal');
  modal.querySelector('form').reset();
  document.getElementById('u_password').required = !data;
  document.getElementById('u_pass_required').classList.toggle('hidden', !!data);
  document.getElementById('u_pass_hint').classList.toggle('hidden', !data);
  if (data && data.id) {
    document.getElementById('userModalTitle').textContent = 'Edit Akun Admin';
    document.getElementById('u_id').value = data.id;
    document.getElementById('u_nama').value = data.nama || '';
    document.getElementById('u_username').value = data.username || '';
    document.getElementById('u_email').value = data.email || '';
    document.getElementById('u_role').value = data.role || 'staff';
  } else {
    document.getElementById('userModalTitle').textContent = 'Tambah Akun Admin';
    document.getElementById('u_id').value = '';
  }
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}
function closeUserModal() {
  const modal = document.getElementById('userModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

function openTenantAccModal(data) {
  const modal = document.getElementById('tenantAccModal');
  modal.querySelector('form').reset();
  document.getElementById('t_password').required = !data;
  document.getElementById('t_pass_required').classList.toggle('hidden', !!data);
  document.getElementById('t_pass_hint').classList.toggle('hidden', !data);
  if (data && data.id) {
    document.getElementById('tenantAccModalTitle').textContent = 'Edit Akun Penyewa';
    document.getElementById('t_id').value = data.id;
    document.getElementById('t_nama').value = data.nama || '';
    document.getElementById('t_email').value = data.email || '';
    document.getElementById('t_telepon').value = data.telepon || '';
    document.getElementById('t_username').value = data.username || '';
  } else {
    document.getElementById('tenantAccModalTitle').textContent = 'Tambah Akun Penyewa';
    document.getElementById('t_id').value = '';
  }
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}
function closeTenantAccModal() {
  const modal = document.getElementById('tenantAccModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}
</script>
