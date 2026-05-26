<?php
// Αναφορά εράνου — φόρμα αναφοράς ύποπτων/ακατάλληλων εράνων

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/header.php';

$pdo = db();
$campaignId = (int) ($_GET['campaign_id'] ?? 0);

// Ανάκτηση στοιχείων εράνου
$stmt = $pdo->prepare("SELECT id, title FROM campaigns WHERE id = :id");
$stmt->execute([':id' => $campaignId]);
$campaign = $stmt->fetch();

if (!$campaign) {
  redirect(BASE_URL . "/explore.php");
}

$sent = false;
$error = null;

if (is_post()) {
  csrf_verify();

  // Συλλογή δεδομένων αναφοράς
  $email = trim($_POST['email'] ?? '');
  $reason = $_POST['reason'] ?? '';
  $description = trim($_POST['description'] ?? '');

  // Δεδομένα συγκατάθεσης GDPR
  $privacyConsent = isset($_POST['privacy_consent']) ? 1 : 0;
  $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

  // Επικύρωση δεδομένων
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Εισάγετε έγκυρο email.";
  } elseif (!in_array($reason, ['scam', 'fake_info', 'inappropriate', 'other'])) {
    $error = "Επιλέξτε λόγο αναφοράς.";
  } elseif (!$privacyConsent) {
    $error = "Πρέπει να αποδεχτείτε την Πολιτική Απορρήτου.";
  } else {
    // Εισαγωγή αναφοράς με καταγραφή συγκατάθεσης GDPR
    $ins = $pdo->prepare("INSERT INTO reports (campaign_id, reporter_email, reason, description, privacy_consent, ip_address) VALUES (:cid, :email, :reason, :desc, :consent, :ip)");
    $ins->execute([
      ':cid' => $campaignId,
      ':email' => $email,
      ':reason' => $reason,
      ':desc' => ($description === '' ? null : $description),
      ':consent' => $privacyConsent,
      ':ip' => $ipAddress,
    ]);
    $sent = true;

    // Καταγραφή αναφοράς στο αρχείο επεξεργασίας (Άρθρο 30 GDPR)
    $reportId = (int) $pdo->lastInsertId();
    logDataProcessing($pdo, 'campaign', $campaignId, 'create', 'user', 0, 'Υποβολή αναφοράς #' . $reportId . ' για έρανο #' . $campaignId);
  }
}
?>

<div style="max-width: 500px; margin: 40px auto; padding: 0 24px;">
  <!-- Breadcrumb -->
  <nav class="breadcrumb">
    <a href="<?php echo BASE_URL; ?>/">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
        <polyline points="9 22 9 12 15 12 15 22" />
      </svg>
      Αρχική
    </a>
    <span class="breadcrumb-separator">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="9 18 15 12 9 6" />
      </svg>
    </span>
    <a href="<?php echo BASE_URL; ?>/campaign.php?id=<?php echo $campaignId; ?>">
      Έρανος
    </a>
    <span class="breadcrumb-separator">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="9 18 15 12 9 6" />
      </svg>
    </span>
    <span class="breadcrumb-current">Αναφορά</span>
  </nav>

  <div class="card">
    <div style="text-align: center; margin-bottom: 24px;">
      <div style="margin-bottom: 12px; color: #dc3545;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" />
          <line x1="4" y1="22" x2="4" y2="15" />
        </svg>
      </div>
      <h1 style="margin: 0 0 8px;">Αναφορά Εράνου</h1>
      <p class="muted" style="margin: 0;">
        "<?php echo e(mb_strimwidth($campaign['title'], 0, 50, '...', 'UTF-8')); ?>"
      </p>
    </div>

    <?php if ($sent): ?>
      <div class="notice ok" style="text-align: center; padding: 24px;">
        <div style="margin-bottom: 12px; color: var(--primary);">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12" />
          </svg>
        </div>
        <strong>Η αναφορά σας υποβλήθηκε!</strong>
        <p class="small muted" style="margin: 12px 0 0;">Θα εξετάσουμε την αναφορά σας το συντομότερο.</p>
      </div>
      <a class="btn" href="<?php echo BASE_URL; ?>/campaign.php?id=<?php echo $campaignId; ?>"
        style="width: 100%; margin-top: 16px; justify-content: center;">
        ← Επιστροφή στον έρανο
      </a>
    <?php else: ?>
      <?php if ($error): ?>
        <div class="notice warn" style="margin-bottom: 20px;"><?php echo e($error); ?></div>
      <?php endif; ?>

      <form method="post">
        <?php echo csrf_field(); ?>

        <label>Το email σας *</label>
        <input name="email" type="email" required placeholder="email@example.com"
          value="<?php echo e($_POST['email'] ?? ''); ?>">
        <p class="small muted" style="margin-top: 4px;">Για να σας ενημερώσουμε για την εξέλιξη</p>

        <label>Λόγος αναφοράς *</label>
        <select name="reason" required>
          <option value="">Επιλέξτε...</option>
          <option value="scam" <?php echo ($_POST['reason'] ?? '') === 'scam' ? 'selected' : ''; ?>>Απάτη / Scam</option>
          <option value="fake_info" <?php echo ($_POST['reason'] ?? '') === 'fake_info' ? 'selected' : ''; ?>>Ψευδή στοιχεία
          </option>
          <option value="inappropriate" <?php echo ($_POST['reason'] ?? '') === 'inappropriate' ? 'selected' : ''; ?>>
            Ακατάλληλο περιεχόμενο</option>
          <option value="other" <?php echo ($_POST['reason'] ?? '') === 'other' ? 'selected' : ''; ?>>Άλλο</option>
        </select>

        <label>Περιγραφή (προαιρετικό)</label>
        <textarea name="description"
          placeholder="Εξηγήστε γιατί πιστεύετε ότι αυτός ο έρανος πρέπει να ελεγχθεί..."><?php echo e($_POST['description'] ?? ''); ?></textarea>

        <div class="consent-section" style="margin-top: 16px;">
          <label class="checkbox-label">
            <input type="checkbox" name="privacy_consent" value="1" <?php echo isset($_POST['privacy_consent']) ? 'checked' : ''; ?> required>
            <span>
              Αποδέχομαι την <a href="<?php echo BASE_URL; ?>/privacy.php" target="_blank">Πολιτική Απορρήτου</a>
              και συναινώ στην επεξεργασία του email μου για την εξέταση της αναφοράς. *
            </span>
          </label>
        </div>

        <button class="btn primary" type="submit" style="width: 100%; margin-top: 20px;">Υποβολή Αναφοράς</button>

        <a class="btn" href="<?php echo BASE_URL; ?>/campaign.php?id=<?php echo $campaignId; ?>"
          style="width: 100%; margin-top: 12px; justify-content: center;">Ακύρωση</a>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>