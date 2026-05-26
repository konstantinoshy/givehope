<?php
$adminPageTitle = 'Έλεγχος Εράνου';
$adminMainStyle = 'max-width: 1200px;';

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

if (!current_admin()) {
    redirect(BASE_URL . "/admin/login.php");
}

$pdo = db();
$admin = current_admin();
$id = (int) ($_GET['id'] ?? 0);

// Ανάκτηση εράνου με όλες τις λεπτομέρειες
$stmt = $pdo->prepare("
    SELECT c.*, cat.name AS category_name, cat.icon AS category_icon, cat.requires_verification,
           u.id AS user_id, u.name AS user_name, u.email AS user_email, u.phone AS user_phone, u.id_verified AS user_verified, u.created_at AS user_created,
           o.id AS org_id, o.name AS org_name, o.email AS org_email, o.verified AS org_verified
    FROM campaigns c
    JOIN categories cat ON cat.id = c.category_id
    LEFT JOIN users u ON u.id = c.user_id
    LEFT JOIN organizations o ON o.id = c.org_id
    WHERE c.id = :id
");
$stmt->execute([':id' => $id]);
$campaign = $stmt->fetch();

if (!$campaign) {
  redirect(BASE_URL . "/admin/campaigns.php");
}

// Διαχείριση έγκρισης/απόρριψης
if (is_post()) {
  csrf_verify();
  $action = $_POST['action'] ?? '';
  $reason = trim($_POST['rejection_reason'] ?? '');

  if ($action === 'approve') {
    $upd = $pdo->prepare("UPDATE campaigns SET status = 'approved', approved_at = NOW(), rejection_reason = NULL WHERE id = :id");
    $upd->execute([':id' => $id]);

    $log = $pdo->prepare("INSERT INTO data_processing_log (entity_type, entity_id, action, actor_type, actor_id, description, ip_address) VALUES ('campaign', :eid, 'update', 'admin', :aid, 'Έγκριση εράνου', :ip)");
    $log->execute([':eid' => $id, ':aid' => $admin['id'] ?? 0, ':ip' => $_SERVER['REMOTE_ADDR']]);

    redirect(BASE_URL . "/admin/campaigns.php?msg=approved");
  } elseif ($action === 'reject') {
    $upd = $pdo->prepare("UPDATE campaigns SET status = 'rejected', rejection_reason = :reason WHERE id = :id");
    $upd->execute([':id' => $id, ':reason' => $reason]);

    $log = $pdo->prepare("INSERT INTO data_processing_log (entity_type, entity_id, action, actor_type, actor_id, description, ip_address) VALUES ('campaign', :eid, 'update', 'admin', :aid, :desc, :ip)");
    $log->execute([':eid' => $id, ':aid' => $admin['id'] ?? 0, ':desc' => 'Απόρριψη εράνου. Λόγος: ' . $reason, ':ip' => $_SERVER['REMOTE_ADDR']]);

    redirect(BASE_URL . "/admin/campaigns.php?msg=rejected");
  } elseif ($action === 'suspend') {
    $upd = $pdo->prepare("UPDATE campaigns SET status = 'suspended', rejection_reason = :reason WHERE id = :id");
    $upd->execute([':id' => $id, ':reason' => $reason]);

    $log = $pdo->prepare("INSERT INTO data_processing_log (entity_type, entity_id, action, actor_type, actor_id, description, ip_address) VALUES ('campaign', :eid, 'update', 'admin', :aid, :desc, :ip)");
    $log->execute([':eid' => $id, ':aid' => $admin['id'] ?? 0, ':desc' => 'Αναστολή εράνου. Λόγος: ' . $reason, ':ip' => $_SERVER['REMOTE_ADDR']]);

    redirect(BASE_URL . "/admin/campaigns.php?msg=suspended");
  }
}

require_once __DIR__ . '/includes/header.php';

$defaultImage = 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=800&q=80';
$statusLabels = [
  'pending' => ['', 'Αναμονή', '#f59e0b'],
  'approved' => ['', 'Εγκρίθηκε', '#02a95c'],
  'rejected' => ['', 'Απορρίφθηκε', '#dc3545'],
  'suspended' => ['', 'Αναστολή', '#dc3545'],
];
$status = $statusLabels[$campaign['status']] ?? ['', 'Άγνωστο', '#666'];
?>
    <a href="<?php echo BASE_URL; ?>/admin/campaigns.php" class="muted"
      style="display: inline-block; margin-bottom: 16px;">← Πίσω στους εράνους</a>

    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 24px;">
      <!-- Λεπτομέρειες Εράνου -->
      <div>
        <div class="card" style="margin-bottom: 24px;">
          <div style="display: flex; gap: 8px; margin-bottom: 16px;">
            <span class="badge" style="background: <?php echo $status[2]; ?>15; color: <?php echo $status[2]; ?>;">
              <?php echo $status[1]; ?>
            </span>
            <span class="badge"><?php echo category_icon($campaign['category_id']); ?>
              <?php echo e($campaign['category_name']); ?></span>
            <?php if ($campaign['requires_verification']): ?>
              <span class="badge" style="background: #fff3cd; color: #856404;">Απαιτεί Επαλήθευση</span>
            <?php endif; ?>
          </div>

          <h1 style="margin: 0 0 16px; font-size: 24px;"><?php echo e($campaign['title']); ?></h1>

          <?php if ($campaign['image_url']): ?>
            <img src="<?php echo e($campaign['image_url']); ?>" alt=""
              style="width: 100%; height: 250px; object-fit: cover; border-radius: 12px; margin-bottom: 16px;">
          <?php endif; ?>

          <h3>Περιγραφή</h3>
          <p><?php echo e($campaign['description']); ?></p>

          <?php if ($campaign['story']): ?>
            <h3>Ιστορία</h3>
            <p style="white-space: pre-line;"><?php echo e($campaign['story']); ?></p>
          <?php endif; ?>

          <?php if ($campaign['target_amount']): ?>
            <div class="hr"></div>
            <p><strong>Στόχος:</strong> <?php echo money_eur((int) $campaign['target_amount']); ?></p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Πλαϊνή Στήλη: Πληροφορίες Δημιουργού & Ενέργειες -->
      <div>
        <!-- Πληροφορίες Δημιουργού -->
        <div class="card" style="margin-bottom: 24px;">
          <h3 style="margin-top: 0;">Δημιουργός</h3>

          <?php if ($campaign['user_id']): ?>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
              <div class="creator-avatar">
                <svg viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                  <circle cx="12" cy="7" r="4" />
                </svg>
              </div>
              <div>
                <strong><?php echo e($campaign['user_name']); ?></strong>
                <?php if ($campaign['user_verified']): ?>
                  <span style="color: var(--primary);">✓</span>
                <?php endif; ?>
                <br>
                <span class="small muted"><?php echo e($campaign['user_email']); ?></span>
              </div>
            </div>
            <p class="small muted" style="margin: 0;">
              Τηλ: <?php echo e($campaign['user_phone'] ?: 'Δεν δόθηκε'); ?><br>
              Μέλος από <?php echo date('d/m/Y', strtotime($campaign['user_created'])); ?><br>
              <?php echo $campaign['user_verified'] ? '<span style="color: var(--primary);">✓ Επαληθευμένος</span>' : '<span style="color: #f59e0b;">Μη επαληθευμένος</span>'; ?>
            </p>
          <?php else: ?>
            <div style="display: flex; align-items: center; gap: 12px;">
              <div class="creator-avatar">
                <svg viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2">
                  <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                  <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
              </div>
              <div>
                <strong><?php echo e($campaign['org_name']); ?></strong>
                <?php if ($campaign['org_verified']): ?>
                  <span style="color: var(--primary);">✓</span>
                <?php endif; ?>
                <br>
                <span class="small muted"><?php echo e($campaign['org_email']); ?></span>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Ενέργειες -->
        <div class="card">
          <h3 style="margin-top: 0;">Ενέργειες</h3>

          <?php if ($campaign['status'] === 'pending'): ?>
            <form method="post">
              <?php echo csrf_field(); ?>

              <button type="submit" name="action" value="approve" class="btn primary"
                style="width: 100%; margin-bottom: 12px;">
                Έγκριση
              </button>

              <div class="hr"></div>

              <label>Λόγος απόρριψης (αν απορρίψετε)</label>
              <textarea name="rejection_reason" placeholder="Εξηγήστε γιατί απορρίπτεται ο έρανος..."
                style="min-height: 80px;"></textarea>

              <button type="submit" name="action" value="reject" class="btn danger"
                style="width: 100%; margin-top: 12px;">
                Απόρριψη
              </button>
            </form>
          <?php elseif ($campaign['status'] === 'approved'): ?>
            <form method="post">
              <?php echo csrf_field(); ?>
              <p class="small muted">Ο έρανος είναι ενεργός.</p>

              <label>Λόγος αναστολής</label>
              <textarea name="rejection_reason" placeholder="Εξηγήστε γιατί αναστέλλεται..."
                style="min-height: 80px;"></textarea>

              <button type="submit" name="action" value="suspend" class="btn danger"
                style="width: 100%; margin-top: 12px;">
                Αναστολή
              </button>
            </form>
          <?php else: ?>
            <p class="small muted">Κατάσταση: <?php echo $status[1]; ?></p>
            <?php if ($campaign['rejection_reason']): ?>
              <div class="notice warn small" style="margin-top: 12px;">
                <strong>Λόγος:</strong> <?php echo e($campaign['rejection_reason']); ?>
              </div>
            <?php endif; ?>

            <form method="post" style="margin-top: 12px;">
              <?php echo csrf_field(); ?>
              <button type="submit" name="action" value="approve" class="btn primary" style="width: 100%;">
                Επαναφορά & Έγκριση
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>