<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="base-url" content="<?= parse_url(url('/'), PHP_URL_PATH) ?>">
    <title><?= e($title ?? 'Admin') ?> - Admin Panel</title>
    <link href="<?= asset('css/bootstrap-icons.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/admin.css') ?>" rel="stylesheet">
</head>
<body>
    <nav class="admin-topbar">
        <div class="topbar-left">
            <button class="yt-nav-icon d-lg-none" data-sidebar-toggle title="Toggle sidebar"><i class="bi bi-list"></i></button>
            <button class="yt-nav-icon d-none d-lg-flex" data-sidebar-collapse title="Collapse sidebar"><i class="bi bi-list"></i></button>
            <a href="<?= url('/admin/dashboard') ?>" class="admin-brand">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="#FF0000"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0C.488 3.45.029 5.804 0 12c.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0C23.512 20.55 23.971 18.196 24 12c-.029-6.185-.484-8.549-4.385-8.816zM9 16V8l8 3.993L9 16z"/></svg>
                <span>Admin</span>
            </a>
        </div>
        <div class="topbar-right">
            <a href="<?= url('/') ?>" class="yt-nav-icon" title="View site" target="_blank"><i class="bi bi-globe"></i></a>

            <div class="yt-notif-wrapper">
                <button class="yt-nav-icon" data-dropdown-toggle="adminNotifPanel" title="Notifications">
                    <i class="bi bi-bell"></i>
                    <?php if (!empty($adminUnreadCount) && $adminUnreadCount > 0): ?>
                        <span class="yt-notif-count"><?= $adminUnreadCount > 99 ? '99+' : e((string)$adminUnreadCount) ?></span>
                    <?php endif; ?>
                </button>
                <div class="admin-notif-panel" id="adminNotifPanel">
                    <div class="panel-header">
                        <h6>Notifications</h6>
                        <a href="<?= url('/admin/notifications') ?>" style="font-size:12px;color:var(--yt-info)">View all</a>
                    </div>
                    <div class="panel-list">
                        <?php if (!empty($adminNotifications ?? [])): ?>
                            <?php foreach (array_slice($adminNotifications, 0, 10) as $notif): ?>
                                <div class="notif-item">
                                    <div class="notif-icon" style="background:var(--yt-accent-light);color:var(--yt-accent)"><i class="bi bi-bell"></i></div>
                                    <div>
                                        <div class="notif-text"><?= e($notif['message'] ?? '') ?></div>
                                        <div class="notif-time"><?= time_ago($notif['created_at'] ?? '') ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4" style="color:var(--yt-text-muted)"><i class="bi bi-bell-slash fs-3 d-block mb-2"></i><small>No notifications</small></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <button class="yt-nav-icon" data-theme-toggle title="Toggle theme"><i class="bi bi-moon-stars-fill"></i></button>

            <div class="yt-avatar-wrapper">
                <button class="yt-avatar-btn" data-dropdown-toggle="adminUserDropdown">
                    <?= strtoupper(substr($_SESSION['current_user']['display_name'] ?? $_SESSION['current_user']['username'] ?? 'A', 0, 1)) ?>
                </button>
                <div class="yt-user-dropdown" id="adminUserDropdown">
                    <div class="yt-user-dropdown-header">
                        <div style="width:40px;height:40px;border-radius:50%;background:var(--yt-accent);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;flex-shrink:0">
                            <?= strtoupper(substr($_SESSION['current_user']['display_name'] ?? $_SESSION['current_user']['username'] ?? 'A', 0, 1)) ?>
                        </div>
                        <div>
                            <div style="font-weight:500;font-size:14px"><?= e($_SESSION['current_user']['display_name'] ?? $_SESSION['current_user']['username'] ?? 'Admin') ?></div>
                            <div style="font-size:12px;color:var(--yt-text-secondary)">Administrator</div>
                        </div>
                    </div>
                    <a href="<?= url('/') ?>" class="yt-dropdown-item"><i class="bi bi-globe"></i> View site</a>
                    <a href="<?= url('/viewer/profile') ?>" class="yt-dropdown-item"><i class="bi bi-person"></i> Profile</a>
                    <a href="<?= url('/admin/general-settings') ?>" class="yt-dropdown-item"><i class="bi bi-gear"></i> Settings</a>
                    <button class="yt-dropdown-item" data-theme-toggle><i class="bi bi-moon-stars"></i> Dark theme <div class="theme-toggle-track"></div></button>
                    <div class="yt-dropdown-divider"></div>
                    <form method="POST" action="<?= url('/logout') ?>" style="margin:0">
                        <?= csrf_field() ?>
                        <button class="yt-dropdown-item" type="submit"><i class="bi bi-box-arrow-right"></i> Sign out</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="admin-sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="admin-sidebar" id="sidebar">
        <div class="admin-sidebar-logo">
            <a href="<?= url('/admin/dashboard') ?>"><svg viewBox="0 0 24 24" width="24" height="24" fill="#FF0000"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0C.488 3.45.029 5.804 0 12c.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0C23.512 20.55 23.971 18.196 24 12c-.029-6.185-.484-8.549-4.385-8.816zM9 16V8l8 3.993L9 16z"/></svg> <span>Admin Panel</span></a>
        </div>
        <nav style="padding:4px 0">
            <div class="admin-nav-header">Dashboard</div>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>" href="<?= url('/admin/dashboard') ?>"><i class="bi bi-speedometer2"></i><span class="admin-nav-text">Dashboard</span></a>

            <div class="admin-nav-header">Content</div>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'videos' ? 'active' : '' ?>" href="<?= url('/admin/videos') ?>"><i class="bi bi-play-btn"></i><span class="admin-nav-text">Videos</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'comments' ? 'active' : '' ?>" href="<?= url('/admin/comments') ?>"><i class="bi bi-chat-dots"></i><span class="admin-nav-text">Comments</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'categories' ? 'active' : '' ?>" href="<?= url('/admin/categories') ?>"><i class="bi bi-grid"></i><span class="admin-nav-text">Categories</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'tags' ? 'active' : '' ?>" href="<?= url('/admin/tags') ?>"><i class="bi bi-tags"></i><span class="admin-nav-text">Tags</span></a>

            <div class="admin-nav-header">Users &amp; Channels</div>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'users' ? 'active' : '' ?>" href="<?= url('/admin/users') ?>"><i class="bi bi-people"></i><span class="admin-nav-text">Users</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'creators' ? 'active' : '' ?>" href="<?= url('/admin/creators') ?>"><i class="bi bi-camera-video"></i><span class="admin-nav-text">Creators</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'channels' ? 'active' : '' ?>" href="<?= url('/admin/channels') ?>"><i class="bi bi-broadcast"></i><span class="admin-nav-text">Channels</span></a>

            <div class="admin-nav-header">Moderation</div>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'reports' ? 'active' : '' ?>" href="<?= url('/admin/reports') ?>"><i class="bi bi-flag"></i><span class="admin-nav-text">Reports</span><?php if (!empty($pendingReports ?? 0)): ?><span class="admin-nav-badge"><?= $pendingReports ?></span><?php endif; ?></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'copyright' ? 'active' : '' ?>" href="<?= url('/admin/copyright') ?>"><i class="bi bi-shield-check"></i><span class="admin-nav-text">Copyright</span></a>

            <div class="admin-nav-header">Monetization</div>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'monetization' ? 'active' : '' ?>" href="<?= url('/admin/monetization') ?>"><i class="bi bi-cash"></i><span class="admin-nav-text">Monetization</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'payouts' ? 'active' : '' ?>" href="<?= url('/admin/payouts') ?>"><i class="bi bi-wallet2"></i><span class="admin-nav-text">Payouts</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'payments' ? 'active' : '' ?>" href="<?= url('/admin/payments') ?>"><i class="bi bi-credit-card"></i><span class="admin-nav-text">Payments</span></a>

            <div class="admin-nav-header">Advertising</div>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'advertisements' ? 'active' : '' ?>" href="<?= url('/admin/advertisements') ?>"><i class="bi bi-megaphone"></i><span class="admin-nav-text">Advertisements</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'revenue' ? 'active' : '' ?>" href="<?= url('/admin/revenue') ?>"><i class="bi bi-graph-up-arrow"></i><span class="admin-nav-text">Revenue</span></a>

            <div class="admin-nav-header">CMS</div>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'pages' ? 'active' : '' ?>" href="<?= url('/admin/pages') ?>"><i class="bi bi-file-earmark"></i><span class="admin-nav-text">Pages</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'blog' ? 'active' : '' ?>" href="<?= url('/admin/blog') ?>"><i class="bi bi-journal-richtext"></i><span class="admin-nav-text">Blog</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'faqs' ? 'active' : '' ?>" href="<?= url('/admin/faqs') ?>"><i class="bi bi-question-circle"></i><span class="admin-nav-text">FAQs</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'contact-messages' ? 'active' : '' ?>" href="<?= url('/admin/contact-messages') ?>"><i class="bi bi-envelope"></i><span class="admin-nav-text">Contact Messages</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'notifications' ? 'active' : '' ?>" href="<?= url('/admin/notifications') ?>"><i class="bi bi-bell"></i><span class="admin-nav-text">Notifications</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'privacy-policy' || ($activeMenu ?? '') === 'terms' ? 'active' : '' ?>" href="<?= url('/admin/privacy-policy') ?>"><i class="bi bi-file-earmark-text"></i><span class="admin-nav-text">Policies</span></a>

            <div class="admin-nav-header">Settings</div>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'general-settings' ? 'active' : '' ?>" href="<?= url('/admin/general-settings') ?>"><i class="bi bi-gear"></i><span class="admin-nav-text">General</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'security-settings' ? 'active' : '' ?>" href="<?= url('/admin/security-settings') ?>"><i class="bi bi-shield-lock"></i><span class="admin-nav-text">Security</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'storage-settings' ? 'active' : '' ?>" href="<?= url('/admin/storage-settings') ?>"><i class="bi bi-cloud"></i><span class="admin-nav-text">Storage</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'email-settings' ? 'active' : '' ?>" href="<?= url('/admin/email-settings') ?>"><i class="bi bi-envelope-gear"></i><span class="admin-nav-text">Email</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'sms-settings' ? 'active' : '' ?>" href="<?= url('/admin/sms-settings') ?>"><i class="bi bi-phone"></i><span class="admin-nav-text">SMS</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'api-settings' ? 'active' : '' ?>" href="<?= url('/admin/api-settings') ?>"><i class="bi bi-plug"></i><span class="admin-nav-text">API</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'payment-gateways' ? 'active' : '' ?>" href="<?= url('/admin/payment-gateways') ?>"><i class="bi bi-bank"></i><span class="admin-nav-text">Payments Config</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'social-login' ? 'active' : '' ?>" href="<?= url('/admin/social-login') ?>"><i class="bi bi-share"></i><span class="admin-nav-text">Social Login</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'seo-settings' ? 'active' : '' ?>" href="<?= url('/admin/seo-settings') ?>"><i class="bi bi-search"></i><span class="admin-nav-text">SEO</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'roles' || ($activeMenu ?? '') === 'permissions' ? 'active' : '' ?>" href="<?= url('/admin/roles') ?>"><i class="bi bi-shield-fill-check"></i><span class="admin-nav-text">Roles &amp; Permissions</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'email-templates' ? 'active' : '' ?>" href="<?= url('/admin/email-templates') ?>"><i class="bi bi-envelope-paper"></i><span class="admin-nav-text">Email Templates</span></a>

            <div class="admin-nav-header">System</div>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'analytics' ? 'active' : '' ?>" href="<?= url('/admin/analytics') ?>"><i class="bi bi-graph-up"></i><span class="admin-nav-text">Analytics</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'activity-logs' ? 'active' : '' ?>" href="<?= url('/admin/activity-logs') ?>"><i class="bi bi-journal-text"></i><span class="admin-nav-text">Activity Logs</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'login-logs' ? 'active' : '' ?>" href="<?= url('/admin/login-logs') ?>"><i class="bi bi-box-arrow-in-right"></i><span class="admin-nav-text">Login Logs</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'audit-logs' ? 'active' : '' ?>" href="<?= url('/admin/audit-logs') ?>"><i class="bi bi-clipboard-check"></i><span class="admin-nav-text">Audit Logs</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'backup' ? 'active' : '' ?>" href="<?= url('/admin/backup') ?>"><i class="bi bi-cloud-arrow-up"></i><span class="admin-nav-text">Backup</span></a>
            <a class="admin-nav-item <?= ($activeMenu ?? '') === 'system-health' ? 'active' : '' ?>" href="<?= url('/admin/system-health') ?>"><i class="bi bi-heart-pulse"></i><span class="admin-nav-text">System Health</span></a>
        </nav>
    </aside>

    <main class="admin-main">
        <?php if (isset($success) && $success !== null): ?>
            <div class="yt-alert yt-alert-success"><span class="alert-icon"><i class="bi bi-check-circle-fill"></i></span><span class="alert-text"><?= e($success) ?></span><span class="alert-close"><i class="bi bi-x"></i></span></div>
        <?php endif; ?>
        <?php if (isset($error) && $error !== null): ?>
            <div class="yt-alert yt-alert-error"><span class="alert-icon"><i class="bi bi-exclamation-circle-fill"></i></span><span class="alert-text"><?= e($error) ?></span><span class="alert-close"><i class="bi bi-x"></i></span></div>
        <?php endif; ?>

        <div class="admin-breadcrumb">
            <a href="<?= url('/admin/dashboard') ?>">Admin</a>
            <span class="separator">/</span>
            <?php if (isset($title) && $title !== 'Admin Dashboard'): ?>
                <span class="current"><?= e($title) ?></span>
            <?php else: ?>
                <span class="current">Dashboard</span>
            <?php endif; ?>
        </div>

        <?= $__content ?>

        <footer class="mt-4 pt-3 border-top text-center" style="color:var(--yt-text-muted);font-size:12px">
            <p class="mb-0">&copy; <?= date('Y') ?> YouTube Clone Admin Panel</p>
        </footer>
    </main>

    <script src="<?= asset('js/app.js') ?>"></script>
    <script src="<?= asset('js/charts.js') ?>"></script>
    <script>
    (function() {
        const sidebarCollapse = document.querySelector('[data-sidebar-collapse]');
        const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if (sidebarCollapse) {
            sidebarCollapse.addEventListener('click', () => sidebar.classList.toggle('collapsed'));
        }
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('mobile-show');
                overlay.classList.toggle('show');
            });
        }
        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('mobile-show');
                overlay.classList.remove('show');
            });
        }
    })();
    </script>
</body>
</html>
