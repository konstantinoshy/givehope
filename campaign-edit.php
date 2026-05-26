<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/header.php';

require_user();
$pdo = db();
$user = current_user();
$userId = (int)$user['id'];
$id = (int)($_GET['id'] ?? 0);

// Ανάκτηση εράνου (πρέπει να ανήκει στον χρήστη)
$stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = :id AND user_id = :uid");
$stmt->execute([':id' => $id, ':uid' => $userId]);
$campaign = $stmt->fetch();

if (!$campaign) {
    redirect(BASE_URL . "/my-campaigns.php");
}

$error = null;
$success = null;

if (is_post()) {
    csrf_verify();
    
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $story = trim($_POST['story'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');
    $targetAmount = (int)($_POST['target_amount'] ?? 0);
    
    if (mb_strlen($title, 'UTF-8') < 10) {
        $error = "Ο τίτλος πρέπει να έχει τουλάχιστον 10 χαρακτήρες.";
    } elseif (mb_strlen($description, 'UTF-8') < 50) {
        $error = "Η περιγραφή πρέπει να έχει τουλάχιστον 50 χαρακτήρες.";
    } else {
        // FIX: Change status to pending if it was previously approved
        $newStatus = ($campaign['status'] === 'approved') ? 'pending' : $campaign['status'];

        $upd = $pdo->prepare("
            UPDATE campaigns 
            SET title = :title, description = :desc, story = :story, image_url = :img, target_amount = :target, status = :status
            WHERE id = :id AND user_id = :uid
        ");
        $upd->execute([
            ':title' => $title,
            ':desc' => $description,
            ':story' => ($story === '' ? null : $story),
            ':img' => ($imageUrl === '' ? null : $imageUrl),
            ':target' => max(100, $targetAmount),
            ':status' => $newStatus,
            ':id' => $id,
            ':uid' => $userId,
        ]);
        
        // Ανανέωση δεδομένων εράνου
        $stmt->execute([':id' => $id, ':uid' => $userId]);
        $campaign = $stmt->fetch();
        
        $success = "Οι αλλαγές αποθηκεύτηκαν!" . ($newStatus === 'pending' ? " Ο έρανος θα επανελεγχθεί από την ομάδα μας." : "");
    }
}
?>

<div style="max-width: 700px; margin: 0 auto; padding: 32px 24px;">
  <div class="card">
    <div style="margin-bottom: 24px;">
      <a href="<?php echo BASE_URL; ?>/my-campaigns.php" class="muted" style="font-size: 14px;">← Πίσω στους εράνους μου</a>
      <h1 style="margin: 12px 0 8px;">Επεξεργασία Εράνου</h1>
      
      <?php 
      $statusLabels = [
          'pending' => ['⏳', 'Αναμονή Έγκρισης', '#f59e0b'],
          'approved' => ['✅', 'Ενεργός', '#02a95c'],
          'rejected' => ['❌', 'Απορρίφθηκε', '#dc3545'],
          'suspended' => ['⚠️', 'Σε Αναστολή', '#dc3545'],
      ];
      $status = $statusLabels[$campaign['status']] ?? ['❓', 'Άγνωστο', '#666'];
      ?>
      <span class="badge" style="background: <?php echo $status[2]; ?>15; color: <?php echo $status[2]; ?>;">
        <?php echo $status[0] . ' ' . $status[1]; ?>
      </span>
    </div>
    
    <?php if ($success): ?>
      <div class="notice ok" style="margin-bottom: 20px;"><?php echo e($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
      <div class="notice warn" style="margin-bottom: 20px;"><?php echo e($error); ?></div>
    <?php endif; ?>
    
    <?php if ($campaign['status'] === 'rejected' && $campaign['rejection_reason']): ?>
      <div class="notice warn" style="margin-bottom: 20px;">
        <strong>Λόγος απόρριψης:</strong> <?php echo e($campaign['rejection_reason']); ?>
      </div>
    <?php endif; ?>

    <form method="post">
      <?php echo csrf_field(); ?>
      
      <label>Τίτλος εράνου *</label>
      <input name="title" required value="<?php echo e($campaign['title']); ?>" maxlength="180">

      <label>Σύντομη περιγραφή *</label>
      <textarea name="description" required style="min-height: 100px;"><?php echo e($campaign['description']); ?></textarea>

      <label>Η ιστορία σας</label>
      <textarea name="story" style="min-height: 200px;"><?php echo e($campaign['story'] ?? ''); ?></textarea>

      <label>URL Εικόνας</label>
      <input name="image_url" type="url" value="<?php echo e($campaign['image_url'] ?? ''); ?>" placeholder="https://example.com/image.jpg">

      <label>Στόχος (€) *</label>
      <input name="target_amount" type="number" min="100" step="100" required value="<?php echo (int)$campaign['target_amount']; ?>">
      
      <?php if ($campaign['current_amount'] > 0): ?>
        <div class="notice" style="margin-top: 12px;">
          <strong>Πρόοδος:</strong> <?php echo money_eur((int)$campaign['current_amount']); ?> / <?php echo money_eur((int)$campaign['target_amount']); ?>
        </div>
      <?php endif; ?>

      <div class="hr"></div>
      
      <div class="row">
        <a href="<?php echo BASE_URL; ?>/my-campaigns.php" class="btn" style="justify-content: center;">Ακύρωση</a>
        <button class="btn primary" type="submit" style="justify-content: center;">Αποθήκευση</button>
      </div>
      
      <?php if ($campaign['status'] === 'rejected'): ?>
        <div class="hr"></div>
        <p class="small muted" style="text-align: center;">
          Αφού κάνετε τις διορθώσεις, ο έρανος θα υποβληθεί ξανά για έγκριση.
        </p>
      <?php endif; ?>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

