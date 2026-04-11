<?php
/**
 * WorkPulse Pro — Reusable Functions
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

/* ══════════════════════════════════════════════════════
   AUTH HELPERS
   ══════════════════════════════════════════════════════ */

function requireLogin(): void
{
    if (empty($_SESSION['user_id']) || empty($_SESSION['company_id'])) {
        header('Location: index.php');
        exit;
    }
}

function currentCompanyId(): int
{
    return (int) ($_SESSION['company_id'] ?? 0);
}

function currentUserId(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function currentCompanyName(): string
{
    return htmlspecialchars($_SESSION['company_name'] ?? '', ENT_QUOTES, 'UTF-8');
}

function currentOwnerName(): string
{
    return htmlspecialchars($_SESSION['owner_name'] ?? '', ENT_QUOTES, 'UTF-8');
}

/* ══════════════════════════════════════════════════════
   INPUT SANITISATION
   ══════════════════════════════════════════════════════ */

function sanitize(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function postStr(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

function postFloat(string $key, float $default = 0.0): float
{
    return isset($_POST[$key]) ? (float) $_POST[$key] : $default;
}

function getStr(string $key, string $default = ''): string
{
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

function getInt(string $key, int $default = 0): int
{
    return isset($_GET[$key]) ? (int) $_GET[$key] : $default;
}

/* ══════════════════════════════════════════════════════
   FLASH MESSAGES
   ══════════════════════════════════════════════════════ */

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function renderFlash(): void
{
    $flash = getFlash();
    if (!$flash) return;

    $type    = $flash['type'] === 'success' ? 'flash-success' : 'flash-error';
    $message = htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8');
    echo "<div class=\"flash {$type}\">{$message}</div>";
}

/* ══════════════════════════════════════════════════════
   COMPANY / AUTH
   ══════════════════════════════════════════════════════ */

function registerCompany(string $companyName, string $ownerName, string $email, string $password): array
{
    $pdo = getPDO();

    $stmt = $pdo->prepare('SELECT id FROM companies WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['ok' => false, 'error' => 'An account with this email already exists.'];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $pdo->prepare(
        'INSERT INTO companies (company_name, owner_name, email, password) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$companyName, $ownerName, $email, $hash]);

    return ['ok' => true];
}

function loginUser(string $email, string $password): array
{
    $pdo  = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM companies WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $company = $stmt->fetch();

    if (!$company || !password_verify($password, $company['password'])) {
        return ['ok' => false, 'error' => 'Invalid email or password.'];
    }

    $_SESSION['user_id']      = $company['id'];
    $_SESSION['company_id']   = $company['id'];
    $_SESSION['company_name'] = $company['company_name'];
    $_SESSION['owner_name']   = $company['owner_name'];

    return ['ok' => true];
}

/* ══════════════════════════════════════════════════════
   WORKERS
   ══════════════════════════════════════════════════════ */

function getWorkers(int $companyId, string $search = ''): array
{
    $pdo = getPDO();
    if ($search !== '') {
        $stmt = $pdo->prepare(
            'SELECT * FROM workers
             WHERE company_id = ?
               AND (name LIKE ? OR worker_id LIKE ? OR role LIKE ?)
             ORDER BY name ASC'
        );
        $like = "%{$search}%";
        $stmt->execute([$companyId, $like, $like, $like]);
    } else {
        $stmt = $pdo->prepare('SELECT * FROM workers WHERE company_id = ? ORDER BY name ASC');
        $stmt->execute([$companyId]);
    }
    return $stmt->fetchAll();
}

function getWorkerById(int $id, int $companyId): ?array
{
    $pdo  = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM workers WHERE id = ? AND company_id = ? LIMIT 1');
    $stmt->execute([$id, $companyId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function addWorker(int $companyId, string $name, string $workerId, string $role, float $hourlyRate): array
{
    $pdo  = getPDO();
    $stmt = $pdo->prepare(
        'SELECT id FROM workers WHERE company_id = ? AND worker_id = ? LIMIT 1'
    );
    $stmt->execute([$companyId, $workerId]);
    if ($stmt->fetch()) {
        return ['ok' => false, 'error' => 'Worker ID already exists in your company.'];
    }

    $stmt = $pdo->prepare(
        'INSERT INTO workers (company_id, name, worker_id, role, hourly_rate) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$companyId, $name, $workerId, $role, $hourlyRate]);
    return ['ok' => true];
}

function updateWorker(int $id, int $companyId, string $name, string $workerId, string $role, float $hourlyRate): array
{
    $pdo  = getPDO();
    $stmt = $pdo->prepare(
        'SELECT id FROM workers WHERE company_id = ? AND worker_id = ? AND id != ? LIMIT 1'
    );
    $stmt->execute([$companyId, $workerId, $id]);
    if ($stmt->fetch()) {
        return ['ok' => false, 'error' => 'Another worker already uses that Worker ID.'];
    }

    $stmt = $pdo->prepare(
        'UPDATE workers SET name=?, worker_id=?, role=?, hourly_rate=? WHERE id=? AND company_id=?'
    );
    $stmt->execute([$name, $workerId, $role, $hourlyRate, $id, $companyId]);
    return ['ok' => true];
}

function deleteWorker(int $id, int $companyId): void
{
    $pdo  = getPDO();
    $stmt = $pdo->prepare('DELETE FROM workers WHERE id = ? AND company_id = ?');
    $stmt->execute([$id, $companyId]);
}

/* ══════════════════════════════════════════════════════
   ATTENDANCE
   ══════════════════════════════════════════════════════ */

function getAttendance(int $companyId, string $date = '', int $workerId = 0): array
{
    $pdo   = getPDO();
    $where = ['a.company_id = ?'];
    $params = [$companyId];

    if ($date !== '') {
        $where[]  = 'a.date = ?';
        $params[] = $date;
    }
    if ($workerId > 0) {
        $where[]  = 'a.worker_id = ?';
        $params[] = $workerId;
    }

    $sql = '
        SELECT a.*, w.name AS worker_name, w.worker_id AS wid, w.role, w.hourly_rate
        FROM attendance a
        JOIN workers w ON w.id = a.worker_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY a.date DESC, w.name ASC
    ';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function markAttendance(int $companyId, int $workerId, string $date, float $hours): array
{
    $pdo = getPDO();

    // Verify worker belongs to company
    $stmt = $pdo->prepare('SELECT id FROM workers WHERE id = ? AND company_id = ? LIMIT 1');
    $stmt->execute([$workerId, $companyId]);
    if (!$stmt->fetch()) {
        return ['ok' => false, 'error' => 'Invalid worker.'];
    }

    // Check duplicate
    $stmt = $pdo->prepare(
        'SELECT id FROM attendance WHERE company_id=? AND worker_id=? AND date=? LIMIT 1'
    );
    $stmt->execute([$companyId, $workerId, $date]);
    if ($stmt->fetch()) {
        return ['ok' => false, 'error' => 'Attendance already marked for this worker on this date.'];
    }

    $stmt = $pdo->prepare(
        'INSERT INTO attendance (company_id, worker_id, date, hours_worked) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$companyId, $workerId, $date, $hours]);
    return ['ok' => true];
}

function updateAttendance(int $id, int $companyId, float $hours): void
{
    $pdo  = getPDO();
    $stmt = $pdo->prepare('UPDATE attendance SET hours_worked=? WHERE id=? AND company_id=?');
    $stmt->execute([$hours, $id, $companyId]);
}

function deleteAttendance(int $id, int $companyId): void
{
    $pdo  = getPDO();
    $stmt = $pdo->prepare('DELETE FROM attendance WHERE id = ? AND company_id = ?');
    $stmt->execute([$id, $companyId]);
}

/* ══════════════════════════════════════════════════════
   SALARY
   ══════════════════════════════════════════════════════ */

function getMonthlySalary(int $companyId, string $month): array
{
    // $month = 'YYYY-MM'
    $pdo  = getPDO();
    $stmt = $pdo->prepare('
        SELECT
            w.id          AS worker_id,
            w.name,
            w.worker_id   AS wid,
            w.role,
            w.hourly_rate,
            SUM(a.hours_worked)                          AS total_hours,
            SUM(a.hours_worked * w.hourly_rate)          AS total_salary,
            COUNT(a.id)                                  AS days_worked
        FROM workers w
        LEFT JOIN attendance a
               ON a.worker_id  = w.id
              AND a.company_id = w.company_id
              AND DATE_FORMAT(a.date, "%Y-%m") = ?
        WHERE w.company_id = ?
        GROUP BY w.id
        ORDER BY w.name ASC
    ');
    $stmt->execute([$month, $companyId]);
    return $stmt->fetchAll();
}

/* ══════════════════════════════════════════════════════
   DASHBOARD STATS
   ══════════════════════════════════════════════════════ */

function getDashboardStats(int $companyId): array
{
    $pdo   = getPDO();
    $today = date('Y-m-d');
    $month = date('Y-m');

    // Total workers
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM workers WHERE company_id = ?');
    $stmt->execute([$companyId]);
    $totalWorkers = (int) $stmt->fetchColumn();

    // Today attendance count
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM attendance WHERE company_id = ? AND date = ?'
    );
    $stmt->execute([$companyId, $today]);
    $todayAttendance = (int) $stmt->fetchColumn();

    // Today total hours
    $stmt = $pdo->prepare(
        'SELECT COALESCE(SUM(hours_worked), 0) FROM attendance WHERE company_id = ? AND date = ?'
    );
    $stmt->execute([$companyId, $today]);
    $todayHours = (float) $stmt->fetchColumn();

    // Monthly salary expense
    $stmt = $pdo->prepare('
        SELECT COALESCE(SUM(a.hours_worked * w.hourly_rate), 0)
        FROM attendance a
        JOIN workers w ON w.id = a.worker_id
        WHERE a.company_id = ? AND DATE_FORMAT(a.date, "%Y-%m") = ?
    ');
    $stmt->execute([$companyId, $month]);
    $monthlyExpense = (float) $stmt->fetchColumn();

    return [
        'total_workers'    => $totalWorkers,
        'today_attendance' => $todayAttendance,
        'today_hours'      => $todayHours,
        'monthly_expense'  => $monthlyExpense,
    ];
}

/* ══════════════════════════════════════════════════════
   UTILITIES
   ══════════════════════════════════════════════════════ */

function formatInr(float $amount): string
{
    return CURRENCY . number_format($amount, 2);
}

function activePage(string $page): string
{
    $current = basename($_SERVER['PHP_SELF']);
    return $current === $page ? 'active' : '';
}
