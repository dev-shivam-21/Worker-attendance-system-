<?php
/**
 * WorkPulse Pro — Logout
 */
require_once __DIR__ . '/config.php';

session_unset();
session_destroy();

// Restart session briefly to set flash
session_start();
$_SESSION['flash'] = ['type' => 'success', 'message' => 'You have been securely logged out.'];

header('Location: index.php');
exit;
