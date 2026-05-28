<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/header.php';

require_org();
$pdo = db();
$org = current_org();
$orgId = (int) $org['id'];
$id = (int) ($_GET['id'] ?? 0);

// Ανάκτηση εράνου
$stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = :id AND org_id = :oid");
$stmt->execute([':id' => $id, ':oid' => $orgId]);
$campaign = $stmt->fetch();

if (!$campaign) {
  redirect(BASE_URL . "/dashboard/index.php");
}

$error = null;
$success = null;

if (is_post()) {
  csrf_verify();

  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $targetAmount = (int) ($_POST['target_amount'] ?? 0);
  $imageUrl = trim($_POST['image_url'] ?? '');

  $newStatus = $campaign['status'];
  if (isset($_POST['mark_completed']) && $campaign['status'] === 'approved') {
    $newStatus = 'completed';
  }

  if ($imageUrl !== '' && !is_safe_image_url($imageUrl)) {
    $error = "Το URL εικόνας δεν είναι έγκυρο (επιτρέπονται μόνο http/https).";
  } elseif (mb_strlen($title, 'UTF-8') < 5) {
    $error = "Ο τίτλος πρέπει να έχει τουλάχιστον 5 χαρακτήρες.";
  } elseif (mb_strlen($description, 'UTF-8') < 15) {
    $error = "Η περιγραφή πρέπει να έχει τουλάχιστον 15 χαρακτήρες.";
  } else {
    $upd = $pdo->prepare("
            UPDATE campaigns
            SET title = :t, description = :d, type = :ty, target_amount = :ta, status = :s, image_url = :img
            WHERE id = :id AND org_id = :oid
        ");
    $upd->execute([
      ':t' => $title,
      ':d' => $description,
      ':ty' => 'money',
      ':ta' => max(100, $targetAmount),
      ':s' => $newStatus,
      ':img' => ($imageUrl === '' ? null : $imageUrl),
      ':id' => $id,
      ':oid' => $orgId,
    ]);

    // Ανανέωση
    $stmt->execute([':id' => $id, ':oid' => $orgId]);
    $campaign = $stmt->fetch();

    $success = "Αποθηκεύτηκε!";
  }
}
?>

<div style="max-width: 700px; margin: 0 auto; padding: 32px 24px;">
  <div class="card">
    <div style="margin-bottom: 24px;">
      <a href="<?php echo BASE_URL; ?>/dashboard/index.php" class="muted" style="font-size: 14px;">← Πίσω στο
        Dashboard</a>
      <h1 style="margin: 12px 0 8px;">Επεξεργασία Εράνου</h1>
    </div>

    <?php if ($success): ?>
      <div class="notice ok" style="margin-bottom: 20px;"><?php echo e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="notice warn" style="margin-bottom: 20px;"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post">
      <?php echo csrf_field(); ?>

      <label>Τίτλος *</label>
      <input name="title" required value="<?php echo e($campaign['title']); ?>">

      <label>Περιγραφή *</label>
      <textarea name="description" required><?php echo e($campaign['description']); ?></textarea>

      <label>URL Εικόνας</label>
      <input name="image_url" type="url" value="<?php echo e($campaign['image_url'] ?? ''); ?>"
        placeholder="https://...">

      <?php if ($campaign['status'] === 'approved'): ?>
      <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 16px;">
        <input type="checkbox" name="mark_completed" value="1" style="width: 18px; height: 18px;">
        <span>Σήμανση ως Ολοκληρώθηκε</span>
      </label>
      <?php endif; ?>

      <label>Στόχος (€) *</label>
      <input name="target_amount" type="number" min="100" required
        value="<?php echo (int) ($campaign['target_amount'] ?? 1000); ?>">

      <div class="notice" style="margin-top: 12px;">
        <strong>Πρόοδος:</strong> <?php echo money_eur((int) $campaign['current_amount']); ?> /
        <?php echo money_eur((int) $campaign['target_amount']); ?>
      </div>

      <div class="hr"></div>

      <div class="row">
        <a href="<?php echo BASE_URL; ?>/dashboard/index.php" class="btn" style="justify-content: center;">Ακύρωση</a>
        <button class="btn primary" type="submit" style="justify-content: center;">Αποθήκευση</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>