<?php
// Διαχείριση προσωπικών δεδομένων — προβολή, επεξεργασία, εξαγωγή, διαγραφή

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

// Η σελίδα λειτουργεί για χρήστες και οργανισμούς
$currentUser = current_user();
$currentOrg = current_org();

if (!$currentUser && !$currentOrg) {
  redirect(BASE_URL . "/login.php");
}

$pdo = db();
$isUser = $currentUser !== null;
$entityType = $isUser ? 'user' : 'organization';
$entityId = $isUser ? $currentUser['id'] : $currentOrg['id'];
$entityEmail = $isUser ? $currentUser['email'] : $currentOrg['email'];

$success = null;
$error = null;

// POST actions (πριν το header.php)
if (is_post() && isset($_POST['action'])) {
  csrf_verify();

  // Εξαγωγή δεδομένων
  if ($_POST['action'] === 'export') {
    $exportData = generateDataExport($pdo, $entityType, $entityId);
    logDataProcessing($pdo, $entityType, $entityId, 'export', $entityType, $entityId);

    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="my-data-' . date('Y-m-d') . '.json"');
    echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
  }

  // Επεξεργασία προφίλ
  if ($_POST['action'] === 'update_profile') {
    $newName = trim($_POST['name'] ?? '');
    $newPhone = trim($_POST['phone'] ?? '');
    $newEmail = trim($_POST['new_email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $currentPassword = $_POST['current_password'] ?? '';

    // Επικύρωση
    if (mb_strlen($newName, 'UTF-8') < 3) {
      $error = "Το όνομα πρέπει να έχει τουλάχιστον 3 χαρακτήρες.";
    } elseif ($newEmail && !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
      $error = "Μη έγκυρο email.";
    } elseif ($newPassword && mb_strlen($newPassword, 'UTF-8') < 8) {
      $error = "Ο νέος κωδικός πρέπει να έχει τουλάχιστον 8 χαρακτήρες.";
    } elseif ($newPassword && $newPassword !== $confirmPassword) {
      $error = "Οι κωδικοί δεν ταιριάζουν.";
    } else {
      // Επαλήθευση τρέχοντος κωδικού αν αλλάζει email ή κωδικός
      if ($newEmail || $newPassword) {
        if ($isUser) {
          $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
        } else {
          $stmt = $pdo->prepare("SELECT password_hash FROM organizations WHERE id = :id");
        }
        $stmt->execute([':id' => $entityId]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($currentPassword, $hash)) {
          $error = "Ο τρέχων κωδικός είναι λάθος.";
        }
      }

      if (!$error) {
        // Δημιουργία ερωτήματος ενημέρωσης
        $fields = [];
        $params = [':id' => $entityId];

        $fields[] = "name = :name";
        $params[':name'] = $newName;

        $fields[] = "phone = :phone";
        $params[':phone'] = ($newPhone === '' ? null : $newPhone);

        if ($newEmail) {
          $fields[] = "email = :email";
          $params[':email'] = $newEmail;
        }

        if ($newPassword) {
          $fields[] = "password_hash = :pass";
          $params[':pass'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $table = $isUser ? 'users' : 'organizations';
        $sql = "UPDATE $table SET " . implode(', ', $fields) . " WHERE id = :id";
        $pdo->prepare($sql)->execute($params);

        // Ενημέρωση session
        if ($isUser) {
          $_SESSION['user']['name'] = $newName;
          if ($newEmail)
            $_SESSION['user']['email'] = $newEmail;
        } else {
          $_SESSION['org']['name'] = $newName;
          if ($newEmail)
            $_SESSION['org']['email'] = $newEmail;
        }

        logDataProcessing($pdo, $entityType, $entityId, 'update', $entityType, $entityId, 'Ενημέρωση προφίλ');
        $success = "Τα στοιχεία σας ενημερώθηκαν επιτυχώς.";
      }
    }
  }

  // Διαγραφή λογαριασμού
  if ($_POST['action'] === 'delete') {
    $confirmEmail = trim($_POST['confirm_email'] ?? '');
    $confirmText = trim($_POST['confirm_text'] ?? '');

    if ($confirmEmail !== $entityEmail) {
      $error = "Το email δεν ταιριάζει με το email του λογαριασμού σας.";
    } elseif ($confirmText !== 'ΔΙΑΓΡΑΦΗ') {
      $error = "Πληκτρολογήστε ΔΙΑΓΡΑΦΗ για επιβεβαίωση.";
    } else {
      $token = bin2hex(random_bytes(32));
      $ins = $pdo->prepare("
                INSERT INTO gdpr_requests (type, requester_type, requester_id, requester_email, verification_token, status, ip_address)
                VALUES ('delete', :type, :id, :email, :token, 'verified', :ip)
            ");
      $ins->execute([
        ':type' => $entityType,
        ':id' => $entityId,
        ':email' => $entityEmail,
        ':token' => $token,
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? null
      ]);

      if ($isUser) {
        $pdo->prepare("UPDATE users SET deleted_at = NOW() WHERE id = :id")->execute([':id' => $entityId]);
        // FIX: Suspend all campaigns belonging to this user
        $pdo->prepare("UPDATE campaigns SET status = 'suspended' WHERE user_id = :id")->execute([':id' => $entityId]);
      } else {
        $pdo->prepare("UPDATE organizations SET deleted_at = NOW() WHERE id = :id")->execute([':id' => $entityId]);
        // FIX: Suspend all campaigns belonging to this organization
        $pdo->prepare("UPDATE campaigns SET status = 'suspended' WHERE org_id = :id")->execute([':id' => $entityId]);
      }

      logDataProcessing($pdo, $entityType, $entityId, 'delete', $entityType, $entityId, 'Αίτηση διαγραφής λογαριασμού (Καμπάνιες σε αναστολή)');

      session_destroy();
      redirect(BASE_URL . "/index.php?deleted=1");
    }
  }
}

// Βοηθητικές συναρτήσεις

function generateDataExport($pdo, $entityType, $entityId)
{
  $export = [
    'export_date' => date('c'),
    'export_type' => 'Εξαγωγή Δεδομένων GDPR (Άρθρο 20)',
    'entity_type' => $entityType,
  ];

  if ($entityType === 'user') {
    $stmt = $pdo->prepare("SELECT id, name, email, phone, id_verified, created_at FROM users WHERE id = :id");
    $stmt->execute([':id' => $entityId]);
    $export['account'] = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT id, title, description, story, category_id, type, target_amount, current_amount, status, created_at FROM campaigns WHERE user_id = :id");
    $stmt->execute([':id' => $entityId]);
    $export['campaigns'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $campaignIds = array_column($export['campaigns'], 'id');
    if (!empty($campaignIds)) {
      $placeholders = implode(',', array_fill(0, count($campaignIds), '?'));
      $stmt = $pdo->prepare("SELECT id, campaign_id, donor_name, amount, message, is_anonymous, created_at FROM donations WHERE campaign_id IN ($placeholders)");
      $stmt->execute($campaignIds);
      $export['donations_received'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
      $export['donations_received'] = [];
    }
  } else {
    $stmt = $pdo->prepare("SELECT id, name, email, phone, website, description, verified, created_at FROM organizations WHERE id = :id");
    $stmt->execute([':id' => $entityId]);
    $export['account'] = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT id, title, description, story, category_id, type, target_amount, current_amount, status, created_at FROM campaigns WHERE org_id = :id");
    $stmt->execute([':id' => $entityId]);
    $export['campaigns'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $campaignIds = array_column($export['campaigns'], 'id');
    if (!empty($campaignIds)) {
      $placeholders = implode(',', array_fill(0, count($campaignIds), '?'));
      $stmt = $pdo->prepare("SELECT id, campaign_id, donor_name, amount, message, is_anonymous, created_at FROM donations WHERE campaign_id IN ($placeholders)");
      $stmt->execute($campaignIds);
      $export['donations_received'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
      $export['donations_received'] = [];
    }
  }

  return $export;
}

// Ανάκτηση δεδομένων
// Ανανέωση δεδομένων μετά από πιθανό update
$currentUser = current_user();
$currentOrg = current_org();
$entityEmail = $isUser ? ($currentUser['email'] ?? $entityEmail) : ($currentOrg['email'] ?? $entityEmail);

if ($isUser) {
  $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
  $stmt->execute([':id' => $entityId]);
  $account = $stmt->fetch();

  $stmt = $pdo->prepare("SELECT COUNT(*) FROM campaigns WHERE user_id = :id");
  $stmt->execute([':id' => $entityId]);
  $campaignCount = (int) $stmt->fetchColumn();
} else {
  $stmt = $pdo->prepare("SELECT * FROM organizations WHERE id = :id");
  $stmt->execute([':id' => $entityId]);
  $account = $stmt->fetch();

  $stmt = $pdo->prepare("SELECT COUNT(*) FROM campaigns WHERE org_id = :id");
  $stmt->execute([':id' => $entityId]);
  $campaignCount = (int) $stmt->fetchColumn();
}

// Φόρτωση header ΜΟΝΟ εδώ (μετά τη λογική POST)
require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 800px; margin: 0 auto; padding: 32px 24px;">
  <div style="margin-bottom: 24px;">
    <a href="<?php echo BASE_URL; ?>/<?php echo $isUser ? 'my-campaigns.php' : 'dashboard/index.php'; ?>" class="muted"
      style="font-size: 14px;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        style="vertical-align: -2px;">
        <path d="M19 12H5M12 19l-7-7 7-7" />
      </svg>
      Πίσω
    </a>
    <h1 style="margin: 12px 0 8px;">Τα Δεδομένα μου</h1>
    <p class="muted">Διαχειριστείτε τα προσωπικά σας δεδομένα σύμφωνα με τον Κανονισμό (ΕΕ) 2016/679 (GDPR)</p>
  </div>

  <?php if ($success): ?>
    <div class="notice ok" style="margin-bottom: 20px;"><?php echo e($success); ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="notice warn" style="margin-bottom: 20px;"><?php echo e($error); ?></div>
  <?php endif; ?>

  <!-- ============ ΕΠΕΞΕΡΓΑΣΙΑ ΠΡΟΦΙΛ (Άρθρο 16) ============ -->
  <div class="card" style="margin-bottom: 24px;">
    <h2 style="margin: 0 0 8px; display: flex; align-items: center; gap: 10px;">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
        <circle cx="12" cy="7" r="4" />
      </svg>
      Στοιχεία Λογαριασμού
    </h2>
    <p class="small muted" style="margin: 0 0 20px;">Άρθρο 16 GDPR - Δικαίωμα Διόρθωσης</p>

    <form method="post" class="js-validate">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="update_profile">

      <label><?php echo $isUser ? 'Ονοματεπώνυμο' : 'Επωνυμία Οργανισμού'; ?> *</label>
      <input name="name" required minlength="3" value="<?php echo e($account['name']); ?>"
        data-error="Το όνομα πρέπει να έχει τουλάχιστον 3 χαρακτήρες.">

      <label>Τηλέφωνο</label>
      <input name="phone" type="tel" value="<?php echo e($account['phone'] ?? ''); ?>" placeholder="+30 694 000 0000">

      <div class="hr"></div>

      <p class="small muted" style="margin-bottom: 16px;">
        Για αλλαγή email ή κωδικού, συμπληρώστε τον <strong>τρέχοντα κωδικό</strong> σας.
      </p>

      <label>Νέο Email <span class="small muted">(αφήστε κενό αν δεν θέλετε αλλαγή)</span></label>
      <input name="new_email" type="email" value="" placeholder="<?php echo e($account['email']); ?>"
        data-error="Μη έγκυρο email.">

      <div class="row">
        <div>
          <label>Νέος Κωδικός</label>
          <input name="new_password" type="password" minlength="8" placeholder="Τουλάχιστον 8 χαρακτήρες"
            data-error="Ο κωδικός πρέπει να έχει τουλάχιστον 8 χαρακτήρες.">
        </div>
        <div>
          <label>Επιβεβαίωση Κωδικού</label>
          <input name="confirm_password" type="password" placeholder="Επαναλάβετε τον κωδικό">
        </div>
      </div>

      <label>Τρέχων Κωδικός <span class="small muted">(απαιτείται για αλλαγή email/κωδικού)</span></label>
      <input name="current_password" type="password" placeholder="Ο τρέχων κωδικός σας">

      <button type="submit" class="btn primary" style="width: 100%; margin-top: 20px;">
        Αποθήκευση Αλλαγών
      </button>
    </form>
  </div>

  <!-- ============ ΕΠΙΣΚΟΠΗΣΗ ΔΕΔΟΜΕΝΩΝ (Άρθρο 15) ============ -->
  <div class="card" style="margin-bottom: 24px;">
    <h2 style="margin: 0 0 8px; display: flex; align-items: center; gap: 10px;">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
        <polyline points="14 2 14 8 20 8" />
        <line x1="16" y1="13" x2="8" y2="13" />
        <line x1="16" y1="17" x2="8" y2="17" />
      </svg>
      Αποθηκευμένα Δεδομένα
    </h2>
    <p class="small muted" style="margin: 0 0 20px;">Άρθρο 15 GDPR - Δικαίωμα Πρόσβασης</p>

    <table class="data-table">
      <tbody>
        <tr>
          <td style="width: 180px;"><strong>Τύπος Λογαριασμού</strong></td>
          <td><?php echo $isUser ? 'Χρήστης (Ιδιώτης)' : 'Οργανισμός'; ?></td>
        </tr>
        <tr>
          <td><strong><?php echo $isUser ? 'Ονοματεπώνυμο' : 'Επωνυμία'; ?></strong></td>
          <td><?php echo e($account['name']); ?></td>
        </tr>
        <tr>
          <td><strong>Email</strong></td>
          <td><?php echo e($account['email']); ?></td>
        </tr>
        <tr>
          <td><strong>Τηλέφωνο</strong></td>
          <td><?php echo e($account['phone'] ?: 'Δεν έχει οριστεί'); ?></td>
        </tr>
        <?php if (!$isUser): ?>
          <tr>
            <td><strong>Website</strong></td>
            <td><?php echo e($account['website'] ?: 'Δεν έχει οριστεί'); ?></td>
          </tr>
          <tr>
            <td><strong>Περιγραφή</strong></td>
            <td><?php echo e($account['description'] ?: 'Δεν έχει οριστεί'); ?></td>
          </tr>
        <?php endif; ?>
        <tr>
          <td><strong>Επαλήθευση</strong></td>
          <td>
            <?php if ($isUser): ?>
              <?php echo $account['id_verified'] ? '<span style="color: var(--primary);">Επαληθευμένος</span>' : '<span class="muted">Μη επαληθευμένος</span>'; ?>
            <?php else: ?>
              <?php echo $account['verified'] ? '<span style="color: var(--primary);">Επαληθευμένος</span>' : '<span class="muted">Μη επαληθευμένος</span>'; ?>
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <td><strong>Έρανοι</strong></td>
          <td><?php echo $campaignCount; ?> έρανοι</td>
        </tr>
        <tr>
          <td><strong>Ημ/νία Εγγραφής</strong></td>
          <td><?php echo date('d/m/Y H:i', strtotime($account['created_at'])); ?></td>
        </tr>
        <?php if (isset($account['privacy_consent_at']) && $account['privacy_consent_at']): ?>
          <tr>
            <td><strong>Συγκατάθεση GDPR</strong></td>
            <td><?php echo date('d/m/Y H:i', strtotime($account['privacy_consent_at'])); ?></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- ============ ΕΞΑΓΩΓΗ ΔΕΔΟΜΕΝΩΝ (Άρθρο 20) ============ -->
  <div class="card" style="margin-bottom: 24px;">
    <h2 style="margin: 0 0 8px; display: flex; align-items: center; gap: 10px;">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
        <polyline points="7 10 12 15 17 10" />
        <line x1="12" y1="15" x2="12" y2="3" />
      </svg>
      Εξαγωγή Δεδομένων
    </h2>
    <p class="small muted" style="margin: 0 0 16px;">Άρθρο 20 GDPR - Δικαίωμα Φορητότητας Δεδομένων</p>

    <p style="color: var(--text-secondary); margin: 0 0 16px;">
      Κατεβάστε όλα τα δεδομένα σας σε μηχαναγνώσιμη μορφή (JSON).
      Περιλαμβάνει στοιχεία λογαριασμού, εράνους και δωρεές.
    </p>

    <form method="post">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="export">
      <button type="submit" class="btn primary" style="gap: 10px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
          <polyline points="7 10 12 15 17 10" />
          <line x1="12" y1="15" x2="12" y2="3" />
        </svg>
        Λήψη Δεδομένων (JSON)
      </button>
    </form>
  </div>

  <!-- ============ ΔΙΑΓΡΑΦΗ ΛΟΓΑΡΙΑΣΜΟΥ (Άρθρο 17) ============ -->
  <div class="card" style="margin-bottom: 24px; border-color: #fecaca;">
    <h2 style="margin: 0 0 8px; color: #ef4444; display: flex; align-items: center; gap: 10px;">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2">
        <polyline points="3 6 5 6 21 6" />
        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
        <line x1="10" y1="11" x2="10" y2="17" />
        <line x1="14" y1="11" x2="14" y2="17" />
      </svg>
      Διαγραφή Λογαριασμού
    </h2>
    <p class="small muted" style="margin: 0 0 16px;">Άρθρο 17 GDPR - Δικαίωμα στη Λήθη</p>

    <div class="notice warn" style="margin-bottom: 20px;">
      <div>
        <strong>Προσοχή! Αυτή η ενέργεια είναι μη αναστρέψιμη.</strong>
        <ul style="margin: 8px 0 0; padding-left: 20px;">
          <li>Ο λογαριασμός σας θα απενεργοποιηθεί άμεσα</li>
          <li>Τα δεδομένα σας θα διαγραφούν εντός 30 ημερών</li>
          <li>Οι έρανοί σας θα αφαιρεθούν από την πλατφόρμα</li>
        </ul>
      </div>
    </div>

    <details style="margin-top: 20px;">
      <summary
        style="cursor: pointer; font-weight: 600; color: #ef4444; padding: 12px; background: #fef2f2; border-radius: var(--radius);">
        Θέλω να διαγράψω τον λογαριασμό μου
      </summary>

      <form method="post" class="js-validate" style="margin-top: 20px;"
        onsubmit="return confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε τον λογαριασμό σας;');">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="delete">

        <label>Επιβεβαιώστε το email σας *</label>
        <input type="email" name="confirm_email" required placeholder="<?php echo e($entityEmail); ?>"
          data-error="Εισάγετε το email του λογαριασμού σας.">

        <label>Πληκτρολογήστε <strong>ΔΙΑΓΡΑΦΗ</strong> για επιβεβαίωση *</label>
        <input type="text" name="confirm_text" required placeholder="ΔΙΑΓΡΑΦΗ" autocomplete="off"
          data-error="Πληκτρολογήστε ΔΙΑΓΡΑΦΗ.">

        <button type="submit" class="btn danger" style="margin-top: 16px; width: 100%;">
          Οριστική Διαγραφή Λογαριασμού
        </button>
      </form>
    </details>
  </div>

  <!-- ============ TA ΔΙΚΑΙΩΜΑΤΑ ΣΑΣ ============ -->
  <div class="card">
    <h3 style="margin: 0 0 16px; display: flex; align-items: center; gap: 10px;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
      </svg>
      Τα Δικαιώματά σας
    </h3>
    <p class="muted" style="margin: 0 0 16px;">Σύμφωνα με τον Κανονισμό (ΕΕ) 2016/679:</p>

    <div class="rights-grid">
      <div class="right-item">
        <h4>Άρθρο 15 - Πρόσβαση</h4>
        <p>Δείτε όλα τα δεδομένα που τηρούμε για εσάς.</p>
      </div>
      <div class="right-item">
        <h4>Άρθρο 16 - Διόρθωση</h4>
        <p>Διορθώστε τα προσωπικά σας στοιχεία.</p>
      </div>
      <div class="right-item">
        <h4>Άρθρο 17 - Διαγραφή</h4>
        <p>Ζητήστε τη διαγραφή των δεδομένων σας.</p>
      </div>
      <div class="right-item">
        <h4>Άρθρο 20 - Φορητότητα</h4>
        <p>Κατεβάστε τα δεδομένα σας σε JSON.</p>
      </div>
    </div>

    <p style="margin: 16px 0 0;">
      <a href="<?php echo BASE_URL; ?>/gdpr-request.php" style="color: var(--primary); font-weight: 500;">
        Υποβολή αιτήματος GDPR
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          style="vertical-align: -2px;">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
      </a>
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>