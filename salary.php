<?php
/**
 * WorkPulse Pro — Salary Overview
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

requireLogin();

$companyId    = currentCompanyId();
$filterMonth  = getStr('month', date('Y-m'));
$filterWorker = getInt('worker_id');

$allWorkers = getWorkers($companyId);
$salaryData = getMonthlySalary($companyId, $filterMonth);

// Apply worker filter in PHP (already grouped by worker)
if ($filterWorker > 0) {
    $salaryData = array_filter($salaryData, fn($r) => (int)$r['worker_id'] === $filterWorker);
}

$totalSalary = array_sum(array_column($salaryData, 'total_salary'));
$totalHours  = array_sum(array_column($salaryData, 'total_hours'));

$pageTitle = 'Salary';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Salary — WorkPulse Pro</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="main-content">
    <?php include __DIR__ . '/header.php'; ?>

    <div class="page-body">
      <?php renderFlash(); ?>

      <!-- Summary Cards -->
      <div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr))">
        <div class="stat-card fade-up">
          <div class="stat-icon emerald">💰</div>
          <div class="stat-value" style="font-size:1.4rem"><?= formatInr($totalSalary) ?></div>
          <div class="stat-label">Total Payout — <?= htmlspecialchars($filterMonth, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="stat-card fade-up delay-1">
          <div class="stat-icon cyan">⏱️</div>
          <div class="stat-value"><?= number_format($totalHours, 1) ?>h</div>
          <div class="stat-label">Total Hours Logged</div>
        </div>
        <div class="stat-card fade-up delay-2">
          <div class="stat-icon blue">👷</div>
          <div class="stat-value"><?= count($salaryData) ?></div>
          <div class="stat-label">Workers in Period</div>
        </div>
      </div>

      <!-- Salary Table -->
      <div class="card fade-up">
        <div class="card-header">
          <h2 class="card-title">Monthly Salary Statement</h2>
          <!-- Filters -->
          <form method="GET" action="salary.php" class="month-filter">
            <input type="month" name="month" class="form-control"
                   style="width:auto"
                   value="<?= htmlspecialchars($filterMonth, ENT_QUOTES, 'UTF-8') ?>">
            <select name="worker_id" class="form-control" style="width:auto">
              <option value="0">All Workers</option>
              <?php foreach ($allWorkers as $w): ?>
                <option value="<?= (int)$w['id'] ?>" <?= $filterWorker === (int)$w['id'] ? 'selected' : '' ?>>
                  <?= sanitize($w['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
            <a href="export.php?month=<?= urlencode($filterMonth) ?>" class="btn btn-success btn-sm">⬇ Export</a>
          </form>
        </div>

        <?php if (empty($salaryData)): ?>
          <div class="empty-state">
            <div class="empty-icon">💸</div>
            <p>No salary data available for <?= htmlspecialchars($filterMonth, ENT_QUOTES, 'UTF-8') ?>.</p>
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Worker</th>
                  <th>ID</th>
                  <th>Role</th>
                  <th>Hourly Rate</th>
                  <th>Days Worked</th>
                  <th>Total Hours</th>
                  <th>Total Salary</th>
                </tr>
              </thead>
              <tbody>
                <?php $i = 0; foreach ($salaryData as $row): $i++; ?>
                  <tr>
                    <td><?= $i ?></td>
                    <td><span class="td-main"><?= sanitize($row['name']) ?></span></td>
                    <td><code style="font-size:0.8rem;color:var(--accent-cyan)"><?= sanitize($row['wid']) ?></code></td>
                    <td><span class="badge badge-violet"><?= sanitize($row['role']) ?></span></td>
                    <td><?= formatInr((float)$row['hourly_rate']) ?>/hr</td>
                    <td>
                      <?php if ((int)$row['days_worked'] > 0): ?>
                        <span class="badge badge-emerald"><?= (int)$row['days_worked'] ?> days</span>
                      <?php else: ?>
                        <span class="badge" style="background:rgba(255,255,255,0.05);color:var(--text-muted)">0 days</span>
                      <?php endif; ?>
                    </td>
                    <td><?= $row['total_hours'] !== null ? number_format((float)$row['total_hours'], 1) . ' hrs' : '—' ?></td>
                    <td class="salary-total">
                      <?= $row['total_salary'] !== null ? formatInr((float)$row['total_salary']) : formatInr(0) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <!-- Total Row -->
                <tr class="salary-total-row">
                  <td colspan="6" style="text-align:right;color:var(--text-secondary);font-size:0.82rem;letter-spacing:.04em;text-transform:uppercase">
                    Grand Total
                  </td>
                  <td><strong><?= number_format($totalHours, 1) ?> hrs</strong></td>
                  <td class="salary-total" style="font-size:1.05rem"><?= formatInr($totalSalary) ?></td>
                </tr>
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
