/* ===== THEME ENGINE ===== */
const ThemeEngine = {
  STORAGE_KEY: 'fs_theme',

  // Aktifkan tema: tambah/hapus class 'light' di body
  apply(theme) {
    document.body.classList.toggle('light', theme === 'light');
    localStorage.setItem(this.STORAGE_KEY, theme);
    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.classList.toggle('light-preload', theme === 'light');
    this._updateCharts(theme);
    this._updateToggleTitle(theme);
  },

  toggle() {
    const current = localStorage.getItem(this.STORAGE_KEY) || 'dark';
    this.apply(current === 'dark' ? 'light' : 'dark');
  },

  init() {
    const saved = localStorage.getItem(this.STORAGE_KEY) || 'dark';
    this.apply(saved);
  },

  // Rebuild Chart.js charts after theme switch (jika ada)
  _updateCharts(theme) {
    const isDark = theme === 'dark';
    const gridColor  = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(30,41,59,0.08)';
    const tickColor  = isDark ? 'rgba(255,255,255,0.4)'  : 'rgba(30,41,59,0.5)';
    if (typeof Chart !== 'undefined') {
      Chart.defaults.color = tickColor;
      Object.values(Chart.instances || {}).forEach(chart => {
        if (chart.options.scales) {
          ['x','y'].forEach(ax => {
            if (chart.options.scales[ax]) {
              chart.options.scales[ax].grid  = { color: gridColor };
              chart.options.scales[ax].ticks = { color: tickColor };
            }
          });
        }
        chart.update('none');
      });
    }
  },

  _updateToggleTitle(theme) {
    document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
      btn.title = theme === 'dark' ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap';
    });
  },
};

// Expose globally
window.toggleTheme = () => ThemeEngine.toggle();

/* ===== Navbar Dropdowns ===== */
function toggleNotif() {
  const box = document.getElementById('notifBox');
  const user = document.getElementById('userBox');
  if (user) user.classList.add('hidden');
  if (box) box.classList.toggle('hidden');
}
function toggleUser() {
  const box = document.getElementById('userBox');
  const notif = document.getElementById('notifBox');
  if (notif) notif.classList.add('hidden');
  if (box) box.classList.toggle('hidden');
}
document.addEventListener('click', (e) => {
  if (!e.target.closest('#notifToggle')) {
    const b = document.getElementById('notifBox');
    if (b) b.classList.add('hidden');
  }
  if (!e.target.closest('#userToggle')) {
    const b = document.getElementById('userBox');
    if (b) b.classList.add('hidden');
  }
});

/* ===== Mobile Sidebar Toggle ===== */
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  if (!sidebar) return;
  sidebar.classList.toggle('open');
  if (overlay) overlay.classList.toggle('show');
}

/* ===== Sidebar Kantin Submenu ===== */
function toggleKantin() {
  const sub   = document.getElementById('kantinSub');
  const arrow = document.getElementById('kantinArrow');
  if (!sub) return;
  sub.classList.toggle('open');
  if (arrow) arrow.classList.toggle('rotate-180');
}

/* ===== Toast Notification ===== */
function showToast(message, type = 'success') {
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const colors = {
    success: '#34d399',
    error:   '#f87171',
    info:    '#60a5fa',
    warn:    '#fbbf24',
  };
  const icons = {
    success: '✓',
    error:   '✕',
    info:    'ℹ',
    warn:    '⚠',
  };
  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.innerHTML = `
    <span style="color:${colors[type]};font-size:16px;font-weight:900;">${icons[type]}</span>
    <span>${message}</span>
  `;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 3500);
}

/* ===== Animated Counters ===== */
function animateCounters() {
  document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseInt(el.getAttribute('data-count'), 10);
    const duration = 1200;
    const start = performance.now();
    const initial = 0;
    function update(now) {
      const elapsed = now - start;
      const progress = Math.min(elapsed / duration, 1);
      const ease = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.round(initial + (target - initial) * ease).toLocaleString('id-ID');
      if (progress < 1) requestAnimationFrame(update);
    }
    requestAnimationFrame(update);
  });
}

