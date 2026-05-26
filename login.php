<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

// Ανακατεύθυνση αν είναι ήδη συνδεδεμένος
if (current_user()) {
  redirect(BASE_URL . "/my-campaigns.php");
}
if (current_org()) {
  redirect(BASE_URL . "/dashboard/index.php");
}

$type = $_GET['type'] ?? 'user'; // 'user' or 'org'
$pdo = db();
$error = null;

if (is_post()) {
  csrf_verify();
  $email = trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';
  $loginType = $_POST['type'] ?? 'user';

  if ($loginType === 'org') {
    $st = $pdo->prepare("SELECT * FROM organizations WHERE email = :e AND deleted_at IS NULL");
    $st->execute([':e' => $email]);
    $account = $st->fetch();

    if (!$account || !password_verify($pass, $account['password_hash'])) {
      $error = "Λάθος email ή κωδικός.";
    } else {
      login_org($account);
      redirect(BASE_URL . "/dashboard/index.php");
    }
  } else {
    $st = $pdo->prepare("SELECT * FROM users WHERE email = :e AND deleted_at IS NULL");
    $st->execute([':e' => $email]);
    $account = $st->fetch();

    if (!$account || !password_verify($pass, $account['password_hash'])) {
      $error = "Λάθος email ή κωδικός.";
    } else {
      login_user($account);
      redirect(BASE_URL . "/my-campaigns.php");
    }
  }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-card-wrapper">
  <div class="card" style="padding: 40px 32px;">
    <!-- Καρτέλες -->
    <div class="auth-tabs">
      <a href="?type=user" class="auth-tab <?php echo $type === 'user' ? 'active' : ''; ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
          <circle cx="12" cy="7" r="4" />
        </svg>
        Χρήστης
      </a>
      <a href="?type=org" class="auth-tab <?php echo $type === 'org' ? 'active' : ''; ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 21h18M5 21V7l8-4 8 4v14M9 21v-4h6v4" />
        </svg>
        Οργανισμός
      </a>
    </div>

    <div style="text-align: center; margin-bottom: 32px;">
      <h1 style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 28px; margin: 0 0 8px;">
        Σύνδεση</h1>
      <p style="color: var(--text-secondary); font-size: 15px; margin: 0;">
        <?php echo $type === 'org' ? 'Λογαριασμός οργανισμού' : 'Προσωπικός λογαριασμός'; ?>
      </p>
    </div>

    <?php if ($error): ?>
      <div class="notice warn" style="margin-bottom: 24px; text-align: left;"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post" class="js-validate">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="type" value="<?php echo e($type); ?>">

      <div style="margin-bottom: 20px;">
        <label style="margin-top: 0;">Email</label>
        <input name="email" type="email" required value="<?php echo e($_POST['email'] ?? ''); ?>"
          placeholder="email@example.com" data-error="Συμπληρώστε έγκυρο email." style="background: var(--bg-gray);">
      </div>

      <div style="margin-bottom: 24px;">
        <label style="margin-top: 0;">Κωδικός</label>
        <input name="password" type="password" required placeholder="••••••••" data-error="Συμπληρώστε τον κωδικό σας."
          style="background: var(--bg-gray);">
        <a href="<?php echo BASE_URL; ?>/contact.php"
          style="display: block; text-align: right; margin-top: 8px; font-size: 13px; color: var(--primary); font-weight: 500;">
          Ξεχάσατε τον κωδικό σας;
        </a>
      </div>

      <button class="btn primary" type="submit" style="width: 100%; border-radius: var(--radius-pill); padding: 16px;">
        Σύνδεση
      </button>
    </form>

    <div class="hr" style="margin: 32px 0;"></div>

    <p style="text-align: center; color: var(--text-secondary); font-size: 14px; margin: 0 0 16px;">Δεν έχετε
      λογαριασμό;</p>

    <?php if ($type === 'org'): ?>
      <a class="btn" href="<?php echo BASE_URL; ?>/register.php?type=org"
        style="width: 100%; justify-content: center; border-radius: var(--radius-pill);">Εγγραφή Οργανισμού</a>
    <?php else: ?>
      <a class="btn" href="<?php echo BASE_URL; ?>/register.php?type=user"
        style="width: 100%; justify-content: center; border-radius: var(--radius-pill);">Δημιουργία Λογαριασμού</a>
    <?php endif; ?>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>