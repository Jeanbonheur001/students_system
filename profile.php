<?php
require_once __DIR__ . '/config.php';
require_login();
$pdo = db();
$me  = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    try {
        // ---- Upload / change photo ----
        if ($action === 'photo') {
            if (empty($_FILES['photo']['name'])) {
                throw new RuntimeException('Choose an image first.');
            }
            $new = save_profile_upload($_FILES['photo'], (int) $me['id']);
            if (!empty($me['photo']) && is_file(UPLOAD_DIR . $me['photo'])) {
                @unlink(UPLOAD_DIR . $me['photo']);
            }
            $pdo->prepare('UPDATE users SET photo = ? WHERE id = ?')->execute([$new, $me['id']]);
            log_activity((int) $me['id'], 'Changed profile photo');
            flash('success', 'Profile photo updated.');
        }

        // ---- Remove custom photo ----
        elseif ($action === 'photo_remove') {
            if (!empty($me['photo']) && is_file(UPLOAD_DIR . $me['photo'])) {
                @unlink(UPLOAD_DIR . $me['photo']);
            }
            $pdo->prepare('UPDATE users SET photo = NULL WHERE id = ?')->execute([$me['id']]);
            log_activity((int) $me['id'], 'Removed profile photo');
            flash('success', 'Photo removed.');
        }

        // ---- Update details ----
        elseif ($action === 'details') {
            $name  = trim((string) ($_POST['full_name'] ?? ''));
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $phone = trim((string) ($_POST['phone'] ?? ''));
            if (mb_strlen($name) < 3) throw new RuntimeException('Full name is too short.');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Invalid email address.');
            if ($phone !== '' && !preg_match('/^[0-9+\s-]{7,20}$/', $phone)) throw new RuntimeException('Invalid phone number.');

            $dup = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?');
            $dup->execute([$email, $me['id']]);
            if ((int) $dup->fetchColumn() > 0) throw new RuntimeException('That email is used by another account.');

            $pdo->prepare('UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?')
                ->execute([$name, $email, $phone ?: null, $me['id']]);
            // keep the students table in sync
            $pdo->prepare('UPDATE students SET full_name = ?, email = ?, phone = ? WHERE reg_number = ?')
                ->execute([$name, $email, $phone ?: null, $me['reg_number']]);
            log_activity((int) $me['id'], 'Updated profile details');
            flash('success', 'Profile details saved.');
        }

        // ---- Change password ----
        elseif ($action === 'password') {
            $cur = (string) ($_POST['current'] ?? '');
            $new = (string) ($_POST['new'] ?? '');
            $con = (string) ($_POST['confirm'] ?? '');
            if (!password_verify($cur, $me['password_hash'])) throw new RuntimeException('Current password is incorrect.');
            if (strlen($new) < 8) throw new RuntimeException('New password must be at least 8 characters.');
            if ($new !== $con) throw new RuntimeException('New passwords do not match.');
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($new, PASSWORD_DEFAULT), $me['id']]);
            log_activity((int) $me['id'], 'Changed password');
            flash('success', 'Password changed.');
        }
    } catch (RuntimeException $ex) {
        flash('error', $ex->getMessage());
    }
    redirect('profile.php');
}

$pageTitle = 'My Profile';
$active = 'profile';
require __DIR__ . '/includes/header.php';
?>

<div class="grid g-profile">
  <div>
    <div class="card" style="text-align:center">
      <?= avatar_html($photo, $me['full_name'], 'lg') ?>
      <h2 style="margin-top:14px"><?= e($me['full_name']) ?></h2>
      <p class="sub" style="margin-bottom:6px"><?= e($me['email']) ?></p>
      <span class="badge">Reg No: <?= e($me['reg_number']) ?></span>

      <hr style="border:0;border-top:1px solid var(--line);margin:20px 0">

      <form method="post" enctype="multipart/form-data" style="text-align:left">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="photo">
        <div class="field">
          <label for="photo">Change profile photo</label>
          <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" required>
          <div class="hint">JPG, PNG or WEBP · max 2 MB</div>
        </div>
        <button class="btn block" type="submit">Upload photo</button>
      </form>

      <?php if (!empty($me['photo'])): ?>
        <form method="post" style="margin-top:10px">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="photo_remove">
          <button class="btn danger block" type="submit" data-confirm="Remove your uploaded photo?">Remove uploaded photo</button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <div>
    <div class="card">
      <h2>Personal details</h2>
      <p class="sub">The registration number cannot be changed.</p>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="details">
        <div class="row2">
          <div class="field"><label>Registration number</label><input type="text" value="<?= e($me['reg_number']) ?>" disabled></div>
          <div class="field"><label for="full_name">Full name</label><input type="text" id="full_name" name="full_name" value="<?= e($me['full_name']) ?>" required></div>
        </div>
        <div class="row2">
          <div class="field"><label for="email">Email</label><input type="email" id="email" name="email" value="<?= e($me['email']) ?>" required></div>
          <div class="field"><label for="phone">Phone</label><input type="tel" id="phone" name="phone" value="<?= e($me['phone']) ?>"></div>
        </div>
        <button class="btn" type="submit">Save changes</button>
      </form>
    </div>

    <div class="card">
      <h2>Change password</h2>
      <p class="sub">Use at least 8 characters.</p>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="password">
        <div class="field"><label for="current">Current password</label><input type="password" id="current" name="current" required></div>
        <div class="row2">
          <div class="field"><label for="new">New password</label><input type="password" id="new" name="new" minlength="8" required></div>
          <div class="field"><label for="confirm">Confirm new password</label><input type="password" id="confirm" name="confirm" minlength="8" required></div>
        </div>
        <button class="btn" type="submit">Update password</button>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
