<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/partials/campaign-card.php';
require_once __DIR__ . '/includes/header.php';

$pdo = db();
$q = trim($_GET['q'] ?? '');
$cat = (int) ($_GET['cat'] ?? 0);

// Ρυθμίσεις σελιδοποίησης
$perPage = 6; // Πλέγμα 3x2 ανά σελίδα
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Ρυθμίσεις ταξινόμησης
$validSorts = [
  'newest' => ['label' => 'Νεότεροι', 'order' => 'c.created_at DESC'],
  'funded' => ['label' => 'Πιο δημοφιλείς', 'order' => 'c.current_amount DESC'],
  'progress' => ['label' => 'Κοντά στον στόχο', 'order' => '(CASE WHEN c.target_amount > 0 THEN c.current_amount / c.target_amount ELSE -1 END) DESC'],
  'goal_low' => ['label' => 'Χαμηλότερος στόχος', 'order' => 'c.target_amount IS NULL, c.target_amount ASC']
];
$sort = isset($_GET['sort']) && isset($validSorts[$_GET['sort']]) ? $_GET['sort'] : 'newest';
$orderBy = $validSorts[$sort]['order'];

// Ανάκτηση κατηγοριών για φίλτρο
$categories = $pdo->query("SELECT * FROM categories ORDER BY id")->fetchAll();

// Κατασκευή ερωτήματος
$params = [];
$where = ["c.status = 'approved'"];

if ($q !== '') {
  $where[] = "(c.title LIKE :q1 OR c.description LIKE :q2)";
  $params[':q1'] = '%' . $q . '%';
  $params[':q2'] = '%' . $q . '%';
}

if ($cat > 0) {
  $where[] = "c.category_id = :cat";
  $params[':cat'] = $cat;
}

$whereStr = implode(' AND ', $where);

// Λήψη συνολικού αριθμού για σελιδοποίηση
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM campaigns c WHERE $whereStr");
$countStmt->execute($params);
$totalCampaigns = (int) $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalCampaigns / $perPage));

// Διασφάλιση ότι η σελίδα είναι εντός ορίων
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

