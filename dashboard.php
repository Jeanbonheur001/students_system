<?php
$pageTitle = 'Dashboard';
$active = 'dashboard';
require __DIR__ . '/includes/header.php';
$pdo = db();

$totalStudents = (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$totalUsers    = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalDepts    = (int) $pdo->query('SELECT COUNT(DISTINCT department) FROM students')->fetchColumn();

$students = $pdo->query('SELECT reg_number, full_name, email, department, year_level FROM students ORDER BY id DESC LIMIT 8')->fetchAll();

$log = $pdo->prepare('SELECT action, ip_address, created_at FROM activity_log WHERE user_id = ? ORDER BY id DESC LIMIT 5');
$log->execute([$me['id']]);
$activity = $log->fetchAll();
?>

<section class="hero">
  <?= avatar_html($photo, $me['full_name'], 'lg') ?>
  <div>
    <h2>Welcome, <?= e($me['full_name']) ?> 👋</h2>
    <p><?= e($me['email']) ?><?= $me['phone'] ? ' · ' . e($me['phone']) : '' ?></p>
    <span class="chip">🪪 Reg No: <?= e($me['reg_number']) ?></span>
    <span class="chip">📅 Joined <?= e(date('d M Y', strtotime($me['created_at']))) ?></span>
    <div style="margin-top:14px"><a class="btn ghost sm" href="profile.php">Edit profile &amp; photo</a></div>
  </div>
</section>

<div class="grid g4" style="margin-bottom:22px">
  <div class="card stat" style="margin:0"><div class="ic bg-b">👥</div><div><div class="v"><?= $totalStudents ?></div><div class="l">Student records</div></div></div>
  <div class="card stat" style="margin:0"><div class="ic bg-g">🔐</div><div><div class="v"><?= $totalUsers ?></div><div class="l">Registered users</div></div></div>
  <div class="card stat" style="margin:0"><div class="ic bg-p">🏫</div><div><div class="v"><?= $totalDepts ?></div><div class="l">Departments</div></div></div>
  <div class="card stat" style="margin:0"><div class="ic bg-o">📝</div><div><div class="v">20</div><div class="l">Marks · Individual works</div></div></div>
</div>

<div class="card">
  <h2>Individual Works · 20 Marks</h2>
  <p class="sub">Each step below is linked to the left panel and shows a live demonstration using PDO.</p>
  <div class="grid g3">
    <a class="task" href="task1_database.php"><div class="n">1</div><div><b>Create database practice_db</b><span>Created through PDO and verified in phpMyAdmin.</span></div></a>
    <a class="task" href="task2_tables.php"><div class="n">2</div><div><b>Create tables (students)</b><span>Table structure, columns and keys.</span></div></a>
    <a class="task" href="task3_sample_data.php"><div class="n">3</div><div><b>Insert sample data</b><span>10 student records inserted safely.</span></div></a>
    <a class="task" href="task4_scripts.php"><div class="n">4</div><div><b>PHP scripts: connect, fetch, insert, update, delete</b><span>Source code plus the live CRUD manager.</span></div></a>
    <a class="task" href="task5_security.php"><div class="n">5</div><div><b>Use prepared statements</b><span>SQL injection protection explained.</span></div></a>
    <a class="task" href="students.php"><div class="n">👥</div><div><b>Students records</b><span>Add, edit, search and delete students.</span></div></a>
  </div>
</div>

<div class="grid g2" style="grid-template-columns:2fr 1fr">
  <div class="card">
    <h2>Latest students</h2>
    <p class="sub">Registration numbers from the <code>students</code> table.</p>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Reg No</th><th>Name</th><th>Department</th><th>Year</th></tr></thead>
        <tbody>
        <?php foreach ($students as $s): ?>
          <tr class="<?= $s['reg_number'] === $me['reg_number'] ? 'me' : '' ?>">
            <td><b><?= e($s['reg_number']) ?></b><?= $s['reg_number'] === $me['reg_number'] ? ' <span class="badge ok">You</span>' : '' ?></td>
            <td><?= e($s['full_name']) ?></td>
            <td><?= e($s['department']) ?></td>
            <td><?= (int) $s['year_level'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p style="margin:14px 0 0"><a href="students.php">View all students →</a></p>
  </div>

  <div class="card">
    <h2>Recent activity</h2>
    <p class="sub">Your latest account events.</p>
    <?php if (!$activity): ?>
      <p class="hint">No activity yet.</p>
    <?php endif; ?>
    <?php foreach ($activity as $a): ?>
      <div style="padding:10px 0;border-top:1px solid var(--line)">
        <b><?= e($a['action']) ?></b>
        <div class="hint"><?= e(date('d M Y, H:i', strtotime($a['created_at']))) ?> · <?= e($a['ip_address']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
