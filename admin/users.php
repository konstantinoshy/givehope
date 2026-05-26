<?php
$adminPageTitle = 'Χρήστες';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/csrf.php';

$pdo = db();

// Διαχείριση εναλλαγής επαλήθευσης
if (is_post()) {
  csrf_verify();
  $action = $_POST['action'] ?? '';

  if ($action === 'verify' || $action === 'unverify') {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $verified = ($action === 'verify') ? 1 : 0;
    $pdo->prepare("UPDATE users SET id_verified = :v WHERE id = :id")->execute([':v' => $verified, ':id' => $userId]);
  }

  if ($action === 'org_verify' || $action === 'org_unverify') {
    $orgId = (int) ($_POST['org_id'] ?? 0);
    $verified = ($action === 'org_verify') ? 1 : 0;
    $pdo->prepare("UPDATE organizations SET verified = :v WHERE id = :id")->execute([':v' => $verified, ':id' => $orgId]);
  }
}

$q = trim($_GET['q'] ?? '');
$tab = $_GET['tab'] ?? 'users';
$perPage = 15;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$searchWhere = '';
$searchParams = [];
if ($q !== '') {
  $searchWhere = "WHERE (u.name LIKE :q1 OR u.email LIKE :q2)";
  $searchParams = [':q1' => "%$q%", ':q2' => "%$q%"];
}

// Χρήστες
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users u $searchWhere");
$countStmt->execute($searchParams);
$totalUsers = (int) $countStmt->fetchColumn();
$totalUserPages = max(1, ceil($totalUsers / $perPage));
$page = min($page, $tab === 'users' ? $totalUserPages : $page);
$userOffset = ($page - 1) * $perPage;

$userSql = "
    SELECT u.*, 
           (SELECT COUNT(*) FROM campaigns WHERE user_id = u.id) AS campaign_count,
           (SELECT COALESCE(SUM(current_amount), 0) FROM campaigns WHERE user_id = u.id) AS total_raised
    FROM users u
    $searchWhere
    ORDER BY u.created_at DESC
    LIMIT $perPage OFFSET $userOffset
";
$userStmt = $pdo->prepare($userSql);
$userStmt->execute($searchParams);
$users = $userStmt->fetchAll();

// Οργανισμοί
$orgSearchWhere = '';
$orgSearchParams = [];
if ($q !== '') {
  $orgSearchWhere = "WHERE (o.name LIKE :q1 OR o.email LIKE :q2)";
  $orgSearchParams = [':q1' => "%$q%", ':q2' => "%$q%"];
}

$orgCountStmt = $pdo->prepare("SELECT COUNT(*) FROM organizations o $orgSearchWhere");
$orgCountStmt->execute($orgSearchParams);
$totalOrgs = (int) $orgCountStmt->fetchColumn();
$totalOrgPages = max(1, ceil($totalOrgs / $perPage));
$orgPage = min($page, $tab === 'orgs' ? $totalOrgPages : $page);
$orgOffset = ($orgPage - 1) * $perPage;

$orgSql = "
    SELECT o.*, 
           (SELECT COUNT(*) FROM campaigns WHERE org_id = o.id) AS campaign_count,
           (SELECT COALESCE(SUM(current_amount), 0) FROM campaigns WHERE org_id = o.id) AS total_raised
    FROM organizations o
    $orgSearchWhere
    ORDER BY o.created_at DESC
    LIMIT $perPage OFFSET $orgOffset
";
$orgStmt = $pdo->prepare($orgSql);
$orgStmt->execute($orgSearchParams);
$orgs = $orgStmt->fetchAll();

