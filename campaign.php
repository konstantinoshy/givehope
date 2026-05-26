<?php
// Προβολή εράνου — λεπτομέρειες, δωρητές, κοινοποίηση

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);

// Ανάκτηση στοιχείων εράνου με joins για κατηγορία και δημιουργό
$stmt = $pdo->prepare("
    SELECT c.*, cat.name AS category_name, cat.icon AS category_icon,
           u.id AS user_id, u.name AS user_name, u.id_verified AS user_verified,
           o.id AS org_id, o.name AS org_name, o.verified AS org_verified,
           o.description AS org_description, o.website AS org_website, o.created_at AS org_created_at
    FROM campaigns c
    JOIN categories cat ON cat.id = c.category_id
    LEFT JOIN users u ON u.id = c.user_id
    LEFT JOIN organizations o ON o.id = c.org_id
    WHERE c.id = :id AND c.status = 'approved'
");
$stmt->execute([':id' => $id]);
$campaign = $stmt->fetch();

// Ορισμός Open Graph meta tags για social sharing (πριν το header)
$defaultImage = 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=800&q=80';
if ($campaign) {
  $pageTitle = $campaign['title'];
  $pageDescription = mb_substr($campaign['description'], 0, 160, 'UTF-8');
  $pageImage = $campaign['image_url'] ?: $defaultImage;
  $pageUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// Φόρτωση header μετά τον ορισμό των meta tags
require_once __DIR__ . '/includes/header.php';

if (!$campaign) {
  echo '<div style="max-width: 600px; margin: 60px auto; text-align: center; padding: 0 24px;">
            <div style="font-size: 64px; margin-bottom: 16px;">😕</div>
            <h2>Ο έρανος δεν βρέθηκε</h2>
            <p class="muted">Ο έρανος δεν υπάρχει ή δεν είναι διαθέσιμος.</p>
            <a class="btn primary" href="' . BASE_URL . '/explore.php" style="margin-top: 16px;">Εξερευνήστε εράνους</a>
          </div>';
  require_once __DIR__ . '/includes/footer.php';
  exit;
}

// Ανάκτηση δωρεών
$donations = $pdo->prepare("SELECT * FROM donations WHERE campaign_id = :id ORDER BY created_at DESC LIMIT 20");
$donations->execute([':id' => $id]);
$donations = $donations->fetchAll();

// Top 3 δωρητές (μεγαλύτερες δωρεές)
$topDonors = $pdo->prepare("SELECT * FROM donations WHERE campaign_id = :id ORDER BY amount DESC LIMIT 3");
$topDonors->execute([':id' => $id]);
$topDonors = $topDonors->fetchAll();

$donationCountStmt = $pdo->prepare("SELECT COUNT(*) FROM donations WHERE campaign_id = :id");
$donationCountStmt->execute([':id' => $id]);
$donationCount = (int) $donationCountStmt->fetchColumn();

// Χρήση της εικόνας που ορίστηκε για OG tags
$image = $campaign['image_url'] ?: $defaultImage;

// URL για κοινοποίηση
$shareUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$shareText = 'Στηρίξτε αυτόν τον έρανο: ' . $campaign['title'];
$actualPct = (int) round(($campaign['current_amount'] / max(1, $campaign['target_amount'])) * 100);
$pct = min(100, $actualPct);
$goalReached = $campaign['current_amount'] >= $campaign['target_amount'] && $campaign['target_amount'] > 0;

$isOrg = $campaign['org_id'] !== null;
$creatorName = $isOrg ? $campaign['org_name'] : $campaign['user_name'];
$isVerified = $isOrg ? $campaign['org_verified'] : $campaign['user_verified'];

// Στατιστικά διοργανωτή
$organizerStats = ['campaign_count' => 0, 'total_raised' => 0];
if ($isOrg && $campaign['org_id']) {
  $statsStmt = $pdo->prepare("
        SELECT COUNT(*) as campaign_count, COALESCE(SUM(current_amount), 0) as total_raised 
        FROM campaigns WHERE org_id = :oid AND status = 'approved'
    ");
  $statsStmt->execute([':oid' => $campaign['org_id']]);
  $organizerStats = $statsStmt->fetch();
} elseif (!$isOrg && $campaign['user_id']) {
  $statsStmt = $pdo->prepare("
        SELECT COUNT(*) as campaign_count, COALESCE(SUM(current_amount), 0) as total_raised 
        FROM campaigns WHERE user_id = :uid AND status = 'approved'
    ");
  $statsStmt->execute([':uid' => $campaign['user_id']]);
  $organizerStats = $statsStmt->fetch();
}
?>

<div class="campaign-detail">
  <!-- Πλοήγηση Breadcrumb -->
  <nav class="breadcrumb">
    <a href="<?php echo BASE_URL; ?>/">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
        <polyline points="9 22 9 12 15 12 15 22" />
      </svg>
      Αρχική
    </a>
    <span class="breadcrumb-separator">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="9 18 15 12 9 6" />
      </svg>
    </span>
    <a href="<?php echo BASE_URL; ?>/explore.php?cat=<?php echo (int) $campaign['category_id']; ?>">
      <?php echo category_icon($campaign['category_id']); ?>
      <?php echo e($campaign['category_name']); ?>
    </a>
    <span class="breadcrumb-separator">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="9 18 15 12 9 6" />
      </svg>
    </span>
    <span class="breadcrumb-current"><?php echo e(mb_strimwidth($campaign['title'], 0, 40, '...', 'UTF-8')); ?></span>
  </nav>

  <div class="campaign-detail-grid">
    <!-- Κύριο Περιεχόμενο -->
    <div>
      <!-- Εικόνα -->
      <img src="<?php echo e($image); ?>" alt="<?php echo e($campaign['title']); ?>" class="campaign-hero-image">

      <!-- Τίτλος & Μετα-πληροφορίες -->
      <?php $catColor = category_color($campaign['category_id']); ?>
      <div style="display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
        <span class="badge-category"
          style="background: <?php echo $catColor[0]; ?>; color: <?php echo $catColor[1]; ?>;">
          <?php echo category_icon($campaign['category_id']); ?>
          <?php echo e($campaign['category_name']); ?>
        </span>
        <?php if ($isVerified): ?>
          <span class="badge-verified" data-tooltip="Αυτός ο έρανος έχει επαληθευτεί από το GiveHope">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
              <polyline points="20 6 9 17 4 12" />
            </svg>
            Verified
          </span>
        <?php endif; ?>
      </div>

      <h1 style="margin: 0 0 16px; font-size: 28px;"><?php echo e($campaign['title']); ?></h1>

      <p style="font-size: 16px; color: var(--text-secondary); margin-bottom: 24px;">
        <?php echo e($campaign['description']); ?>
      </p>

      <?php if ($campaign['story']): ?>
        <?php
        $storyLength = mb_strlen($campaign['story'], 'UTF-8');
        $isLongStory = $storyLength > 500;
        ?>
        <div class="card story-section">
          <h3 style="margin-top: 0; display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
              <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
              <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
            </svg>
            Η Ιστορία
          </h3>

          <div class="story-content <?php echo $isLongStory ? 'collapsed' : ''; ?>" id="story-content">
            <div class="story-text" style="white-space: pre-line; line-height: 1.8; color: var(--text-secondary);">
              <?php echo e($campaign['story']); ?>
            </div>
            <?php if ($isLongStory): ?>
              <div class="story-fade"></div>
            <?php endif; ?>
          </div>

          <?php if ($isLongStory): ?>
            <button type="button" class="story-toggle btn" id="story-toggle" onclick="toggleStory()">
              <span class="toggle-text">Διαβάστε περισσότερα</span>
              <svg class="toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <polyline points="6 9 12 15 18 9" />
              </svg>
            </button>
          <?php endif; ?>
        </div>

        <style>
          .story-section {
            position: relative;
          }

          .story-content.collapsed {
            max-height: 200px;
            overflow: hidden;
            position: relative;
          }

          .story-content.collapsed .story-fade {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 80px;
            background: linear-gradient(transparent, white);
            pointer-events: none;
          }

          .story-content.expanded {
            max-height: none;
          }

          .story-content.expanded .story-fade {
            display: none;
          }

          .story-toggle {
            width: 100%;
            margin-top: 12px;
            justify-content: center;
            gap: 8px;
          }

          .story-toggle .toggle-icon {
            transition: transform 0.3s ease;
          }

          .story-toggle.expanded .toggle-icon {
            transform: rotate(180deg);
          }
        </style>

        <script>
          function toggleStory() {
            const content = document.getElementById('story-content');
            const btn = document.getElementById('story-toggle');
            const text = btn.querySelector('.toggle-text');

            if (content.classList.contains('collapsed')) {
              content.classList.remove('collapsed');
              content.classList.add('expanded');
              btn.classList.add('expanded');
              text.textContent = 'Λιγότερα';
            } else {
              content.classList.remove('expanded');
              content.classList.add('collapsed');
              btn.classList.remove('expanded');
              text.textContent = 'Διαβάστε περισσότερα';
            }
          }
        </script>
      <?php endif; ?>

      <!-- Social Sharing / Κοινοποίηση -->
      <div class="card share-section" style="margin-top: 24px;">
        <h3 style="margin-top: 0;">Κοινοποιήστε τον έρανο</h3>
        <p class="small muted" style="margin-bottom: 16px;">Βοηθήστε να φτάσει σε περισσότερους ανθρώπους!</p>

        <div class="share-buttons">
          <!-- Κοινοποίηση Facebook -->
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($shareUrl); ?>" target="_blank"
            rel="noopener noreferrer" class="share-btn facebook"
            onclick="window.open(this.href, 'share-facebook', 'width=600,height=400,menubar=no,toolbar=no'); return false;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path
                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
            </svg>
            Facebook
          </a>

          <!-- Κοινοποίηση Twitter/X -->
          <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($shareUrl); ?>&text=<?php echo urlencode($shareText); ?>"
            target="_blank" rel="noopener noreferrer" class="share-btn twitter"
            onclick="window.open(this.href, 'share-twitter', 'width=600,height=400,menubar=no,toolbar=no'); return false;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path
                d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
            </svg>
            Twitter
          </a>

          <!-- Κοινοποίηση LinkedIn -->
          <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($shareUrl); ?>"
            target="_blank" rel="noopener noreferrer" class="share-btn linkedin"
            onclick="window.open(this.href, 'share-linkedin', 'width=600,height=400,menubar=no,toolbar=no'); return false;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path
                d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
            </svg>
            LinkedIn
          </a>

          <!-- Κοινοποίηση WhatsApp -->
          <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($shareText . ' ' . $shareUrl); ?>"
            target="_blank" rel="noopener noreferrer" class="share-btn whatsapp">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path
                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
            </svg>
            WhatsApp
          </a>

          <!-- Αποστολή με Email -->
          <a href="mailto:?subject=<?php echo rawurlencode($shareText); ?>&body=<?php echo rawurlencode($campaign['description'] . "\n\n" . $shareUrl); ?>"
            class="share-btn email">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
              <polyline points="22,6 12,13 2,6" />
            </svg>
            Email
          </a>

          <!-- Αντιγραφή Συνδέσμου -->
          <button type="button" class="share-btn copy" onclick="copyShareLink('<?php echo e($shareUrl); ?>')">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
              <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
            </svg>
            <span id="copy-text">Αντιγραφή Link</span>
          </button>
        </div>
      </div>

      <!-- Top Δωρητές -->
      <?php if (count($topDonors) > 0): ?>
        <div class="card top-donors-card" style="margin-top: 24px;">
          <h3 style="margin-top: 0; display: flex; align-items: center; gap: 8px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2">
              <path d="M12 15l-2 5-1-1-4 2 2-4-1-1 5-2" />
              <path d="M6 6l2 5 1-1 4 2-2 4 1 1-5 2" />
              <circle cx="12" cy="8" r="3" />
            </svg>
            Top Δωρητές
          </h3>
          <div class="top-donors-grid">
            <?php
            $medals = ['1', '2', '3'];
            foreach ($topDonors as $i => $td):
              $rawName = $td['donor_name'] ?: '';
              $donorName = ($td['is_anonymous'] || mb_strtolower(trim($rawName), 'UTF-8') === 'anonymous') ? 'Ανώνυμος' : ($rawName ?: 'Ανώνυμος');
              $initial = mb_substr($donorName, 0, 1, 'UTF-8');
              ?>
              <div class="top-donor <?php echo $i === 0 ? 'gold' : ($i === 1 ? 'silver' : 'bronze'); ?>">
                <div class="top-donor-medal"><?php echo $medals[$i]; ?></div>
                <div class="top-donor-avatar"><?php echo e($initial); ?></div>
                <div class="top-donor-info">
                  <strong><?php echo e($donorName); ?></strong>
                  <span class="top-donor-amount"><?php echo money_eur((int) $td['amount']); ?></span>
                </div>
                <?php if ($td['message']): ?>
                  <p class="top-donor-message">"<?php echo e(mb_strimwidth($td['message'], 0, 50, '...', 'UTF-8')); ?>"</p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Λίστα Δωρεών -->
      <?php if (count($donations) > 0): ?>
        <div class="card" style="margin-top: 24px;">
          <h3 style="margin-top: 0;">Πρόσφατοι Δωρητές (<?php echo $donationCount; ?>)</h3>
          <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($donations as $d): ?>
              <div
                style="display: flex; justify-content: space-between; padding: 12px; background: var(--bg-gray); border-radius: 8px;">
                <div>
                  <?php
                  $rawDonorName = $d['donor_name'] ?: '';
                  $displayName = ($d['is_anonymous'] || mb_strtolower(trim($rawDonorName), 'UTF-8') === 'anonymous') ? 'Ανώνυμος' : ($rawDonorName ?: 'Ανώνυμος');
                  ?>
                  <strong><?php echo e($displayName); ?></strong>
                  <?php if ($d['message']): ?>
                    <p class="small muted" style="margin: 4px 0 0;">"<?php echo e($d['message']); ?>"</p>
                  <?php endif; ?>
                </div>
                <div style="text-align: right;">
                  <strong style="color: var(--primary);"><?php echo money_eur((int) $d['amount']); ?></strong>
                  <p class="small muted" style="margin: 0;"><?php echo date('d/m/Y', strtotime($d['created_at'])); ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Πλαϊνή Στήλη -->
    <div class="campaign-sidebar">
      <!-- Πρόοδος & Δωρεά -->
      <div class="card <?php echo $goalReached ? 'goal-reached-card' : ''; ?>" style="margin-bottom: 24px;">
        <?php if ($goalReached): ?>
          <div class="goal-reached-banner">
            <span class="goal-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="2.5">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
              </svg>
            </span>
            <div>
              <strong>Στόχος επιτεύχθηκε!</strong>
              <p>Αυτός ο έρανος πέτυχε τον στόχο του<?php if ($actualPct > 100): ?> κατά
                  <?php echo $actualPct; ?>%<?php endif; ?>.
              </p>
            </div>
          </div>
        <?php endif; ?>

        <div
          style="font-size: 32px; font-weight: 800; color: <?php echo $goalReached ? '#f59e0b' : 'var(--primary)'; ?>; margin-bottom: 4px;">
          <?php echo money_eur((int) $campaign['current_amount']); ?>
        </div>
        <p class="muted" style="margin: 0 0 16px;">
          συγκεντρώθηκαν από <?php echo money_eur((int) $campaign['target_amount']); ?>
          <?php if ($goalReached && $actualPct > 100): ?>
            <span class="overfunded">(<?php echo $actualPct; ?>%)</span>
          <?php endif; ?>
        </p>
        <div class="progress-bar <?php echo $goalReached ? 'completed' : ''; ?>"
          style="height: 10px; margin-bottom: 8px;">
          <div class="fill" style="width: <?php echo $pct; ?>%;"></div>
        </div>
        <p class="small muted" style="margin: 0 0 24px;"><?php echo $donationCount; ?> δωρητές •
          <?php echo $actualPct; ?>%
        </p>

        <!-- Κουμπί Δωρεάς -->
        <a href="<?php echo BASE_URL; ?>/donate.php?id=<?php echo (int) $campaign['id']; ?>" class="btn primary"
          style="width: 100%; font-size: 16px; padding: 16px; text-align: center; display: block;">
          Κάνε Δωρεά
        </a>

        <!-- GiveHope Giving Guarantee Message -->
        <div
          style="margin-top: 16px; padding: 12px 16px; background: linear-gradient(135deg, #f0fdf4 0%, #ecfeff 100%); border-radius: 10px; border: 1px solid #d1fae5; display: flex; align-items: flex-start; gap: 10px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"
            style="flex-shrink: 0; margin-top: 2px;">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            <polyline points="9 12 12 15 16 10" stroke-width="2.5" />
          </svg>
          <div style="font-size: 13px; line-height: 1.5; color: #065f46;">
            <strong style="color: #047857;">Το GiveHope προστατεύει τη δωρεά σας</strong><br>
            <a href="<?php echo BASE_URL; ?>/giving-guarantee.php"
              style="color: #059669; font-weight: 600; text-decoration: underline;">Δείτε το GiveHope Giving
              Guarantee</a>
          </div>
        </div>
      </div>

      <!-- Πληροφορίες Διοργανωτή -->
      <div class="card organizer-card" style="margin-bottom: 24px;">
        <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px;">
          <div
            style="width: 56px; height: 56px; background: var(--primary-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
              <?php if ($isOrg): ?>
                <path d="M3 21h18M5 21V7l8-4 8 4v14M9 21v-4h6v4" />
              <?php else: ?>
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              <?php endif; ?>
            </svg>
          </div>
          <div style="flex: 1; min-width: 0;">
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
              <strong style="font-size: 16px;"><?php echo e($creatorName); ?></strong>
              <?php if ($isVerified): ?>
                <span class="badge-verified"
                  data-tooltip="<?php echo $isOrg ? 'Επαληθευμένος οργανισμός' : 'Επαληθευμένος χρήστης'; ?>"
                  style="padding: 4px 8px; font-size: 11px;">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  Verified
                </span>
              <?php endif; ?>
            </div>
            <p class="small muted" style="margin: 4px 0 0;"><?php echo $isOrg ? 'Οργανισμός' : 'Ιδιώτης'; ?></p>
          </div>
        </div>

        <?php if ($isOrg && $campaign['org_description']): ?>
          <p style="font-size: 13px; color: var(--text-secondary); margin: 0 0 16px; line-height: 1.5;">
            <?php echo e(mb_strimwidth($campaign['org_description'], 0, 150, '...', 'UTF-8')); ?>
          </p>
        <?php endif; ?>

        <!-- Στατιστικά Διοργανωτή -->
        <div
          style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding: 12px; background: var(--bg-gray); border-radius: 10px; margin-bottom: 16px;">
          <div style="text-align: center;">
            <div style="font-size: 20px; font-weight: 700; color: var(--primary);">
              <?php echo (int) $organizerStats['campaign_count']; ?>
            </div>
            <div class="small muted">Έρανοι</div>
          </div>
          <div style="text-align: center;">
            <div style="font-size: 20px; font-weight: 700; color: var(--primary);">
              <?php echo money_eur((int) $organizerStats['total_raised']); ?>
            </div>
            <div class="small muted">Συγκεντρώθηκαν</div>
          </div>
        </div>

        <!-- Λεπτομέρειες Επαλήθευσης -->
        <?php if ($isVerified): ?>
          <div
            style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; background: var(--primary-light); border-radius: 8px; margin-bottom: 12px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            </svg>
            <span class="small" style="color: var(--primary);">
              <?php if ($isOrg): ?>
                Επαληθευμένος από την ομάδα GiveHope
              <?php else: ?>
                Ταυτότητα επαληθευμένη
              <?php endif; ?>
            </span>
          </div>
        <?php endif; ?>


      </div>

      <!-- Αναφορά -->
      <div class="card">
        <p class="small muted" style="margin: 0 0 12px;">Κάτι δεν πάει καλά;</p>
        <a href="<?php echo BASE_URL; ?>/report.php?campaign_id=<?php echo (int) $campaign['id']; ?>" class="btn danger"
          style="width: 100%; justify-content: center;">
          Αναφορά εράνου
        </a>
      </div>
    </div>
  </div>
</div>

<?php
// Έλεγχος αν μόλις έγινε δωρεά
$justDonated = isset($_GET['donated']) && $_GET['donated'] == '1';
?>

<?php if ($justDonated): ?>
  <!-- Modal Επιτυχίας -->
  <div id="donation-success-modal" style="
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  animation: fadeIn 0.3s;
">
    <div style="
    background: white;
    border-radius: 24px;
    padding: 48px;
    text-align: center;
    max-width: 420px;
    margin: 24px;
    animation: bounceIn 0.5s;
    box-shadow: 0 25px 80px rgba(0,0,0,0.3);
  ">
      <div style="font-size: 80px; margin-bottom: 16px;">🎉</div>
      <h2 style="margin: 0 0 12px; font-size: 28px;">Ευχαριστούμε!</h2>
      <p style="color: #666; margin: 0 0 24px; font-size: 16px;">
        Η δωρεά σας καταχωρήθηκε επιτυχώς.<br>
        Κάνατε τη διαφορά σήμερα! 💚
      </p>
      <button onclick="closeSuccessModal()" class="btn primary" style="padding: 14px 32px; font-size: 16px;">
        Τέλεια!
      </button>
    </div>
  </div>

  <style>
    @keyframes bounceIn {
      0% {
        transform: scale(0.3);
        opacity: 0;
      }

      50% {
        transform: scale(1.05);
      }

      70% {
        transform: scale(0.95);
      }

      100% {
        transform: scale(1);
        opacity: 1;
      }
    }
  </style>

  <script>
    function closeSuccessModal() {
      document.getElementById('donation-success-modal').style.display = 'none';
      // Αφαίρεση του donated parameter από URL
      const url = new URL(window.location);
      url.searchParams.delete('donated');
      window.history.replaceState({}, '', url);
    }
  </script>
<?php endif; ?>

<!-- Σταθερή Μπάρα Δωρεάς Κινητού -->
<div class="mobile-donate-bar" id="mobileDonateBar">
  <div class="mobile-donate-info">
    <span class="mobile-donate-amount"><?php echo money_eur((int) $campaign['current_amount']); ?></span>
    <span class="mobile-donate-goal">από <?php echo money_eur((int) $campaign['target_amount']); ?>
      (<?php echo $actualPct; ?>%)</span>
  </div>
  <a href="<?php echo BASE_URL; ?>/donate.php?id=<?php echo (int) $campaign['id']; ?>"
    class="btn primary mobile-donate-btn">
    Κάνε Δωρεά
  </a>
</div>
<style>
  .mobile-donate-bar {
    display: none;
  }

  @media (max-width: 900px) {
    .mobile-donate-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      z-index: 90;
      padding: 12px 16px;
      background: white;
      border-top: 1px solid var(--border);
      box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
      transform: translateY(100%);
      transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .mobile-donate-bar.visible {
      transform: translateY(0);
    }

    .mobile-donate-info {
      display: flex;
      flex-direction: column;
      min-width: 0;
    }

    .mobile-donate-amount {
      font-size: 18px;
      font-weight: 800;
      color: var(--primary);
      line-height: 1.2;
    }

    .mobile-donate-goal {
      font-size: 12px;
      color: var(--text-muted);
    }

    .mobile-donate-btn {
      flex-shrink: 0;
      padding: 12px 24px !important;
      font-size: 15px !important;
      border-radius: 50px !important;
      min-height: auto !important;
    }

    /* Κάτω περιθώριο για να μην κρύβεται περιεχόμενο πίσω από τη σταθερή μπάρα */
    .campaign-detail {
      padding-bottom: 80px;
    }
  }
</style>
<script>
  (function () {
    var bar = document.getElementById('mobileDonateBar');
    if (!bar || window.innerWidth > 900) return;

    var sidebar = document.querySelector('.campaign-sidebar');
    if (!sidebar) return;

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          bar.classList.remove('visible');
        } else {
          bar.classList.add('visible');
        }
      });
    }, { threshold: 0.1 });

    observer.observe(sidebar);
  })();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>