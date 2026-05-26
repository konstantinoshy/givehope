<?php
$adminPageTitle = 'Επισκόπηση';
require_once __DIR__ . '/includes/header.php';

$pdo = db();
$admin = current_admin();

// Στατιστικά
$pendingCount = (int) $pdo->query("SELECT COUNT(*) FROM campaigns WHERE status = 'pending'")->fetchColumn();
$approvedCount = (int) $pdo->query("SELECT COUNT(*) FROM campaigns WHERE status = 'approved'")->fetchColumn();
$reportsCount = (int) $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'new'")->fetchColumn();
$totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalOrgs = (int) $pdo->query("SELECT COUNT(*) FROM organizations")->fetchColumn();
$totalDonations = (int) $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM donations")->fetchColumn();

// Πρόσφατοι εκκρεμείς έρανοι
$pending = $pdo->query("
    SELECT c.*, cat.name AS category_name, cat.icon AS category_icon,
           u.name AS user_name, u.email AS user_email,
           o.name AS org_name
    FROM campaigns c
    JOIN categories cat ON cat.id = c.category_id
    LEFT JOIN users u ON u.id = c.user_id
    LEFT JOIN organizations o ON o.id = c.org_id
    WHERE c.status = 'pending'
    ORDER BY c.created_at ASC
    LIMIT 10
")->fetchAll();

// Πρόσφατες δωρεές για το activity feed
$recentDonations = $pdo->query("
    SELECT d.*, c.title AS campaign_title
    FROM donations d
    JOIN campaigns c ON c.id = d.campaign_id
    ORDER BY d.created_at DESC
    LIMIT 5
")->fetchAll();

// Πρόσφατες εγγραφές χρηστών
$recentUsers = $pdo->query("
    SELECT id, name, email, created_at, 'user' AS type FROM users
    UNION ALL
    SELECT id, name, email, created_at, 'org' AS type FROM organizations
    ORDER BY created_at DESC
    LIMIT 5
")->fetchAll();
?>
<div class="page-header">
  <h1 class="page-title">Επισκόπηση Πλατφόρμας</h1>
  <p class="page-subtitle">Συνοπτικά δεδομένα και γρήγορες ενέργειες</p>
</div>

<!-- Quick Actions -->
<div class="quick-actions-row">
  <a href="<?php echo BASE_URL; ?>/admin/campaigns.php?status=pending" class="btn-quick-action primary">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
      <polyline points="22 4 12 14.01 9 11.01" />
    </svg>
    Έγκριση Εράνων
  </a>
  <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="btn-quick-action">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" />
      <line x1="4" y1="22" x2="4" y2="15" />
    </svg>
    Έλεγχος Αναφορών
  </a>
  <a href="<?php echo BASE_URL; ?>/admin/users.php" class="btn-quick-action">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
      <circle cx="9" cy="7" r="4" />
    </svg>
    Διαχείριση Χρηστών
  </a>
</div>

<!-- 6 KPI Grid -->
<div class="kpi-grid">
  <!-- 1 -->
  <div class="kpi-card">
    <div class="kpi-header">
      <span class="kpi-title">Εκκρεμεις Εγκρισεις</span>
      <div class="kpi-icon" style="background: #fef3c7; color: var(--warning-color);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10" />
          <path d="M12 6v6l4 2" />
        </svg>
      </div>
    </div>
    <div class="kpi-value"><?php echo $pendingCount; ?></div>
    <div class="kpi-trend neutral"><span>Απαιτείται έλεγχος</span></div>
  </div>
  <!-- 2 -->
  <div class="kpi-card">
    <div class="kpi-header">
      <span class="kpi-title">Ενεργοι Ερανοι</span>
      <div class="kpi-icon" style="background: #d1fae5; color: var(--success-color);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
          <polyline points="22 4 12 14.01 9 11.01" />
        </svg>
      </div>
    </div>
    <div class="kpi-value"><?php echo $approvedCount; ?></div>
    <div class="kpi-trend positive"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="3">
        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
        <polyline points="17 6 23 6 23 12" />
      </svg> <span>Σταθερή ροή</span></div>
  </div>
  <!-- 3 -->
  <div class="kpi-card">
    <div class="kpi-header">
      <span class="kpi-title">Αναφορες</span>
      <div class="kpi-icon" style="background: #fee2e2; color: var(--danger-color);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" />
          <line x1="4" y1="22" x2="4" y2="15" />
        </svg>
      </div>
    </div>
    <div class="kpi-value"><?php echo $reportsCount; ?></div>
    <div class="kpi-trend neutral"><span>Νέες προς έλεγχο</span></div>
  </div>
  <!-- 4 -->
  <div class="kpi-card">
    <div class="kpi-header">
      <span class="kpi-title">Χρηστες</span>
      <div class="kpi-icon" style="background: #e0e7ff; color: #6366f1;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
          <circle cx="12" cy="7" r="4" />
        </svg>
      </div>
    </div>
    <div class="kpi-value"><?php echo $totalUsers; ?></div>
    <div class="kpi-trend positive"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="3">
        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
        <polyline points="17 6 23 6 23 12" />
      </svg> <span>Ανάπτυξη κοινότητας</span></div>
  </div>
  <!-- 5 -->
  <div class="kpi-card">
    <div class="kpi-header">
      <span class="kpi-title">Οργανισμοι</span>
      <div class="kpi-icon" style="background: #fce7f3; color: #ec4899;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 21h18M5 21V7l8-4 8 4v14M9 21v-4h6v4" />
        </svg>
      </div>
    </div>
    <div class="kpi-value"><?php echo $totalOrgs; ?></div>
    <div class="kpi-trend neutral"><span>Πιστοποιημένοι</span></div>
  </div>
  <!-- 6 -->
  <div class="kpi-card">
    <div class="kpi-header">
      <span class="kpi-title">Συνολο Δωρεων</span>
      <div class="kpi-icon" style="background: #ccfbf1; color: var(--primary-color);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="1" x2="12" y2="23" />
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
        </svg>
      </div>
    </div>
    <div class="kpi-value" style="font-size: 24px;"><?php echo money_eur($totalDonations); ?></div>
    <div class="kpi-trend positive"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="3">
        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
        <polyline points="17 6 23 6 23 12" />
      </svg> <span>Αυξητική τάση</span></div>
  </div>
</div>

<!-- Εκκρεμείς Έρανοι -->
<div class="dashboard-card" style="margin-bottom: 24px;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 class="card-title" style="margin: 0;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color)" stroke-width="2">
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
        <line x1="16" y1="2" x2="16" y2="6" />
        <line x1="8" y1="2" x2="8" y2="6" />
        <line x1="3" y1="10" x2="21" y2="10" />
        <path d="M9 16l2 2 4-4" />
      </svg>
      Έρανοι προς Έγκριση
    </h2>
    <a class="btn-quick-action" style="padding: 6px 12px; font-size: 13px;"
      href="<?php echo BASE_URL; ?>/admin/campaigns.php">Δες όλους</a>
  </div>

  <?php if (count($pending) === 0): ?>
    <div class="empty-state-compact">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--success-color)" stroke-width="2">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
        <polyline points="22 4 12 14.01 9 11.01" />
      </svg>
      <div>
        <div style="font-weight: 600; font-size: 15px; color: var(--text-main);">Όλα ελεγμένα!</div>
        <div class="small muted">Δεν υπάρχουν έρανοι που εκκρεμούν για έγκριση αυτή τη στιγμή.</div>
      </div>
    </div>
  <?php else: ?>
    <div style="overflow-x: auto;">
      <table class="table" style="margin: 0; min-width: 800px;">
        <thead style="background: var(--bg-light);">
          <tr>
            <th style="border-top-left-radius: 8px;">Έρανος</th>
            <th>Δημιουργός</th>
            <th>Κατηγορία</th>
            <th>Στόχος</th>
            <th>Ημ/νία</th>
            <th style="border-top-right-radius: 8px; text-align: right;">Ενέργεια</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pending as $c): ?>
            <tr>
              <td>
                <div style="font-weight: 600; color: var(--text-main);">
                  <?php echo e(mb_strimwidth($c['title'], 0, 40, '...', 'UTF-8')); ?>
                </div>
              </td>
              <td>
                <?php if ($c['user_name']): ?>
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2">
                      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                      <circle cx="12" cy="7" r="4" />
                    </svg>
                    <div>
                      <?php echo e($c['user_name']); ?><br>
                      <span class="small muted"><?php echo e($c['user_email']); ?></span>
                    </div>
                  </div>
                <?php else: ?>
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ec4899" stroke-width="2">
                      <path d="M3 21h18M5 21V7l8-4 8 4v14M9 21v-4h6v4" />
                    </svg>
                    <?php echo e($c['org_name']); ?>
                  </div>
                <?php endif; ?>
              </td>
              <td><?php echo category_icon($c['category_id']); ?>     <?php echo e($c['category_name']); ?></td>
              <td><?php echo $c['target_amount'] ? money_eur((int) $c['target_amount']) : '—'; ?></td>
              <td class="small"><?php echo date('d/m/Y H:i', strtotime($c['created_at'])); ?></td>
              <td style="text-align: right;">
                <a class="btn-quick-action primary" style="padding: 6px 14px; font-size: 13px;"
                  href="<?php echo BASE_URL; ?>/admin/campaign-review.php?id=<?php echo (int) $c['id']; ?>">
                  Έλεγχος
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <!-- Recent Activity -->
  <div class="middle-grid">
    <!-- Πρόσφατες Δωρεές -->
    <div class="dashboard-card">
      <h2 class="card-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color)" stroke-width="2">
          <line x1="12" y1="1" x2="12" y2="23" />
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
        </svg>
        Πρόσφατες Δωρεές
      </h2>

      <?php if (count($recentDonations) === 0): ?>
        <div class="empty-state-compact">
          <span class="muted small">Δεν υπάρχουν δωρεές ακόμα</span>
        </div>
      <?php else: ?>
        <div class="dense-list">
          <?php foreach ($recentDonations as $don): ?>
            <div class="dense-list-item">
              <div class="avatar-sm" style="background: #ccfbf1; color: var(--primary-color);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path
                    d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                </svg>
              </div>
              <div style="flex: 1; min-width: 0;">
                <div style="font-weight: 700; color: var(--text-main);"><?php echo money_eur((int) $don['amount']); ?>
                </div>
                <div class="small muted"
                  style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90%;">
                  <?php echo e($don['campaign_title']); ?>
                </div>
              </div>
              <div class="small" style="color: var(--text-muted); text-align: right;">
                <span
                  style="display: block; font-weight: 500;"><?php echo date('d/m', strtotime($don['created_at'])); ?></span>
                <span style="font-size: 11px;"><?php echo date('H:i', strtotime($don['created_at'])); ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Νέες Εγγραφές -->
    <div class="dashboard-card">
      <h2 class="card-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-color)" stroke-width="2">
          <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
          <circle cx="8.5" cy="7" r="4" />
          <line x1="20" y1="8" x2="20" y2="14" />
          <line x1="23" y1="11" x2="17" y2="11" />
        </svg>
        Νέες Εγγραφές
      </h2>

      <?php if (count($recentUsers) === 0): ?>
        <div class="empty-state-compact">
          <span class="muted small">Δεν υπάρχουν πρόσφατες εγγραφές</span>
        </div>
      <?php else: ?>
        <div class="dense-list">
          <?php foreach ($recentUsers as $usr): ?>
            <div class="dense-list-item">
              <?php if ($usr['type'] === 'user'): ?>
                <div class="avatar-sm" style="background: #e0e7ff; color: #6366f1;">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                  </svg>
                </div>
              <?php else: ?>
                <div class="avatar-sm" style="background: #fce7f3; color: #ec4899;">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 21h18M5 21V7l8-4 8 4v14M9 21v-4h6v4" />
                  </svg>
                </div>
              <?php endif; ?>

              <div style="flex: 1; min-width: 0;">
                <div
                  style="font-weight: 600; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90%;">
                  <?php echo e($usr['name']); ?>
                </div>
                <div class="small muted"
                  style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90%;">
                  <?php echo e($usr['email']); ?>
                </div>
              </div>
              <div class="small" style="color: var(--text-muted); text-align: right;">
                <span
                  style="display: block; font-weight: 500;"><?php echo date('d/m', strtotime($usr['created_at'])); ?></span>
                <span style="font-size: 11px;"><?php echo date('H:i', strtotime($usr['created_at'])); ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Analytics Section -->
  <div class="card" style="margin-top: 24px;">
    <h2 style="margin: 0 0 16px; display: flex; align-items: center; gap: 10px;">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
        <line x1="18" y1="20" x2="18" y2="10" />
        <line x1="12" y1="20" x2="12" y2="4" />
        <line x1="6" y1="20" x2="6" y2="14" />
      </svg>
      Αναλυτικά Πλατφόρμας
    </h2>

    <div class="analytics-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
      <!-- Bar Chart: Δωρεές ανά ημέρα -->
      <div>
        <h3 style="margin: 0 0 16px; font-size: 15px; color: var(--text-muted);">Δωρεές τελευταίων 30 ημερών</h3>
        <div style="position: relative; height: 300px; width: 100%;">
          <canvas id="donationsByDayChart"></canvas>
        </div>
      </div>

      <!-- Pie Chart: Ανά κατηγορία -->
      <div>
        <h3 style="margin: 0 0 16px; font-size: 15px; color: var(--text-muted);">Ανά κατηγορία</h3>
        <div style="position: relative; height: 300px; width: 100%;">
          <canvas id="donationsByCategoryChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Horizontal Bar: Top έρανοι -->
    <div style="margin-top: 32px;">
      <h3 style="margin: 0 0 16px; font-size: 15px; color: var(--text-muted);">Top 5 Έρανοι (κατά ποσό)</h3>
      <div style="position: relative; height: 250px; width: 100%;">
        <canvas id="campaignsProgressChart"></canvas>
      </div>
    </div>
  </div>
  </main>

  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <script>
    // Χρώματα γραφημάτων
    const chartColors = [
      '#0d9488',
      '#3b82f6',
      '#f59e0b',
      '#ef4444',
      '#8b5cf6',
      '#10b981',
      '#ec4899',
      '#0ea5e9',
    ];

    // Ρυθμίσεις εμφάνισης
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#6b7280';

    // Φόρτωση δεδομένων από analytics API
    fetch('<?php echo BASE_URL; ?>/admin/analytics.php')
      .then(res => res.json())
      .then(data => {
        // 1. Bar Chart - Δωρεές ανά ημέρα
        new Chart(document.getElementById('donationsByDayChart'), {
          type: 'bar',
          data: {
            labels: data.donations_by_day.map(d => d.label),
            datasets: [{
              label: 'Ποσό (€)',
              data: data.donations_by_day.map(d => d.total),
              backgroundColor: '#0d9488',
              borderRadius: 4,
              borderSkipped: false
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: {
                backgroundColor: '#1f2937',
                padding: 12,
                cornerRadius: 8,
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                grid: {
                  color: '#f3f4f6',
                  drawBorder: false
                },
                border: { display: false }
              },
              x: {
                grid: { display: false },
                border: { display: false }
              }
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
                backgroundColor: chartColors,
                borderWidth: 0,
                hoverOffset: 4
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              cutout: '70%',
              plugins: {
                legend: {
                  position: 'right',
                  labels: {
                    font: { size: 12 },
                    usePointStyle: true,
                    padding: 20
                  }
                },
                tooltip: {
                  backgroundColor: '#1f2937',
                  padding: 12,
                  cornerRadius: 8,
                }
              }
            }
          });
        } else {
          document.getElementById('donationsByCategoryChart').parentElement.innerHTML =
            '<div class="empty-state-compact"><span class="muted small">Δεν υπάρχουν δωρεές ανά κατηγορία</span></div>';
        }

        // 3. Horizontal Bar Chart - Top έρανοι
        if (data.campaigns_progress.length > 0) {
          new Chart(document.getElementById('campaignsProgressChart'), {
            type: 'bar',
            data: {
              labels: data.campaigns_progress.map(c => c.title.substring(0, 30) + (c.title.length > 30 ? '...' : '')),
              datasets: [{
                label: 'Συγκεντρώθηκαν (€)',
                data: data.campaigns_progress.map(c => c.current_amount),
                backgroundColor: '#3b82f6',
                borderRadius: 4,
                borderSkipped: false,
                barThickness: 16
              }]
            },
            options: {
              indexAxis: 'y',
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: { display: false },
                tooltip: {
                  backgroundColor: '#1f2937',
                  padding: 12,
                  cornerRadius: 8,
                }
              },
              scales: {
                x: {
                  beginAtZero: true,
                  grid: { color: '#f3f4f6' },
                  border: { display: false }
                },
                y: {
                  grid: { display: false },
                  border: { display: false }
                }
              }
            }
          });
        } else {
          document.getElementById('campaignsProgressChart').parentElement.innerHTML =
            '<div class="empty-state-compact"><span class="muted small">Δεν υπάρχουν ενεργοί έρανοι</span></div>';
        }
      })
      .catch(err => console.error('Analytics error:', err));
  </script>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>