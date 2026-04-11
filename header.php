<?php
/**
 * WorkPulse Pro — Top Header
 * $pageTitle must be set before including this file.
 */
$pageTitle = $pageTitle ?? 'Dashboard';
?>
<header class="topbar">
  <div class="topbar-left">
    <button class="menu-toggle" id="menu-toggle" aria-label="Toggle menu">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="3" y1="6"  x2="21" y2="6"/>
        <line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="18" x2="21" y2="18"/>
      </svg>
    </button>
    <h1 class="page-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
  </div>
  <div class="topbar-right">
    <div class="clock-widget">
      <span id="live-time">--:--:--</span>
      <span id="live-date"></span>
    </div>
    <span class="info-chip">
      <?= currentCompanyName() ?>
    </span>
  </div>
</header>
