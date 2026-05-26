<?php
$adminPageTitle = 'Έρανοι';
require_once __DIR__ . '/includes/header.php';

$pdo = db();

$status = $_GET['status'] ?? 'all';
$q = trim($_GET['q'] ?? '');
$msg = $_GET['msg'] ?? '';
$perPage = 15;
$page = max(1, (int) ($_GET['page'] ?? 1));

// Κατασκευή ερωτήματος
$where = ["1=1"];
$params = [];

if ($status !== 'all') {
  $where[] = "c.status = :status";
  $params[':status'] = $status;
}

if ($q !== '') {
  $where[] = "(c.title LIKE :q1 OR u.name LIKE :q2 OR u.email LIKE :q3 OR o.name LIKE :q4)";
  $params[':q1'] = "%$q%";
  $params[':q2'] = "%$q%";
  $params[':q3'] = "%$q%";
  $params[':q4'] = "%$q%";
}

$whereStr = implode(' AND ', $where);

// Μέτρηση συνόλου
$countSql = "
    SELECT COUNT(*)
    FROM campaigns c
    LEFT JOIN users u ON u.id = c.user_id
    LEFT JOIN organizations o ON o.id = c.org_id
    WHERE $whereStr
";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalCampaigns = (int) $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalCampaigns / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

// Ανάκτηση εράνων
$sql = "
    SELECT c.*, cat.name AS category_name, cat.icon AS category_icon,
           u.name AS user_name, u.email AS user_email,
           o.name AS org_name,
           (SELECT COUNT(*) FROM donations WHERE campaign_id = c.id) AS donation_count
    FROM campaigns c
    JOIN categories cat ON cat.id = c.category_id
    LEFT JOIN users u ON u.id = c.user_id
    LEFT JOIN organizations o ON o.id = c.org_id
    WHERE $whereStr
    ORDER BY c.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$campaigns = $stmt->fetchAll();

// Μετρήσεις ανά status
$counts = [];
$countAllStmt = $pdo->query("SELECT status, COUNT(*) as c FROM campaigns GROUP BY status");
while ($row = $countAllStmt->fetch()) {
  $counts[$row['status']] = (int) $row['c'];
}

$statusLabels = [
  'pending' => ['', 'Αναμονή', '#f59e0b'],
  'approved' => ['', 'Ενεργός', '#02a95c'],
  'rejected' => ['', 'Απορρίφθηκε', '#dc3545'],
  'suspended' => ['', 'Αναστολή', '#dc3545'],
  'completed' => ['', 'Ολοκληρώθηκε', '#666'],
];

