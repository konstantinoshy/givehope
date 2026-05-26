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

// Στατιστικά
$stmt = $pdo->prepare("SELECT COUNT(*) FROM campaigns WHERE org_id = :id");
$stmt->execute([':id' => $orgId]);
$campaignCount = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM campaigns WHERE org_id = :id AND status = 'approved'");
$stmt->execute([':id' => $orgId]);
$activeCount = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(current_amount), 0) FROM campaigns WHERE org_id = :id");
$stmt->execute([':id' => $orgId]);
$totalRaised = (int) $stmt->fetchColumn();

// Ανάκτηση εράνων
$stmt = $pdo->prepare("
    SELECT c.*, cat.name AS category_name, cat.icon AS category_icon
    FROM campaigns c
    JOIN categories cat ON cat.id = c.category_id
    WHERE c.org_id = :id
    ORDER BY c.status ASC, c.created_at DESC
");
$stmt->execute([':id' => $orgId]);
$campaigns = $stmt->fetchAll();

$defaultImage = 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=800&q=80';
$statusLabels = [
  'draft' => ['📝', 'Πρόχειρο', '#666'],
  'pending' => ['⏳', 'Αναμονή', '#f59e0b'],
  'approved' => ['✅', 'Ενεργός', '#02a95c'],
  'rejected' => ['❌', 'Απορρίφθηκε', '#dc3545'],
  'suspended' => ['⚠️', 'Αναστολή', '#dc3545'],
  'completed' => ['🎉', 'Ολοκληρώθηκε', '#666'],
];
?>

<div style="max-width: 1100px; margin: 0 auto; padding: 32px 24px;">
  <!-- Κεφαλίδα -->
  <div class="card" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
      <div>
        <p class="small muted" style="margin-bottom: 4px;">Dashboard Οργανισμού</p>
        <h1 style="margin: 0 0 8px;"><?php echo e($org['name']); ?></h1>
        <?php if ($org['verified']): ?>
          <span class="badge" style="background: var(--primary-light); color: var(--primary);">✓ Επαληθευμένος
            Οργανισμός</span>
        <?php endif; ?>
      </div>
      <a class="btn primary" href="<?php echo BASE_URL; ?>/dashboard/campaign-new.php">+ Νέος Έρανος</a>
    </div>

    <div class="hr"></div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
      <div class="kpi">
        <div class="val"><?php echo $campaignCount; ?></div>
        <div class="lbl">Σύνολο Εράνων</div>
      </div>
      <div class="kpi">
        <div class="val"><?php echo $activeCount; ?></div>
        <div class="lbl">Ενεργοί</div>
      </div>
      <div class="kpi">
        <div class="val"><?php echo money_eur($totalRaised); ?></div>
        <div class="lbl">Συγκεντρώθηκαν</div>
      </div>
    </div>

    <div class="hr"></div>

    <div class="row">
      <a class="btn" href="<?php echo BASE_URL; ?>/dashboard/donations.php" style="justify-content: center;">📋
        Δωρεές</a>
      <a class="btn" href="<?php echo BASE_URL; ?>/dashboard/profile.php" style="justify-content: center;">⚙️
        Ρυθμίσεις</a>
    </div>
  </div>

  <!-- Λίστα Εράνων -->
  <div class="card">
    <h2>Οι έρανοί σας</h2>

    <?php if (count($campaigns) === 0): ?>
      <div style="text-align: center; padding: 40px;">
        <div style="font-size: 48px; margin-bottom: 16px;">📝</div>
        <p class="muted" style="margin-bottom: 16px;">Δεν έχετε δημιουργήσει εράνους ακόμη</p>
        <a class="btn primary" href="<?php echo BASE_URL; ?>/dashboard/campaign-new.php">+ Δημιουργία Εράνου</a>
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
              <p class="small muted" style="margin: 0;"><?php echo money_eur((int) $c['current_amount']); ?> /
                <?php echo money_eur((int) $c['target_amount']); ?>
              </p>
            </div>

            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
              <?php if ($c['status'] === 'approved'): ?>
                <a class="btn" href="<?php echo BASE_URL; ?>/campaign.php?id=<?php echo (int) $c['id']; ?>">Προβολή</a>
              <?php endif; ?>
              <a class="btn"
                href="<?php echo BASE_URL; ?>/dashboard/campaign-edit.php?id=<?php echo (int) $c['id']; ?>">Επεξεργασία</a>
              <form method="post" action="<?php echo BASE_URL; ?>/dashboard/campaign-delete.php" style="display:inline;"
                onsubmit="return confirm('Σίγουρα διαγραφή;');">
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

  <!-- Analytics Section -->
  <div class="card" style="margin-top: 24px;">
    <h2>📊 Αναλυτικά</h2>
    <div class="hr"></div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-top: 16px;">
      <!-- Line Chart: Δωρεές ανά ημέρα -->
      <div>
        <h3 style="margin: 0 0 16px; font-size: 16px;">Δωρεές τελευταίων 30 ημερών</h3>
        <canvas id="donationsByDayChart" height="200"></canvas>
      </div>

      <!-- Doughnut Chart: Ανά κατηγορία -->
      <div>
        <h3 style="margin: 0 0 16px; font-size: 16px;">Ανά κατηγορία</h3>
        <canvas id="donationsByCategoryChart" height="200"></canvas>
      </div>
    </div>

    <!-- Bar Chart: Top Έρανοι -->
    <div style="margin-top: 32px;">
      <h3 style="margin: 0 0 16px; font-size: 16px;">Top Έρανοι</h3>
      <canvas id="campaignsProgressChart" height="120"></canvas>
    </div>
  </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
  // Χρώματα γραφημάτων
  const chartColors = [
    '#02a95c', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6',
    '#06b6d4', '#ec4899', '#84cc16'
  ];

  // Φόρτωση δεδομένων από analytics API
  fetch('<?php echo BASE_URL; ?>/dashboard/analytics.php')
    .then(res => res.json())
    .then(data => {
      // 1. Line Chart - Δωρεές ανά ημέρα
      new Chart(document.getElementById('donationsByDayChart'), {
        type: 'line',
        data: {
          labels: data.donations_by_day.map(d => d.label),
          datasets: [{
            label: 'Ποσό (€)',
            data: data.donations_by_day.map(d => d.total),
            borderColor: '#02a95c',
            backgroundColor: 'rgba(2, 169, 92, 0.1)',
            fill: true,
            tension: 0.3
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false }
          },
          scales: {
            y: { beginAtZero: true }
          }
        }
      });

      // 2. Doughnut Chart - Ανά κατηγορία
      if (data.donations_by_category.length > 0) {
        new Chart(document.getElementById('donationsByCategoryChart'), {
          type: 'doughnut',
          data: {
            labels: data.donations_by_category.map(d => d.icon + ' ' + d.name),
            datasets: [{
              data: data.donations_by_category.map(d => d.total),
              backgroundColor: chartColors
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: { position: 'bottom' }
            }
          }
        });
      } else {
        document.getElementById('donationsByCategoryChart').parentElement.innerHTML =
          '<p class="muted" style="text-align: center; padding: 40px;">Δεν υπάρχουν δωρεές ακόμη</p>';
      }

      // 3. Bar Chart - Top έρανοι
      if (data.campaigns_progress.length > 0) {
        new Chart(document.getElementById('campaignsProgressChart'), {
          type: 'bar',
          data: {
            labels: data.campaigns_progress.map(c => c.title.substring(0, 30) + (c.title.length > 30 ? '...' : '')),
            datasets: [{
              label: 'Συγκεντρώθηκαν (€)',
              data: data.campaigns_progress.map(c => c.current_amount),
              backgroundColor: '#3b82f6'
            }]
          },
          options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
              legend: { display: false }
            }
          }
        });
      } else {
        document.getElementById('campaignsProgressChart').parentElement.innerHTML =
          '<p class="muted" style="text-align: center; padding: 20px;">Δεν υπάρχουν ενεργοί χρηματικοί έρανοι</p>';
      }
    })
    .catch(err => console.error('Analytics error:', err));
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>