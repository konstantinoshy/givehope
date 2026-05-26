<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';

require_org();
$pdo = db();
$orgId = (int) current_org()['id'];

$stmt = $pdo->prepare("
  SELECT d.*, c.title AS campaign_title
  FROM donations d
  JOIN campaigns c ON c.id = d.campaign_id
  WHERE c.org_id = :org
  ORDER BY d.created_at DESC
  LIMIT 200
");
$stmt->execute([':org' => $orgId]);
$donations = $stmt->fetchAll();

$totalAmount = array_sum(array_column($donations, 'amount'));
?>

<div style="max-width: 1100px; margin: 0 auto; padding: 32px 24px;">
  <div class="card" style="margin-bottom: 24px;">
    <div style="margin-bottom: 16px;">
      <a href="<?php echo BASE_URL; ?>/dashboard/index.php" class="muted" style="font-size: 14px;">← Πίσω στο
        Dashboard</a>
      <h1 style="margin: 12px 0 8px;">Δωρεές</h1>
    </div>

    <div class="row" style="max-width: 400px;">
      <div class="kpi">
        <div class="val"><?php echo count($donations); ?></div>
        <div class="lbl">Δωρεές</div>
      </div>
      <div class="kpi">
        <div class="val"><?php echo money_eur($totalAmount); ?></div>
        <div class="lbl">Σύνολο</div>
      </div>
    </div>
  </div>

  <div class="card">
    <?php if (count($donations) === 0): ?>
      <div style="text-align: center; padding: 40px;">
        <div style="font-size: 48px; margin-bottom: 16px;">💝</div>
        <p class="muted">Δεν υπάρχουν δωρεές ακόμη.</p>
      </div>
    <?php else: ?>
      <table class="table">
        <thead>
          <tr>
            <th>Ημερομηνία</th>
            <th>Δωρητής</th>
            <th>Ποσό</th>
            <th>Έρανος</th>
            <th>Μήνυμα</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($donations as $d): ?>
            <tr>
              <td>
                <strong><?php echo date('d/m/Y', strtotime($d['created_at'])); ?></strong><br>
                <span class="small muted"><?php echo date('H:i', strtotime($d['created_at'])); ?></span>
              </td>
              <td>
                <?php
                $rawDName = $d['donor_name'] ?: '';
                $dName = ($d['is_anonymous'] || mb_strtolower(trim($rawDName), 'UTF-8') === 'anonymous') ? 'Ανώνυμος' : ($rawDName ?: 'Ανώνυμος');
                echo e($dName);
                ?>
                <?php if ($d['donor_email'] && !$d['is_anonymous']): ?>
                  <br><span class="small muted"><?php echo e($d['donor_email']); ?></span>
                <?php endif; ?>
              </td>
              <td><strong style="color: var(--primary);"><?php echo money_eur((int) $d['amount']); ?></strong></td>
              <td class="small"><?php echo e($d['campaign_title']); ?></td>
              <td class="small muted"><?php echo e($d['message'] ?: '—'); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>