function buildUsersUrl($params = []) {
  $defaults = ['q' => $_GET['q'] ?? '', 'tab' => $_GET['tab'] ?? 'users', 'page' => 1];
  $merged = array_merge($defaults, $params);
  $parts = [];
  foreach ($merged as $k => $v) {
    if ($v !== '' && $v !== null) $parts[] = urlencode($k) . '=' . urlencode($v);
  }
  return 'users.php' . ($parts ? '?' . implode('&', $parts) : '');
}
?>
    <div class="page-header">
      <h1 class="page-title">Χρήστες & Οργανισμοί</h1>
    </div>

    <!-- Search -->
    <div class="card" style="padding: 16px; margin-bottom: 24px;">
      <form method="get" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
        <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="Αναζήτηση ονόματος ή email..."
          style="flex: 1; min-width: 200px; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
        <button type="submit" class="btn primary" style="padding: 10px 20px;">Αναζήτηση</button>
        <?php if ($q !== ''): ?>
          <a href="users.php" class="btn" style="padding: 10px 16px;">Καθαρισμός</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Tabs -->
    <div class="filter-tabs" style="margin-bottom: 24px;">
      <a href="<?php echo buildUsersUrl(['tab' => 'users', 'page' => 1]); ?>" class="filter-tab <?php echo $tab === 'users' ? 'active' : ''; ?>">
        Χρήστες (<?php echo $totalUsers; ?>)
      </a>
      <a href="<?php echo buildUsersUrl(['tab' => 'orgs', 'page' => 1]); ?>" class="filter-tab <?php echo $tab === 'orgs' ? 'active' : ''; ?>">
        Οργανισμοί (<?php echo $totalOrgs; ?>)
      </a>
    </div>

    <?php if ($tab === 'users'): ?>
    <!-- Χρήστες -->
    <div class="card">
      <?php if (count($users) === 0): ?>
        <div style="text-align: center; padding: 40px;">
          <p class="muted"><?php echo $q ? 'Δεν βρέθηκαν χρήστες για "' . e($q) . '".' : 'Δεν υπάρχουν χρήστες.'; ?></p>
        </div>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Όνομα</th>
              <th>Email</th>
              <th>Τηλέφωνο</th>
              <th>Έρανοι</th>
              <th>Συγκέντρωσε</th>
              <th>Εγγραφή</th>
              <th>Επαλήθευση</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
              <tr>
                <td class="small muted">#<?php echo $u['id']; ?></td>
                <td><strong><?php echo e($u['name']); ?></strong></td>
                <td class="small"><?php echo e($u['email']); ?></td>
                <td class="small"><?php echo e($u['phone'] ?: '—'); ?></td>
                <td><?php echo (int) $u['campaign_count']; ?></td>
                <td><?php echo money_eur((int) $u['total_raised']); ?></td>
                <td class="small muted"><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></td>
                <td>
                  <form method="post" style="display: inline;">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="user_id" value="<?php echo (int) $u['id']; ?>">
                    <?php if ($u['id_verified']): ?>
                      <button type="submit" name="action" value="unverify" class="btn"
                        style="padding: 6px 12px; font-size: 13px;">
                        <span style="color: var(--primary);">✓</span> Επαληθευμένος
                      </button>
                    <?php else: ?>
                      <button type="submit" name="action" value="verify" class="btn primary"
                        style="padding: 6px 12px; font-size: 13px;">
                        Επαλήθευση
                      </button>
                    <?php endif; ?>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <?php if ($totalUserPages > 1): ?>
          <div style="margin-top: 20px; padding: 0 16px 16px; display: flex; justify-content: center; gap: 6px;">
            <?php for ($i = 1; $i <= $totalUserPages; $i++): ?>
              <a href="<?php echo buildUsersUrl(['page' => $i]); ?>"
                class="btn <?php echo $i === $page ? 'primary' : ''; ?>" style="padding: 6px 12px; min-width: 32px;">
                <?php echo $i; ?>
              </a>
            <?php endfor; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <?php else: ?>
    <!-- Οργανισμοί -->
    <div class="card">
      <?php if (count($orgs) === 0): ?>
        <div style="text-align: center; padding: 40px;">
          <p class="muted"><?php echo $q ? 'Δεν βρέθηκαν οργανισμοί για "' . e($q) . '".' : 'Δεν υπάρχουν οργανισμοί.'; ?></p>
        </div>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Όνομα</th>
              <th>Email</th>
              <th>Website</th>
              <th>Έρανοι</th>
              <th>Συγκέντρωσε</th>
              <th>Εγγραφή</th>
              <th>Κατάσταση</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orgs as $o): ?>
              <tr>
                <td class="small muted">#<?php echo $o['id']; ?></td>
                <td><strong><?php echo e($o['name']); ?></strong></td>
                <td class="small"><?php echo e($o['email']); ?></td>
                <td class="small"><?php echo e($o['website'] ?: '—'); ?></td>
                <td><?php echo (int) $o['campaign_count']; ?></td>
                <td><?php echo money_eur((int) $o['total_raised']); ?></td>
                <td class="small muted"><?php echo date('d/m/Y', strtotime($o['created_at'])); ?></td>
                <td>
                  <form method="post" style="display: inline;">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="org_id" value="<?php echo (int) $o['id']; ?>">
                    <?php if ($o['verified']): ?>
                      <button type="submit" name="action" value="org_unverify" class="btn"
                        style="padding: 6px 12px; font-size: 13px;">
                        <span style="color: var(--primary);">✓</span> Επαληθευμένος
                      </button>
                    <?php else: ?>
                      <button type="submit" name="action" value="org_verify" class="btn primary"
                        style="padding: 6px 12px; font-size: 13px;">
                        Επαλήθευση
                      </button>
                    <?php endif; ?>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <?php if ($totalOrgPages > 1): ?>
          <div style="margin-top: 20px; padding: 0 16px 16px; display: flex; justify-content: center; gap: 6px;">
            <?php for ($i = 1; $i <= $totalOrgPages; $i++): ?>
              <a href="<?php echo buildUsersUrl(['tab' => 'orgs', 'page' => $i]); ?>"
                class="btn <?php echo $i === $orgPage ? 'primary' : ''; ?>" style="padding: 6px 12px; min-width: 32px;">
                <?php echo $i; ?>
              </a>
            <?php endfor; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
    <?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
