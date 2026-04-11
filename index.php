<?php
/**
 * WorkPulse Pro — Login Page
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Already logged in?
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = postStr('email');
    $password = postStr('password');

    if ($email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $result = loginUser($email, $password);
        if ($result['ok']) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — WorkPulse Pro</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card fade-up">

    <div class="auth-logo">
      <div class="logo-icon">⚡</div>
      <div class="logo-text">Work<span>Pulse</span> Pro</div>
    </div>
    <p class="auth-tagline">Factory Workforce Intelligence Platform</p>

    <h2 class="auth-title">Welcome back</h2>
    <p class="auth-subtitle">Sign in to your company account</p>

    <?php if ($error !== ''): ?>
      <div class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php" novalidate>
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="you@company.com"
               value="<?= htmlspecialchars(postStr('email'), ENT_QUOTES, 'UTF-8') ?>"
               autocomplete="email" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control"
               placeholder="••••••••" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-full" style="margin-top:0.5rem">
        Sign In →
      </button>
    </form>

    <div class="auth-link">
      Don't have an account? <a href="register.php">Create one free</a>
    </div>
  </div>
</div>
<script src="script.js"></script>
</body>
</html>
