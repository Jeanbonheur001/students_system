<?php
/**
 * Shared layout: sidebar (left panel) + top bar.
 * Set $pageTitle and $active before including this file.
 */
require_once __DIR__ . '/../config.php';
require_login();
$me     = current_user();
$photo  = photo_url($me);
$active = $active ?? '';
$pageTitle = $pageTitle ?? 'Dashboard';

function nav_link(string $href, string $key, string $label, string $icon, string $active, bool $numbered = false): void
{
    $cls = $key === $active ? ' class="active"' : '';
    $badge = $numbered ? '<span class="num">' . e($icon) . '</span>' : '<span class="ico">' . $icon . '</span>';
    echo '<a href="' . e($href) . '"' . $cls . '>' . $badge . '<span>' . e($label) . '</span></a>';
}

function avatar_html(?string $photo, string $name, string $cls = ''): string
{
    if ($photo) {
        return '<img class="avatar ' . $cls . '" src="' . e($photo) . '" alt="' . e($name) . '">';
    }
    return '<span class="avatar ' . $cls . '">' . e(initials($name)) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="layout">

  <!-- ============ LEFT PANEL ============ -->
  <aside class="sidebar" id="sidebar">
    <div class="brand"><span class="logo">🎓</span> <?= e(APP_NAME) ?></div>

    <div class="side-user">
      <?= avatar_html($photo, $me['full_name']) ?>
      <div>
        <div class="nm"><?= e($me['full_name']) ?></div>
        <div class="rg"><?= e($me['reg_number']) ?></div>
      </div>
    </div>

    <nav class="nav">
      <div class="nav-title">Main</div>
      <?php nav_link('dashboard.php', 'dashboard', 'Dashboard', '🏠', $active); ?>

      <div class="nav-title">Individual Works · 20 Marks</div>
      <?php
      nav_link('task1_database.php',    't1', 'Create Database practice_db', '1', $active, true);
      nav_link('task2_tables.php',      't2', 'Create Tables (students)',    '2', $active, true);
      nav_link('task3_sample_data.php', 't3', 'Insert Sample Data',          '3', $active, true);
      nav_link('task4_scripts.php',     't4', 'PHP Scripts (CRUD)',          '4', $active, true);
      nav_link('task5_security.php',    't5', 'Prepared Statements',         '5', $active, true);
      ?>

      <div class="nav-title">Manage</div>
      <?php
      nav_link('students.php', 'students', 'Students Records', '👥', $active);
      nav_link('profile.php',  'profile',  'My Profile',       '⚙️', $active);
      nav_link('logout.php',   'logout',   'Logout',           '🚪', $active);
      ?>
    </nav>

    <div class="side-foot">Database: <b><?= e(DB_NAME) ?></b><br>© <?= date('Y') ?> <?= e(APP_NAME) ?></div>
  </aside>

  <!-- ============ MAIN ============ -->
  <div class="main">
    <header class="topbar">
      <button class="burger" type="button" onclick="document.getElementById('sidebar').classList.toggle('open')" aria-label="Menu">☰</button>
      <div>
        <h1><?= e($pageTitle) ?></h1>
        <div class="crumb">Home / <?= e($pageTitle) ?></div>
      </div>
      <div class="sp"></div>
      <div class="top-user">
        <span><?= e($me['full_name']) ?> · <b><?= e($me['reg_number']) ?></b></span>
        <?= avatar_html($photo, $me['full_name'], 'sm') ?>
      </div>
    </header>
    <main class="content">
      <?php if ($f = flash()): ?>
        <div class="alert <?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
      <?php endif; ?>
