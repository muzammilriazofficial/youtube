<?php $__layout = 'layouts.dashboard'; ?>

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <?php if (!empty($user['avatar'])): ?>
                <img src="<?= e($user['avatar']) ?>" class="rounded-circle me-4" width="80" height="80" alt="">
            <?php else: ?>
                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-4" style="width:80px;height:80px"><i class="bi bi-person fs-2"></i></div>
            <?php endif; ?>
            <div>
                <h4 class="mb-0"><?= e($user['display_name'] ?? $user['username']) ?></h4>
                <p class="text-muted mb-1">@<?= e($user['username']) ?> <?php if (!empty($user['is_verified'])): ?><i class="bi bi-patch-check-fill text-primary"></i><?php endif; ?></p>
                <small class="text-muted"><?= e($user['email']) ?></small>
            </div>
            <a href="<?= url('/viewer/profile/edit') ?>" class="btn btn-outline-primary btn-sm ms-auto">Edit Profile</a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card bg-secondary bg-opacity-25 border-0"><div class="card-body text-center"><h3 class="mb-0"><?= $videoCount ?? 0 ?></h3><small class="text-muted">Videos</small></div></div></div>
    <div class="col-md-3"><div class="card bg-secondary bg-opacity-25 border-0"><div class="card-body text-center"><h3 class="mb-0"><?= format_number($totalViews ?? 0) ?></h3><small class="text-muted">Total Views</small></div></div></div>
    <div class="col-md-3"><div class="card bg-secondary bg-opacity-25 border-0"><div class="card-body text-center"><h3 class="mb-0"><?= $subscriptionCount ?? 0 ?></h3><small class="text-muted">Subscriptions</small></div></div></div>
    <div class="col-md-3"><div class="card bg-secondary bg-opacity-25 border-0"><div class="card-body text-center"><h3 class="mb-0"><?= $channel['subscriber_count'] ?? 0 ?></h3><small class="text-muted">Subscribers</small></div></div></div>
</div>

<div class="row g-3">
    <div class="col-md-6"><a href="<?= url('/viewer/playlists') ?>" class="text-decoration-none"><div class="card bg-secondary bg-opacity-25 border-0"><div class="card-body"><i class="bi bi-collection-play fs-4 me-2"></i>My Playlists</div></div></a></div>
    <div class="col-md-6"><a href="<?= url('/viewer/history') ?>" class="text-decoration-none"><div class="card bg-secondary bg-opacity-25 border-0"><div class="card-body"><i class="bi bi-clock-history fs-4 me-2"></i>Watch History</div></div></a></div>
    <div class="col-md-6"><a href="<?= url('/viewer/watch-later') ?>" class="text-decoration-none"><div class="card bg-secondary bg-opacity-25 border-0"><div class="card-body"><i class="bi bi-clock fs-4 me-2"></i>Watch Later</div></div></a></div>
    <div class="col-md-6"><a href="<?= url('/viewer/liked-videos') ?>" class="text-decoration-none"><div class="card bg-secondary bg-opacity-25 border-0"><div class="card-body"><i class="bi bi-hand-thumbs-up fs-4 me-2"></i>Liked Videos</div></div></a></div>
</div>
