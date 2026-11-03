<?php
$pageTitle = 'Task 4 · PHP Scripts (CRUD)';
$active = 't4';
require __DIR__ . '/includes/header.php';
$pdo = db();

$count = (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$sample = $pdo->prepare('SELECT reg_number, full_name, department FROM students WHERE year_level = :y ORDER BY id LIMIT 5');
$sample->execute([':y' => 1]);
$sampleRows = $sample->fetchAll();
?>

<div class="card">
  <h2>4. PHP scripts to connect, fetch, insert, update and delete</h2>
  <p class="sub">Source code of each operation, followed by a link to the working manager (<a href="students.php">Students Records</a>).</p>
  <div class="alert success">✔ Connected to <b><?= e(DB_NAME) ?></b> · <?= $count ?> student(s) found.</div>
</div>

<div class="grid g2">
  <div class="card">
    <h2>① Connect</h2>
    <p class="sub">config.php</p>
    <pre class="code">try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=practice_db;charset=utf8mb4',
        'root', '',
        [
            PDO::ATTR_ERRMODE            =&gt; PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE =&gt; PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   =&gt; false,
        ]
    );
} catch (PDOException $e) {
    die('Connection failed: ' . $e-&gt;getMessage());
}</pre>
  </div>

  <div class="card">
    <h2>② Fetch</h2>
    <p class="sub">students.php · SELECT</p>
    <pre class="code">$stmt = $pdo-&gt;prepare(
    'SELECT * FROM students WHERE year_level = :y'
);
$stmt-&gt;execute([':y' =&gt; 1]);
$students = $stmt-&gt;fetchAll();

foreach ($students as $s) {
    echo $s['reg_number'] . ' - ' . $s['full_name'];
}</pre>
    <p class="hint" style="margin:0 0 8px">Live output (first 5 year-1 students):</p>
    <?php foreach ($sampleRows as $s): ?>
      <div><b><?= e($s['reg_number']) ?></b> – <?= e($s['full_name']) ?> <span class="badge"><?= e($s['department']) ?></span></div>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <h2>③ Insert</h2>
    <p class="sub">students.php · INSERT</p>
    <pre class="code">$stmt = $pdo-&gt;prepare(
  'INSERT INTO students
     (reg_number, full_name, email, phone, department, year_level)
   VALUES (:reg, :name, :email, :phone, :dept, :year)'
);
$stmt-&gt;execute([
  ':reg'   =&gt; '25RP14899',
  ':name'  =&gt; 'New Student',
  ':email' =&gt; 'new@example.com',
  ':phone' =&gt; '0788111222',
  ':dept'  =&gt; 'ICT',
  ':year'  =&gt; 1,
]);
echo 'New ID: ' . $pdo-&gt;lastInsertId();</pre>
  </div>

  <div class="card">
    <h2>④ Update</h2>
    <p class="sub">students.php · UPDATE</p>
    <pre class="code">$stmt = $pdo-&gt;prepare(
  'UPDATE students
      SET full_name = :name, email = :email
    WHERE id = :id'
);
$stmt-&gt;execute([
  ':name'  =&gt; 'Updated Name',
  ':email' =&gt; 'updated@example.com',
  ':id'    =&gt; 3,
]);
echo $stmt-&gt;rowCount() . ' row(s) updated';</pre>
  </div>
</div>

<div class="card">
  <h2>⑤ Delete</h2>
  <p class="sub">students.php · DELETE</p>
  <pre class="code">$stmt = $pdo-&gt;prepare('DELETE FROM students WHERE id = :id');
$stmt-&gt;execute([':id' =&gt; 3]);
echo $stmt-&gt;rowCount() . ' row(s) deleted';</pre>
  <a class="btn" href="students.php">Open the working CRUD manager →</a>
  <a class="btn ghost" href="students.php?add=1">＋ Insert a student</a>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
