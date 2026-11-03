<?php
require_once __DIR__ . '/config.php';
$pdo = db();

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$old = ['full_name' => '', 'reg_number' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $old['full_name']  = trim((string) ($_POST['full_name'] ?? ''));
    $old['reg_number'] = strtoupper(trim((string) ($_POST['reg_number'] ?? '')));
    $old['email']      = strtolower(trim((string) ($_POST['email'] ?? '')));
    $old['phone']      = trim((string) ($_POST['phone'] ?? ''));
    $password          = (string) ($_POST['password'] ?? '');
    $confirm           = (string) ($_POST['confirm'] ?? '');

    if (mb_strlen($old['full_name']) < 3) {
        $errors[] = 'Full name must have at least 3 characters.';
    }
    if (!preg_match('/^\d{2}RP\d{5}$/', $old['reg_number'])) {
        $errors[] = 'Registration number must look like 25RP14889.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if ($old['phone'] !== '' && !preg_match('/^[0-9+\s-]{7,20}$/', $old['phone'])) {
        $errors[] = 'Phone number format is invalid.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE reg_number = :reg OR email = :email');
        $check->execute([':reg' => $old['reg_number'], ':email' => $old['email']]);
        if ((int) $check->fetchColumn() > 0) {
            $errors[] = 'That registration number or email is already registered.';
        }
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            $ins = $pdo->prepare(
                'INSERT INTO users (full_name, reg_number, email, phone, password_hash)
                 VALUES (:name, :reg, :email, :phone, :hash)'
            );
            $ins->execute([
                ':name'  => $old['full_name'],
                ':reg'   => $old['reg_number'],
                ':email' => $old['email'],
                ':phone' => $old['phone'] ?: null,
                ':hash'  => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $userId = (int) $pdo->lastInsertId();

            // Also list the new user in the students table (shown in dashboard & records)
            $stu = $pdo->prepare(
                'INSERT INTO students (reg_number, full_name, email, phone)
                 VALUES (:reg, :name, :email, :phone)
                 ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), email = VALUES(email), phone = VALUES(phone)'
            );
            $stu->execute([
                ':reg' => $old['reg_number'], ':name' => $old['full_name'],
                ':email' => $old['email'], ':phone' => $old['phone'] ?: null,
            ]);

            $pdo->commit();
            try {
                log_activity($userId, 'Registered account');
            } catch (PDOException $ignored) {
                // logging must never block a successful registration
            }
            flash('success', 'Account created successfully. Please log in.');
            redirect('login.php');
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Could not create the account.' . (APP_DEBUG ? ' Reason: ' . $ex->getMessage() : ' Please try again.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Register · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth">
  <section class="auth-art">
    <h1>Create your<br>student account</h1>
    <p>Register once, log in, and manage your profile and student records from one professional dashboard.</p>
    <ul>
      <li>Secure password hashing</li>
      <li>PDO prepared statements everywhere</li>
      <li>Profile photo upload</li>
      <li>Full CRUD on student records</li>
    </ul>
  </section>

  <section class="auth-form">
    <div class="auth-box">
      <h2>Register</h2>
      <p class="sub">Fill in your details to get started.</p>

      <?php if ($errors): ?>
        <div class="alert error"><?php foreach ($errors as $er): ?>• <?= e($er) ?><br><?php endforeach; ?></div>
      <?php endif; ?>

      <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="field">
          <label for="full_name">Full name</label>
          <input type="text" id="full_name" name="full_name" value="<?= e($old['full_name']) ?>" required>
        </div>
        <div class="row2">
          <div class="field">
            <label for="reg_number">Registration number</label>
            <input type="text" id="reg_number" name="reg_number" value="<?= e($old['reg_number']) ?>" placeholder="25RP14889" maxlength="9" required>
          </div>
          <div class="field">
            <label for="phone">Phone (optional)</label>
            <input type="tel" id="phone" name="phone" value="<?= e($old['phone']) ?>" placeholder="07XXXXXXXX">
          </div>
        </div>
        <div class="field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" required>
        </div>
        <div class="row2">
          <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" minlength="8" required>
          </div>
          <div class="field">
            <label for="confirm">Confirm password</label>
            <input type="password" id="confirm" name="confirm" minlength="8" required>
          </div>
        </div>
        <button class="btn block" type="submit">Create account</button>
      </form>
      <p class="foot-link">Already registered? <a href="login.php"><b>Log in</b></a></p>
    </div>
  </section>
</div>
</body>
</html>
