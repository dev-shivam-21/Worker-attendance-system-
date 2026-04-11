<?php
/**
 * WorkPulse Pro — Attendance Management
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

requireLogin();

$companyId = currentCompanyId();
$action    = getStr('action');

/* ── POST Handlers ─────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = postStr('form_action');

    if ($postAction === 'mark') {
        $result = markAttendance(
            $companyId,
            (int) postStr('worker_id'),
            postStr('date'),
            postFloat('hours_worked')
        );
        setFlash($result['ok'] ? 'success' : 'error',
                 $result['ok'] ? 'Attendance marked successfully.' : $result['error']);
        header('Location: attendance.php');
        exit;
    }

    if ($postAction === 'edit') {
        $attId = (int) postStr('att_id');
        $hours = postFloat('hours_worked');
        updateAttendance($attId, $companyId, $hours);
        setFlash('success', 'Attendance updated.');
        header('Location: attendance.php');
        exit;
    }
}

/* ── GET: Delete ───────────────────────────────────── */
if ($action === 'delete' && getInt('id') > 0) {
    deleteAttendance(getInt('id'), $companyId);
    setFlash('success', 'Attendance record removed.');
    header('Location: attendance.php');
    exit;
}

/* ── Filters ───────────────────────────────────────── */
$filterDate   = getStr('date', date('Y-m-d'));
$filterWorker = getInt('worker_id');

$allWorkers  = getWorkers($companyId);
$attendance  = getAttendance($companyId, $filterDate, $filterWorker);

// Workers already marked today (to show in select)
$markedToday  = array_column(
    getAttendance($companyId, $filterDate),
    'worker_id'
);

$pageTitle = 'Attendance';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance — WorkPulse Pro</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .hours-slider { width: 100%; accent-color: var(--accent-blue); cursor: pointer; }
    .slider-val   { font-weight: 600; color: var(--accent-cyan); font-size: 0.9rem; }
  </style>
</head>
<body>
<div class="app-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="main-content">
    <?php include __DIR__ . '/header.php'; ?>

    <div class="page-body">
      <?php renderFlash(); ?>

      <!-- Mark Attendance -->
      <div class="card fade-up" style="margin-bottom:1.3rem">
        <div class="card-header">
          <h2 class="card-title">Mark Attendance</h2>
        </div>
        <div class="card-body">
          <?php if (empty($allWorkers)): ?>
            <p style="color:var(--text-muted);font-size:0.88rem">
              No workers found. <a href="workers.php?action=add" style="color:var(--accent-blue)">Add a worker first</a>.
            </p>
          <?php else: ?>
            <form method="POST" action="attendance.php">
              <input type="hidden" name="form_action" value="mark">
              <div class="form-row" style="align-items:end;gap:1rem;flex-wrap:wrap">
                <div class="form-group" style="min-width:180px">
                  <label>Worker</label>
                  <select name="worker_id" class="form-control" required>
                    <option value="">— Select Worker —</option>
                    <?php foreach ($allWorkers as $w): ?>
                      <?php $marked = in_array((int)$w['id'], $markedToday); ?>
                      <option value="<?= (int)$w['id'] ?>" <?= $marked ? 'disabled' : '' ?>>
                        <?= sanitize($w['name']) ?> (<?= sanitize($w['worker_id']) ?>)
                        <?= $marked ? ' ✓ Marked' : '' ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group" style="min-width:160px">
                  <label>Date</label>
                  <input type="date" name="date" class="form-control"
                         value="<?= date('Y-m-d') ?>"
                         max="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group" style="flex:1;min-width:200px">
                  <label>
                    Hours Worked — <span class="slider-val" id="slider-display">8.0 hrs</span>
                  </label>
                  <input type="range" name="hours_worked" class="hours-slider"
                         data-display="slider-display"
                         min="0.5" max="24" step="0.5" value="8">
                </div>
                <div class="form-group" style="flex:0">
                  <button type="submit" class="btn btn-primary" style="margin-top:1.55rem">
                    Mark ✓
                  </button>
                </div>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </div>

      <!-- Attendance Records -->
      <div class="card fade-up">
        <div class="card-header">
          <h2 class="card-title">Attendance Records</h2>
          <!-- Filter -->
          <form method="GET" action="attendance.php" class="month-filter">
            <input type="date" name="date" class="form-control"
                   style="width:auto"
                   value="<?= htmlspecialchars($filterDate, ENT_QUOTES, 'UTF-8') ?>">
            <select name="worker_id" class="form-control" style="width:auto">
              <option value="0">All Workers</option>
              <?php foreach ($allWorkers as $w): ?>
                <option value="<?= (int)$w['id'] ?>" <?= $filterWorker === (int)$w['id'] ? 'selected' : '' ?>>
                  <?= sanitize($w['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
          </form>
        </div>

        <?php if (empty($attendance)): ?>
          <div class="empty-state">
            <div class="empty-icon">📅</div>
            <p>No attendance records for the selected filters.</p>
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Worker</th>
                  <th>ID</th>
                  <th>Role</th>
                  <th>Date</th>
                  <th>Hours</th>
                  <th>Daily Earning</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($attendance as $row): ?>
                  <tr>
                    <td><span class="td-main"><?= sanitize($row['worker_name']) ?></span></td>
                    <td><code style="font-size:0.8rem;color:var(--accent-cyan)"><?= sanitize($row['wid']) ?></code></td>
                    <td><span class="badge badge-violet"><?= sanitize($row['role']) ?></span></td>
                    <td><?= htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge badge-amber"><?= number_format((float)$row['hours_worked'], 1) ?> hrs</span></td>
                    <td class="salary-total">
                      <?= formatInr((float)$row['hours_worked'] * (float)$row['hourly_rate']) ?>
                    </td>
                    <td>
                      <div class="td-actions">
                        <button class="btn btn-secondary btn-sm btn-icon"
                                title="Edit Hours"
                                onclick="editAttendance(<?= (int)$row['id'] ?>, <?= (float)$row['hours_worked'] ?>)">✏️</button>
                        <button class="btn btn-danger btn-sm btn-icon"
                                title="Delete"
                                onclick="confirmDelete('attendance.php?action=delete&id=<?= (int)$row['id'] ?>',
                                '<?= addslashes(sanitize($row['worker_name'])) ?> on <?= htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8') ?>')">🗑</button>
                      </div>
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

<!-- Edit Attendance Modal -->
<div class="modal-overlay" id="att-edit-modal">
  <div class="modal">
    <button class="modal-close" onclick="closeModal('att-edit-modal')">✕</button>
    <div class="modal-title">✏️ Update Hours Worked</div>
    <form method="POST" action="attendance.php">
      <input type="hidden" name="form_action" value="edit">
      <input type="hidden" name="att_id" id="att-edit-id">
      <div class="form-group">
        <label>
          Hours Worked — <span class="slider-val" id="edit-slider-display">8.0 hrs</span>
        </label>
        <input type="range" name="hours_worked" id="att-edit-hours" class="hours-slider"
               data-display="edit-slider-display"
               min="0.5" max="24" step="0.5" value="8">
      </div>
      <button type="submit" class="btn btn-primary btn-full">Update</button>
    </form>
  </div>
</div>

<script src="script.js"></script>
</body>
</html>
