<?php
$adminPageTitle = 'Μηνύματα';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/csrf.php';

$pdo = db();

if (is_post()) {
  csrf_verify();
  $action = $_POST['action'] ?? '';
  $msgId = (int) ($_POST['message_id'] ?? 0);

  if ($action === 'mark_read' && $msgId > 0) {
    $pdo->prepare("UPDATE messages SET status = 'read' WHERE id = :id")->execute([':id' => $msgId]);
  } elseif ($action === 'mark_unread' && $msgId > 0) {
    $pdo->prepare("UPDATE messages SET status = 'new' WHERE id = :id")->execute([':id' => $msgId]);
  } elseif ($action === 'delete' && $msgId > 0) {
    $pdo->prepare("DELETE FROM messages WHERE id = :id")->execute([':id' => $msgId]);
  }
}

$q = trim($_GET['q'] ?? '');
$filter = $_GET['filter'] ?? 'all';
$perPage = 15;
$page = max(1, (int) ($_GET['page'] ?? 1));

$where = ["1=1"];
$params = [];

if ($filter === 'unread') {
  $where[] = "m.status = 'new'";
} elseif ($filter === 'read') {
  $where[] = "m.status = 'read'";
}

if ($q !== '') {
  $where[] = "(m.name LIKE :q1 OR m.email LIKE :q2 OR m.subject LIKE :q3)";
  $params[':q1'] = "%$q%";
  $params[':q2'] = "%$q%";
  $params[':q3'] = "%$q%";
}

$whereStr = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM messages m WHERE $whereStr");
$countStmt->execute($params);
$totalMessages = (int) $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalMessages / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$unreadCount = (int) $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'new'")->fetchColumn();
$allCount = (int) $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();

$sql = "
    SELECT m.*, o.name AS org_name
    FROM messages m
    LEFT JOIN organizations o ON o.id = m.org_id
    WHERE $whereStr
    ORDER BY m.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

function buildMessagesUrl($params = []) {
  $defaults = ['filter' => $_GET['filter'] ?? 'all', 'q' => $_GET['q'] ?? '', 'page' => 1];
  $merged = array_merge($defaults, $params);
  $parts = [];
  foreach ($merged as $k => $v) {
    if ($v !== '' && $v !== null) $parts[] = urlencode($k) . '=' . urlencode($v);
  }
  return 'messages.php' . ($parts ? '?' . implode('&', $parts) : '');
}
?>
    <div class="page-header">
      <h1 class="page-title">Μηνύματα Επικοινωνίας</h1>
      <p class="page-subtitle"><?php echo $unreadCount; ?> μη αναγνωσμένα από <?php echo $allCount; ?> σύνολο</p>
    </div>

    <!-- Search -->
    <div class="card" style="padding: 16px; margin-bottom: 24px;">
      <form method="get" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <input type="hidden" name="filter" value="<?php echo e($filter); ?>">
        <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="Αναζήτηση ονόματος, email ή θέματος..."
          style="flex: 1; min-width: 200px; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
        <button type="submit" class="btn primary" style="padding: 10px 20px;">Αναζήτηση</button>
        <?php if ($q !== ''): ?>
          <a href="<?php echo buildMessagesUrl(['q' => '', 'page' => 1]); ?>" class="btn" style="padding: 10px 16px;">Καθαρισμός</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs" style="margin-bottom: 24px;">
      <a href="<?php echo buildMessagesUrl(['filter' => 'all', 'page' => 1]); ?>" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
        Όλα (<?php echo $allCount; ?>)
      </a>
      <a href="<?php echo buildMessagesUrl(['filter' => 'unread', 'page' => 1]); ?>" class="filter-tab <?php echo $filter === 'unread' ? 'active' : ''; ?>">
        Μη αναγνωσμένα (<?php echo $unreadCount; ?>)
      </a>
      <a href="<?php echo buildMessagesUrl(['filter' => 'read', 'page' => 1]); ?>" class="filter-tab <?php echo $filter === 'read' ? 'active' : ''; ?>">
        Αναγνωσμένα (<?php echo $allCount - $unreadCount; ?>)
      </a>
    </div>

    <?php if (count($messages) === 0): ?>
      <div class="card" style="text-align: center; padding: 60px 24px;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="1.5" style="margin-bottom: 16px;">
          <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
          <polyline points="22,6 12,13 2,6"/>
        </svg>
        <p class="muted"><?php echo $q ? 'Δεν βρέθηκαν μηνύματα για "' . e($q) . '".' : 'Δεν υπάρχουν μηνύματα.'; ?></p>
      </div>
    <?php else: ?>
      <?php foreach ($messages as $m):
        $isUnread = $m['status'] === 'new';
      ?>
        <div class="card" style="margin-bottom: 12px; padding: 20px; <?php echo $isUnread ? 'border-left: 3px solid var(--primary);' : 'opacity: 0.75;'; ?>">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 0;">
              <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <?php if ($isUnread): ?>
                  <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary); flex-shrink: 0;"></span>
                <?php endif; ?>
                <strong style="font-size: 15px;"><?php echo e($m['subject']); ?></strong>
              </div>
              <div class="small" style="margin-bottom: 10px; color: var(--text-secondary);">
                <strong><?php echo e($m['name']); ?></strong> &lt;<?php echo e($m['email']); ?>&gt;
                <?php if (!empty($m['org_name'])): ?>
                  &rarr; <span style="color: var(--primary);"><?php echo e($m['org_name']); ?></span>
                <?php endif; ?>
              </div>
              <p style="margin: 0; font-size: 14px; line-height: 1.6; color: var(--text); white-space: pre-line;"><?php echo e($m['body']); ?></p>
            </div>
            <div style="text-align: right; flex-shrink: 0;">
              <div class="small muted" style="margin-bottom: 8px;">
                <?php echo date('d/m/Y H:i', strtotime($m['created_at'])); ?>
              </div>
              <div style="display: flex; gap: 6px; justify-content: flex-end;">
                <form method="post" style="display: inline;">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="message_id" value="<?php echo (int) $m['id']; ?>">
                  <?php if ($isUnread): ?>
                    <button type="submit" name="action" value="mark_read" class="btn" style="padding: 4px 10px; font-size: 12px;">
                      ✓ Αναγνώστηκε
                    </button>
                  <?php else: ?>
                    <button type="submit" name="action" value="mark_unread" class="btn" style="padding: 4px 10px; font-size: 12px;">
                      Ως νέο
                    </button>
                  <?php endif; ?>
                </form>
                <form method="post" style="display: inline;" data-confirm="Διαγραφή μηνύματος;">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="message_id" value="<?php echo (int) $m['id']; ?>">
                  <button type="submit" name="action" value="delete" class="btn" style="padding: 4px 10px; font-size: 12px; color: #dc3545;">
                    Διαγραφή
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if ($totalPages > 1): ?>
        <div style="margin-top: 20px; display: flex; justify-content: center; gap: 6px;">
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?php echo buildMessagesUrl(['page' => $i]); ?>"
              class="btn <?php echo $i === $page ? 'primary' : ''; ?>" style="padding: 6px 12px; min-width: 32px;">
              <?php echo $i; ?>
            </a>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
