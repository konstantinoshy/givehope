<?php
$adminPageTitle = 'Αναφορές';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/csrf.php';

$pdo = db();

// Διαχείριση επίλυσης
if (is_post()) {
  csrf_verify();
  $reportId = (int) ($_POST['report_id'] ?? 0);
  $action = $_POST['action'] ?? '';

  if ($action === 'resolve') {
    $pdo->prepare("UPDATE reports SET status = 'resolved' WHERE id = :id")->execute([':id' => $reportId]);
  }
}

// Ανάκτηση αναφορών
$reports = $pdo->query("
    SELECT r.*, c.title AS campaign_title, c.id AS campaign_id, c.status AS campaign_status
    FROM reports r
    JOIN campaigns c ON c.id = r.campaign_id
    ORDER BY r.status ASC, r.created_at DESC
")->fetchAll();

$reasonLabels = [
  'scam' => 'Απάτη',
  'fake_info' => 'Ψευδή στοιχεία',
  'inappropriate' => 'Ακατάλληλο',
  'other' => 'Άλλο',
];
?>
    <div class="page-header">
      <h1 class="page-title">Αναφορές Χρηστών</h1>
    </div>

    <div class="card">
      <?php if (count($reports) === 0): ?>
        <div style="text-align: center; padding: 40px;">
          <div style="font-size: 48px; margin-bottom: 12px; color: var(--primary);">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20 6 9 17 4 12" />
            </svg>
          </div>
          <p class="muted">Δεν υπάρχουν αναφορές!</p>
        </div>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr>
              <th>Ημ/νία</th>
              <th>Έρανος</th>
              <th>Λόγος</th>
              <th>Περιγραφή</th>
              <th>Email</th>
              <th>Κατάσταση</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($reports as $r): ?>
              <tr style="<?php echo $r['status'] === 'resolved' ? 'opacity: 0.5;' : ''; ?>">
                <td class="small"><?php echo date('d/m/Y H:i', strtotime($r['created_at'])); ?></td>
                <td>
                  <a href="<?php echo BASE_URL; ?>/admin/campaign-review.php?id=<?php echo (int) $r['campaign_id']; ?>">
                    <?php echo e(mb_strimwidth($r['campaign_title'], 0, 30, '...', 'UTF-8')); ?>
                  </a>
                </td>
                <td><?php echo $reasonLabels[$r['reason']] ?? $r['reason']; ?></td>
                <td class="small"><?php echo e(mb_strimwidth($r['description'] ?? '', 0, 50, '...', 'UTF-8')); ?></td>
                <td class="small muted"><?php echo e($r['reporter_email']); ?></td>
                <td>
                  <?php if ($r['status'] === 'new'): ?>
                    <span style="color: #f59e0b; font-weight: 500;">Νέα</span>
                  <?php else: ?>
                    <span class="muted">✓ Επιλύθηκε</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($r['status'] === 'new'): ?>
                    <form method="post" style="display: inline;">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="report_id" value="<?php echo (int) $r['id']; ?>">
                      <button type="submit" name="action" value="resolve" class="btn">✓ Επίλυση</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>