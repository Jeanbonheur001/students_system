<?php
require_once __DIR__ . '/config.php';
if (is_logged_in()) {
    log_activity((int) $_SESSION['user_id'], 'Logout');
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
session_start();
flash('success', 'You have been logged out.');
redirect('login.php');
