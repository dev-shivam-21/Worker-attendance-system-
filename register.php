<?php
/**
 * WorkPulse Pro — Company Registration
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyName = postStr('company_name');
    $ownerName   = postStr('owner_name');
    $email       = postStr('email');
    $password    = postStr('password');
    $confirm     = postStr('confirm_password');

    if ($companyName === '' || $ownerName === '' || $email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $result = registerCompany($companyName, $ownerName, $email, $password);
        if ($result['ok']) {
            $success = 'Account created! You can now sign in.';
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
  <title>Register — WorkPulse Pro</title>
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

    <h2 class="auth-title">Create your account</h2>
    <p class="auth-subtitle">Set up your company workspace in seconds</p>

    <?php if ($error !== ''): ?>
      <div class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
      <div class="flash flash-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php" novalidate>
      <div class="form-row">
        <div class="form-group">
          <label for="company_name">Company Name</label>
          <input type="text" id="company_name" name="company_name" class="form-control"
                 placeholder="Acme Factory"
                 value="<?= sanitize(postStr('company_name')) ?>" required>
        </div>
        <div class="form-group">
          <label for="owner_name">Owner Name</label>
          <input type="text" id="owner_name" name="owner_name" class="form-control"
                 placeholder="Rajan Mehta"
                 value="<?= sanitize(postStr('owner_name')) ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="you@company.com"
               value="<?= sanitize(postStr('email')) ?>" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Min. 6 characters" required>
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm Password</label>
          <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                 placeholder="Repeat password" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full" style="margin-top:0.5rem">
        Create Account →
      </button>
    </form>

    <div class="auth-link">
      Already have an account? <a href="index.php">Sign in</a>
    </div>
  </div>
</div>
<script src="script.js"></script>
</body>
</html>
