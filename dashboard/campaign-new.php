<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/header.php';

require_org();
$pdo = db();
$org = current_org();
$orgId = (int)$org['id'];

$categories = $pdo->query("SELECT * FROM categories ORDER BY id")->fetchAll();

$error = null;

if (is_post()) {
    csrf_verify();
    
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $type = 'money';
    $targetAmount = (int)($_POST['target_amount'] ?? 0);
    $imageUrl = trim($_POST['image_url'] ?? '');
    
    if (mb_strlen($title, 'UTF-8') < 5) {
        $error = "Ο τίτλος πρέπει να έχει τουλάχιστον 5 χαρακτήρες.";
    } elseif (mb_strlen($description, 'UTF-8') < 15) {
        $error = "Η περιγραφή πρέπει να έχει τουλάχιστον 15 χαρακτήρες.";
    } elseif ($categoryId <= 0) {
        $error = "Επιλέξτε κατηγορία.";
    } else {
        // Οι επαληθευμένοι οργανισμοί εγκρίνονται αυτόματα
        $status = $org['verified'] ? 'approved' : 'pending';
        
        $ins = $pdo->prepare("
            INSERT INTO campaigns (org_id, title, description, category_id, type, target_amount, image_url, status, approved_at)
            VALUES (:oid, :title, :desc, :cat, :type, :target, :img, :status, " . ($status === 'approved' ? 'NOW()' : 'NULL') . ")
        ");
        $ins->execute([
            ':oid' => $orgId,
            ':title' => $title,
            ':desc' => $description,
            ':cat' => $categoryId,
            ':type' => 'money',
            ':target' => max(1, $targetAmount),
            ':img' => ($imageUrl === '' ? null : $imageUrl),
            ':status' => $status,
        ]);
        
        redirect(BASE_URL . "/dashboard/index.php");
    }
}

$form = [
    'title' => $_POST['title'] ?? '',
    'description' => $_POST['description'] ?? '',
    'category_id' => $_POST['category_id'] ?? '',
    'type' => 'money',
    'target_amount' => $_POST['target_amount'] ?? 1000,
    'image_url' => $_POST['image_url'] ?? '',
];
?>

<div style="max-width: 700px; margin: 0 auto; padding: 32px 24px;">
  <div class="card">
    <div style="margin-bottom: 24px;">
      <a href="<?php echo BASE_URL; ?>/dashboard/index.php" class="muted" style="font-size: 14px;">← Πίσω στο Dashboard</a>
      <h1 style="margin: 12px 0 8px;">Νέος Έρανος</h1>
      <?php if ($org['verified']): ?>
        <p class="small" style="color: var(--primary);">✓ Ως επαληθευμένος οργανισμός, οι έρανοί σας εγκρίνονται αυτόματα.</p>
      <?php else: ?>
        <p class="small muted">Ο έρανος θα υποβληθεί για έγκριση.</p>
      <?php endif; ?>
    </div>
    
    <?php if ($error): ?>
      <div class="notice warn" style="margin-bottom: 20px;"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post">
      <?php echo csrf_field(); ?>

      <label>Κατηγορία *</label>
      <select name="category_id" required>
        <option value="">Επιλέξτε...</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?php echo (int)$cat['id']; ?>" <?php echo $form['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
            <?php echo e($cat['icon'] . ' ' . $cat['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label>Τίτλος *</label>
      <input name="title" required value="<?php echo e($form['title']); ?>" placeholder="π.χ. Ιατρική υποστήριξη για οικογένειες">

      <label>Περιγραφή *</label>
      <textarea name="description" required placeholder="Περιγράψτε τον σκοπό του εράνου..."><?php echo e($form['description']); ?></textarea>

      <label>URL Εικόνας</label>
      <input name="image_url" type="url" value="<?php echo e($form['image_url']); ?>" placeholder="https://example.com/image.jpg">

      <label>Στόχος (€) *</label>
      <input name="target_amount" type="number" min="100" required value="<?php echo (int)$form['target_amount']; ?>" placeholder="π.χ. 5000">
      <p class="small muted" style="margin-top: 4px;">Ελάχιστο 100€</p>

      <div class="hr"></div>
      
      <div class="row">
        <a href="<?php echo BASE_URL; ?>/dashboard/index.php" class="btn" style="justify-content: center;">Ακύρωση</a>
        <button class="btn primary" type="submit" style="justify-content: center;">Δημιουργία</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

