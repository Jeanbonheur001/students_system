<?php
require_once __DIR__ . '/config.php';
require_login();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $added = seed_students($pdo);   // INSERT IGNORE with a prepared statement
    flash($added ? 'success' : 'info', $added ? "$added sample student(s) inserted." : 'All sample students already exist - nothing new to insert.');
    redirect('task3_sample_data.php');
}

$pageTitle = 'Task 3 · Insert Sample Data';
$active = 't3';
require __DIR__ . '/includes/header.php';

$rows = $pdo->query('SELECT id, reg_number, full_name, email, phone, department, year_level, created_at FROM students ORDER BY id')->fetchAll();
?>

<div class="card">
  <h2>3. Insert sample data</h2>
  <p class="sub">Ten student records are inserted automatically when the table is empty. Use the button to insert any that are missing.</p>

  <pre class="code">$stmt = $pdo-&gt;prepare(
    'INSERT IGNORE INTO students (reg_number, full_name, email, phone, department, year_level)
     VALUES (?, ?, ?, ?, ?, ?)'
);
foreach ($rows as $r) {
    $stmt-&gt;execute($r);      // values are sent separately from the SQL
}</pre>

  <form method="post" class="toolbar">
    <?= csrf_field() ?>
    <button class="btn ok" type="submit">⬇ Insert sample data now</button>
    <span class="hint">Duplicates are skipped (registration number is UNIQUE).</span>
  </form>
</div>

<div class="card">
  <h2>Data currently in <code>students</code></h2>
  <p class="sub"><code>SELECT * FROM students</code> → <?= count($rows) ?> row(s)</p>
  <div class="table-wrap">
    <table>
      <thead><tr><th>ID</th><th>Reg No</th><th>Name</th><th>Email</th><th>Phone</th><th>Department</th><th>Year</th><th>Created</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr class="<?= $r['reg_number'] === $me['reg_number'] ? 'me' : '' ?>">
          <td><?= (int) $r['id'] ?></td><td><b><?= e($r['reg_number']) ?></b></td><td><?= e($r['full_name']) ?></td>
          <td><?= e($r['email']) ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['department']) ?></td>
          <td><?= (int) $r['year_level'] ?></td><td><?= e(date('d M Y H:i', strtotime($r['created_at']))) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
