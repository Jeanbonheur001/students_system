<?php
$pageTitle = 'Task 2 · Create Tables';
$active = 't2';
require __DIR__ . '/includes/header.php';
$pdo = db();

$cols = $pdo->query('SHOW COLUMNS FROM students')->fetchAll();
$create = $pdo->query('SHOW CREATE TABLE students')->fetch();
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="card">
  <h2>2. Create tables (<code>students</code>)</h2>
  <p class="sub">The table is created with PDO and <code>CREATE TABLE IF NOT EXISTS</code>, so it is safe to run many times.</p>

  <pre class="code">$pdo-&gt;exec("CREATE TABLE IF NOT EXISTS students (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reg_number  VARCHAR(30)  NOT NULL UNIQUE,
    full_name   VARCHAR(120) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    phone       VARCHAR(30)  NULL,
    department  VARCHAR(100) NOT NULL DEFAULT 'ICT',
    year_level  TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");</pre>

  <div class="alert success">✔ Table <b>students</b> exists in <b><?= e(DB_NAME) ?></b>.</div>
</div>

<div class="card">
  <h2>Live structure of <code>students</code></h2>
  <p class="sub"><code>SHOW COLUMNS FROM students</code></p>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr></thead>
      <tbody>
      <?php foreach ($cols as $c): ?>
        <tr>
          <td><b><?= e($c['Field']) ?></b></td><td><?= e($c['Type']) ?></td><td><?= e($c['Null']) ?></td>
          <td><?= $c['Key'] ? '<span class="badge">' . e($c['Key']) . '</span>' : '' ?></td>
          <td><?= e((string) $c['Default']) ?></td><td><?= e($c['Extra']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="grid g2">
  <div class="card">
    <h2>All tables in <?= e(DB_NAME) ?></h2>
    <p class="sub">Created automatically for the system features.</p>
    <dl class="kv">
      <dt>students</dt><dd>Student records (assignment table)</dd>
      <dt>users</dt><dd>Registered accounts, password hashes, photo</dd>
      <dt>activity_log</dt><dd>Login / profile events</dd>
    </dl>
    <p class="hint">Found on server: <?= e(implode(', ', $tables)) ?></p>
  </div>
  <div class="card">
    <h2>Generated SQL</h2>
    <p class="sub"><code>SHOW CREATE TABLE students</code></p>
    <pre class="code" style="margin:0"><?= e($create['Create Table'] ?? '') ?></pre>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
