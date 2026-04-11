<?php
/**
 * WorkPulse Pro — Sidebar
 * Included inside every authenticated page.
 */
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/config.php';
}
?>

<div class="sidebar-overlay" id="sidebar-overlay"></div>

<aside class="sidebar" id="sidebar">
  <!-- Brand -->
  <div class="sidebar-brand">
    <div class="brand-wrap">
      <div class="brand-icon">⚡</div>
      <div>
        <div class="brand-name">Work<span>Pulse</span> Pro</div>
        <div class="brand-company" title="<?= currentCompanyName() ?>">
          <?= currentCompanyName() ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="sidebar-nav">
    <div class="nav-label">Main</div>

    <a href="dashboard.php" class="nav-item <?= activePage('dashboard.php') ?>">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <rect x="3" y="3" width="7" height="7" rx="1.5"/>
        <rect x="14" y="3" width="7" height="7" rx="1.5"/>
        <rect x="3" y="14" width="7" height="7" rx="1.5"/>
        <rect x="14" y="14" width="7" height="7" rx="1.5"/>
      </svg>
      Dashboard
    </a>

    <a href="workers.php" class="nav-item <?= activePage('workers.php') ?>">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <circle cx="9" cy="7" r="3"/>
        <path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        <path d="M21 21v-2a4 4 0 0 0-3-3.87"/>
      </svg>
      Workers
    </a>

    <a href="attendance.php" class="nav-item <?= activePage('attendance.php') ?>">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8"  y1="2" x2="8"  y2="6"/>
        <line x1="3"  y1="10" x2="21" y2="10"/>
        <path d="M9 16l2 2 4-4"/>
      </svg>
      Attendance
    </a>

    <div class="nav-label" style="margin-top:0.5rem">Finance</div>

    <a href="salary.php" class="nav-item <?= activePage('salary.php') ?>">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <line x1="12" y1="1" x2="12" y2="23"/>
        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
      </svg>
      Salary
    </a>

    <a href="export.php" class="nav-item <?= activePage('export.php') ?>">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="7 10 12 15 17 10"/>
        <line x1="12" y1="15" x2="12" y2="3"/>
      </svg>
      Export
    </a>

    <div class="divider"></div>

    <a href="#" class="nav-item" id="logout-trigger">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
        <polyline points="16 17 21 12 16 7"/>
        <line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
      Logout
    </a>
  </nav>

  <!-- Footer -->
  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar">
        <?= strtoupper(substr(currentOwnerName(), 0, 1)) ?>
      </div>
      <div class="user-info">
        <div class="user-name"><?= currentOwnerName() ?></div>
        <div class="user-role">Admin</div>
      </div>
    </div>
  </div>
</aside>

<!-- Logout Confirm Modal -->
<div class="modal-overlay" id="logout-overlay">
  <div class="modal logout-modal">
    <div class="modal-icon">🚪</div>
    <div class="modal-title">Logging out?</div>
    <p>You'll need to sign back in to access your WorkPulse Pro dashboard.</p>
    <div class="btn-row">
      <button class="btn btn-secondary" id="logout-cancel">Stay Logged In</button>
      <a href="logout.php" class="btn btn-danger" id="logout-confirm">Yes, Logout</a>
    </div>
  </div>
</div>
