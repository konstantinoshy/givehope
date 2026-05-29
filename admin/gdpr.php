<?php
$adminPageTitle = 'GDPR';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/csrf.php';
$pdo = db();

$notice = null; // ['type' => 'ok'|'error', 'msg' => '...']

/* ------------------------------------------------------------------ */
/* POST: αλλαγή status αιτήματος (μόνο σε completed ή rejected)        */
/* ------------------------------------------------------------------ */
if (is_post()) {
  csrf_verify();
  $action = $_POST['action'] ?? '';

  if ($action === 'update_status') {
    $requestId = (int) ($_POST['request_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    $rejectionReason = trim($_POST['rejection_reason'] ?? '');
    $allowed = ['completed', 'rejected'];

    $admin = current_admin();
    $adminId = $admin['id'] ?? null;

    if ($requestId <= 0 || !in_array($newStatus, $allowed, true)) {
      $notice = ['type' => 'error', 'msg' => 'Μη έγκυρο αίτημα.'];
    } elseif ($newStatus === 'rejected' && $rejectionReason === '') {
      $notice = ['type' => 'error', 'msg' => 'Για απόρριψη πρέπει να συμπληρώσεις λόγο.'];
    } else {
      // Έλεγχος ότι υπάρχει και δεν είναι ήδη οριστικοποιημένο
      $stmt = $pdo->prepare("SELECT id, status FROM gdpr_requests WHERE id = :id");
      $stmt->execute([':id' => $requestId]);
      $req = $stmt->fetch();

      if (!$req) {
        $notice = ['type' => 'error', 'msg' => 'Το αίτημα δεν βρέθηκε.'];
      } elseif (in_array($req['status'], ['completed', 'rejected'], true)) {
        $notice = ['type' => 'error', 'msg' => 'Το αίτημα έχει ήδη οριστικοποιηθεί.'];
      } else {
        try {
          $pdo->beginTransaction();

          if ($newStatus === 'completed') {
            $pdo->prepare("
              UPDATE gdpr_requests
              SET status = 'completed',
                  handled_by = :admin,
                  handled_at = NOW(),
                  completed_at = NOW(),
                  rejection_reason = NULL
              WHERE id = :id
            ")->execute([':admin' => $adminId, ':id' => $requestId]);
            $logDesc = 'Αίτημα GDPR ολοκληρώθηκε από admin.';
          } else {
            $pdo->prepare("
              UPDATE gdpr_requests
              SET status = 'rejected',
                  handled_by = :admin,
                  handled_at = NOW(),
                  rejection_reason = :reason
              WHERE id = :id
            ")->execute([':admin' => $adminId, ':reason' => $rejectionReason, ':id' => $requestId]);
            $logDesc = 'Αίτημα GDPR απορρίφθηκε από admin. Λόγος: ' . $rejectionReason;
          }

          // Audit trail στο data_processing_log
          $pdo->prepare("
            INSERT INTO data_processing_log
              (entity_type, entity_id, action, actor_type, actor_id, description, ip_address)
            VALUES
              ('gdpr_request', :eid, 'update', 'admin', :aid, :descr, :ip)
          ")->execute([
            ':eid'   => $requestId,
            ':aid'   => $adminId,
            ':descr' => $logDesc,
            ':ip'    => $_SERVER['REMOTE_ADDR'] ?? null,
          ]);

          $pdo->commit();
          $notice = ['type' => 'ok', 'msg' => 'Το αίτημα ενημερώθηκε.'];
        } catch (Throwable $ex) {
          if ($pdo->inTransaction()) {
            $pdo->rollBack();
          }
          $notice = ['type' => 'error', 'msg' => 'Σφάλμα κατά την ενημέρωση.'];
        }
      }
    }
  }
}

/* ------------------------------------------------------------------ */
/* Queries (τρέχουν ΜΕΤΑ το POST ώστε να δείχνουν ενημερωμένα δεδομένα) */
/* ------------------------------------------------------------------ */
$requests = $pdo->query("
  SELECT r.*, a.username AS handler_username
  FROM gdpr_requests r
  LEFT JOIN admins a ON a.id = r.handled_by
  ORDER BY
    CASE WHEN r.status IN ('pending','verified','processing') THEN 0 ELSE 1 END,
    r.created_at DESC
")->fetchAll();

$logs = $pdo->query("
  SELECT *
  FROM data_processing_log
  ORDER BY created_at DESC, id DESC
  LIMIT 100
")->fetchAll();

/* ------------------------------------------------------------------ */
/* Labels                                                             */
/* ------------------------------------------------------------------ */
$typeLabels = [
  'access'        => 'Πρόσβαση',
  'export'        => 'Εξαγωγή',
  'delete'        => 'Διαγραφή',
  'rectification' => 'Διόρθωση',
];
$requesterTypeLabels = [
  'user'         => 'Χρήστης',
  'organization' => 'Οργανισμός',
  'donor'        => 'Δωρητής',
  'visitor'      => 'Επισκέπτης',
];
$statusLabels = [
  'pending'    => 'Σε εκκρεμότητα',
  'verified'   => 'Επαληθευμένο',
  'processing' => 'Σε επεξεργασία',
  'completed'  => 'Ολοκληρώθηκε',
  'rejected'   => 'Απορρίφθηκε',
];
$statusColors = [
  'pending'    => '#f59e0b',
  'verified'   => '#3b82f6',
  'processing' => '#3b82f6',
  'completed'  => '#10b981',
  'rejected'   => '#ef4444',
];
$entityLabels = [
  'user'         => 'Χρήστης',
  'organization' => 'Οργανισμός',
  'donation'     => 'Δωρεά',
  'message'      => 'Μήνυμα',
  'campaign'     => 'Έρανος',
  'gdpr_request' => 'Αίτημα GDPR',
];
$actionLabels = [
  'create'    => 'Δημιουργία',
  'read'      => 'Ανάγνωση',
  'update'    => 'Ενημέρωση',
  'delete'    => 'Διαγραφή',
  'export'    => 'Εξαγωγή',
  'anonymize' => 'Ανωνυμοποίηση',
];
$actorLabels = [
  'user'         => 'Χρήστης',
  'organization' => 'Οργανισμός',
  'admin'        => 'Διαχειριστής',
  'system'       => 'Σύστημα',
];
$actionColors = [
  'delete'    => '#ef4444',
  'anonymize' => '#ef4444',
  'export'    => '#f59e0b',
];

$openStatuses = ['pending', 'verified', 'processing'];
?>
    <div class="page-header">
      <h1 class="page-title">Διαχείριση GDPR</h1>
    </div>

    <?php if ($notice): ?>
      <div class="notice"<?php echo $notice['type'] === 'error' ? ' style="color: #ef4444;"' : ''; ?>>
        <?php echo e($notice['msg']); ?>
      </div>
    <?php endif; ?>

    <!-- ============== ΕΝΟΤΗΤΑ 1: Αιτήματα GDPR ============== -->
    <div class="card">
      <h2 style="margin: 0 0 16px;">Αιτήματα GDPR (<?php echo count($requests); ?>)</h2>
      <?php if (count($requests) === 0): ?>
        <div style="text-align: center; padding: 40px;">
          <p class="muted">Δεν υπάρχουν αιτήματα GDPR.</p>
        </div>
      <?php else: ?>
        <div style="overflow-x: auto;">
        <table class="table">
          <thead>
            <tr>
              <th>Ημ/νία</th>
              <th>Τύπος</th>
              <th>Αιτών</th>
              <th>Email</th>
              <th>Κατάσταση</th>
              <th>Χειρισμός</th>
              <th>Ενέργειες</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($requests as $r): ?>
              <?php $isOpen = in_array($r['status'], $openStatuses, true); ?>
              <tr style="<?php echo $isOpen ? '' : 'opacity: 0.6;'; ?>">
                <td class="small"><?php echo date('d/m/Y H:i', strtotime($r['created_at'])); ?></td>
                <td><?php echo e($typeLabels[$r['type']] ?? $r['type']); ?></td>
                <td>
                  <?php if (!empty($r['requester_name'])): ?>
                    <?php echo e($r['requester_name']); ?><br>
                  <?php endif; ?>
                  <span class="small muted">
                    <?php echo e($requesterTypeLabels[$r['requester_type']] ?? $r['requester_type']); ?>
                  </span>
                </td>
                <td class="small"><?php echo e($r['requester_email']); ?></td>
                <td>
                  <?php $c = $statusColors[$r['status']] ?? '#6b7280'; ?>
                  <span style="color: <?php echo $c; ?>; font-weight: 600;">
                    <?php echo e($statusLabels[$r['status']] ?? $r['status']); ?>
                  </span>
                </td>
                <td class="small">
                  <?php if (!empty($r['handled_at'])): ?>
                    <?php echo e($r['handler_username'] ?? '—'); ?><br>
                    <span class="muted"><?php echo date('d/m/Y H:i', strtotime($r['handled_at'])); ?></span>
                    <?php if ($r['status'] === 'rejected' && !empty($r['rejection_reason'])): ?>
                      <div class="muted">Λόγος: <?php echo e($r['rejection_reason']); ?></div>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="muted">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($isOpen): ?>
                    <form method="post" style="display: flex; flex-direction: column; gap: 6px;">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="action" value="update_status">
                      <input type="hidden" name="request_id" value="<?php echo (int) $r['id']; ?>">
                      <input type="text" name="rejection_reason" placeholder="Λόγος απόρριψης" maxlength="1000"
                        style="width: 100%; box-sizing: border-box;">
                      <div style="display: flex; gap: 6px;">
                        <button type="submit" name="status" value="completed" class="btn primary"
                          onclick="return confirm('Σήμανση του αιτήματος ως ολοκληρωμένο;');">
                          Ολοκλήρωση
                        </button>
                        <button type="submit" name="status" value="rejected" class="btn danger"
                          onclick="if(!this.form.rejection_reason.value.trim()){alert('Συμπλήρωσε λόγο απόρριψης.');return false;}return confirm('Απόρριψη του αιτήματος;');">
                          Απόρριψη
                        </button>
                      </div>
                    </form>
                  <?php else: ?>
                    <span class="muted">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- ============== ΕΝΟΤΗΤΑ 2: Data Processing Logs ============== -->
    <div class="card" style="margin-top: 24px;">
      <h2 style="margin: 0 0 16px;">Data Processing Logs <span class="muted small">(τελευταία 100)</span></h2>
      <?php if (count($logs) === 0): ?>
        <div style="text-align: center; padding: 40px;">
          <p class="muted">Δεν υπάρχουν logs.</p>
        </div>
      <?php else: ?>
        <div style="overflow-x: auto;">
        <table class="table">
          <thead>
            <tr>
              <th>Ημ/νία</th>
              <th>Οντότητα</th>
              <th>Ενέργεια</th>
              <th>Δράστης</th>
              <th>Περιγραφή</th>
              <th>IP</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $log): ?>
              <tr>
                <td class="small"><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
                <td class="small">
                  <?php echo e($entityLabels[$log['entity_type']] ?? $log['entity_type']); ?>
                  #<?php echo (int) $log['entity_id']; ?>
                </td>
                <td class="small">
                  <?php $ac = $actionColors[$log['action']] ?? null; ?>
                  <span<?php echo $ac ? ' style="color: ' . $ac . '; font-weight: 500;"' : ''; ?>>
                    <?php echo e($actionLabels[$log['action']] ?? $log['action']); ?>
                  </span>
                </td>
                <td class="small">
                  <?php echo e($actorLabels[$log['actor_type']] ?? $log['actor_type']); ?>
                  <?php if ($log['actor_id'] !== null): ?>#<?php echo (int) $log['actor_id']; ?><?php endif; ?>
                </td>
                <td class="small muted">
                  <?php echo e(mb_strimwidth((string) ($log['description'] ?? ''), 0, 80, '...', 'UTF-8')); ?>
                </td>
                <td class="small muted"><?php echo e($log['ip_address'] ?? '—'); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      <?php endif; ?>
    </div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