function buildCampaignsUrl($params = []) {
  $defaults = ['status' => $_GET['status'] ?? 'all', 'q' => $_GET['q'] ?? '', 'page' => 1];
  $merged = array_merge($defaults, $params);
  $parts = [];
  foreach ($merged as $k => $v) {
    if ($v !== '' && $v !== null) $parts[] = urlencode($k) . '=' . urlencode($v);
  }
  return 'campaigns.php' . ($parts ? '?' . implode('&', $parts) : '');
}
?>
    <div class="page-header">
      <h1 class="page-title">Διαχείριση Εράνων</h1>
    </div>

    <?php if ($msg): ?>
      <div class="notice ok" style="margin-bottom: 24px;">
        <?php if ($msg === 'approved'): ?>Ο έρανος εγκρίθηκε!
        <?php elseif ($msg === 'rejected'): ?>Ο έρανος απορρίφθηκε.
        <?php elseif ($msg === 'suspended'): ?>Ο έρανος τέθηκε σε αναστολή.
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Search -->
    <div class="card" style="padding: 16px; margin-bottom: 24px;">
      <form method="get" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <input type="hidden" name="status" value="<?php echo e($status); ?>">
        <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="Αναζήτηση τίτλου, δημιουργού ή email..."
          style="flex: 1; min-width: 200px; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
        <button type="submit" class="btn primary" style="padding: 10px 20px;">Αναζήτηση</button>
        <?php if ($q !== ''): ?>
          <a href="<?php echo buildCampaignsUrl(['q' => '', 'page' => 1]); ?>" class="btn" style="padding: 10px 16px;">Καθαρισμός</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Καρτέλες Φίλτρων -->
    <div class="filter-tabs">
      <a href="<?php echo buildCampaignsUrl(['status' => 'all', 'page' => 1]); ?>" class="filter-tab <?php echo $status === 'all' ? 'active' : ''; ?>">
        Όλοι (<?php echo array_sum($counts); ?>)
      </a>
      <a href="<?php echo buildCampaignsUrl(['status' => 'pending', 'page' => 1]); ?>" class="filter-tab <?php echo $status === 'pending' ? 'active' : ''; ?>">
        Προς Έγκριση (<?php echo $counts['pending'] ?? 0; ?>)
      </a>
      <a href="<?php echo buildCampaignsUrl(['status' => 'approved', 'page' => 1]); ?>" class="filter-tab <?php echo $status === 'approved' ? 'active' : ''; ?>">
        Ενεργοί (<?php echo $counts['approved'] ?? 0; ?>)
      </a>
      <a href="<?php echo buildCampaignsUrl(['status' => 'rejected', 'page' => 1]); ?>" class="filter-tab <?php echo $status === 'rejected' ? 'active' : ''; ?>">
        Απορριφθέντες (<?php echo $counts['rejected'] ?? 0; ?>)
      </a>
      <a href="<?php echo buildCampaignsUrl(['status' => 'suspended', 'page' => 1]); ?>" class="filter-tab <?php echo $status === 'suspended' ? 'active' : ''; ?>">
        Σε Αναστολή (<?php echo $counts['suspended'] ?? 0; ?>)
      </a>
    </div>

    <div class="card">
      <?php if (count($campaigns) === 0): ?>
        <div style="text-align: center; padding: 40px;">
          <p class="muted"><?php echo $q ? 'Δεν βρέθηκαν έρανοι για "' . e($q) . '".' : 'Δεν βρέθηκαν έρανοι.'; ?></p>
        </div>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Έρανος</th>
              <th>Δημιουργός</th>
              <th>Κατηγορία</th>
              <th>Κατάσταση</th>
              <th>Στόχος</th>
              <th>Δωρεές</th>
              <th>Ημ/νία</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($campaigns as $c):
              $st = $statusLabels[$c['status']] ?? ['❓', 'Άγνωστο', '#666'];
              ?>
              <tr>
                <td class="small muted">#<?php echo $c['id']; ?></td>
                <td><strong><?php echo e(mb_strimwidth($c['title'], 0, 35, '...', 'UTF-8')); ?></strong></td>
                <td class="small">
                  <?php if ($c['user_name']): ?>
                    <?php echo e($c['user_name']); ?>
                  <?php else: ?>
                    <?php echo e($c['org_name']); ?>
                  <?php endif; ?>
                </td>
                <td class="small"><?php echo category_icon($c['category_id']); ?></td>
                <td>
                  <span style="color: <?php echo $st[2]; ?>; font-weight: 500;">
                    <?php echo $st[0] . ' ' . $st[1]; ?>
                  </span>
                </td>
                <td class="small"><?php echo $c['target_amount'] ? money_eur((int) $c['target_amount']) : '—'; ?></td>
                <td class="small"><?php echo (int) $c['donation_count']; ?></td>
                <td class="small muted"><?php echo date('d/m/y', strtotime($c['created_at'])); ?></td>
                <td>
                  <a class="btn" href="<?php echo BASE_URL; ?>/admin/campaign-review.php?id=<?php echo (int) $c['id']; ?>">
                    <?php echo $c['status'] === 'pending' ? 'Έλεγχος' : 'Προβολή'; ?>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
          <div style="margin-top: 20px; padding: 0 16px 16px; display: flex; justify-content: center; gap: 6px;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <a href="<?php echo buildCampaignsUrl(['page' => $i]); ?>"
                class="btn <?php echo $i === $page ? 'primary' : ''; ?>" style="padding: 6px 12px; min-width: 32px;">
                <?php echo $i; ?>
              </a>
            <?php endfor; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <div class="small muted" style="text-align: center; margin-top: 16px;">
      Σύνολο: <?php echo $totalCampaigns; ?> έρανοι<?php echo $q ? ' (φιλτράρισμα: "' . e($q) . '")' : ''; ?>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
