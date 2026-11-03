<?php
require_once __DIR__ . '/config.php';
$pdo = db();

if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
$identity = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $identity = trim((string) ($_POST['identity'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    // simple brute-force protection: 5 failures => 60 second lock
    $_SESSION['fails'] = $_SESSION['fails'] ?? 0;
    if ($_SESSION['fails'] >= 5 && time() < ($_SESSION['lock_until'] ?? 0)) {
        $error = 'Too many attempts. Wait a minute and try again.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE reg_number = :id OR email = :id2 LIMIT 1');
        $stmt->execute([':id' => strtoupper($identity), ':id2' => strtolower($identity)]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['fails'] = 0;
            log_activity((int) $user['id'], 'Login');
            redirect('dashboard.php');
        }

        $_SESSION['fails']++;
        if ($_SESSION['fails'] >= 5) {
            $_SESSION['lock_until'] = time() + 60;
        }
        $error = 'Invalid registration number / email or password.';
    }
}
$flash = flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth">
  <section class="auth-art">
    <h1>Welcome back to<br><?= e(APP_NAME) ?></h1>
    <p>Log in to open your dashboard, manage student records and update your profile photo.</p>
    <ul>
      <li>Database: <?= e(DB_NAME) ?> (PDO + MySQL)</li>
      <li>Individual works – tasks 1 to 5</li>
      <li>Responsive professional dashboard</li>
    </ul>
  </section>

  <section class="auth-form">
    <div class="auth-box">
      <h2>Login</h2>
      <p class="sub">Use your registration number or email.</p>

      <?php if ($flash): ?><div class="alert <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

      <form method="post">
        <?= csrf_field() ?>
        <div class="field">
          <label for="identity">Registration number or email</label>
          <input type="text" id="identity" name="identity" value="<?= e($identity) ?>" placeholder="25RP14889" required autofocus>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <div class="pw-wrap">
            <input type="password" id="password" name="password" required>
            <button type="button" onclick="var i=document.getElementById('password');i.type=i.type==='password'?'text':'password'" aria-label="Show password">👁</button>
          </div>
        </div>
        <button class="btn block" type="submit">Login</button>
      </form>
      <p class="foot-link">No account yet? <a href="register.php"><b>Register</b></a></p>
    </div>
  </section>
</div>
</body>
</html>