// Ανάκτηση εράνων με σελιδοποίηση
$stmt = $pdo->prepare("
    SELECT c.*, cat.name AS category_name, cat.icon AS category_icon,
           u.name AS user_name, o.name AS org_name
    FROM campaigns c
    JOIN categories cat ON cat.id = c.category_id
    LEFT JOIN users u ON u.id = c.user_id
    LEFT JOIN organizations o ON o.id = c.org_id
    WHERE $whereStr
    ORDER BY $orderBy
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$campaigns = $stmt->fetchAll();

$defaultImage = 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=800&q=80';

// Βοηθητική συνάρτηση για δημιουργία URL σελιδοποίησης
function buildPageUrl($page, $q, $cat, $sort)
{
  $params = ['page' => $page];
  if ($q)
    $params['q'] = $q;
  if ($cat)
    $params['cat'] = $cat;
  if ($sort && $sort !== 'newest')
    $params['sort'] = $sort;
  return BASE_URL . '/explore.php?' . http_build_query($params);
}

// Βοηθητική συνάρτηση για δημιουργία URL ταξινόμησης (επαναφορά στη σελίδα 1)
function buildSortUrl($newSort, $q, $cat)
{
  $params = [];
  if ($q)
    $params['q'] = $q;
  if ($cat)
    $params['cat'] = $cat;
  if ($newSort && $newSort !== 'newest')
    $params['sort'] = $newSort;
  return BASE_URL . '/explore.php' . ($params ? '?' . http_build_query($params) : '');
}

// Βοηθητική συνάρτηση για δημιουργία URL κατηγορίας (επαναφορά στη σελίδα 1, διατήρηση ταξινόμησης)
function buildCategoryUrl($newCat, $q, $sort)
{
  $params = [];
  if ($q)
    $params['q'] = $q;
  if ($newCat)
    $params['cat'] = $newCat;
  if ($sort && $sort !== 'newest')
    $params['sort'] = $sort;
  return BASE_URL . '/explore.php' . ($params ? '?' . http_build_query($params) : '');
}
?>

<!-- Ενότητα Αναζήτησης - Νέο Style -->
<section class="search-hero">
  <h1>Αναζήτηση Εράνων</h1>
  <p>Βρείτε εράνους που σας ενδιαφέρουν</p>

  <form action="" method="get" class="search-box">
    <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="Αναζήτηση...">
    <?php if ($cat): ?><input type="hidden" name="cat" value="<?php echo $cat; ?>"><?php endif; ?>
  </form>
</section>

<!-- Μπάρα Φίλτρων -->
<div class="filter-bar">
  <div class="filter-bar-left">
    <p class="muted">
      <?php
      $start = $totalCampaigns > 0 ? $offset + 1 : 0;
      $end = min($offset + $perPage, $totalCampaigns);
      echo "Εμφάνιση $start-$end από $totalCampaigns εράνους";
      ?>
      <?php if ($q): ?> για "<?php echo e($q); ?>"<?php endif; ?>
      <?php if ($q || $cat): ?>
        <a href="<?php echo BASE_URL; ?>/explore.php" class="clear-filters">Καθαρισμός</a>
      <?php endif; ?>
    </p>
  </div>
  <div class="filter-bar-right">
    <!-- Αναπτυσσόμενο Μενού Κατηγοριών -->
    <div class="custom-dropdown" id="category-dropdown">
      <button type="button" class="dropdown-trigger" aria-haspopup="listbox" aria-expanded="false">
        <span class="dropdown-icon">
          <?php if ($cat): ?>
            <?php 
            $currentCat = array_filter($categories, fn($c) => $c['id'] == $cat);
            $currentCat = reset($currentCat);
            echo $currentCat ? $currentCat['icon'] : '📌';
            ?>
          <?php else: ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/>
            </svg>
          <?php endif; ?>
        </span>
        <span class="dropdown-label">
          <?php 
          if ($cat && isset($currentCat)) {
            echo e($currentCat['name']);
          } else {
            echo 'Όλες οι κατηγορίες';
          }
          ?>
        </span>
        <span class="dropdown-arrow">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m6 9 6 6 6-6"/>
          </svg>
        </span>
      </button>
      <ul class="dropdown-menu" role="listbox">
        <li>
          <a href="<?php echo buildCategoryUrl(0, $q, $sort); ?>" 
             class="dropdown-item <?php echo !$cat ? 'active' : ''; ?>"
             role="option"
             <?php echo !$cat ? 'aria-selected="true"' : ''; ?>>
            <span class="item-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/>
              </svg>
            </span>
            <span class="item-label">Όλες οι κατηγορίες</span>
            <?php if (!$cat): ?>
              <span class="item-check">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                  <path d="M20 6 9 17l-5-5"/>
                </svg>
              </span>
            <?php endif; ?>
          </a>
        </li>
        <?php foreach ($categories as $c): ?>
          <li>
            <a href="<?php echo buildCategoryUrl($c['id'], $q, $sort); ?>" 
               class="dropdown-item <?php echo $cat == $c['id'] ? 'active' : ''; ?>"
               role="option"
               <?php echo $cat == $c['id'] ? 'aria-selected="true"' : ''; ?>>
              <span class="item-icon"><?php echo e($c['icon']); ?></span>
              <span class="item-label"><?php echo e($c['name']); ?></span>
              <?php if ($cat == $c['id']): ?>
                <span class="item-check">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <path d="M20 6 9 17l-5-5"/>
                  </svg>
                </span>
              <?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <!-- Αναπτυσσόμενο Μενού Ταξινόμησης -->
    <div class="custom-dropdown" id="sort-dropdown">
      <button type="button" class="dropdown-trigger" aria-haspopup="listbox" aria-expanded="false">
        <span class="dropdown-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 6h18M6 12h12M9 18h6" />
          </svg>
        </span>
        <span class="dropdown-label"><?php echo e($validSorts[$sort]['label']); ?></span>
        <span class="dropdown-arrow">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m6 9 6 6 6-6" />
          </svg>
        </span>
      </button>
      <ul class="dropdown-menu" role="listbox">
        <?php foreach ($validSorts as $key => $sortOption): ?>
          <li>
            <a href="<?php echo buildSortUrl($key, $q, $cat); ?>"
              class="dropdown-item <?php echo $sort === $key ? 'active' : ''; ?>" role="option" <?php echo $sort === $key ? 'aria-selected="true"' : ''; ?>>
              <?php if ($key === 'newest'): ?>
                <span class="item-icon">•</span>
              <?php elseif ($key === 'funded'): ?>
                <span class="item-icon">•</span>
              <?php elseif ($key === 'progress'): ?>
                <span class="item-icon">•</span>
              <?php else: ?>
                <span class="item-icon">•</span>
              <?php endif; ?>
              <span class="item-label"><?php echo e($sortOption['label']); ?></span>
              <?php if ($sort === $key): ?>
                <span class="item-check">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <path d="M20 6 9 17l-5-5" />
                  </svg>
                </span>
              <?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>

<script>
// Εναλλαγή Αναπτυσσόμενου Μενού - υποστηρίζει πολλαπλά dropdown
document.addEventListener('DOMContentLoaded', function() {
  const dropdowns = document.querySelectorAll('.custom-dropdown');
  
  dropdowns.forEach(function(dropdown) {
    const trigger = dropdown.querySelector('.dropdown-trigger');
    
    trigger.addEventListener('click', function(e) {
      e.stopPropagation();
      
      // Κλείσιμο όλων των υπόλοιπων dropdown πρώτα
      dropdowns.forEach(function(other) {
        if (other !== dropdown) {
          other.classList.remove('open');
          other.querySelector('.dropdown-trigger').setAttribute('aria-expanded', 'false');
        }
      });
      
      // Εναλλαγή αυτού του dropdown
      const isOpen = dropdown.classList.contains('open');
      dropdown.classList.toggle('open');
      trigger.setAttribute('aria-expanded', !isOpen);
    });

    // Κλείσιμο dropdown κατά την επιλογή item (πριν το navigation)
    dropdown.querySelectorAll('.dropdown-item').forEach(function(item) {
      item.addEventListener('click', function() {
        dropdown.classList.remove('open');
        trigger.setAttribute('aria-expanded', 'false');
      });
    });
  });
  
  // Κλείσιμο με κλικ έξω
  document.addEventListener('click', function(e) {
    dropdowns.forEach(function(dropdown) {
      if (!dropdown.contains(e.target)) {
        dropdown.classList.remove('open');
        dropdown.querySelector('.dropdown-trigger').setAttribute('aria-expanded', 'false');
      }
    });
  });
  
  // Κλείσιμο με πλήκτρο Escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      dropdowns.forEach(function(dropdown) {
        dropdown.classList.remove('open');
        dropdown.querySelector('.dropdown-trigger').setAttribute('aria-expanded', 'false');
      });
    }
  });
});
</script>

