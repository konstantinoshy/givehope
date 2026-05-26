<?php
// Εγγραφή χρήστη ή οργανισμού

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

// Ανακατεύθυνση αν ο χρήστης είναι ήδη συνδεδεμένος
if (current_user() || current_org()) {
  redirect(BASE_URL . "/index.php");
}

$type = $_GET['type'] ?? 'user';
$pdo = db();
$error = null;

if (is_post()) {
  csrf_verify();

  // Συλλογή δεδομένων φόρμας
  $regType = $_POST['type'] ?? 'user';
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';
  $phone = trim($_POST['phone'] ?? '');

  // Συλλογή συγκατάθεσης GDPR
  $privacyConsent = isset($_POST['privacy_consent']) ? 1 : 0;
  $dataProcessingConsent = isset($_POST['data_processing_consent']) ? 1 : 0;
  $marketingConsent = isset($_POST['marketing_consent']) ? 1 : 0;
  $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
  $now = date('Y-m-d H:i:s');

  // Επικύρωση δεδομένων εισόδου
  if (mb_strlen($name, 'UTF-8') < 3)
    $error = "Το όνομα πρέπει να έχει τουλάχιστον 3 χαρακτήρες.";
  elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
    $error = "Μη έγκυρο email.";
  elseif (mb_strlen($pass, 'UTF-8') < 8)
    $error = "Ο κωδικός πρέπει να έχει τουλάχιστον 8 χαρακτήρες.";
  elseif (!$privacyConsent)
    $error = "Πρέπει να αποδεχτείτε την Πολιτική Απορρήτου και τους Όρους Χρήσης.";
  elseif (!$dataProcessingConsent)
    $error = "Πρέπει να συναινέσετε στην επεξεργασία των δεδομένων σας.";

  if (!$error) {
    if ($regType === 'org') {
      // Εγγραφή οργανισμού
      $website = trim($_POST['website'] ?? '');
      $desc = trim($_POST['description'] ?? '');

      // Έλεγχος αν υπάρχει ήδη το email
      $st = $pdo->prepare("SELECT id FROM organizations WHERE email=:e");
      $st->execute([':e' => $email]);
      if ($st->fetch())
        $error = "Υπάρχει ήδη οργανισμός με αυτό το email.";

      if (!$error) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        // Εισαγωγή οργανισμού με καταγραφή συγκατάθεσης GDPR
        $ins = $pdo->prepare("
                    INSERT INTO organizations (name, email, password_hash, phone, website, description,
                                               privacy_consent, privacy_consent_at, terms_consent, terms_consent_at, ip_address) 
                    VALUES (:n, :e, :h, :p, :w, :d, :pc, :pca, :tc, :tca, :ip)
                ");
        $ins->execute([
          ':n' => $name,
          ':e' => $email,
          ':h' => $hash,
          ':p' => ($phone === '' ? null : $phone),
          ':w' => ($website === '' ? null : $website),
          ':d' => ($desc === '' ? null : $desc),
          ':pc' => $privacyConsent,
          ':pca' => $privacyConsent ? $now : null,
          ':tc' => $privacyConsent,
          ':tca' => $privacyConsent ? $now : null,
          ':ip' => $ipAddress,
        ]);
        $id = (int) $pdo->lastInsertId();
        login_org(['id' => $id, 'name' => $name, 'email' => $email, 'verified' => 0]);
        redirect(BASE_URL . "/dashboard/index.php");
      }
    } else {
      // Εγγραφή χρήστη
      $st = $pdo->prepare("SELECT id FROM users WHERE email=:e");
      $st->execute([':e' => $email]);
      if ($st->fetch())
        $error = "Υπάρχει ήδη λογαριασμός με αυτό το email.";

      if (!$error) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        // Εισαγωγή χρήστη με καταγραφή συγκατάθεσης GDPR
        $ins = $pdo->prepare("
                    INSERT INTO users (name, email, password_hash, phone,
                                       privacy_consent, privacy_consent_at, 
                                       data_processing_consent, data_processing_consent_at,
                                       marketing_consent, marketing_consent_at, ip_address) 
                    VALUES (:n, :e, :h, :p, :pc, :pca, :dpc, :dpca, :mc, :mca, :ip)
                ");
        $ins->execute([
          ':n' => $name,
          ':e' => $email,
          ':h' => $hash,
          ':p' => ($phone === '' ? null : $phone),
          ':pc' => $privacyConsent,
          ':pca' => $privacyConsent ? $now : null,
          ':dpc' => $dataProcessingConsent,
          ':dpca' => $dataProcessingConsent ? $now : null,
          ':mc' => $marketingConsent,
          ':mca' => $marketingConsent ? $now : null,
          ':ip' => $ipAddress,
        ]);
        $id = (int) $pdo->lastInsertId();
        login_user(['id' => $id, 'name' => $name, 'email' => $email, 'id_verified' => 0]);
        redirect(BASE_URL . "/my-campaigns.php");
      }
    }
  }
}
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-card-wrapper wide">
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
        <?php echo $type === 'org' ? 'Εγγραφή Οργανισμού' : 'Δημιουργία Λογαριασμού'; ?>
      </h1>
      <p style="color: var(--text-secondary); font-size: 15px; margin: 0;">
        <?php echo $type === 'org' ? 'Για ΜΚΟ και οργανισμούς' : 'Ξεκινήστε τον δικό σας έρανο'; ?>
      </p>
    </div>

    <?php if ($error): ?>
      <div class="notice warn" style="margin-bottom: 24px; text-align: left;"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post" class="js-validate">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="type" value="<?php echo e($type); ?>">

      <div class="row" style="margin-bottom: 20px;">
        <div style="flex: 1;">
          <label style="margin-top: 0;"><?php echo $type === 'org' ? 'Όνομα Οργανισμού' : 'Ονοματεπώνυμο'; ?> *</label>
          <input name="name" required minlength="3" value="<?php echo e($_POST['name'] ?? ''); ?>"
            placeholder="<?php echo $type === 'org' ? 'π.χ. Σύλλογος Αλληλεγγύης' : 'π.χ. Μαρία Παπαδοπούλου'; ?>"
            data-error="Το όνομα πρέπει να έχει τουλάχιστον 3 χαρακτήρες." style="background: var(--bg-gray);">
        </div>
      </div>

      <div class="row" style="margin-bottom: 20px;">
        <div style="flex: 1;">
          <label style="margin-top: 0;">Email *</label>
          <input name="email" type="email" required value="<?php echo e($_POST['email'] ?? ''); ?>"
            placeholder="email@example.com" data-error="Συμπληρώστε έγκυρο email." style="background: var(--bg-gray);">
        </div>
        <div style="flex: 1;">
          <label style="margin-top: 0;">Τηλέφωνο</label>
          <input name="phone" type="tel" value="<?php echo e($_POST['phone'] ?? ''); ?>" placeholder="+30 694..."
            style="background: var(--bg-gray);">
        </div>
      </div>

      <div style="margin-bottom: 24px;">
        <label style="margin-top: 0;">Κωδικός *</label>
        <input name="password" type="password" required minlength="8" placeholder="Τουλάχιστον 8 χαρακτήρες"
          data-error="Ο κωδικός πρέπει να έχει τουλάχιστον 8 χαρακτήρες." style="background: var(--bg-gray);">
      </div>

      <?php if ($type === 'org'): ?>
        <div style="margin-bottom: 20px;">
          <label style="margin-top: 0;">Website</label>
          <input name="website" type="url" value="<?php echo e($_POST['website'] ?? ''); ?>" placeholder="https://..."
            data-error="Το URL του website δεν είναι έγκυρο." style="background: var(--bg-gray);">
        </div>

        <div style="margin-bottom: 24px;">
          <label style="margin-top: 0;">Περιγραφή</label>
          <textarea name="description" placeholder="Λίγα λόγια για τον οργανισμό..."
            style="background: var(--bg-gray); min-height: 100px;"><?php echo e($_POST['description'] ?? ''); ?></textarea>
        </div>
      <?php endif; ?>

      <div class="hr" style="margin: 32px 0;"></div>

      <div class="consent-section"
        style="background: rgba(46, 64, 54, 0.03); border: 1px solid rgba(46, 64, 54, 0.1); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 24px;">
        <label class="checkbox-label"
          style="display: flex; gap: 12px; margin: 0 0 16px; cursor: pointer; align-items: flex-start;">
          <input type="checkbox" name="privacy_consent" value="1" <?php echo isset($_POST['privacy_consent']) ? 'checked' : ''; ?> required data-error="Πρέπει να αποδεχτείτε την Πολιτική Απορρήτου και τους Όρους Χρήσης."
            style="width: 20px; height: 20px; margin-top: 2px;">
          <span style="font-size: 14px; font-weight: 400; color: var(--text-secondary); line-height: 1.5;">
            Έχω διαβάσει και αποδέχομαι την <a href="<?php echo BASE_URL; ?>/privacy.php" target="_blank"
              style="color: var(--primary); font-weight: 500;">Πολιτική
              Απορρήτου</a>
            και τους <a href="<?php echo BASE_URL; ?>/terms.php" target="_blank"
              style="color: var(--primary); font-weight: 500;">Όρους Χρήσης</a>. <span style="color: #dc3545;">*</span>
          </span>
        </label>

        <label class="checkbox-label"
          style="display: flex; gap: 12px; margin: 0 0 16px; cursor: pointer; align-items: flex-start;">
          <input type="checkbox" name="data_processing_consent" value="1" <?php echo isset($_POST['data_processing_consent']) ? 'checked' : ''; ?> required
            data-error="Πρέπει να συναινέσετε στην επεξεργασία των δεδομένων."
            style="width: 20px; height: 20px; margin-top: 2px;">
          <span style="font-size: 14px; font-weight: 400; color: var(--text-secondary); line-height: 1.5;">
            Συναινώ στην επεξεργασία των προσωπικών μου δεδομένων για τη δημιουργία και διαχείριση του λογαριασμού μου
            σύμφωνα με τον <a href="https://eur-lex.europa.eu/eli/reg/2016/679/oj" target="_blank" rel="noopener"
              style="color: var(--primary); font-weight: 500;">Κανονισμό (ΕΕ) 2016/679 (GDPR)</a>. <span
              style="color: #dc3545;">*</span>
          </span>
        </label>

        <label class="checkbox-label"
          style="display: flex; gap: 12px; margin: 0; cursor: pointer; align-items: flex-start;">
          <input type="checkbox" name="marketing_consent" value="1" <?php echo isset($_POST['marketing_consent']) ? 'checked' : ''; ?> style="width: 20px; height: 20px; margin-top: 2px;">
          <span style="font-size: 14px; font-weight: 400; color: var(--text-secondary); line-height: 1.5;">
            Επιθυμώ να λαμβάνω ενημερώσεις και ειδοποιήσεις σχετικά με νέους εράνους και δράσεις. <span
              style="color: var(--text-muted);">(προαιρετικό)</span>
          </span>
        </label>
      </div>

      <button class="btn primary" type="submit" style="width: 100%; border-radius: var(--radius-pill); padding: 16px;">
        <?php echo $type === 'org' ? 'Δημιουργία Λογαριασμού' : 'Εγγραφή'; ?>
      </button>
    </form>

    <div class="hr" style="margin: 32px 0;"></div>

    <p style="text-align: center; color: var(--text-secondary); font-size: 14px; margin: 0 0 16px;">Έχετε ήδη
      λογαριασμό;</p>
    <a class="btn" href="<?php echo BASE_URL; ?>/login.php?type=<?php echo e($type); ?>"
      style="width: 100%; justify-content: center; border-radius: var(--radius-pill);">Σύνδεση</a>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>