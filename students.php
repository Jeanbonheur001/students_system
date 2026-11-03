<?php
require_once __DIR__ . '/config.php';
require_login();
$pdo = db();
$me  = current_user();

$errors = [];
$departments = ['ICT', 'Networking', 'Software Dev', 'Electrical', 'Civil', 'Mechanical', 'Other'];

// ------------------------------------------------------------------
// POST actions: insert / update / delete  (all with prepared statements)
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM students WHERE id = :id');
        $stmt->execute([':id' => $id]);
        flash($stmt->rowCount() ? 'success' : 'error', $stmt->rowCount() ? 'Student deleted.' : 'Student not found.');
        redirect('students.php');
    }

    if ($action === 'save') {
        $id    = (int) ($_POST['id'] ?? 0);
        $reg   = strtoupper(trim((string) ($_POST['reg_number'] ?? '')));
        $name  = trim((string) ($_POST['full_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $dept  = trim((string) ($_POST['department'] ?? 'ICT'));
        $year  = (int) ($_POST['year_level'] ?? 1);

        if (!preg_match('/^\d{2}RP\d{5}$/', $reg)) $errors[] = 'Registration number must look like 25RP14889.';
        if (mb_strlen($name) < 3)                   $errors[] = 'Full name is too short.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
        if ($phone !== '' && !preg_match('/^[0-9+\s-]{7,20}$/', $phone)) $errors[] = 'Invalid phone number.';
        if (!in_array($dept, $departments, true))   $errors[] = 'Choose a valid department.';
        if ($year < 1 || $year > 6)                 $errors[] = 'Year must be between 1 and 6.';

        if (!$errors) {
            $dup = $pdo->prepare('SELECT COUNT(*) FROM students WHERE reg_number = :reg AND id <> :id');
            $dup->execute([':reg' => $reg, ':id' => $id]);
            if ((int) $dup->fetchColumn() > 0) $errors[] = 'That registration number already exists.';
        }

        if (!$errors) {
            if ($id > 0) {
                $stmt = $pdo->prepare(
                    'UPDATE students SET reg_number = :reg, full_name = :name, email = :email,
                            phone = :phone, department = :dept, year_level = :year WHERE id = :id'
                );
                $stmt->execute([':reg' => $reg, ':name' => $name, ':email' => $email, ':phone' => $phone ?: null,
                                ':dept' => $dept, ':year' => $year, ':id' => $id]);
                flash('success', 'Student updated.');
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO students (reg_number, full_name, email, phone, department, year_level)
                     VALUES (:reg, :name, :email, :phone, :dept, :year)'
                );
                $stmt->execute([':reg' => $reg, ':name' => $name, ':email' => $email, ':phone' => $phone ?: null,
                                ':dept' => $dept, ':year' => $year]);
                flash('success', 'Student added.');
            }
            redirect('students.php');
        }
        // fall through: show the form again with the errors
        $formData = ['id' => $id, 'reg_number' => $reg, 'full_name' => $name, 'email' => $email,
                     'phone' => $phone, 'department' => $dept, 'year_level' => $year];
    }
}

// ------------------------------------------------------------------
// Form state (add / edit)
// ------------------------------------------------------------------
$formData = $formData ?? ['id' => 0, 'reg_number' => '', 'full_name' => '', 'email' => '', 'phone' => '', 'department' => 'ICT', 'year_level' => 1];
$showForm = isset($_GET['add']) || isset($_GET['edit']) || $errors;

if (isset($_GET['edit']) && !$errors) {
    $stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id');
    $stmt->execute([':id' => (int) $_GET['edit']]);
    $row = $stmt->fetch();
    if ($row) {
        $formData = $row;
    } else {
        flash('error', 'Student not found.');
        redirect('students.php');
    }
}

$students = $pdo->query('SELECT * FROM students ORDER BY reg_number ASC')->fetchAll();

$pageTitle = 'Students Records';
$active = 'students';
require __DIR__ . '/includes/header.php';
?>

<?php if ($showForm): ?>
<div class="card">
  <h2><?= $formData['id'] ? 'Edit student' : 'Add new student' ?></h2>
  <p class="sub">Saved with a PDO prepared statement.</p>

  <?php if ($errors): ?>
    <div class="alert error"><?php foreach ($errors as $er): ?>• <?= e($er) ?><br><?php endforeach; ?></div>
  <?php endif; ?>

  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int) $formData['id'] ?>">
    <div class="row2">
      <div class="field"><label>Registration number</label>
        <input type="text" name="reg_number" value="<?= e($formData['reg_number']) ?>" placeholder="25RP14889" maxlength="9" required></div>
      <div class="field"><label>Full name</label>
        <input type="text" name="full_name" value="<?= e($formData['full_name']) ?>" required></div>
    </div>
    <div class="row2">
      <div class="field"><label>Email</label>
        <input type="email" name="email" value="<?= e($formData['email']) ?>" required></div>
      <div class="field"><label>Phone</label>
        <input type="tel" name="phone" value="<?= e($formData['phone']) ?>"></div>
    </div>
    <div class="row2">
      <div class="field"><label>Department</label>
        <select name="department">
          <?php foreach ($departments as $d): ?>
            <option <?= $d === $formData['department'] ? 'selected' : '' ?>><?= e($d) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="field"><label>Year level</label>
        <input type="number" name="year_level" min="1" max="6" value="<?= (int) $formData['year_level'] ?>"></div>
    </div>
    <button class="btn" type="submit"><?= $formData['id'] ? 'Update student' : 'Save student' ?></button>
    <a class="btn ghost" href="students.php">Cancel</a>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <h2>All students</h2>
  <p class="sub"><?= count($students) ?> record(s) in table <code>students</code> · database <code><?= e(DB_NAME) ?></code></p>
  <div class="toolbar">
    <input type="text" id="tableSearch" placeholder="Search students…">
    <div class="sp"></div>
    <a class="btn" href="students.php?add=1">＋ Add student</a>
  </div>
  <div class="table-wrap">
    <table id="dataTable">
      <thead><tr><th>#</th><th>Reg No</th><th>Name</th><th>Email</th><th>Phone</th><th>Department</th><th>Year</th><th style="text-align:right">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($students as $i => $s): ?>
        <tr class="<?= $s['reg_number'] === $me['reg_number'] ? 'me' : '' ?>">
          <td><?= $i + 1 ?></td>
          <td><b><?= e($s['reg_number']) ?></b><?= $s['reg_number'] === $me['reg_number'] ? ' <span class="badge ok">You</span>' : '' ?></td>
          <td><?= e($s['full_name']) ?></td>
          <td><?= e($s['email']) ?></td>
          <td><?= e($s['phone']) ?></td>
          <td><span class="badge"><?= e($s['department']) ?></span></td>
          <td><?= (int) $s['year_level'] ?></td>
          <td style="text-align:right;white-space:nowrap">
            <a class="btn ghost sm" href="students.php?edit=<?= (int) $s['id'] ?>">Edit</a>
            <form method="post" style="display:inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
              <button class="btn danger sm" type="submit" data-confirm="Delete <?= e($s['full_name']) ?>?">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$students): ?><tr><td colspan="8" class="hint">No students yet. Click “Add student”.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
