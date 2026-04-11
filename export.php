<?php
/**
 * WorkPulse Pro — Export Reports
 * Handles both the UI page and the actual CSV download.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

requireLogin();

$companyId = currentCompanyId();
$doExport  = getStr('export');
$month     = getStr('month', date('Y-m'));

/* ── Trigger CSV Download ──────────────────────────── */
if ($doExport === 'csv') {
    $salaryData = getMonthlySalary($companyId, $month);

    $filename = sprintf(
        'workpulse-salary-%s-%s.csv',
        preg_replace('/[^a-z0-9]/i', '-', currentCompanyName()),
        $month
    );

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');

    // BOM for Excel UTF-8 compatibility
    fputs($out, "\xEF\xBB\xBF");

    // Report Header
    fputcsv($out, ['WorkPulse Pro — Salary Report']);
    fputcsv($out, ['Company', currentCompanyName()]);
    fputcsv($out, ['Month', $month]);
    fputcsv($out, ['Generated', date('Y-m-d H:i:s')]);
    fputcsv($out, []);

    // Column Headers
    fputcsv($out, [
        '#',
        'Worker Name',
        'Worker ID',
        'Role',
        'Hourly Rate (INR)',
        'Days Worked',
        'Total Hours',
        'Total Salary (INR)',
    ]);

    $grandTotal = 0;
    $grandHours = 0;
    $i          = 0;

    foreach ($salaryData as $row) {
        $i++;
        $salary      = (float) ($row['total_salary'] ?? 0);
        $hours       = (float) ($row['total_hours']  ?? 0);
        $grandTotal += $salary;
        $grandHours += $hours;

        fputcsv($out, [
            $i,
            $row['name'],
            $row['wid'],
            $row['role'],
            number_format((float)$row['hourly_rate'], 2),
            (int) ($row['days_worked'] ?? 0),
            number_format($hours, 2),
            number_format($salary, 2),
        ]);
    }

    fputcsv($out, []);
    fputcsv($out, [
        '', '', '', '', '',
        'Grand Total',
        number_format($grandHours, 2),
        number_format($grandTotal, 2),
    ]);

    fclose($out);
    exit;
}

/* ── Attendance CSV ────────────────────────────────── */
if ($doExport === 'attendance') {
    $fromDate = getStr('from', date('Y-m-01'));
    $toDate   = getStr('to',   date('Y-m-d'));

    // Custom range query
    $pdo   = getPDO();
    $stmt  = $pdo->prepare('
        SELECT a.date, w.name, w.worker_id AS wid, w.role, w.hourly_rate,
               a.hours_worked,
               (a.hours_worked * w.hourly_rate) AS daily_salary
        FROM attendance a
        JOIN workers w ON w.id = a.worker_id
        WHERE a.company_id = ?
          AND a.date BETWEEN ? AND ?
        ORDER BY a.date DESC, w.name ASC
    ');
    $stmt->execute([$companyId, $fromDate, $toDate]);
    $rows = $stmt->fetchAll();

    $filename = sprintf('workpulse-attendance-%s-%s-to-%s.csv',
        preg_replace('/[^a-z0-9]/i', '-', currentCompanyName()),
        $fromDate, $toDate);

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');

    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF");

    fputcsv($out, ['WorkPulse Pro — Attendance Report']);
    fputcsv($out, ['Company', currentCompanyName()]);
    fputcsv($out, ['Period', "$fromDate to $toDate"]);
    fputcsv($out, ['Generated', date('Y-m-d H:i:s')]);
    fputcsv($out, []);

    fputcsv($out, ['Date','Worker Name','Worker ID','Role','Hours Worked','Hourly Rate (INR)','Daily Salary (INR)']);

    foreach ($rows as $r) {
        fputcsv($out, [
            $r['date'],
            $r['name'],
            $r['wid'],
            $r['role'],
            number_format((float)$r['hours_worked'], 2),
            number_format((float)$r['hourly_rate'], 2),
            number_format((float)$r['daily_salary'], 2),
        ]);
    }

    fclose($out);
    exit;
}

/* ── UI Page ───────────────────────────────────────── */
$pageTitle = 'Export';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Export — WorkPulse Pro</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="main-content">
    <?php include __DIR__ . '/header.php'; ?>

    <div class="page-body">
      <?php renderFlash(); ?>

      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.2rem">

        <!-- Salary Export -->
        <div class="card fade-up">
          <div class="card-header">
            <h2 class="card-title">📊 Monthly Salary Report</h2>
          </div>
          <div class="card-body">
            <p style="color:var(--text-secondary);font-size:0.88rem;margin-bottom:1.2rem">
              Export a full salary breakdown for any month — includes worker details, hours worked, hourly rate, and total payout.
            </p>
            <form method="GET" action="export.php">
              <input type="hidden" name="export" value="csv">
              <div class="form-group">
                <label>Select Month</label>
                <input type="month" name="month" class="form-control"
                       value="<?= htmlspecialchars($month, ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <button type="submit" class="btn btn-success btn-full">
                ⬇ Download Salary CSV
              </button>
            </form>
          </div>
        </div>

        <!-- Attendance Export -->
        <div class="card fade-up delay-1">
          <div class="card-header">
            <h2 class="card-title">📋 Attendance Report</h2>
          </div>
          <div class="card-body">
            <p style="color:var(--text-secondary);font-size:0.88rem;margin-bottom:1.2rem">
              Export attendance records for a custom date range — includes daily hours and per-day earning calculations.
            </p>
            <form method="GET" action="export.php">
              <input type="hidden" name="export" value="attendance">
              <div class="form-row">
                <div class="form-group">
                  <label>From Date</label>
                  <input type="date" name="from" class="form-control"
                         value="<?= date('Y-m-01') ?>" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                  <label>To Date</label>
                  <input type="date" name="to" class="form-control"
                         value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
                </div>
              </div>
              <button type="submit" class="btn btn-success btn-full">
                ⬇ Download Attendance CSV
              </button>
            </form>
          </div>
        </div>

      </div>

      <!-- Info Box -->
      <div class="card fade-up delay-2" style="margin-top:1.2rem">
        <div class="card-body">
          <p style="color:var(--text-muted);font-size:0.83rem;display:flex;gap:0.5rem;align-items:flex-start">
            <span>ℹ️</span>
            <span>
              All exported CSV files are scoped to <strong style="color:var(--text-secondary)"><?= currentCompanyName() ?></strong> only.
              Files include a UTF-8 BOM for seamless compatibility with Microsoft Excel.
            </span>
          </p>
        </div>
      </div>

    </div><!-- /page-body -->
  </div><!-- /main-content -->
</div>
<script src="script.js"></script>
</body>
</html>
