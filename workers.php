<?php
/**
 * WorkPulse Pro — Worker Management
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

requireLogin();

$companyId = currentCompanyId();
$action    = getStr('action');
$search    = getStr('search');

/* ── POST Handlers ─────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = postStr('form_action');

    if ($postAction === 'add') {
        $result = addWorker(
            $companyId,
            postStr('name'),
            postStr('worker_id'),
            postStr('role'),
            postFloat('hourly_rate')
        );
        setFlash($result['ok'] ? 'success' : 'error',
                 $result['ok'] ? 'Worker added successfully.' : $result['error']);
        header('Location: workers.php');
        exit;
    }

    if ($postAction === 'edit') {
        $result = updateWorker(
            getInt('id') ?: (int) postStr('edit_id'),
            $companyId,
            postStr('name'),
            postStr('worker_id'),
            postStr('role'),
            postFloat('hourly_rate')
        );
        setFlash($result['ok'] ? 'success' : 'error',
                 $result['ok'] ? 'Worker updated successfully.' : $result['error']);
        header('Location: workers.php');
        exit;
    }
}

/* ── GET: Delete ───────────────────────────────────── */
if ($action === 'delete' && getInt('id') > 0) {
    deleteWorker(getInt('id'), $companyId);
    setFlash('success', 'Worker removed successfully.');
    header('Location: workers.php');
    exit;
}

$workers   = getWorkers($companyId, $search);
$pageTitle = 'Workers';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Workers — WorkPulse Pro</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="main-content">
    <?php include __DIR__ . '/header.php'; ?>

    <div class="page-body">
      <?php renderFlash(); ?>

      <div class="card fade-up">
        <div class="card-header">
          <h2 class="card-title">All Workers
            <span class="badge badge-blue" style="margin-left:0.5rem"><?= count($workers) ?></span>
          </h2>
          <div style="display:flex;gap:0.6rem;flex-wrap:wrap;align-items:center;">
            <!-- Search -->
            <form method="GET" action="workers.php" class="search-wrap">
              <div class="search-input-wrap">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" class="form-control"
                       placeholder="Search workers…"
                       value="<?= sanitize($search) ?>">
              </div>
              <button type="submit" class="btn btn-secondary btn-sm">Search</button>
              <?php if ($search): ?>
                <a href="workers.php" class="btn btn-secondary btn-sm">✕ Clear</a>
              <?php endif; ?>
            </form>
            <button class="btn btn-primary btn-sm" onclick="openModal('add-modal')">+ Add Worker</button>
          </div>
        </div>

        <?php if (empty($workers)): ?>
          <div class="empty-state">
            <div class="empty-icon">👷</div>
            <p><?= $search ? 'No workers match your search.' : 'No workers yet. Add your first worker!' ?></p>
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Worker ID</th>
                  <th>Role</th>
                  <th>Hourly Rate</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($workers as $i => $w): ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td><span class="td-main"><?= sanitize($w['name']) ?></span></td>
                    <td><code style="font-size:0.8rem;color:var(--accent-cyan)"><?= sanitize($w['worker_id']) ?></code></td>
                    <td><span class="badge badge-violet"><?= sanitize($w['role']) ?></span></td>
                    <td class="salary-total"><?= formatInr((float)$w['hourly_rate']) ?>/hr</td>
                    <td>
                      <div class="td-actions">
                        <button class="btn btn-secondary btn-sm btn-icon"
                                title="Edit"
                                onclick="editWorker(
                                  <?= (int)$w['id'] ?>,
                                  '<?= addslashes(sanitize($w['name'])) ?>',
                                  '<?= addslashes(sanitize($w['worker_id'])) ?>',
                                  '<?= addslashes(sanitize($w['role'])) ?>',
                                  '<?= (float)$w['hourly_rate'] ?>'
                                )">✏️</button>
                        <button class="btn btn-danger btn-sm btn-icon"
                                title="Delete"
                                onclick="confirmDelete('workers.php?action=delete&id=<?= (int)$w['id'] ?>',
                                '<?= addslashes(sanitize($w['name'])) ?>')">🗑</button>
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

<!-- Add Worker Modal -->
<div class="modal-overlay" id="add-modal">
  <div class="modal">
    <button class="modal-close" onclick="closeModal('add-modal')">✕</button>
    <div class="modal-title">➕ Add New Worker</div>
    <form method="POST" action="workers.php">
      <input type="hidden" name="form_action" value="add">
      <div class="form-row">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="name" class="form-control" placeholder="Ramesh Kumar" required>
        </div>
        <div class="form-group">
          <label>Worker ID</label>
          <input type="text" name="worker_id" class="form-control" placeholder="WRK-001" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Role / Designation</label>
          <input type="text" name="role" class="form-control" placeholder="Machine Operator" required>
        </div>
        <div class="form-group">
          <label>Hourly Rate (₹)</label>
          <input type="number" name="hourly_rate" class="form-control"
                 placeholder="120" min="0" step="0.01" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full">Add Worker</button>
    </form>
  </div>
</div>

<!-- Edit Worker Modal -->
<div class="modal-overlay" id="edit-modal">
  <div class="modal">
    <button class="modal-close" onclick="closeModal('edit-modal')">✕</button>
    <div class="modal-title">✏️ Edit Worker</div>
    <form method="POST" action="workers.php">
      <input type="hidden" name="form_action" value="edit">
      <input type="hidden" name="edit_id" id="edit-id">
      <div class="form-row">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="name" id="edit-name" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Worker ID</label>
          <input type="text" name="worker_id" id="edit-worker-id" class="form-control" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Role / Designation</label>
          <input type="text" name="role" id="edit-role" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Hourly Rate (₹)</label>
          <input type="number" name="hourly_rate" id="edit-hourly-rate" class="form-control"
                 min="0" step="0.01" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full">Save Changes</button>
    </form>
  </div>
</div>

<script src="script.js"></script>
<?php if (getStr('action') === 'add'): ?>
<script>openModal('add-modal');</script>
<?php endif; ?>
</body>
</html>
