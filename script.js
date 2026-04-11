/**
 * WorkPulse Pro — script.js
 * Real-time clock, logout modal, sidebar toggle, and helpers.
 */

/* ── Live Clock ──────────────────────────────────── */
(function initClock() {
  const timeEl = document.getElementById('live-time');
  const dateEl = document.getElementById('live-date');
  if (!timeEl) return;

  const weekdays = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
  const months   = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

  function tick() {
    const now  = new Date();
    const hh   = String(now.getHours()).padStart(2, '0');
    const mm   = String(now.getMinutes()).padStart(2, '0');
    const ss   = String(now.getSeconds()).padStart(2, '0');
    timeEl.textContent = `${hh}:${mm}:${ss}`;

    if (dateEl) {
      dateEl.textContent = `${weekdays[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
    }
  }

  tick();
  setInterval(tick, 1000);
})();

/* ── Logout Modal ────────────────────────────────── */
(function initLogout() {
  const trigger  = document.getElementById('logout-trigger');
  const overlay  = document.getElementById('logout-overlay');
  const cancelBtn = document.getElementById('logout-cancel');
  const confirmBtn = document.getElementById('logout-confirm');
  if (!trigger || !overlay) return;

  function openModal() {
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    overlay.classList.remove('open');
    document.body.style.overflow = '';
  }

  trigger.addEventListener('click', function (e) {
    e.preventDefault();
    openModal();
  });

  if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

  overlay.addEventListener('click', function (e) {
    if (e.target === overlay) closeModal();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeModal();
  });
})();

/* ── Generic Modal ───────────────────────────────── */
function openModal(id) {
  const el = document.getElementById(id);
  if (el) {
    el.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (el) {
    el.classList.remove('open');
    document.body.style.overflow = '';
  }
}

// Close any modal on overlay click
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('modal-overlay') && !e.target.id.startsWith('logout')) {
    e.target.classList.remove('open');
    document.body.style.overflow = '';
  }
});

/* ── Sidebar Mobile Toggle ───────────────────────── */
(function initSidebar() {
  const toggle   = document.getElementById('menu-toggle');
  const sidebar  = document.getElementById('sidebar');
  const overlay  = document.getElementById('sidebar-overlay');
  if (!toggle || !sidebar) return;

  function openSidebar() {
    sidebar.classList.add('mobile-open');
    if (overlay) overlay.classList.add('open');
  }

  function closeSidebar() {
    sidebar.classList.remove('mobile-open');
    if (overlay) overlay.classList.remove('open');
  }

  toggle.addEventListener('click', openSidebar);
  if (overlay) overlay.addEventListener('click', closeSidebar);
})();

/* ── Edit Worker Modal Population ───────────────── */
function editWorker(id, name, workerId, role, rate) {
  document.getElementById('edit-id').value          = id;
  document.getElementById('edit-name').value        = name;
  document.getElementById('edit-worker-id').value   = workerId;
  document.getElementById('edit-role').value        = role;
  document.getElementById('edit-hourly-rate').value = rate;
  openModal('edit-modal');
}

/* ── Edit Attendance Modal Population ───────────── */
function editAttendance(id, hours) {
  document.getElementById('att-edit-id').value    = id;
  document.getElementById('att-edit-hours').value = hours;
  openModal('att-edit-modal');
}

/* ── Hours Slider ────────────────────────────────── */
(function initSliders() {
  document.querySelectorAll('.hours-slider').forEach(function (slider) {
    const display = document.getElementById(slider.dataset.display);
    if (display) {
      display.textContent = parseFloat(slider.value).toFixed(1) + ' hrs';
      slider.addEventListener('input', function () {
        display.textContent = parseFloat(slider.value).toFixed(1) + ' hrs';
      });
    }
  });
})();

/* ── Flash auto-dismiss ──────────────────────────── */
(function initFlash() {
  const flash = document.querySelector('.flash');
  if (flash) {
    setTimeout(function () {
      flash.style.transition = 'opacity 0.5s ease';
      flash.style.opacity = '0';
      setTimeout(function () { flash.remove(); }, 500);
    }, 4000);
  }
})();

/* ── Confirm Delete ──────────────────────────────── */
function confirmDelete(url, name) {
  if (confirm('Delete "' + name + '"? This action cannot be undone.')) {
    window.location.href = url;
  }
}
