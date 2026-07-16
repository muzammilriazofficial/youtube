<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="base-url" content="<?= parse_url(url('/'), PHP_URL_PATH) ?>">
    <meta name="description" content="<?= e($metaDescription ?? 'YouTube Clone - Watch, Upload and Share Videos') ?>">
    <meta name="theme-color" content="#FF0000">
    <title><?= e($title ?? 'YouTube Clone') ?></title>
    <link href="<?= asset('vendor/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/bootstrap-icons.css') ?>" rel="stylesheet">
</head>
<body>
    <div class="yt-loading-bar"><div class="bar"></div></div>
    <nav class="yt-navbar">
        <div class="nav-left">
            <button class="yt-nav-icon" data-sidebar-toggle title="Menu">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
            </button>
            <a href="<?= ($studioMode ?? false) ? url('/creator') : url('/') ?>" class="yt-logo">
                <?php if (($studioMode ?? false)): ?>
                    <svg viewBox="0 0 24 24" width="28" height="28" fill="#FF0000"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0C.488 3.45.029 5.804 0 12c.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0C23.512 20.55 23.971 18.196 24 12c-.029-6.185-.484-8.549-4.385-8.816zM9 16V8l8 3.993L9 16z"/></svg>
                    <span style="font-weight:600;font-size:15px;color:var(--yt-text-primary)">YouTube Studio</span>
                <?php else: ?>
                    <svg viewBox="0 0 28 20" width="28" height="20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M27.9727 3.12324C27.6435 1.89323 26.6768 0.926623 25.4468 0.597366C23.2197 0 14.285 0 14.285 0C14.285 0 5.35042 0 3.12323 0.597366C1.89323 0.926623 0.926623 1.89323 0.597366 3.12324C0 5.35042 0 10 0 10C0 10 0 14.6496 0.597366 16.8768C0.926623 18.1068 1.89323 19.0734 3.12323 19.4026C5.35042 20 14.285 20 14.285 20C14.285 20 23.2197 20 25.4468 19.4026C26.6768 19.0734 27.6435 18.1068 27.9727 16.8768C28.5701 14.6496 28.5701 10 28.5701 10C28.5701 10 28.5677 5.35042 27.9727 3.12324Z" fill="#FF0000"/>
                        <path d="M11.4253 14.2854L18.8477 10.0004L11.4253 5.71533V14.2854Z" fill="#fff"/>
                    </svg>
                    <span style="font-weight:900;letter-spacing:-1.5px;font-family:'Roboto',Arial,sans-serif">YouTube</span>
                <?php endif; ?>
            </a>
        </div>

        <div class="nav-center">
        <?php if (!($studioMode ?? false)): ?>
            <div class="yt-search-wrapper yt-search">
                <form class="yt-search-form" action="<?= url('/search') ?>" method="GET">
                    <input type="search" class="yt-search-input" name="q" placeholder="Search" value="<?= e($searchQuery ?? '') ?>" autocomplete="off">
                    <button class="yt-search-btn" type="submit"><svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg></button>
                </form>
                <div class="yt-search-autocomplete"></div>
            </div>
        <?php endif; ?>
        </div>

        <div class="nav-right">
        <?php if (!($studioMode ?? false)): ?>
            <button class="yt-nav-icon yt-search-mobile-btn" data-search-expand title="Search">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
            </button>
        <?php endif; ?>

            <?php if (($currentUser ?? null) !== null): ?>
                <a href="<?= ($studioMode ?? false) ? url('/creator/videos/create') : url('/creator/videos/create') ?>" class="yt-create-btn yt-nav-icon" title="Create">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M14 13h-3v3H9v-3H6v-2h3V8h2v3h3v2zm3-6H3v12h14v-6.39l4 1.83V8.56l-4 1.83V7m1-2v2.83L22 8v8l-4-1.83V18H2V6h16z"/></svg>
                    <span>Create</span>
                </a>

                <div class="yt-notif-wrapper">
                    <button class="yt-nav-icon" data-dropdown-toggle="notifDropdown" data-notif-poll title="Notifications">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                        <?php if (!empty($unreadNotificationCount) && $unreadNotificationCount > 0): ?>
                            <span class="yt-notif-count"><?= $unreadNotificationCount > 99 ? '99+' : e((string)$unreadNotificationCount) ?></span>
                        <?php else: ?>
                            <span class="yt-notif-count" style="display:none">0</span>
                        <?php endif; ?>
                    </button>
                    <div class="yt-notif-dropdown" id="notifDropdown">
                        <div class="yt-notif-header">
                            <h6>Notifications</h6>
                            <button class="yt-nav-icon" style="width:28px;height:28px;font-size:14px" data-tooltip="Mark all as read" onclick="fetch('<?= url('/viewer/notifications/read-all') ?>',{method:'POST',headers:{'X-CSRF-Token':CSRF.getToken(),'X-Requested-With':'XMLHttpRequest'}}).then(()=>Toast.success('All marked as read'))"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 7l-1.41-1.41-6.34 6.34 1.41 1.41L18 7zm4.24-1.41L11.66 16.17 7.48 12l-1.41 1.41L11.66 19l12-12-1.42-1.41zM.41 13.41L6 19l1.41-1.41L1.83 12 .41 13.41z"/></svg></button>
                        </div>
                        <div class="yt-notif-list">
                            <?php if (!empty($notifications ?? [])): ?>
                                <?php foreach ($notifications as $notif): ?>
                                    <a href="<?= e($notif['link'] ?? '#') ?>" class="yt-notif-item <?= empty($notif['read_at']) ? 'unread' : '' ?>">
                                        <div style="width:40px;height:40px;border-radius:50%;background:var(--yt-surface);display:flex;align-items:center;justify-content:center;flex-shrink:0"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg></div>
                                        <div>
                                            <div class="notif-text"><?= e($notif['message'] ?? '') ?></div>
                                            <div class="notif-time"><?= time_ago($notif['created_at'] ?? '') ?></div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4" style="color:var(--yt-text-muted)"><svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor" style="opacity:0.3"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/><line x1="3" y1="3" x2="21" y2="21" stroke="currentColor" stroke-width="2"/></svg><small style="display:block;margin-top:8px">No notifications yet</small></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="yt-avatar-wrapper">
                    <button class="yt-avatar-btn" data-dropdown-toggle="userDropdown">
                        <?php if (!empty($currentUser['avatar'])): ?>
                            <img src="<?= url(e($currentUser['avatar'])) ?>" alt="<?= e($currentUser['username'] ?? '') ?>">
                        <?php else: ?>
                            <?= strtoupper(substr($currentUser['username'] ?? 'U', 0, 1)) ?>
                        <?php endif; ?>
                    </button>
                    <div class="yt-user-dropdown" id="userDropdown">
                        <div class="yt-user-dropdown-header">
                            <div style="width:40px;height:40px;border-radius:50%;background:var(--yt-accent);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;flex-shrink:0">
                                <?php if (!empty($currentUser['avatar'])): ?>
                                    <img src="<?= url(e($currentUser['avatar'])) ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
                                <?php else: ?>
                                    <?= strtoupper(substr($currentUser['username'] ?? 'U', 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div style="font-weight:500;font-size:14px"><?= e($currentUser['username'] ?? '') ?></div>
                                <div style="font-size:12px;color:var(--yt-text-secondary)">@<?= e($currentUser['username'] ?? '') ?></div>
                            </div>
                        </div>
                        <?php if (!empty($currentUser['channel_slug']) || !empty($currentUser['custom_url'])): ?>
                        <a href="<?= url('/channel/' . e($currentUser['custom_url'] ?? $currentUser['channel_slug'] ?? '')) ?>" class="yt-dropdown-item"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg> Your channel</a>
                        <?php else: ?>
                        <a href="<?= url('/channel/create') ?>" class="yt-dropdown-item"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg> Create channel</a>
                        <?php endif; ?>
                        <a href="<?= url('/creator/dashboard') ?>" class="yt-dropdown-item"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg> Creator Studio</a>
                        <a href="<?= url('/viewer/playlists') ?>" class="yt-dropdown-item"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M15 6H3v2h12V6zm0 4H3v2h12v-2zM3 16h8v-2H3v2zM17 6v8.18c-.31-.11-.65-.18-1-.18-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3V8h3V6h-5z"/></svg> Playlists</a>
                        <a href="<?= url('/viewer/history') ?>" class="yt-dropdown-item"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg> Watch history</a>
                        <a href="<?= url('/viewer/notifications') ?>" class="yt-dropdown-item"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg> Notifications</a>
                        <div class="yt-dropdown-divider"></div>
                        <a href="<?= url('/viewer/profile') ?>" class="yt-dropdown-item"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg> Settings</a>
                        <a href="<?= url('/viewer/profile/edit') ?>" class="yt-dropdown-item"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9c.83 0 1.5-.67 1.5-1.5 0-.39-.15-.74-.39-1.01-.23-.26-.38-.61-.38-1 0-.83.67-1.5 1.5-1.5H16c2.76 0 5-2.24 5-5 0-4.42-4.03-8-9-8zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9 8 9.67 8 10.5 7.33 12 6.5 12zm3-4C8.67 8 8 7.33 8 6.5S8.67 5 9.5 5s1.5.67 1.5 1.5S10.33 8 9.5 8zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 5 14.5 5s1.5.67 1.5 1.5S15.33 8 14.5 8zm3 4c-.83 0-1.5-.67-1.5-1.5S16.67 9 17.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg> Appearance</a>
                        <button class="yt-dropdown-item" data-theme-toggle>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M9 2c-1.05 0-2.05.16-3 .46 4.06 1.27 7 5.06 7 9.54 0 4.48-2.94 8.27-7 9.54.95.3 1.95.46 3 .46 5.52 0 10-4.48 10-10S14.52 2 9 2z"/></svg> Dark theme
                            <div class="theme-toggle-track"></div>
                        </button>
                        <div class="yt-dropdown-divider"></div>
                        <form method="POST" action="<?= url('/logout') ?>" style="margin:0">
                            <?= csrf_field() ?>
                            <button class="yt-dropdown-item" type="submit"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg> Sign out</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= url('/login') ?>" class="yt-nav-icon" title="Sign In"><svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg></a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="yt-sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="yt-sidebar collapsed" id="sidebar">
    <?php if (($studioMode ?? false)): ?>
        <div class="yt-sidebar-section">
            <a href="<?= url('/creator') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg><span class="sidebar-label">Dashboard</span>
            </a>
            <a href="<?= url('/creator/channel') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'channel' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg><span class="sidebar-label">Channel</span>
            </a>
        </div>

        <div class="yt-sidebar-section">
            <div class="sidebar-section-title">Content</div>
            <a href="<?= url('/creator/videos') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'videos' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg><span class="sidebar-label sidebar-sub-text">Videos</span>
            </a>
            <a href="<?= url('/creator/shorts') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'shorts' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M10 14.65v-5.3L15 12l-5 2.65zm7.77-4.33-1.2-.5L18 9.06c1.84-.96 2.53-3.23 1.56-5.06s-3.24-2.53-5.07-1.56L6 6.94c-1.29.68-2.07 2.04-2 3.49.07 1.42.93 2.67 2.22 3.25.03.01 1.2.5 1.2.5L6 14.93c-1.83.97-2.53 3.24-1.56 5.07.97 1.83 3.24 2.53 5.07 1.56l8.5-4.5c1.29-.68 2.06-2.04 1.99-3.49-.07-1.42-.94-2.68-2.23-3.25z"/></svg><span class="sidebar-label sidebar-sub-text">Shorts</span>
            </a>
            <a href="<?= url('/creator/live') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'live' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg><span class="sidebar-label sidebar-sub-text">Live</span>
            </a>
            <a href="<?= url('/creator/playlists') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'playlists' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M15 6H3v2h12V6zm0 4H3v2h12v-2zM3 16h8v-2H3v2zM17 6v8.18c-.31-.11-.65-.18-1-.18-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3V8h3V6h-5z"/></svg><span class="sidebar-label sidebar-sub-text">Playlists</span>
            </a>
        </div>

        <div class="yt-sidebar-section">
            <div class="sidebar-section-title">Engagement</div>
            <a href="<?= url('/creator/comments') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'comments' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M21.99 4c0-1.1-.89-2-1.99-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4-.01-18zM18 14H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/></svg><span class="sidebar-label sidebar-sub-text">Comments</span>
            </a>
            <a href="<?= url('/creator/community') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'community' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg><span class="sidebar-label sidebar-sub-text">Community</span>
            </a>
        </div>

        <div class="yt-sidebar-section">
            <div class="sidebar-section-title">Analytics</div>
            <a href="<?= url('/creator/analytics') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'analytics' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99z"/></svg><span class="sidebar-label sidebar-sub-text">Analytics</span>
            </a>
            <a href="<?= url('/creator/monetization') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'monetization' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg><span class="sidebar-label sidebar-sub-text">Monetization</span>
            </a>
            <a href="<?= url('/creator/copyright') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'copyright' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg><span class="sidebar-label sidebar-sub-text">Copyright</span>
            </a>
        </div>

        <div class="yt-sidebar-section">
            <a href="<?= url('/') ?>" class="sidebar-nav-item">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg><span class="sidebar-label">Back to YouTube</span>
            </a>
        </div>
    <?php else: ?>
        <div class="yt-sidebar-section">
            <a href="<?= url('/') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'home' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg><span class="sidebar-label">Home</span>
            </a>
            <a href="<?= url('/shorts') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'shorts' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M10 14.65v-5.3L15 12l-5 2.65zm7.77-4.33-1.2-.5L18 9.06c1.84-.96 2.53-3.23 1.56-5.06s-3.24-2.53-5.07-1.56L6 6.94c-1.29.68-2.07 2.04-2 3.49.07 1.42.93 2.67 2.22 3.25.03.01 1.2.5 1.2.5L6 14.93c-1.83.97-2.53 3.24-1.56 5.07.97 1.83 3.24 2.53 5.07 1.56l8.5-4.5c1.29-.68 2.06-2.04 1.99-3.49-.07-1.42-.94-2.68-2.23-3.25z"/></svg><span class="sidebar-label">Shorts</span>
            </a>
            <a href="<?= url('/subscriptions-preview') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'subscriptions' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6h16v2H4zm2-4h12v2H6zm14 8H4c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2v-8c0-1.1-.9-2-2-2zm0 10H4v-8h16v8zm-6-2.5c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5.67 1.5 1.5 1.5 1.5-.67 1.5-1.5zm3-3c0-.83-.67-1.5-1.5-1.5S15 11.67 15 12.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5z"/></svg><span class="sidebar-label">Subscriptions</span>
            </a>
        </div>

        <?php if (($currentUser ?? null) !== null): ?>
        <div class="yt-sidebar-section yt-sidebar-you">
            <a href="<?= url('/viewer/history') ?>" class="sidebar-section-title sidebar-you-link">You</a>
            <a href="<?= url('/viewer/history') ?>" class="sidebar-nav-item">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg><span class="sidebar-label sidebar-sub-text">History</span>
            </a>
            <a href="<?= url('/viewer/playlists') ?>" class="sidebar-nav-item">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M15 6H3v2h12V6zm0 4H3v2h12v-2zM3 16h8v-2H3v2zM17 6v8.18c-.31-.11-.65-.18-1-.18-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3V8h3V6h-5z"/></svg><span class="sidebar-label sidebar-sub-text">Playlists</span>
            </a>
            <a href="<?= url('/viewer/watch-later') ?>" class="sidebar-nav-item">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg><span class="sidebar-label sidebar-sub-text">Watch later</span>
            </a>
            <a href="<?= url('/viewer/liked-videos') ?>" class="sidebar-nav-item">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M1 21h4V9H1v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L14.17 1 7.59 7.59C7.22 7.95 7 8.45 7 9v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2z"/></svg><span class="sidebar-label sidebar-sub-text">Liked videos</span>
            </a>
        </div>
        <?php endif; ?>

        <div class="yt-sidebar-section">
            <div class="sidebar-section-title">Explore</div>
            <a href="<?= url('/trending') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'trending' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M17.53 11.2c-.23-.3-.51-.56-.77-.82-.67-.6-1.42-1.03-2.03-1.66C13.3 7.26 13 5.65 13.71 4c-2.62.94-4.64 3.11-5.35 5.87-.55 2.15.33 4.47 1.92 6.05.34.33.7.63 1.07.91-.26.17-.53.32-.8.45l-.17.08zM16.27 13.7c.17-.12.33-.25.47-.39.88-.84 1.49-1.93 1.73-3.17.09-.46.12-.94.02-1.41-.16-.8-.76-1.39-1.55-1.39-.45 0-.86.22-1.11.58l-.18.26-.23-.08c-.56-.23-1.18-.26-1.77-.07-.59.19-1.07.59-1.32 1.12l-.11.23-.24-.01c-.47-.05-.95.08-1.36.36-.72.49-1.06 1.39-.88 2.27l.07.28.17.14c.45.38.73.92.8 1.51l.02.21c0 .09-.01.18-.01.27 0 .18.03.36.08.54.05.18.13.35.23.51.4.63 1.09 1.03 1.83 1.13.74.1 1.48-.1 2.05-.57.46-.37.77-.89.88-1.47l.04-.19.12-.01z"/></svg><span class="sidebar-label sidebar-sub-text">Trending</span>
            </a>
            <a href="<?= url('/music') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'music' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg><span class="sidebar-label sidebar-sub-text">Music</span>
            </a>
            <a href="<?= url('/gaming') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'gaming' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M21.58 16.09l-1.09-7.66C20.21 6.46 18.52 5 16.53 5H7.47C5.48 5 3.79 6.46 3.51 8.43l-1.09 7.66C2.2 17.63 3.39 19 4.94 19c.68 0 1.32-.27 1.8-.75L9 16h6l2.25 2.25c.48.48 1.13.75 1.8.75 1.56 0 2.75-1.37 2.53-2.91zM11 11H9v2H8v-2H6v-1h2V8h1v2h2v1zm4 2c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm2-3c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z"/></svg><span class="sidebar-label sidebar-sub-text">Gaming</span>
            </a>
            <a href="<?= url('/news') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'news' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M22 3H2C.9 3 0 3.9 0 5v14c0 1.1.9 2 2 2h20c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H2V5h20v14zM4 7h16v2H4V7zm0 4h16v2H4v-2zm0 4h10v2H4v-2z"/></svg><span class="sidebar-label sidebar-sub-text">News</span>
            </a>
            <a href="<?= url('/sports') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'sports' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94.63 1.5 1.98 2.63 3.61 2.96V19H7v2h10v-2h-4v-3.1c1.63-.33 2.98-1.46 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/></svg><span class="sidebar-label sidebar-sub-text">Sports</span>
            </a>
            <a href="<?= url('/learning') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'learning' ? 'active' : '' ?>">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/></svg><span class="sidebar-label sidebar-sub-text">Learning</span>
            </a>
        </div>

        <div class="yt-sidebar-section">
            <a href="<?= url('/categories') ?>" class="sidebar-nav-item">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h8v8H3V3zm0 10h8v8H3v-8zM13 3h8v8h-8V3zm0 10h8v8h-8v-8z"/></svg><span class="sidebar-label sidebar-sub-text">Categories</span>
            </a>
        </div>

        <div class="yt-sidebar-section sidebar-sub-list">
            <div class="sidebar-section-title">More from YouTube</div>
            <a href="#" class="sidebar-nav-item">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="#FF0000"><path d="M10 9.35 15 12l-5 2.65ZM12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20m0 19a9 9 0 1 1 0-18 9 9 0 0 1 0 18"/></svg><span class="sidebar-label sidebar-sub-text">YouTube Premium</span>
            </a>
            <a href="#" class="sidebar-nav-item">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="#FF0000"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20m0 19a9 9 0 1 1 0-18 9 9 0 0 1 0 18"/><path d="M10 14.65v-5.3L15 12l-5 2.65" fill="#fff"/></svg><span class="sidebar-label sidebar-sub-text">YouTube Music</span>
            </a>
            <a href="#" class="sidebar-nav-item">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="#FF0000"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20m0 19a9 9 0 1 1 0-18 9 9 0 0 1 0 18"/><path d="M9.5 8.5c0-.28.22-.5.5-.5h4c.28 0 .5.22.5.5v7c0 .28-.22.5-.5.5h-4c-.28 0-.5-.22-.5-.5z" fill="#fff"/></svg><span class="sidebar-label sidebar-sub-text">YouTube Kids</span>
            </a>
        </div>

        <div class="yt-sidebar-section">
            <div style="padding:12px 24px;font-size:11px;color:var(--yt-text-muted);line-height:1.8">
                <span class="sidebar-label sidebar-sub-text">
                    <a href="<?= url('/about') ?>" style="color:var(--yt-text-muted)">About</a> &middot;
                    <a href="<?= url('/terms') ?>" style="color:var(--yt-text-muted)">Terms</a> &middot;
                    <a href="<?= url('/privacy') ?>" style="color:var(--yt-text-muted)">Privacy</a> &middot;
                    <a href="<?= url('/contact') ?>" style="color:var(--yt-text-muted)">Contact</a>
                </span>
            </div>
            <div style="padding:0 24px 16px;font-size:11px;color:var(--yt-text-muted)" class="sidebar-label sidebar-sub-text">
                &copy; <?= date('Y') ?> YouTube Clone
            </div>
        </div>
    <?php endif; ?>
    </aside>

    <main class="yt-main">
        <?php if (($success ?? null) !== null): ?>
            <div class="yt-alert yt-alert-success"><span class="alert-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></span><span class="alert-text"><?= e($success) ?></span><span class="alert-close"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></span></div>
        <?php endif; ?>
        <?php if (($error ?? null) !== null): ?>
            <div class="yt-alert yt-alert-error"><span class="alert-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg></span><span class="alert-text"><?= e($error) ?></span><span class="alert-close"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></span></div>
        <?php endif; ?>

        <?= $__content ?>
    </main>

    <button class="yt-scroll-top" title="Scroll to top"><svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6z"/></svg></button>

    <div id="reportModal" class="yt-modal-backdrop">
        <div class="yt-modal">
            <div class="yt-modal-header">
                <h5>Report</h5>
                <button class="yt-modal-close" onclick="Report.close()"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></button>
            </div>
            <form onsubmit="event.preventDefault(); Report.submit(this)">
                <input type="hidden" name="reportable_type" value="">
                <input type="hidden" name="reportable_id" value="">
                <div class="yt-modal-body">
                    <div class="yt-form-group">
                        <label>Reason</label>
                        <select name="reason" class="yt-form-select" required>
                            <option value="">Select a reason</option>
                            <option value="spam">Spam or misleading</option>
                            <option value="abusive">Abusive content</option>
                            <option value="violence">Violent or graphic content</option>
                            <option value="harassment">Harassment or bullying</option>
                            <option value="copyright">Copyright infringement</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="yt-form-group">
                        <label>Additional details</label>
                        <textarea name="details" class="yt-form-textarea" rows="4" placeholder="Provide additional details..."></textarea>
                    </div>
                </div>
                <div class="yt-modal-footer">
                    <button type="button" class="yt-btn yt-btn-ghost" onclick="Report.close()">Cancel</button>
                    <button type="submit" class="yt-btn yt-btn-danger">Submit Report</button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= asset('js/app.js') ?>"></script>
    <script src="<?= asset('js/charts.js') ?>"></script>
</body>
</html>
