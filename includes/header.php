<?php
// Κοινό header — προαιρετικά: $pageTitle, $pageDescription, $pageImage, $pageUrl (OG / meta)

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$currentUser = current_user();
$currentOrg = current_org();

// Προεπιλεγμένες τιμές για meta tags
$metaTitle = isset($pageTitle) ? $pageTitle . ' | ' . APP_NAME : APP_NAME;
$metaDescription = isset($pageDescription) ? $pageDescription : 'Πλατφόρμα δωρεών για οργανισμούς και ιδιώτες';
$metaImage = isset($pageImage) ? $pageImage : 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=1200&q=80';
$metaUrl = isset($pageUrl) ? $pageUrl : (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<!doctype html>
<html lang="el">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?php echo e($metaDescription); ?>">
  <title><?php echo e($metaTitle); ?></title>
  <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/public/images/logo.svg">
  <link rel="canonical" href="<?php echo e($metaUrl); ?>">

  <!-- Open Graph Meta Tags για κοινοποίηση σε Facebook/LinkedIn -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?php echo e($metaTitle); ?>">
  <meta property="og:description" content="<?php echo e($metaDescription); ?>">
  <meta property="og:image" content="<?php echo e($metaImage); ?>">
  <meta property="og:url" content="<?php echo e($metaUrl); ?>">
  <meta property="og:site_name" content="<?php echo e(APP_NAME); ?>">
  <meta property="og:locale" content="el_GR">

  <!-- Twitter Card Meta Tags για κοινοποίηση -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo e($metaTitle); ?>">
  <meta name="twitter:description" content="<?php echo e($metaDescription); ?>">
  <meta name="twitter:image" content="<?php echo e($metaImage); ?>">

  <link rel="stylesheet"
    href="<?php echo BASE_URL; ?>/public/css/style.css?v=<?php echo filemtime(__DIR__ . '/../public/css/style.css'); ?>">

<?php if (!empty($loadGsap)): ?>
  <!-- Preload GSAP early so it's ready by the time the body parses -->
  <link rel="preload" as="script"
        href="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"
        crossorigin="anonymous">
<?php endif; ?>

<!-- INLINE GUARD: must be inline + synchronous + in <head> to prevent FOUC.
     Only hides hero elements if we're confident JS+GSAP will run.
     Safety net auto-reveals after 2s if animations never start. -->
<?php if (!empty($loadGsap)): ?>
<script>
  (function () {
    var html = document.documentElement;
    // Mark that JS is running — CSS uses this to apply opacity:0
    html.classList.add('js-ready');

    // Safety net: if GSAP hasn't taken over within 2s, reveal everything.
    // This covers CDN failures, SRI mismatches, network timeouts, JS errors.
    window.__heroRevealTimeout = setTimeout(function () {
      html.classList.add('js-failed');
      html.classList.remove('js-ready');
    }, 2000);

    // Also reveal if the user has reduced-motion preference
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      clearTimeout(window.__heroRevealTimeout);
      html.classList.add('js-failed');
      html.classList.remove('js-ready');
    }
  })();
</script>
<?php endif; ?>

  <script defer
    src="<?php echo BASE_URL; ?>/public/js/app.js?v=<?php echo filemtime(__DIR__ . '/../public/js/app.js'); ?>"></script>
</head>

