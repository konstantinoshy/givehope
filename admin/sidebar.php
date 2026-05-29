<?php
// Sidebar διαχείρισης
if (!isset($pdo)) {
    $pdo = db();
}

$pendingCountSidebar = $pendingCount ?? $pdo->query("SELECT COUNT(*) FROM campaigns WHERE status = 'pending'")->fetchColumn();
$reportsCountSidebar = $reportsCount ?? $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'new'")->fetchColumn();
$messagesCountSidebar = $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'new'")->fetchColumn();
$gdprPendingCount = $pdo->query("SELECT COUNT(*) FROM gdpr_requests WHERE status IN ('pending','verified')")->fetchColumn();

// Ενεργή σελίδα
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Mobile Hamburger Toggle -->
<button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
    </svg>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
            </svg>
            <span>GiveHope Admin</span>
        </div>
        <button class="sidebar-close" id="sidebarClose" aria-label="Close menu">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <nav class="sidebar-nav">
        <a href="<?php echo BASE_URL; ?>/admin/index.php"
            class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="9"></rect>
                <rect x="14" y="3" width="7" height="5"></rect>
                <rect x="14" y="12" width="7" height="9"></rect>
                <rect x="3" y="16" width="7" height="5"></rect>
            </svg>
            <span>Dashboard</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/campaigns.php"
            class="<?php echo $current_page == 'campaigns.php' ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path
                    d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
            </svg>
            <span>Έρανοι</span>
            <?php if ($pendingCountSidebar > 0): ?><span class="sidebar-badge">
                    <?php echo $pendingCountSidebar; ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/reports.php"
            class="<?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" />
                <line x1="4" y1="22" x2="4" y2="15" />
            </svg>
            <span>Αναφορές</span>
            <?php if ($reportsCountSidebar > 0): ?><span class="sidebar-badge">
                    <?php echo $reportsCountSidebar; ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/messages.php"
            class="<?php echo $current_page == 'messages.php' ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                <polyline points="22,6 12,13 2,6" />
            </svg>
            <span>Μηνύματα</span>
            <?php if ($messagesCountSidebar > 0): ?><span class="sidebar-badge">
                    <?php echo $messagesCountSidebar; ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/gdpr.php"
            class="<?php echo $current_page == 'gdpr.php' ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
            </svg>
            <span>GDPR</span>
            <?php if ($gdprPendingCount > 0): ?><span class="sidebar-badge">
                    <?php echo $gdprPendingCount; ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/users.php"
            class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
            <span>Χρήστες & Οργ.</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-profile mb-2">
            <div class="user-avatar">
                <?php echo isset($admin['username']) ? strtoupper(substr($admin['username'], 0, 1)) : 'A'; ?>
            </div>
            <div style="min-width: 0;">
                <div
                    style="font-weight: 700; font-size: 14px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                    <?php echo isset($admin['username']) ? e($admin['username']) : 'Admin'; ?>
                </div>
                <div style="font-size: 12px; color: var(--text-muted);">Administrator</div>
            </div>
        </div>
        <a href="<?php echo BASE_URL; ?>/admin/logout.php" class="logout-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Έξοδος
        </a>
    </div>
</aside>

<script>
    (function () {
        const toggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const closeBtn = document.getElementById('sidebarClose');

        function openSidebar() {
            sidebar.classList.add('open');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (toggle) toggle.addEventListener('click', openSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
    })();
</script>