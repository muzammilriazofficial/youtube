<?php
$currentUserLocal = $currentUser ?? null;
?>
<nav class="yt-navbar">
    <div class="nav-left">
        <button class="yt-nav-icon" data-sidebar-toggle title="Menu"><i class="bi bi-list"></i></button>
        <a href="<?= url('/') ?>" class="yt-logo">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="#FF0000"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0C.488 3.45.029 5.804 0 12c.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0C23.512 20.55 23.971 18.196 24 12c-.029-6.185-.484-8.549-4.385-8.816zM9 16V8l8 3.993L9 16z"/></svg>
        </a>
    </div>

    <div class="nav-center">
        <div class="yt-search-wrapper yt-search">
            <form class="yt-search-form" action="<?= url('/search') ?>" method="GET">
                <input type="search" class="yt-search-input" name="q" placeholder="Search" value="<?= e($searchQuery ?? '') ?>" autocomplete="off">
                <button class="yt-search-btn" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <div class="yt-search-autocomplete"></div>
        </div>
    </div>

    <div class="nav-right">
        <button class="yt-nav-icon yt-search-mobile-btn" data-search-expand title="Search"><i class="bi bi-search"></i></button>

        <?php if ($currentUserLocal !== null): ?>
            <a href="<?= url('/creator/videos/create') ?>" class="yt-create-btn yt-nav-icon" title="Create"><i class="bi bi-plus-circle"></i></a>

            <div class="yt-notif-wrapper">
                <button class="yt-nav-icon" data-dropdown-toggle="notifDropdown" data-notif-poll title="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="yt-notif-count" style="display:none">0</span>
                </button>
                <div class="yt-notif-dropdown" id="notifDropdown">
                    <div class="yt-notif-header"><h6>Notifications</h6></div>
                    <div class="yt-notif-list">
                        <div class="text-center py-4" style="color:var(--yt-text-muted)"><small>No notifications yet</small></div>
                    </div>
                </div>
            </div>

            <div class="yt-avatar-wrapper">
                <button class="yt-avatar-btn" data-dropdown-toggle="userDropdown">
                    <?php if (!empty($currentUserLocal['avatar'])): ?>
                        <img src="<?= url(e($currentUserLocal['avatar'])) ?>" alt="">
                    <?php else: ?>
                        <?= strtoupper(substr($currentUserLocal['username'] ?? 'U', 0, 1)) ?>
                    <?php endif; ?>
                </button>
                <div class="yt-user-dropdown" id="userDropdown">
                    <a href="<?= url('/') ?>" class="yt-dropdown-item"><i class="bi bi-house"></i> Home</a>
                    <a href="<?= url('/viewer/dashboard') ?>" class="yt-dropdown-item"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    <a href="<?= url('/viewer/profile') ?>" class="yt-dropdown-item"><i class="bi bi-person"></i> Profile</a>
                    <a href="<?= url('/viewer/notifications') ?>" class="yt-dropdown-item"><i class="bi bi-bell"></i> Notifications</a>
                    <div class="yt-dropdown-divider"></div>
                    <button class="yt-dropdown-item" data-theme-toggle><i class="bi bi-moon-stars"></i> Dark theme <div class="theme-toggle-track"></div></button>
                    <div class="yt-dropdown-divider"></div>
                    <form method="POST" action="<?= url('/logout') ?>" style="margin:0">
                        <?= csrf_field() ?>
                        <button class="yt-dropdown-item" type="submit"><i class="bi bi-box-arrow-right"></i> Sign out</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <a href="<?= url('/login') ?>" class="yt-nav-icon" title="Sign In"><i class="bi bi-person-circle"></i></a>
        <?php endif; ?>
    </div>
</nav>