<?php if (count($campaigns) === 0): ?>
  <div style="max-width: 600px; margin: 60px auto; text-align: center; padding: 0 24px;">
    <div style="font-size: 64px; margin-bottom: 16px;">🔍</div>
    <h2>Δεν βρέθηκαν έρανοι</h2>
    <p class="muted">Δοκιμάστε διαφορετικούς όρους αναζήτησης</p>
    <a href="<?php echo BASE_URL; ?>/explore.php" class="btn primary" style="margin-top: 16px;">Δείτε όλους</a>
  </div>
<?php else: ?>
  <section class="cards-grid" style="padding: 0 24px 32px;">
    <?php foreach ($campaigns as $c):
      $creator = $c['user_name'] ?: $c['org_name'];
      render_campaign_card($c, [
        'badge' => category_icon($c['category_id']) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . e($creator),
      ]);
    endforeach; ?>
  </section>

  <!-- Σελιδοποίηση -->
  <?php if ($totalPages > 1): ?>
    <nav class="pagination" aria-label="Πλοήγηση σελίδων">
      <div class="pagination-container">
        <!-- Κουμπί Προηγούμενο -->
        <?php if ($page > 1): ?>
          <a href="<?php echo buildPageUrl($page - 1, $q, $cat, $sort); ?>" class="pagination-btn pagination-prev"
            aria-label="Προηγούμενη σελίδα">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m15 18-6-6 6-6" />
            </svg>
            <span>Προηγ.</span>
          </a>
        <?php else: ?>
          <span class="pagination-btn pagination-prev disabled" aria-disabled="true">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m15 18-6-6 6-6" />
            </svg>
            <span>Προηγ.</span>
          </span>
        <?php endif; ?>

        <!-- Αριθμοί Σελίδων -->
        <div class="pagination-numbers">
          <?php
          // Εμφάνιση πρώτης σελίδας
          if ($page > 3): ?>
            <a href="<?php echo buildPageUrl(1, $q, $cat, $sort); ?>" class="pagination-num">1</a>
            <?php if ($page > 4): ?>
              <span class="pagination-ellipsis">…</span>
            <?php endif; ?>
          <?php endif; ?>

          <?php
          // Εμφάνιση σελίδων γύρω από την τρέχουσα
          $start = max(1, $page - 2);
          $end = min($totalPages, $page + 2);

          for ($i = $start; $i <= $end; $i++): ?>
            <?php if ($i === $page): ?>
              <span class="pagination-num active" aria-current="page"><?php echo $i; ?></span>
            <?php else: ?>
              <a href="<?php echo buildPageUrl($i, $q, $cat, $sort); ?>" class="pagination-num"><?php echo $i; ?></a>
            <?php endif; ?>
          <?php endfor; ?>

          <?php
          // Εμφάνιση τελευταίας σελίδας
          if ($page < $totalPages - 2): ?>
            <?php if ($page < $totalPages - 3): ?>
              <span class="pagination-ellipsis">…</span>
            <?php endif; ?>
            <a href="<?php echo buildPageUrl($totalPages, $q, $cat, $sort); ?>"
              class="pagination-num"><?php echo $totalPages; ?></a>
          <?php endif; ?>
        </div>

        <!-- Κουμπί Επόμενο -->
        <?php if ($page < $totalPages): ?>
          <a href="<?php echo buildPageUrl($page + 1, $q, $cat, $sort); ?>" class="pagination-btn pagination-next"
            aria-label="Επόμενη σελίδα">
            <span>Επόμ.</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m9 18 6-6-6-6" />
            </svg>
          </a>
        <?php else: ?>
          <span class="pagination-btn pagination-next disabled" aria-disabled="true">
            <span>Επόμ.</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m9 18 6-6-6-6" />
            </svg>
          </span>
        <?php endif; ?>
      </div>

      <!-- Πληροφορίες σελίδας για κινητά -->
      <div class="pagination-info">
        Σελίδα <?php echo $page; ?> από <?php echo $totalPages; ?>
      </div>
    </nav>
  <?php endif; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>