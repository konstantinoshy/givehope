<?php
// Φόρμα επικοινωνίας (γενική ή προς οργανισμό)

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/header.php';

$pdo = db();

// Έλεγχος αν το μήνυμα απευθύνεται σε συγκεκριμένο οργανισμό
$org_id = (int) ($_GET['org_id'] ?? 0);
$org = null;
if ($org_id > 0) {
  $st = $pdo->prepare("SELECT id, name, email FROM organizations WHERE id=:id");
  $st->execute([':id' => $org_id]);
  $org = $st->fetch();
}

$sent = false;
$error = null;

if (is_post()) {
  csrf_verify();

  // Συλλογή δεδομένων φόρμας
  $org_id_post = (int) ($_POST['org_id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $body = trim($_POST['body'] ?? '');

  // Δεδομένα συγκατάθεσης GDPR
  $privacyConsent = isset($_POST['privacy_consent']) ? 1 : 0;
  $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

  // Επικύρωση δεδομένων
  if ($name === '' || $email === '' || $subject === '' || $body === '') {
    $error = "Συμπληρώστε όλα τα πεδία.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Μη έγκυρο email.";
  } elseif (!$privacyConsent) {
    $error = "Πρέπει να αποδεχτείτε την Πολιτική Απορρήτου.";
  } else {
    // Εισαγωγή μηνύματος με καταγραφή συγκατάθεσης GDPR
    $ins = $pdo->prepare("INSERT INTO messages (org_id, name, email, subject, body, privacy_consent, ip_address) VALUES (:org,:n,:e,:s,:b,:consent,:ip)");
    $ins->execute([
      ':org' => ($org_id_post > 0 ? $org_id_post : null),
      ':n' => $name,
      ':e' => $email,
      ':s' => $subject,
      ':b' => $body,
      ':consent' => $privacyConsent,
      ':ip' => $ipAddress
    ]);
    $sent = true;
  }
}

// Prefill: POST (retains input on validation error) → logged-in profile → empty
$prefillName  = $_POST['name']  ?? $currentUser['name']  ?? $currentOrg['name']  ?? '';
$prefillEmail = $_POST['email'] ?? $currentUser['email'] ?? $currentOrg['email'] ?? '';
?>

<div style="max-width: 560px; margin: 120px auto 60px; padding: 0 24px;">
  <div class="card">
    <div style="text-align: center; margin-bottom: 24px;">
      <h1 style="margin: 0 0 8px;">Επικοινωνία</h1>
      <p class="muted" style="margin: 0;">Στείλτε μας το μήνυμά σας</p>
    </div>

    <?php if ($sent): ?>
      <div style="text-align: center; padding: 24px;">
        <div style="font-size: 64px; margin-bottom: 16px;">✅</div>
        <h2 style="margin: 0 0 8px;">Εστάλη!</h2>
        <p class="muted">Το μήνυμά σας καταχωρήθηκε επιτυχώς.</p>
        <a class="btn" href="<?php echo BASE_URL; ?>/index.php" style="margin-top: 16px;">← Αρχική</a>
      </div>
    <?php else: ?>
      <?php if ($error): ?>
        <div class="notice warn" style="margin-bottom: 20px;"><?php echo e($error); ?></div>
      <?php endif; ?>

      <?php if ($org): ?>
        <div class="notice" style="margin-bottom: 20px;">
          Προς: <strong><?php echo e($org['name']); ?></strong>
        </div>
      <?php endif; ?>

      <form method="post">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="org_id" value="<?php echo $org ? (int) $org['id'] : 0; ?>">

        <div class="row">
          <div>
            <label>Ονοματεπώνυμο *</label>
            <input name="name" required value="<?php echo e($prefillName); ?>">
          </div>
          <div>
            <label>Email *</label>
            <input name="email" type="email" required value="<?php echo e($prefillEmail); ?>">
          </div>
        </div>

        <label>Θέμα *</label>
        <input name="subject" required
          value="<?php echo e($_POST['subject'] ?? ($org ? 'Προς ' . $org['name'] : '')); ?>">

        <label>Μήνυμα *</label>
        <textarea name="body" required><?php echo e($_POST['body'] ?? ''); ?></textarea>

        <div class="consent-section" style="margin-top: 16px;">
          <label class="checkbox-label">
            <input type="checkbox" name="privacy_consent" value="1" <?php echo isset($_POST['privacy_consent']) ? 'checked' : ''; ?> required>
            <span>
              Αποδέχομαι την <a href="<?php echo BASE_URL; ?>/privacy.php" target="_blank">Πολιτική Απορρήτου</a>
              και συναινώ στην επεξεργασία των δεδομένων μου για την απάντηση στο μήνυμά μου. *
            </span>
          </label>
        </div>

        <button class="btn primary" type="submit" style="width: 100%; margin-top: 20px;">Αποστολή</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>