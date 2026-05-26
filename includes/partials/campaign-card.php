<?php
// Κάρτα εράνου (partial)
// $c: δεδομένα εράνου, $options: extra_class, badge, show_description, desc_length, show_creator, gradient
function render_campaign_card(array $c, array $options = []): void
{
  $extraClass = $options['extra_class'] ?? '';
  $showDesc = $options['show_description'] ?? true;
  $descLen = $options['desc_length'] ?? 100;
  $showCreator = $options['show_creator'] ?? false;
  $gradient = $options['gradient'] ?? false;
  $badge = $options['badge'] ?? null;

  $image = $c['image_url'] ?: 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=800&q=80';
  $actualPct = (int) round(($c['current_amount'] / max(1, $c['target_amount'])) * 100);
  $displayPct = min(100, $actualPct);
  $goalReached = $c['current_amount'] >= $c['target_amount'] && $c['target_amount'] > 0;

  $creator = $c['user_name'] ?? ($c['org_name'] ?? '');
  $creatorInitial = mb_substr($creator ?: '?', 0, 1, 'UTF-8');

  if ($badge === null) {
    $badge = category_icon($c['category_id']) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . e($c['category_name']);
  }

  $classes = 'campaign-card';
  if ($goalReached)
    $classes .= ' goal-reached';
  if ($extraClass)
    $classes .= ' ' . $extraClass;
  ?>
  <a href="<?php echo BASE_URL; ?>/campaign.php?id=<?php echo (int) $c['id']; ?>" class="<?php echo $classes; ?>">
    <div class="card-image">
      <img src="<?php echo e($image); ?>" alt="<?php echo e($c['title']); ?>">
      <?php if ($gradient): ?>
        <div class="card-image-overlay"></div><?php endif; ?>
      <span class="card-badge"><?php echo $badge; ?></span>
      <?php if ($goalReached): ?>
        <span class="goal-badge">Στόχος επιτεύχθηκε!</span>
      <?php endif; ?>
    </div>
    <div class="card-content">
      <h3><?php echo e($c['title']); ?></h3>
      <?php if ($showDesc): ?>
        <p><?php echo e(mb_strimwidth($c['description'], 0, $descLen, '…', 'UTF-8')); ?></p>
      <?php endif; ?>
      <div style="margin-top: auto;">
        <div
          class="progress-bar <?php echo $gradient ? 'progress-bar-gradient' : ''; ?> <?php echo $goalReached ? 'completed' : ''; ?>">
          <div class="fill" style="width: <?php echo $displayPct; ?>%"></div>
        </div>
        <div class="progress-text">
          <?php echo money_eur((int) $c['current_amount']); ?>
          <span>συγκεντρώθηκαν</span>
          <span style="float: right; color: var(--primary); font-weight: 700;"><?php echo $displayPct; ?>%</span>
          <?php if ($goalReached && $actualPct > 100): ?>
            <span class="overfunded">(<?php echo $actualPct; ?>%)</span>
          <?php endif; ?>
        </div>
        <?php if ($showCreator && $creator): ?>
          <div class="card-creator" style="margin-top: 12px;">
            <span class="creator-avatar"><?php echo e($creatorInitial); ?></span>
            <span class="creator-name"><?php echo e($creator); ?></span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </a>
  <?php
}
