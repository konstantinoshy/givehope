<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/header.php';

require_user();
$pdo = db();
$user = current_user();
$userId = (int) $user['id'];

// Ανάκτηση εράνων του χρήστη
$stmt = $pdo->prepare("
    SELECT c.*, cat.name AS category_name, cat.icon AS category_icon,
           (SELECT COUNT(*) FROM donations WHERE campaign_id = c.id) AS donation_count
    FROM campaigns c
    JOIN categories cat ON cat.id = c.category_id
    WHERE c.user_id = :uid
    ORDER BY c.created_at DESC
");
$stmt->execute([':uid' => $userId]);
$campaigns = $stmt->fetchAll();

// Στατιστικά
$totalRaised = 0;
$totalDonations = 0;
foreach ($campaigns as $c) {
  $totalRaised += (int) $c['current_amount'];
  $totalDonations += (int) $c['donation_count'];
}

$statusLabels = [
  'draft' => ['', 'Πρόχειρο', '#666'],
  'pending' => ['', 'Αναμονή Έγκρισης', '#f59e0b'],
  'approved' => ['', 'Ενεργός', '#02a95c'],
  'rejected' => ['', 'Απορρίφθηκε', '#dc3545'],
  'suspended' => ['', 'Σε Αναστολή', '#dc3545'],
  'completed' => ['', 'Ολοκληρώθηκε', '#666'],
];

$defaultImage = 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=800&q=80';
?>

<?php $flash = get_flash(); ?>
<div style="max-width: 1100px; margin: 0 auto; padding: 32px 24px;">
  <?php if ($flash): ?>
    <div class="notice <?php echo e($flash['type']); ?>" style="margin-bottom: 20px;">
      <?php echo strip_tags($flash['message'], '<strong><em><br>'); ?>
    </div>
  <?php endif; ?>

  <!-- Κεφαλίδα -->
  <div class="card" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
      <div>
        <p class="small muted" style="margin-bottom: 4px;">Καλώς ήρθες,</p>
        <h1 style="margin: 0 0 8px;"><?php echo e($user['name']); ?></h1>
        <p class="muted" style="margin: 0;">Διαχειρίσου τους εράνους σου</p>
        <?php if (!$user['id_verified']): ?>
          <div class="notice warn small" style="margin-top: 12px; display: inline-block;">
            Ο λογαριασμός σου δεν είναι επαληθευμένος. Οι έρανοι απαιτούν έγκριση.
          </div>
        <?php endif; ?>
      </div>
      <a class="btn primary" href="<?php echo BASE_URL; ?>/campaign-create.php">+ Νέος Έρανος</a>
    </div>

    <div class="hr"></div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
      <div class="kpi">
        <div class="val"><?php echo count($campaigns); ?></div>
        <div class="lbl">Έρανοι</div>
      </div>
      <div class="kpi">
        <div class="val"><?php echo $totalDonations; ?></div>
        <div class="lbl">Δωρητές</div>
      </div>
      <div class="kpi">
        <div class="val"><?php echo money_eur($totalRaised); ?></div>
        <div class="lbl">Συγκεντρώθηκαν</div>
      </div>
    </div>

    <div class="hr"></div>

    <div class="row">
      <a class="btn" href="<?php echo BASE_URL; ?>/dashboard/my-data.php" style="justify-content: center;">🛡️ Τα Δεδομένα μου (GDPR)</a>
    </div>
  </div>

  <!-- Λίστα Εράνων -->
  <div class="card">
    <h2>Οι έρανοί σου</h2>

    <?php if (count($campaigns) === 0): ?>
      <div style="text-align: center; padding: 40px;">
        <div style="margin-bottom: 16px; color: var(--primary);">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" />
          </svg>
        </div>
        <p class="muted" style="margin-bottom: 16px;">Δεν έχεις δημιουργήσει έρανο ακόμη</p>
        <a class="btn primary" href="<?php echo BASE_URL; ?>/campaign-create.php">Ξεκίνα τον πρώτο σου έρανο</a>
      </div>
    <?php else: ?>
      <div class="hr"></div>

      <div style="display: flex; flex-direction: column; gap: 16px;">
        <?php foreach ($campaigns as $c):
          $image = $c['image_url'] ?: $defaultImage;
          $status = $statusLabels[$c['status']] ?? ['❓', 'Άγνωστο', '#666'];
          $pct = min(100, (int) round(($c['current_amount'] / max(1, $c['target_amount'])) * 100));
          ?>
          <div
            style="display: flex; gap: 16px; padding: 16px; background: var(--bg-gray); border-radius: 12px; align-items: flex-start; flex-wrap: wrap;">
            <img src="<?php echo e($image); ?>" alt=""
              style="width: 120px; height: 90px; object-fit: cover; border-radius: 8px;">

            <div style="flex: 1; min-width: 200px;">
              <div style="display: flex; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
                <span class="badge"
                  style="font-size: 11px; padding: 4px 10px; background: <?php echo $status[2]; ?>15; color: <?php echo $status[2]; ?>;">
                  <?php echo $status[0] . ' ' . $status[1]; ?>
                </span>
                <span class="badge" style="font-size: 11px; padding: 4px 10px;">
                  <?php echo category_icon($c['category_id']); ?>     <?php echo e($c['category_name']); ?>
                </span>
              </div>

              <h3 style="margin: 0 0 8px; font-size: 16px;"><?php echo e($c['title']); ?></h3>

              <div class="progress-bar" style="max-width: 250px; margin-bottom: 4px;">
                <div class="fill" style="width: <?php echo $pct; ?>%;"></div>
              </div>
              <p class="small muted" style="margin: 0;">
                <?php echo money_eur((int) $c['current_amount']); ?> / <?php echo money_eur((int) $c['target_amount']); ?>
                (<?php echo $c['donation_count']; ?> δωρεές)
              </p>

              <?php if ($c['status'] === 'rejected' && $c['rejection_reason']): ?>
                <div class="notice warn small" style="margin-top: 8px; padding: 8px 12px;">
                  <strong>Λόγος απόρριψης:</strong> <?php echo e($c['rejection_reason']); ?>
                </div>
              <?php endif; ?>
            </div>

            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
              <?php if ($c['status'] === 'approved'): ?>
                <a class="btn" href="<?php echo BASE_URL; ?>/campaign.php?id=<?php echo (int) $c['id']; ?>">Προβολή</a>
              <?php endif; ?>
              <a class="btn"
                href="<?php echo BASE_URL; ?>/campaign-edit.php?id=<?php echo (int) $c['id']; ?>">Επεξεργασία</a>
              <form method="post" action="<?php echo BASE_URL; ?>/campaign-delete.php" style="display:inline;"
                data-confirm="Σίγουρα θέλετε να διαγράψετε αυτόν τον έρανο;">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo (int) $c['id']; ?>">
                <button type="submit" class="btn danger">Διαγραφή</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>