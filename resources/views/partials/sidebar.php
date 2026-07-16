<?php $currentUserLocal = $currentUser ?? null; ?>
<aside class="yt-sidebar" id="sidebar">
    <div class="yt-sidebar-section">
        <a href="<?= url('/') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'home' ? 'active' : '' ?>">
            <i class="bi bi-house-fill"></i><span class="sidebar-label">Home</span>
        </a>
        <a href="<?= url('/shorts') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'shorts' ? 'active' : '' ?>">
            <i class="bi bi-lightning-charge"></i><span class="sidebar-label">Shorts</span>
        </a>
        <a href="<?= url('/subscriptions-preview') ?>" class="sidebar-nav-item <?= ($activeMenu ?? '') === 'subscriptions' ? 'active' : '' ?>">
            <i class="bi bi-collection-play"></i><span class="sidebar-label">Subscriptions</span>
        </a>
    </div>

    <?php if ($currentUserLocal !== null): ?>
    <div class="yt-sidebar-section">
        <div class="sidebar-section-title">You</div>
        <a href="<?= url('/viewer/history') ?>" class="sidebar-nav-item"><i class="bi bi-clock-history"></i><span class="sidebar-label sidebar-sub-text">History</span></a>
        <a href="<?= url('/viewer/playlists') ?>" class="sidebar-nav-item"><i class="bi bi-collection-play"></i><span class="sidebar-label sidebar-sub-text">Playlists</span></a>
        <a href="<?= url('/viewer/watch-later') ?>" class="sidebar-nav-item"><i class="bi bi-clock"></i><span class="sidebar-label sidebar-sub-text">Watch later</span></a>
        <a href="<?= url('/viewer/liked-videos') ?>" class="sidebar-nav-item"><i class="bi bi-hand-thumbs-up"></i><span class="sidebar-label sidebar-sub-text">Liked videos</span></a>
    </div>
    <?php endif; ?>

    <div class="yt-sidebar-section">
        <div class="sidebar-section-title">Explore</div>
        <a href="<?= url('/trending') ?>" class="sidebar-nav-item"><i class="bi bi-fire"></i><span class="sidebar-label sidebar-sub-text">Trending</span></a>
        <a href="<?= url('/music') ?>" class="sidebar-nav-item"><i class="bi bi-music-note-beamed"></i><span class="sidebar-label sidebar-sub-text">Music</span></a>
        <a href="<?= url('/gaming') ?>" class="sidebar-nav-item"><i class="bi bi-controller"></i><span class="sidebar-label sidebar-sub-text">Gaming</span></a>
        <a href="<?= url('/news') ?>" class="sidebar-nav-item"><i class="bi bi-newspaper"></i><span class="sidebar-label sidebar-sub-text">News</span></a>
        <a href="<?= url('/sports') ?>" class="sidebar-nav-item"><i class="bi bi-trophy"></i><span class="sidebar-label sidebar-sub-text">Sports</span></a>
        <a href="<?= url('/learning') ?>" class="sidebar-nav-item"><i class="bi bi-book"></i><span class="sidebar-label sidebar-sub-text">Learning</span></a>
    </div>

    <div class="yt-sidebar-section">
        <a href="<?= url('/categories') ?>" class="sidebar-nav-item"><i class="bi bi-grid"></i><span class="sidebar-label sidebar-sub-text">Categories</span></a>
        <a href="<?= url('/channels') ?>" class="sidebar-nav-item"><i class="bi bi-broadcast"></i><span class="sidebar-label sidebar-sub-text">Channels</span></a>
    </div>
</aside>