/* ===== Search/Filter in Penyewa Table ===== */
function initSearch() {
  const input = document.getElementById('searchInput');
  if (!input) return;
  input.addEventListener('input', () => {
    const q = input.value.toLowerCase();
    document.querySelectorAll('.table-row').forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(q) ? '' : 'none';
    });
  });
}

/* ===== Filter Buttons ===== */
function filterTable(status) {
  document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
  const activeBtn = document.querySelector(`[data-filter="${status}"]`);
  if (activeBtn) activeBtn.classList.add('active');

  document.querySelectorAll('.table-row').forEach(row => {
    if (status === 'all') {
      row.style.display = '';
    } else {
      const rowStatus = row.getAttribute('data-status') || '';
      row.style.display = rowStatus.toLowerCase() === status.toLowerCase() ? '' : 'none';
    }
  });
}

/* ===== Unit Booking Modal ===== */
function openBooking(realId, kodeUnit, unitName, harga) {
  const modal = document.getElementById('bookingModal');
  if (!modal) return;
  document.getElementById('modalUnitId').textContent   = kodeUnit;
  document.getElementById('modalUnitName').textContent = unitName;
  document.getElementById('modalHarga').textContent    = harga;
  const idInput = document.getElementById('bookingUnitIdInput');
  if (idInput) idInput.value = realId;
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}
function closeBooking() {
  const modal = document.getElementById('bookingModal');
  if (!modal) return;
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

/* ===== Dashboard Chart (home page) ===== */
function initRevenueChart() {
  const ctx = document.getElementById('revenueChart');
  if (!ctx) return;
  const labels = ctx.dataset.labels ? JSON.parse(ctx.dataset.labels) : ['Jan','Feb','Mar','Apr','Mei','Jun'];
  const values = ctx.dataset.values ? JSON.parse(ctx.dataset.values) : [0,0,0,0,0,0];
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Tagihan (juta)',
        data: values,
        fill: true,
        backgroundColor: 'rgba(99,102,241,0.15)',
        borderColor: '#6366f1',
        borderWidth: 2.5,
        pointBackgroundColor: '#6366f1',
        pointRadius: 4,
        tension: 0.4,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: 'rgba(255,255,255,0.4)', font:{size:11} } },
        y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: 'rgba(255,255,255,0.4)', font:{size:11}, callback: v => v+'jt' } },
      },
    },
  });
}

function initOccupancyChart() {
  const ctx = document.getElementById('occupancyChart');
  if (!ctx) return;
  const values = ctx.dataset.values ? JSON.parse(ctx.dataset.values) : [0,0,0,0,0,0];
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Gudang Terisi','Gudang Kosong','Toko Terisi','Toko Kosong','Kantin Terisi','Kantin Kosong'],
      datasets: [{
        data: values,
        backgroundColor: ['#6366f1','rgba(99,102,241,0.2)','#f59e0b','rgba(245,158,11,0.2)','#10b981','rgba(16,185,129,0.2)'],
        borderWidth: 0,
        hoverOffset: 8,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%',
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(10,15,30,0.95)',
          borderColor: 'rgba(255,255,255,0.1)',
          borderWidth: 1,
          titleColor: '#fff',
          bodyColor: 'rgba(255,255,255,0.6)',
          padding: 12,
        },
      },
    },
  });
}

/* ===== Init on DOM ready ===== */
document.addEventListener('DOMContentLoaded', () => {
  ThemeEngine.init();
  animateCounters();
  initSearch();
  initRevenueChart();
  initOccupancyChart();

  // Progress bars animate in
  document.querySelectorAll('.progress-fill[data-width]').forEach(el => {
    const w = el.getAttrib
    ute('data-width');
    setTimeout(() => { el.style.width = w + '%'; }, 200);
  });
});
