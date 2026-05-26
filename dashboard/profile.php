<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/header.php';

require_org();
$pdo = db();
$orgSess = current_org();
$orgId = (int)$orgSess['id'];

$st = $pdo->prepare("SELECT * FROM organizations WHERE id=:id");
$st->execute([':id'=>$orgId]);
$org = $st->fetch();

if (!$org) {
    echo '<div class="card"><h1>Δεν βρέθηκε οργανισμός</h1></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$ok = null;
$error = null;

if (is_post()) {
    csrf_verify();
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');
    $newPass = $_POST['new_password'] ?? '';

    if (mb_strlen($name,'UTF-8') < 3) $error = "Το όνομα πρέπει να έχει τουλάχιστον 3 χαρακτήρες.";
    elseif ($newPass !== '' && mb_strlen($newPass,'UTF-8') < 8) $error = "Ο νέος κωδικός πρέπει να έχει τουλάχιστον 8 χαρακτήρες.";
    else {
        $pdo->beginTransaction();
        try {
            $upd = $pdo->prepare("UPDATE organizations SET name=:n, phone=:p, website=:w, description=:d, image_url=:img WHERE id=:id");
            $upd->execute([
                ':n'=>$name,
                ':p'=>($phone===''?null:$phone),
                ':w'=>($website===''?null:$website),
                ':d'=>($desc===''?null:$desc),
                ':img'=>($imageUrl===''?null:$imageUrl),
                ':id'=>$orgId
            ]);

            if ($newPass !== '') {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $upd2 = $pdo->prepare("UPDATE organizations SET password_hash=:h WHERE id=:id");
                $upd2->execute([':h'=>$hash, ':id'=>$orgId]);
            }

            $pdo->commit();
            $_SESSION['org']['name'] = $name;
            
            $st = $pdo->prepare("SELECT * FROM organizations WHERE id=:id");
            $st->execute([':id'=>$orgId]);
            $org = $st->fetch();
            
            // Καταγραφή ενημέρωσης προφίλ στο αρχείο επεξεργασίας (Άρθρο 30 GDPR)
            logDataProcessing($pdo, 'organization', $orgId, 'update', 'organization', $orgId, 'Ενημέρωση προφίλ οργανισμού');

            $ok = "Οι αλλαγές αποθηκεύτηκαν!";
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}

$defaultImage = 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=800&q=80';
?>

<div style="max-width: 700px; margin: 0 auto; padding: 32px 24px;">
  <div class="card">
    <div style="margin-bottom: 24px;">
      <a href="<?php echo BASE_URL; ?>/dashboard/index.php" class="muted" style="font-size: 14px;">← Πίσω στο Dashboard</a>
      <h1 style="margin: 12px 0 8px;">Ρυθμίσεις Οργανισμού</h1>
    </div>
    
    <?php if ($ok): ?>
      <div class="notice ok" style="margin-bottom: 20px;"><?php echo e($ok); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="notice warn" style="margin-bottom: 20px;"><?php echo e($error); ?></div>
    <?php endif; ?>

    <!-- Προεπισκόπηση Τρέχουσας Εικόνας -->
    <div style="margin-bottom: 24px; text-align: center;">
      <img src="<?php echo e($org['image_url'] ?: $defaultImage); ?>" alt="<?php echo e($org['name']); ?>" 
           style="width: 150px; height: 150px; object-fit: cover; border-radius: 16px; box-shadow: var(--shadow-md);">
    </div>

    <form method="post">
      <?php echo csrf_field(); ?>
      
      <label>Όνομα Οργανισμού *</label>
      <input name="name" required value="<?php echo e($org['name']); ?>">

      <label>URL Εικόνας Προφίλ</label>
      <input name="image_url" type="url" value="<?php echo e($org['image_url'] ?? ''); ?>" placeholder="https://example.com/logo.jpg">
      <p class="small muted" style="margin-top: 4px;">Η κύρια εικόνα του οργανισμού σας</p>

      <div class="row">
        <div>
          <label>Τηλέφωνο</label>
          <input name="phone" type="tel" value="<?php echo e($org['phone'] ?? ''); ?>" placeholder="+30 210 1234567">
        </div>
        <div>
          <label>Website</label>
          <input name="website" type="url" value="<?php echo e($org['website'] ?? ''); ?>" placeholder="https://...">
        </div>
      </div>

      <label>Περιγραφή</label>
      <textarea name="description" placeholder="Περιγράψτε τον οργανισμό σας..."><?php echo e($org['description'] ?? ''); ?></textarea>

      <div class="hr"></div>
      
      <h3 style="margin-bottom: 8px;">Αλλαγή κωδικού</h3>
      <p class="small muted" style="margin-bottom: 12px;">Αφήστε κενό αν δεν θέλετε αλλαγή</p>
      
      <label>Νέος κωδικός</label>
      <input name="new_password" type="password" placeholder="Τουλάχιστον 8 χαρακτήρες">

      <div class="hr"></div>
      
      <div class="notice" style="margin-bottom: 20px;">
        <strong>Email:</strong> <?php echo e($org['email']); ?><br>
        <span class="small muted">Το email δεν μπορεί να αλλάξει</span>
      </div>

      <button class="btn primary" type="submit" style="width: 100%;">Αποθήκευση Αλλαγών</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
