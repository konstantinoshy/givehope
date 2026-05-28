<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

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
    $story = trim($_POST['story'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $type = 'money';
    $targetAmount = (int)($_POST['target_amount'] ?? 0);
    $imageUrl = trim($_POST['image_url'] ?? '');
    
    // Επικύρωση δεδομένων
    if (mb_strlen($title, 'UTF-8') < 10) {
        $error = "Ο τίτλος πρέπει να έχει τουλάχιστον 10 χαρακτήρες.";
    } elseif (mb_strlen($description, 'UTF-8') < 50) {
        $error = "Η περιγραφή πρέπει να έχει τουλάχιστον 50 χαρακτήρες.";
    } elseif ($categoryId <= 0) {
        $error = "Επιλέξτε κατηγορία.";
    } elseif ($targetAmount < 100) {
        $error = "Ο στόχος πρέπει να είναι τουλάχιστον 100€.";
    } else {
        // Έλεγχος διπλοεγγραφής (Double Submission)
        $dupCheck = $pdo->prepare("SELECT id FROM campaigns WHERE org_id = :oid AND title = :title AND created_at > (NOW() - INTERVAL 1 MINUTE)");
        $dupCheck->execute([':oid' => $orgId, ':title' => $title]);
        
        if ($dupCheck->fetch()) {
            flash('ok', 'Ο έρανος έχει ήδη υποβληθεί.');
            redirect(BASE_URL . "/dashboard/index.php");
        }

        // Οι επαληθευμένοι οργανισμοί εγκρίνονται αυτόματα
        $status = $org['verified'] ? 'approved' : 'pending';
        
        $ins = $pdo->prepare("
            INSERT INTO campaigns (org_id, title, description, story, category_id, type, target_amount, image_url, status, approved_at)
            VALUES (:oid, :title, :desc, :story, :cat, :type, :target, :img, :status, " . ($status === 'approved' ? 'NOW()' : 'NULL') . ")
        ");
        $ins->execute([
            ':oid' => $orgId,
            ':title' => $title,
            ':desc' => $description,
            ':story' => ($story === '' ? null : $story),
            ':cat' => $categoryId,
            ':type' => 'money',
            ':target' => max(100, $targetAmount),
            ':img' => ($imageUrl === '' ? null : $imageUrl),
            ':status' => $status,
        ]);
        
        if ($status === 'approved') {
            flash('ok', 'Ο έρανος "<strong>' . e($title) . '</strong>" δημοσιεύτηκε επιτυχώς!');
        } else {
            flash('ok', 'Ο έρανος "<strong>' . e($title) . '</strong>" υποβλήθηκε για έγκριση! Η ομάδα μας θα τον ελέγξει εντός 1-2 εργάσιμων ημερών.');
        }
        redirect(BASE_URL . "/dashboard/index.php");
    }
}

// Διατήρηση τιμών φόρμας
$form = [
    'title' => $_POST['title'] ?? '',
    'description' => $_POST['description'] ?? '',
    'story' => $_POST['story'] ?? '',
    'category_id' => $_POST['category_id'] ?? '',
    'type' => 'money',
    'target_amount' => $_POST['target_amount'] ?? 5000,
    'image_url' => $_POST['image_url'] ?? '',
];

require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 700px; margin: 0 auto; padding: 32px 24px;">
  <div class="card">
    <div style="text-align: center; margin-bottom: 32px;">
      <h1 style="margin: 0 0 8px;">Δημιουργία Εράνου</h1>
      <p class="muted" style="margin: 0;">Συμπληρώστε τα στοιχεία του εράνου σας</p>
      <?php if ($org['verified']): ?>
        <p class="small" style="color: var(--primary); margin-top: 8px;">✓ Ως επαληθευμένος οργανισμός, οι έρανοί σας εγκρίνονται αυτόματα.</p>
      <?php endif; ?>
    </div>
    
    <?php if ($error): ?>
      <div class="notice warn" style="margin-bottom: 20px;"><?php echo e($error); ?></div>
    <?php endif; ?>
    
    <!-- Πληροφορίες -->
    <div class="notice" style="margin-bottom: 24px; padding: 20px;">
      <strong style="display: block; margin-bottom: 16px; font-size: 15px;">ℹ️ Πώς λειτουργεί:</strong>
      <div style="display: flex; flex-direction: column; gap: 12px;">
        <div style="display: flex; align-items: flex-start; gap: 12px;">
          <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: bold; flex-shrink: 0; margin-top: 1px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">1</div>
          <div style="font-size: 14px; color: var(--text);">Συμπληρώνετε τα στοιχεία του εράνου</div>
        </div>
        <div style="display: flex; align-items: flex-start; gap: 12px;">
          <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: bold; flex-shrink: 0; margin-top: 1px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">2</div>
          <div style="font-size: 14px; color: var(--text);">Ο έρανος υποβάλλεται για έγκριση</div>
        </div>
        <div style="display: flex; align-items: flex-start; gap: 12px;">
          <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: bold; flex-shrink: 0; margin-top: 1px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">3</div>
          <div style="font-size: 14px; color: var(--text);">Η ομάδα μας ελέγχει και εγκρίνει</div>
        </div>
        <div style="display: flex; align-items: flex-start; gap: 12px;">
          <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: bold; flex-shrink: 0; margin-top: 1px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">4</div>
          <div style="font-size: 14px; color: var(--text);">Ο έρανος γίνεται δημόσιος!</div>
        </div>
      </div>
    </div>

    <form method="post" class="js-validate">
      <?php echo csrf_field(); ?>
      
      <!-- Κατηγορία -->
      <label>Κατηγορία *</label>
      <div class="category-grid" data-radio-group="category_id" data-error="Επιλέξτε κατηγορία."
        style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 16px;">
        <?php foreach ($categories as $cat): ?>
          <label style="display: flex; align-items: center; gap: 12px; padding: 14px; border: 2px solid var(--border); border-radius: 12px; cursor: pointer; margin: 0; transition: all 0.15s ease;"
                 class="category-option">
            <input type="radio" name="category_id" value="<?php echo (int)$cat['id']; ?>" 
                   <?php echo $form['category_id'] == $cat['id'] ? 'checked' : ''; ?>
                   style="width: 18px; height: 18px;" required>
            <span style="font-size: 24px;"><?php echo e($cat['icon']); ?></span>
            <span style="font-weight: 500;"><?php echo e($cat['name']); ?></span>
          </label>
        <?php endforeach; ?>
      </div>

      <!-- Τίτλος -->
      <label>Τίτλος εράνου * <span class="char-count" id="titleCount"></span></label>
      <input name="title" required minlength="10" value="<?php echo e($form['title']); ?>" 
             placeholder="π.χ. Βοήθεια για επέμβαση καρδιάς" maxlength="180"
             data-char-count="#titleCount" data-error="Ο τίτλος πρέπει να έχει τουλάχιστον 10 χαρακτήρες.">
      <p class="small muted" style="margin-top: 4px;">Σύντομος και περιγραφικός τίτλος</p>

      <!-- Περιγραφή -->
      <label>Σύντομη περιγραφή * <span class="char-count" id="descriptionCount"></span></label>
      <textarea name="description" required minlength="50"
        placeholder="Περιγράψτε σύντομα τον σκοπό του εράνου..." style="min-height: 100px;"
        data-char-count="#descriptionCount" data-recommended="200"
        data-error="Η περιγραφή πρέπει να έχει τουλάχιστον 50 χαρακτήρες."><?php echo e($form['description']); ?></textarea>
      <p class="small muted" style="margin-top: 4px;">Αυτό εμφανίζεται στις καρτέλες (50-200 χαρακτήρες)</p>

      <!-- Ιστορία -->
      <label>Η ιστορία σας <span class="char-count" id="storyCount"></span></label>
      <textarea name="story"
        placeholder="Πείτε την ιστορία σας αναλυτικά. Γιατί χρειάζεστε βοήθεια; Πώς θα χρησιμοποιηθούν τα χρήματα;"
        style="min-height: 200px;" data-char-count="#storyCount" data-recommended="1000"><?php echo e($form['story']); ?></textarea>
      <p class="small muted" style="margin-top: 4px;">Μια συγκινητική ιστορία αυξάνει τις δωρεές</p>

      <!-- Στόχος -->
      <label>Στόχος (€) *</label>
      <input name="target_amount" id="targetAmount" type="number" min="100" step="100" required
        value="<?php echo (int)$form['target_amount']; ?>" data-error="Ο στόχος πρέπει να είναι τουλάχιστον 100€."
        placeholder="π.χ. 5000">
      <p class="small muted" style="margin-top: 4px;">Ελάχιστο 100€</p>

      <!-- Εικόνα -->
      <label>Εικόνα εράνου</label>
      <input name="image_url" type="url" value="<?php echo e($form['image_url']); ?>" placeholder="https://example.com/image.jpg"
        data-image-preview="#campaignImagePreviewImg" data-image-preview-wrapper="#campaignImagePreview"
        data-error="Το URL της εικόνας δεν είναι έγκυρο.">
      <p class="small muted" style="margin-top: 4px;">URL εικόνας που αντιπροσωπεύει τον έρανο</p>
      <div class="image-preview" id="campaignImagePreview" style="display: none;">
        <img id="campaignImagePreviewImg" src="" alt="Προεπισκόπηση εικόνας">
        <div class="image-preview-meta">Προεπισκόπηση εικόνας</div>
      </div>

      <div class="hr"></div>
      
      <!-- Όροι -->
      <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; margin-bottom: 20px;">
        <input type="checkbox" name="terms_confirm" required style="width: 20px; height: 20px; margin-top: 2px;"
          data-error="Πρέπει να αποδεχτείτε τους όρους πριν συνεχίσετε.">
        <span class="small">
          Δηλώνω ότι οι πληροφορίες είναι αληθείς και αναλαμβάνω την ευθύνη για την ακρίβειά τους. 
          Κατανοώ ότι ψευδείς δηλώσεις μπορεί να οδηγήσουν σε διαγραφή του εράνου.
        </span>
      </label>

      <button class="btn primary" type="submit" style="width: 100%;">Υποβολή για Έγκριση</button>
      
      <p class="small muted" style="text-align: center; margin-top: 16px;">
        <?php if ($org['verified']): ?>
          Ως επαληθευμένος οργανισμός, ο έρανος θα δημοσιευτεί αυτόματα.
        <?php else: ?>
          Ο έρανος θα ελεγχθεί από την ομάδα μας πριν δημοσιευτεί.
        <?php endif; ?>
      </p>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
