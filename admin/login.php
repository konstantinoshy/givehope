<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

if (current_admin()) {
  redirect(BASE_URL . "/admin/index.php");
}

$pdo = db();
$error = null;

if (is_post()) {
  csrf_verify();
  $username = trim($_POST['username'] ?? '');
  $pass = $_POST['password'] ?? '';

  $st = $pdo->prepare("SELECT * FROM admins WHERE username = :u1 OR email = :u2");
  $st->execute([':u1' => $username, ':u2' => $username]);
  $admin = $st->fetch();

  if (!$admin || !password_verify($pass, $admin['password_hash'])) {
    $error = "Λάθος στοιχεία.";
  } else {
    login_admin($admin);
    redirect(BASE_URL . "/admin/index.php");
  }
}
?>
<!doctype html>
<html lang="el">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css?v=<?php echo filemtime(__DIR__ . '/../public/css/style.css'); ?>">
</head>

<body
  style="padding-top: 0; background: var(--bg-gray); min-height: 100vh; display: flex; align-items: center; justify-content: center;">

  <div style="width: 100%; max-width: 400px; padding: 24px;">
    <div class="card">
      <div style="text-align: center; margin-bottom: 24px;">
        <div style="margin-bottom: 12px; color: var(--primary);">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
          </svg>
        </div>
        <h1 style="margin: 0 0 8px;">Admin Panel</h1>
        <p class="muted" style="margin: 0;">Σύνδεση διαχειριστή</p>
      </div>

      <?php if ($error): ?>
        <div class="notice warn" style="margin-bottom: 20px;"><?php echo e($error); ?></div>
      <?php endif; ?>

      <form method="post">
        <?php echo csrf_field(); ?>

        <label>Username ή Email</label>
        <input name="username" required autofocus>

        <label>Password</label>
        <input name="password" type="password" required>

        <button class="btn primary" type="submit" style="width: 100%; margin-top: 20px;">Σύνδεση</button>
      </form>
    </div>
  </div>

</body>

</html>