<body>
  <header class="site-header floating-island">
    <div class="header-row">
      <nav class="nav-left">
        <a class="brand" href="<?php echo BASE_URL; ?>/index.php">
          <img src="<?php echo BASE_URL; ?>/public/images/logo.svg" alt="GiveHope" class="brand-logo">
          <?php echo e(APP_NAME); ?>
        </a>
      </nav>

      <nav class="nav-center">
        <a href="<?php echo BASE_URL; ?>/explore.php" class="nav-link">Έρανοι</a>
        <a href="<?php echo BASE_URL; ?>/how-it-works.php" class="nav-link">Πώς Λειτουργεί</a>
        <a href="<?php echo BASE_URL; ?>/giving-guarantee.php" class="nav-link">Εγγύηση</a>
      </nav>

      <nav class="nav-right">
        <a href="<?php echo BASE_URL; ?>/explore.php" class="nav-icon-link" aria-label="Αναζήτηση"
          style="margin-right: 8px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </a>

        <?php if ($currentUser): ?>
          <a href="<?php echo BASE_URL; ?>/my-campaigns.php" class="nav-link">Οι Έρανοί μου</a>
          <div class="nav-separator"></div>
          <a href="<?php echo BASE_URL; ?>/campaign-create.php" class="btn-header">+ Νέος</a>
          <a href="<?php echo BASE_URL; ?>/logout.php" class="nav-icon-link" aria-label="Έξοδος">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" class="feather feather-log-out">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
              <polyline points="16 17 21 12 16 7"></polyline>
              <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
          </a>
        <?php elseif ($currentOrg): ?>
          <a href="<?php echo BASE_URL; ?>/dashboard/index.php" class="nav-link">Dashboard</a>
          <div class="nav-separator"></div>
          <a href="<?php echo BASE_URL; ?>/logout.php" class="nav-icon-link" aria-label="Έξοδος">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
              <polyline points="16 17 21 12 16 7"></polyline>
              <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
          </a>
        <?php else: ?>
          <a href="<?php echo BASE_URL; ?>/login.php" class="nav-link">Σύνδεση</a>
          <a href="<?php echo BASE_URL; ?>/campaign-create.php" class="btn-header btn-accent">Ξεκίνα Έρανο</a>
        <?php endif; ?>
      </nav>

      <!-- Κουμπί Hamburger για κινητά -->
      <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Άνοιγμα μενού">
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>
  </header>

  <!-- Full-Screen Mobile Menu -->
  <nav class="mobile-nav-fullscreen" id="mobileNav">
    <div class="mobile-nav-fullscreen-inner">
      <div class="mobile-nav-header">
        <a class="brand" href="<?php echo BASE_URL; ?>/index.php">
          <img src="<?php echo BASE_URL; ?>/public/images/logo.svg" alt="GiveHope" class="brand-logo"
            style="width:32px; height:32px;">
          <?php echo e(APP_NAME); ?>
        </a>
      </div>

      <div class="mobile-nav-links">
        <a href="<?php echo BASE_URL; ?>/explore.php" class="mobile-nav-link">Έρανοι</a>
        <a href="<?php echo BASE_URL; ?>/how-it-works.php" class="mobile-nav-link">Πώς Λειτουργεί</a>
        <a href="<?php echo BASE_URL; ?>/giving-guarantee.php" class="mobile-nav-link">Εγγύηση</a>

        <?php if ($currentUser): ?>
          <a href="<?php echo BASE_URL; ?>/my-campaigns.php" class="mobile-nav-link">Οι Έρανοί μου</a>
          <a href="<?php echo BASE_URL; ?>/dashboard/my-data.php" class="mobile-nav-link">Τα Δεδομένα μου</a>
          <a href="<?php echo BASE_URL; ?>/logout.php" class="mobile-nav-link" style="color: var(--clay);">Έξοδος</a>
          <div class="mobile-nav-cta">
            <a href="<?php echo BASE_URL; ?>/campaign-create.php" class="btn">+ Νέος Έρανος</a>
          </div>
        <?php elseif ($currentOrg): ?>
          <a href="<?php echo BASE_URL; ?>/dashboard/index.php" class="mobile-nav-link">Dashboard</a>
          <a href="<?php echo BASE_URL; ?>/dashboard/my-data.php" class="mobile-nav-link">Τα Δεδομένα μου</a>
          <a href="<?php echo BASE_URL; ?>/logout.php" class="mobile-nav-link" style="color: var(--clay);">Έξοδος</a>
        <?php else: ?>
          <a href="<?php echo BASE_URL; ?>/login.php" class="mobile-nav-link">Σύνδεση</a>
          <a href="<?php echo BASE_URL; ?>/register.php" class="mobile-nav-link">Εγγραφή</a>
          <div class="mobile-nav-cta">
            <a href="<?php echo BASE_URL; ?>/campaign-create.php" class="btn">Ξεκίνα Έρανο</a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Στοιχεία επικοινωνίας -->
      <div class="mobile-nav-footer">
        <p class="font-data small muted">ΣΥΣΤΗΜΑ G-HOPE</p>
        <p class="small"><a href="mailto:hello@givehope.gr">hello@givehope.gr</a></p>
      </div>
    </div>
  </nav>

  <main>