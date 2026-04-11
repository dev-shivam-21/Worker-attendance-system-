<?php
/**
 * WorkPulse Pro — Dashboard
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

requireLogin();

$stats     = getDashboardStats(currentCompanyId());
$pageTitle = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — WorkPulse Pro</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="main-content">
    <?php include __DIR__ . '/header.php'; ?>

    <div class="page-body">
      <?php renderFlash(); ?>

      <!-- Stat Cards -->
      <div class="stat-grid">

        <div class="stat-card fade-up delay-1">
          <div class="stat-icon blue">👷</div>
          <div class="stat-value"><?= $stats['total_workers'] ?></div>
          <div class="stat-label">Total Workers</div>
        </div>

        <div class="stat-card fade-up delay-2">
          <div class="stat-icon violet">✅</div>
          <div class="stat-value"><?= $stats['today_attendance'] ?></div>
          <div class="stat-label">Present Today</div>
        </div>

        <div class="stat-card fade-up delay-3">
          <div class="stat-icon cyan">⏱️</div>
          <div class="stat-value"><?= number_format($stats['today_hours'], 1) ?>h</div>
          <div class="stat-label">Total Hours Today</div>
        </div>

        <div class="stat-card fade-up delay-4">
          <div class="stat-icon emerald">💰</div>
          <div class="stat-value" style="font-size:1.4rem">
            <?= formatInr($stats['monthly_expense']) ?>
          </div>
          <div class="stat-label">Monthly Salary Expense</div>
        </div>

      </div>

      <!-- Quick Actions -->
      <div class="card fade-up">
        <div class="card-header">
          <h2 class="card-title">Quick Actions</h2>
        </div>
        <div class="card-body" style="display:flex;gap:0.75rem;flex-wrap:wrap;">
          <a href="workers.php?action=add" class="btn btn-primary">
            + Add Worker
          </a>
          <a href="attendance.php" class="btn btn-secondary">
            📋 Mark Attendance
          </a>
          <a href="salary.php" class="btn btn-secondary">
            💸 View Salary
          </a>
          <a href="export.php" class="btn btn-success">
            ⬇ Export Report
          </a>
        </div>
      </div>

      <!-- Today's Attendance Preview -->
      <?php
        $todayRows = getAttendance(currentCompanyId(), date('Y-m-d'));
      ?>
      <div class="card fade-up" style="margin-top:1.3rem">
        <div class="card-header">
          <h2 class="card-title">Today's Attendance</h2>
          <a href="attendance.php" class="btn btn-secondary btn-sm">View All →</a>
        </div>
        <?php if (empty($todayRows)): ?>
          <div class="empty-state">
            <div class="empty-icon">📭</div>
            <p>No attendance marked for today yet.</p>
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Worker</th>
                  <th>ID</th>
                  <th>Role</th>
                  <th>Hours</th>
                  <th>Earnings</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($todayRows as $row): ?>
                  <tr>
                    <td><span class="td-main"><?= sanitize($row['worker_name']) ?></span></td>
                    <td><?= sanitize($row['wid']) ?></td>
                    <td><span class="badge badge-blue"><?= sanitize($row['role']) ?></span></td>
                    <td><?= number_format((float)$row['hours_worked'], 1) ?> hrs</td>
                    <td class="salary-total">
                      <?= formatInr((float)$row['hours_worked'] * (float)$row['hourly_rate']) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div><!-- /page-body -->
  </div><!-- /main-content -->
</div>
<script src="script.js"></script>
</body>
</html>
