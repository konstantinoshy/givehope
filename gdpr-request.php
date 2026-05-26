<?php
// Φόρμα αιτημάτων GDPR (πρόσβαση, εξαγωγή, διαγραφή, διόρθωση)

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/header.php';

$pdo = db();
$currentUser = current_user();
$currentOrg = current_org();

$submitted = false;
$error = null;

if (is_post()) {
  csrf_verify();

  $type = $_POST['request_type'] ?? '';
  $email = trim($_POST['email'] ?? '');
  $name = trim($_POST['name'] ?? '');
  $notes = trim($_POST['notes'] ?? '');
  $consent = isset($_POST['privacy_consent']) ? 1 : 0;

  // Έγκυροι τύποι αιτημάτων GDPR
  $validTypes = ['access', 'export', 'delete', 'rectification'];

  // Επικύρωση δεδομένων εισόδου
  if (!in_array($type, $validTypes)) {
    $error = "Επιλέξτε έγκυρο τύπο αιτήματος.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Εισάγετε έγκυρο email.";
  } elseif (!$consent) {
    $error = "Πρέπει να αποδεχτείτε την επεξεργασία του αιτήματος.";
  } else {
    // Προσδιορισμός τύπου αιτούντος για σωστή διαχείριση του αιτήματος
    $requesterType = 'visitor';
    $requesterId = null;

    if ($currentUser) {
      $requesterType = 'user';
      $requesterId = $currentUser['id'];
    } elseif ($currentOrg) {
      $requesterType = 'organization';
      $requesterId = $currentOrg['id'];
    } else {
      // Αναζήτηση email σε χρήστες και οργανισμούς για ταυτοποίηση
      $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :e");
      $stmt->execute([':e' => $email]);
      if ($user = $stmt->fetch()) {
        $requesterType = 'user';
        $requesterId = $user['id'];
      } else {
        $stmt = $pdo->prepare("SELECT id FROM organizations WHERE email = :e");
        $stmt->execute([':e' => $email]);
        if ($org = $stmt->fetch()) {
          $requesterType = 'organization';
          $requesterId = $org['id'];
        } else {
          // Έλεγχος αν το email υπάρχει στις δωρεές
          $stmt = $pdo->prepare("SELECT id FROM donations WHERE donor_email = :e LIMIT 1");
          $stmt->execute([':e' => $email]);
          if ($stmt->fetch()) {
            $requesterType = 'donor';
          }
        }
      }
    }

    // Δημιουργία token επαλήθευσης για ασφάλεια
    $token = bin2hex(random_bytes(32));

    $ins = $pdo->prepare("
            INSERT INTO gdpr_requests (type, requester_type, requester_id, requester_email, requester_name, verification_token, notes, ip_address)
            VALUES (:type, :rtype, :rid, :email, :name, :token, :notes, :ip)
        ");

    $ins->execute([
      ':type' => $type,
      ':rtype' => $requesterType,
      ':rid' => $requesterId,
      ':email' => $email,
      ':name' => ($name === '' ? null : $name),
      ':token' => $token,
      ':notes' => ($notes === '' ? null : $notes),
      ':ip' => $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    $requestId = (int) $pdo->lastInsertId();
    $actorTypeLog = 'system';
    $actorIdLog = null;
    if ($currentUser) {
      $actorTypeLog = 'user';
      $actorIdLog = (int) $currentUser['id'];
    } elseif ($currentOrg) {
      $actorTypeLog = 'organization';
      $actorIdLog = (int) $currentOrg['id'];
    }
    $typeLabels = [
      'access' => 'Πρόσβαση',
      'export' => 'Εξαγωγή',
      'delete' => 'Διαγραφή',
      'rectification' => 'Διόρθωση',
    ];
    $typeLabel = $typeLabels[$type] ?? $type;
    logDataProcessing($pdo, 'gdpr_request', $requestId, 'create', $actorTypeLog, $actorIdLog, 'Αίτημα GDPR: ' . $typeLabel);

    $submitted = true;
  }
}

$requestTypes = [
  'access' => [
    'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--moss)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
    'title' => 'Πρόσβαση στα Δεδομένα',
    'description' => 'Λήψη αντιγράφου όλων των δεδομένων που έχουμε για εσάς (Άρθρο 15 GDPR)'
  ],
  'export' => [
    'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--moss)" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
    'title' => 'Εξαγωγή Δεδομένων',
    'description' => 'Λήψη των δεδομένων σας σε μηχαναγνώσιμη μορφή (Άρθρο 20 GDPR - Φορητότητα)'
  ],
  'delete' => [
    'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--moss)" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>',
    'title' => 'Διαγραφή Δεδομένων',
    'description' => 'Διαγραφή όλων των δεδομένων σας (Άρθρο 17 GDPR - Δικαίωμα στη Λήθη)'
  ],
  'rectification' => [
    'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--moss)" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
    'title' => 'Διόρθωση Δεδομένων',
    'description' => 'Διόρθωση ανακριβών δεδομένων (Άρθρο 16 GDPR)'
  ]
];
?>

<div style="max-width: 700px; margin: 120px auto 60px; padding: 0 24px;">
  <div class="card">
    <div style="text-align: center; margin-bottom: 24px;">
      <div style="font-size: 48px; margin-bottom: 12px;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--moss)" stroke-width="2">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
          <path d="M7 11V7a5 5 0 0 1 10 0v4" />
        </svg>
      </div>
      <h1 style="margin: 0 0 8px;">Αίτημα GDPR</h1>
      <p class="muted" style="margin: 0;">Ασκήστε τα δικαιώματά σας σύμφωνα με τον Κανονισμό (ΕΕ) 2016/679</p>
    </div>

    <?php if ($submitted): ?>
      <div style="text-align: center; padding: 24px 0;">
        <div style="font-size: 64px; margin-bottom: 16px;">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
            <polyline points="22 4 12 14.01 9 11.01" />
          </svg>
        </div>
        <h2 style="margin: 0 0 12px;">Το αίτημά σας υποβλήθηκε</h2>
        <p class="muted" style="margin: 0 0 24px;">Θα λάβετε email επιβεβαίωσης στο
          <strong><?php echo e($_POST['email']); ?></strong>
        </p>

        <div class="notice" style="text-align: left; margin: 24px 0;">
          <h4 style="margin: 0 0 8px;">Επόμενα βήματα:</h4>
          <ol style="margin: 0; padding-left: 20px;">
            <li>Ελέγξτε το email σας για σύνδεσμο επιβεβαίωσης</li>
            <li>Κάντε κλικ στον σύνδεσμο για να επαληθεύσετε το αίτημα</li>
            <li>Θα επεξεργαστούμε το αίτημα εντός <strong>30 ημερών</strong></li>
          </ol>
        </div>

        <a href="<?php echo BASE_URL; ?>/index.php" class="btn primary">← Επιστροφή στην Αρχική</a>
      </div>
    <?php else: ?>

      <?php if ($error): ?>
        <div class="notice warn" style="margin-bottom: 20px;"><?php echo e($error); ?></div>
      <?php endif; ?>

      <form method="post">
        <?php echo csrf_field(); ?>

        <h3 style="margin: 0 0 16px;">1. Επιλέξτε τύπο αιτήματος</h3>
        <div class="gdpr-options">
          <?php foreach ($requestTypes as $key => $info): ?>
            <label class="gdpr-option">
              <input type="radio" name="request_type" value="<?php echo $key; ?>" <?php echo ($_POST['request_type'] ?? '') === $key ? 'checked' : ''; ?> required>
              <div class="gdpr-option-content">
                <span class="gdpr-option-icon"><?php echo $info['icon']; ?></span>
                <div>
                  <strong><?php echo $info['title']; ?></strong>
                  <p class="small muted"><?php echo $info['description']; ?></p>
                </div>
              </div>
            </label>
          <?php endforeach; ?>
        </div>

        <div class="hr"></div>

        <h3 style="margin: 0 0 16px;">2. Στοιχεία επικοινωνίας</h3>

        <?php if ($currentUser): ?>
          <div class="notice" style="margin-bottom: 16px;">
            Συνδεδεμένος ως: <strong><?php echo e($currentUser['name']); ?></strong>
            (<?php echo e($currentUser['email']); ?>)
          </div>
          <input type="hidden" name="email" value="<?php echo e($currentUser['email']); ?>">
          <input type="hidden" name="name" value="<?php echo e($currentUser['name']); ?>">
        <?php elseif ($currentOrg): ?>
          <div class="notice" style="margin-bottom: 16px;">
            Συνδεδεμένος ως: <strong><?php echo e($currentOrg['name']); ?></strong> (<?php echo e($currentOrg['email']); ?>)
          </div>
          <input type="hidden" name="email" value="<?php echo e($currentOrg['email']); ?>">
          <input type="hidden" name="name" value="<?php echo e($currentOrg['name']); ?>">
        <?php else: ?>
          <label>Email *</label>
          <input type="email" name="email" required value="<?php echo e($_POST['email'] ?? ''); ?>"
            placeholder="Το email που χρησιμοποιήσατε στην πλατφόρμα">

          <label>Ονοματεπώνυμο</label>
          <input type="text" name="name" value="<?php echo e($_POST['name'] ?? ''); ?>"
            placeholder="Για ταυτοποίηση του αιτήματος">
        <?php endif; ?>

        <label>Πρόσθετες πληροφορίες (προαιρετικό)</label>
        <textarea name="notes"
          placeholder="Περιγράψτε τυχόν συγκεκριμένες απαιτήσεις..."><?php echo e($_POST['notes'] ?? ''); ?></textarea>

        <div class="hr"></div>

        <h3 style="margin: 0 0 16px;">3. Συγκατάθεση</h3>

        <label class="checkbox-label">
          <input type="checkbox" name="privacy_consent" value="1" <?php echo isset($_POST['privacy_consent']) ? 'checked' : ''; ?> required>
          <span>
            Κατανοώ ότι το αίτημά μου θα υποβληθεί σε επεξεργασία σύμφωνα με την
            <a href="<?php echo BASE_URL; ?>/privacy.php" target="_blank">Πολιτική Απορρήτου</a>
            και ότι μπορεί να απαιτηθεί επαλήθευση ταυτότητας. *
          </span>
        </label>

        <button type="submit" class="btn primary" style="width: 100%; margin-top: 24px;">
          Υποβολή Αιτήματος
        </button>

        <p class="small muted" style="text-align: center; margin-top: 16px;">
          Θα απαντήσουμε εντός 30 ημερών σύμφωνα με τον GDPR.<br>
          Για επείγοντα αιτήματα, επικοινωνήστε: privacy@givehope.gr
        </p>
      </form>
    <?php endif; ?>
  </div>

  <div class="card" style="margin-top: 24px;">
    <h3 style="margin: 0 0 12px; display: flex; align-items: center; gap: 8px;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--moss)" stroke-width="2">
        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
      </svg>
      Τα δικαιώματά σας
    </h3>
    <p class="muted" style="margin: 0 0 16px;">Σύμφωνα με τον Γενικό Κανονισμό Προστασίας Δεδομένων (GDPR):</p>
    <ul style="margin: 0; padding-left: 20px; color: var(--text-secondary);">
      <li><strong>Άρθρο 15:</strong> Δικαίωμα πρόσβασης</li>
      <li><strong>Άρθρο 16:</strong> Δικαίωμα διόρθωσης</li>
      <li><strong>Άρθρο 17:</strong> Δικαίωμα διαγραφής («δικαίωμα στη λήθη»)</li>
      <li><strong>Άρθρο 18:</strong> Δικαίωμα περιορισμού της επεξεργασίας</li>
      <li><strong>Άρθρο 20:</strong> Δικαίωμα φορητότητας δεδομένων</li>
      <li><strong>Άρθρο 21:</strong> Δικαίωμα εναντίωσης</li>
    </ul>
    <p class="small muted" style="margin: 16px 0 0;">
      <a href="https://eur-lex.europa.eu/eli/reg/2016/679/oj" target="_blank" rel="noopener">Διαβάστε τον πλήρη
        Κανονισμό →</a>
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>