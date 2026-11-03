<?php
$pageTitle = 'Task 5 · Prepared Statements';
$active = 't5';
require __DIR__ . '/includes/header.php';
$pdo = db();

$input = trim((string) ($_GET['q'] ?? "' OR '1'='1"));

// SAFE: prepared statement - the input is treated purely as data
$safe = $pdo->prepare('SELECT reg_number, full_name FROM students WHERE full_name = :name');
$safe->execute([':name' => $input]);
$safeRows = $safe->fetchAll();

// What an UNSAFE concatenated query would look like (shown only as text, never executed)
$unsafeSql = "SELECT * FROM students WHERE full_name = '" . $input . "'";
?>

<div class="card">
  <h2>5. Use prepared statements for security</h2>
  <p class="sub">Prepared statements send the SQL and the data separately, so user input can never change the meaning of a query (SQL injection).</p>

  <div class="grid g2">
    <div>
      <h2 style="color:var(--bad)">✘ Unsafe (never do this)</h2>
      <pre class="code">$name = $_GET['name'];
$sql  = "SELECT * FROM students
         WHERE full_name = '$name'";
$result = $pdo-&gt;query($sql);</pre>
    </div>
    <div>
      <h2 style="color:var(--ok)">✔ Safe (used in this project)</h2>
      <pre class="code">$stmt = $pdo-&gt;prepare(
  'SELECT * FROM students WHERE full_name = :name'
);
$stmt-&gt;execute([':name' =&gt; $_GET['name']]);
$rows = $stmt-&gt;fetchAll();</pre>
    </div>
  </div>
</div>

<div class="card">
  <h2>Live demonstration</h2>
  <p class="sub">Type an input (the default is a classic injection attempt) and see how the prepared statement handles it.</p>
  <form method="get" class="toolbar">
    <input type="text" name="q" value="<?= e($input) ?>" style="max-width:420px">
    <button class="btn" type="submit">Run safe query</button>
  </form>

  <p class="hint" style="margin:0 0 6px">If this input were concatenated, the SQL would become (not executed):</p>
  <pre class="code"><?= e($unsafeSql) ?></pre>

  <p class="hint" style="margin:0 0 6px">Prepared statement result:</p>
  <?php if ($safeRows): ?>
    <div class="alert success">Matched <?= count($safeRows) ?> student(s):
      <?php foreach ($safeRows as $r): ?><b><?= e($r['reg_number']) ?></b> <?= e($r['full_name']) ?>; <?php endforeach; ?></div>
  <?php else: ?>
    <div class="alert info">🛡 No rows returned. The input was treated as plain text, not as SQL, so the injection attempt failed.</div>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Security features in this system</h2>
  <dl class="kv">
    <dt>PDO prepared statements</dt><dd>Every query that uses user input (register, login, profile, CRUD)</dd>
    <dt>Password hashing</dt><dd><code>password_hash()</code> / <code>password_verify()</code></dd>
    <dt>XSS protection</dt><dd>All output escaped with <code>htmlspecialchars()</code></dd>
    <dt>CSRF protection</dt><dd>Hidden token checked on every POST form</dd>
    <dt>Session safety</dt><dd><code>session_regenerate_id()</code> on login, HttpOnly cookies</dd>
    <dt>Upload validation</dt><dd>Real image check, size limit, random file names</dd>
    <dt>Real prepared statements</dt><dd><code>ATTR_EMULATE_PREPARES = false</code></dd>
  </dl>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
