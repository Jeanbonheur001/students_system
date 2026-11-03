<?php
$pageTitle = 'Task 1 · Create Database';
$active = 't1';
require __DIR__ . '/includes/header.php';
$pdo = db();

$dbs = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
$info = $pdo->query("SELECT DEFAULT_CHARACTER_SET_NAME AS charset, DEFAULT_COLLATION_NAME AS collation
                     FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = " . $pdo->quote(DB_NAME))->fetch();
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="card">
  <h2>1. Create database <code>practice_db</code> in phpMyAdmin</h2>
  <p class="sub">This system creates the database automatically with PDO the first time any page loads. You can also create it by hand in phpMyAdmin.</p>

  <div class="alert success">✔ Database <b><?= e(DB_NAME) ?></b> exists and this page is connected to it.</div>

  <h2 style="margin-top:20px">Option A – phpMyAdmin (manual)</h2>
  <ol class="steps">
    <li>Start <b>Apache</b> and <b>MySQL</b> in XAMPP, then open <code>http://localhost/phpmyadmin</code>.</li>
    <li>Click <b>New</b> in the left menu.</li>
    <li>Database name: <code>practice_db</code> · Collation: <code>utf8mb4_unicode_ci</code>.</li>
    <li>Click <b>Create</b>.</li>
  </ol>

  <h2>Option B – PHP / PDO (used by this project)</h2>
  <pre class="code">&lt;?php
$server = new PDO('mysql:host=localhost;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE =&gt; PDO::ERRMODE_EXCEPTION
]);
$server-&gt;exec("CREATE DATABASE IF NOT EXISTS `practice_db`
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Database practice_db created";</pre>

  <h2>Option C – SQL</h2>
  <pre class="code">CREATE DATABASE IF NOT EXISTS practice_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</pre>
</div>

<div class="grid g2">
  <div class="card">
    <h2>Live check</h2>
    <p class="sub">Result of querying the MySQL server.</p>
    <dl class="kv">
      <dt>Database</dt><dd><?= e(DB_NAME) ?></dd>
      <dt>Character set</dt><dd><?= e($info['charset'] ?? '-') ?></dd>
      <dt>Collation</dt><dd><?= e($info['collation'] ?? '-') ?></dd>
      <dt>Tables inside</dt><dd><?= e(implode(', ', $tables)) ?></dd>
      <dt>MySQL version</dt><dd><?= e((string) $pdo->query('SELECT VERSION()')->fetchColumn()) ?></dd>
    </dl>
  </div>
  <div class="card">
    <h2>Databases on this server</h2>
    <p class="sub"><code>SHOW DATABASES</code></p>
    <?php foreach ($dbs as $d): ?>
      <span class="badge <?= $d === DB_NAME ? 'ok' : '' ?>" style="margin:0 6px 8px 0"><?= e($d) ?></span>
    <?php endforeach; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
