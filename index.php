<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/partials/campaign-card.php';
require_once __DIR__ . '/includes/header.php';

$pdo = db();

// Στατιστικά
$campaignCount = (int) $pdo->query("SELECT COUNT(*) FROM campaigns WHERE status = 'approved'")->fetchColumn();
$donTotal = (int) $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM donations")->fetchColumn();
$donorCount = (int) $pdo->query("SELECT COUNT(*) FROM donations")->fetchColumn();

// Προβεβλημένος έρανος
$featured = $pdo->query("
    SELECT c.*, cat.name AS category_name, cat.icon AS category_icon,
           u.name AS user_name, o.name AS org_name
    FROM campaigns c
    JOIN categories cat ON cat.id = c.category_id
    LEFT JOIN users u ON u.id = c.user_id
    LEFT JOIN organizations o ON o.id = c.org_id
    WHERE c.status = 'approved' AND c.target_amount > 0
    ORDER BY (c.current_amount / c.target_amount) DESC, c.created_at DESC
    LIMIT 1
")->fetch();

// Τελευταίοι έρανοι
$campaigns = $pdo->query("
    SELECT c.*, cat.name AS category_name, cat.icon AS category_icon,
           u.name AS user_name, o.name AS org_name
    FROM campaigns c
    JOIN categories cat ON cat.id = c.category_id
    LEFT JOIN users u ON u.id = c.user_id
    LEFT JOIN organizations o ON o.id = c.org_id
    WHERE c.status = 'approved'
    ORDER BY c.created_at DESC
    LIMIT 8
")->fetchAll();

// Πρόσφατες δωρεές (telemetry)
$recentDonations = $pdo->query("
    SELECT d.amount, d.is_anonymous, d.donor_name, c.title
    FROM donations d
    JOIN campaigns c ON c.id = d.campaign_id AND c.status = 'approved'
    ORDER BY d.created_at DESC
    LIMIT 5
")->fetchAll();

$telemetryStrings = [];
foreach ($recentDonations as $d) {
  // Προετοιμασία τίτλων για JS
  $donor = $d['is_anonymous'] ? 'Ανώνυμη δωρεά' : ($d['donor_name'] ?: 'Κάποιος');
  $amount = money_eur((int) $d['amount']);
  $title = mb_strimwidth($d['title'], 0, 30, '…', 'UTF-8');
  $telemetryStrings[] = "{$donor} – {$amount} στον έρανο \"{$title}\"";
}
if (empty($telemetryStrings)) {
  $telemetryStrings = [
    "Σύστημα έτοιμο για νέες δωρεές...",
    "Αναμονή για υποστήριξη εράνων..."
  ];
}

$defaultImage = 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=800&q=80';
?>

<!-- Hero Section -->
<section class="hero">
  <!-- CSS Noise Overlay -->
  <div class="noise-overlay"></div>

  <div class="hero-content">
    <h1 class="hero-title">
      <span class="sans-part">Βοηθήστε αυτούς που το έχουν</span>
      <span class="serif-part">Ανάγκη.</span>
    </h1>
    <p class="hero-subtitle">
      Γίνετε η αλλαγή που θέλετε να δείτε. Στηρίξτε ιατρικά έξοδα, σπουδές, φιλόδοξα έργα ή απλώς έναν συνάνθρωπο.<br>
      Ανακαλύψτε, υποστηρίξτε και κάντε τη διαφορά σήμερα.
    </p>

    <div class="hero-actions">
      <a href="<?php echo BASE_URL; ?>/explore.php" class="btn-hero-primary">
        Ανακαλύψτε Εράνους
      </a>
      <a href="<?php echo BASE_URL; ?>/campaign-create.php" class="btn-hero-outline">
        <span class="font-outfit">Ξεκινήστε Έρανο</span>
      </a>
    </div>

    <div class="hero-trust">
      <span class="trust-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
          <circle cx="8.5" cy="7" r="4" />
          <polyline points="17 11 19 13 23 9" />
        </svg>
        Επαληθευμένοι Οργανισμοί
      </span>
      <span class="trust-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
          <circle cx="12" cy="12" r="3" />
        </svg>
        100% Διαφάνεια
      </span>
      <span class="trust-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
          <polyline points="22 4 12 14.01 9 11.01" />
        </svg>
        Η Εγγύηση GiveHope
      </span>
    </div>
  </div>
</section>

<!-- Dashboard -->
<section id="how-it-works" class="how-it-works">
  <div class="container">
    <div class="section-header" style="text-align: center; margin-bottom: 4rem;">
      <h2 class="section-title font-outfit" style="font-size: 2.5rem; letter-spacing: -0.05em;">Διαφάνεια και
        αξιοπιστία<br>σε κάθε βήμα.</h2>
      <p class="section-subtitle" style="font-size: 1.1rem; max-width: 500px; margin: 1rem auto;">Εργαλεία και δεδομένα
        σε πραγματικό χρόνο, για να γνωρίζετε εάν η δωρεά σας φτάνει στον προορισμό της.</p>
    </div>

    <div class="steps-grid">

      <!-- Card 1: Community Stats -->
      <div class="step-card micro-ui-card shuffler-card">
        <div class="card-inner-header">
          <span class="micro-label font-data">ΣΤΑΤΙΣΤΙΚΑ ΚΟΙΝΟΤΗΤΑΣ</span>
          <div class="pulsing-dot moss"></div>
        </div>
        <div class="shuffler-container">
          <!-- Cards will be injected & animated via GSAP -->
          <div class="shuffle-item active"><?php echo number_format($campaignCount, 0, ',', '.'); ?> Ενεργοί Έρανοι
          </div>
          <div class="shuffle-item"><?php echo money_eur($donTotal); ?> Συνολικές Δωρεές</div>
          <div class="shuffle-item"><?php echo number_format($donorCount, 0, ',', '.'); ?> Δωρητές</div>
        </div>
        <div class="card-footer-text">Ροή δεδομένων σε πραγματικό χρόνο.</div>
      </div>

      <!-- Card 2: Live Activity Typewriter -->
      <div class="step-card micro-ui-card telemetry-card">
        <div class="card-inner-header">
          <span class="micro-label font-data">LIVE ΔΡΑΣΤΗΡΙΟΤΗΤΑ</span>
          <div class="pulsing-dot clay"></div>
        </div>
        <div class="telemetry-container">
          <span class="typewriter-text font-data"></span><span class="cursor">_</span>
        </div>
        <div class="card-footer-text">Πρόσφατες δωρεές στην πλατφόρμα.</div>

        <!-- Inject JS variables safely -->
        <script>
          window.PlatformTelemetry = <?php echo json_encode($telemetryStrings, JSON_UNESCAPED_UNICODE); ?>;
        </script>
      </div>

      <!-- Card 3: Transparency & Security -->
      <div class="step-card micro-ui-card transparency-card">
        <div class="card-inner-header">
          <span class="micro-label font-data">ΔΙΑΦΑΝΕΙΑ & ΑΣΦΑΛΕΙΑ</span>
          <div class="pulsing-dot" style="background: var(--primary);"></div>
        </div>
        <div class="transparency-container" style="display: flex; flex-direction: column; gap: 12px; margin-top: 16px;">
          <div
            style="display: flex; align-items: center; gap: 12px; background: rgba(46, 64, 54, 0.05); padding: 12px; border-radius: 8px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"
              style="flex-shrink:0;">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            </svg>
            <div style="font-size: 13px; font-weight: 600; color: var(--text);">Πλήρως ελεγμένοι έρανοι</div>
          </div>
          <div
            style="display: flex; align-items: center; gap: 12px; background: rgba(204, 88, 51, 0.05); padding: 12px; border-radius: 8px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--clay)" stroke-width="2"
              style="flex-shrink:0;">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
              <path d="M7 11V7a5 5 0 0 1 10 0v4" />
            </svg>
            <div style="font-size: 13px; font-weight: 600; color: var(--text);">Ασφαλείς συναλλαγές</div>
          </div>
        </div>
        <div class="card-footer-text" style="margin-top: auto;">Εγγύηση GiveHope.</div>
      </div>

    </div>
  </div>
</section>

<!-- Δημοφιλείς Έρανοι -->
<?php if ($featured): ?>
  <section class="featured-section">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title" style="margin: 0;">Δημοφιλείς Έρανοι</h2>
        <div class="tabs" style="padding: 0; margin: 0;">
          <a href="<?php echo BASE_URL; ?>/explore.php?sort=progress" class="tab">Trending</a>
          <a href="<?php echo BASE_URL; ?>/explore.php?sort=newest" class="tab">Νέοι</a>
        </div>
      </div>

      <div class="cards-grid featured">
        <?php
        $pct = min(100, (int) round(($featured['current_amount'] / max(1, $featured['target_amount'])) * 100));
        $image = $featured['image_url'] ?: $defaultImage;
        $creator = $featured['user_name'] ?: $featured['org_name'];
        $featuredInitial = mb_substr($creator ?: '?', 0, 1, 'UTF-8');
        ?>
        <a href="<?php echo BASE_URL; ?>/campaign.php?id=<?php echo (int) $featured['id']; ?>"
          class="campaign-card featured">
          <div class="card-image">
            <img src="<?php echo e($image); ?>" alt="<?php echo e($featured['title']); ?>">
            <div class="card-image-overlay"></div>
            <span class="card-badge"><?php echo category_icon($featured['category_id']); ?>
              <?php echo e($featured['category_name']); ?></span>
          </div>
          <div class="card-content">
            <h3><?php echo e($featured['title']); ?></h3>
            <p><?php echo e(mb_strimwidth($featured['description'], 0, 150, '…', 'UTF-8')); ?></p>
            <div style="margin-top: auto;">
              <div class="progress-bar progress-bar-gradient">
                <div class="fill" style="width: <?php echo $pct; ?>%"></div>
              </div>
              <div class="progress-text">
                <strong><?php echo money_eur((int) $featured['current_amount']); ?></strong>
                <span>από <?php echo money_eur((int) $featured['target_amount']); ?></span>
                <span style="float: right; color: var(--primary); font-weight: 700;"><?php echo $pct; ?>%</span>
              </div>
              <div class="card-creator" style="margin-top: 12px;">
                <span class="creator-avatar"><?php echo e($featuredInitial); ?></span>
                <span class="creator-name"><?php echo e($creator); ?></span>
              </div>
            </div>
          </div>
        </a>

        <!-- Πλαϊνές κάρτες -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
          <?php
          $sideCards = array_slice($campaigns, 0, 4);
          foreach ($sideCards as $c):
            render_campaign_card($c, [
              'show_description' => false,
              'gradient' => true,
            ]);
          endforeach; ?>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

<!-- The Manifesto -->
<section class="manifesto-section">
  <div class="manifesto-bg"></div>
  <div class="container relative z-10">
    <div class="manifesto-content">
      <h2 class="manifesto-q split-text font-drama">Πιστεύουμε ότι η καλοσύνη<br>μπορεί να αλλάξει τον κόσμο.</h2>
      <div class="manifesto-divider"></div>
      <h2 class="manifesto-a split-text">Μαζί μπορούμε να κάνουμε<br><span class="font-drama">θαύματα.</span></h2>
      <p class="manifesto-desc">Το GiveHope δεν είναι απλώς μια πλατφόρμα δωρεών. Είναι μια κινητήρια δύναμη
        αλληλεγγύης. Είτε πρόκειται για ιατρικά έξοδα, έκτακτες ανάγκες, ή όνειρα ζωής, σας φέρνουμε πιο κοντά στους
        ανθρώπους που χρειάζονται τη στήριξή σας.</p>
    </div>
  </div>
</section>

<!-- Περισσότεροι Έρανοι -->
<section class="campaigns-section reveal-section">
  <div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
      <h2 class="section-title" style="margin: 0;">Ανακαλύψτε εράνους</h2>
      <a href="<?php echo BASE_URL; ?>/explore.php" class="btn">Δείτε όλους →</a>
    </div>

    <div class="cards-grid">
      <?php foreach ($campaigns as $c):
        render_campaign_card($c, [
          'extra_class' => 'reveal-item',
          'desc_length' => 80,
          'show_creator' => true,
          'gradient' => true,
        ]);
      endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
  <div class="cta-content">
    <h2>Χρειάζεστε βοήθεια;</h2>
    <p>Ξεκινήστε τον δικό σας έρανο σήμερα και αφήστε την κοινότητά μας να σας στηρίξει</p>
    <a href="<?php echo BASE_URL; ?>/campaign-create.php" class="btn btn-hero-primary">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 5v14" />
        <path d="M5 12h14" />
      </svg>
      Ξεκινήστε Έρανο Τώρα
    </a>
  </div>
</section>

<?php $loadGsap = true;
require_once __DIR__ . '/includes/footer.php'; ?>