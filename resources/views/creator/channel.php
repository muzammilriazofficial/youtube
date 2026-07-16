<?php $__layout = 'layouts.app'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Channel Overview</h4>
    <div>
        <a href="<?= url('/creator/channel/customize') ?>" class="btn btn-outline-primary btn-sm me-2"><i class="bi bi-pencil me-1"></i>Customize</a>
        <a href="<?= url('/creator/channel/branding') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-palette me-1"></i>Branding</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto">
                <?php if (!empty($channel['banner'])): ?>
                    <img src="<?= url(e($channel['banner'])) ?>" alt="Banner" class="rounded" style="width:200px;height:80px;object-fit:cover;">
                <?php else: ?>
                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width:200px;height:80px;"><i class="bi bi-image text-muted"></i></div>
                <?php endif; ?>
            </div>
            <div class="col-auto">
                <?php if (!empty($channel['avatar'])): ?>
                    <img src="<?= url(e($channel['avatar'])) ?>" alt="Avatar" class="rounded-circle" width="80" height="80" style="object-fit:cover;">
                <?php else: ?>
                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:80px;height:80px;"><i class="bi bi-person text-muted fs-2"></i></div>
                <?php endif; ?>
            </div>
            <div class="col">
                <h3 class="mb-1"><?= e($channel['name']) ?> <?php if (!empty($channel['is_verified'])): ?><i class="bi bi-patch-check-fill text-primary"></i><?php endif; ?></h3>
                <p class="text-muted mb-0"><?= e($channel['description'] ?? 'No description yet.') ?></p>
                <?php if (!empty($channel['custom_url'])): ?>
                    <small class="text-muted">Custom URL: <?= e($channel['custom_url']) ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="mb-0"><?= format_number((int) $channel['subscriber_count']) ?></h3>
                <small class="text-muted">Subscribers</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="mb-0"><?= format_number($videoCount ?? 0) ?></h3>
                <small class="text-muted">Videos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="mb-0"><?= format_number((int) ($totalViews ?? 0)) ?></h3>
                <small class="text-muted">Total Views</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="mb-0"><?= format_number($playlistCount ?? 0) ?></h3>
                <small class="text-muted">Playlists</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h6 class="mb-0">Channel Details</h6></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr><td class="text-muted" style="width:180px;">Channel Name</td><td><?= e($channel['name']) ?></td></tr>
                    <tr><td class="text-muted">Custom URL</td><td><?= e($channel['custom_url'] ?? 'Not set') ?></td></tr>
                    <tr><td class="text-muted">Country</td><td><?= e($channel['country'] ?? 'Not specified') ?></td></tr>
                    <tr><td class="text-muted">Website</td><td><?= !empty($channel['website']) ? '<a href="' . e($channel['website']) . '" target="_blank">' . e($channel['website']) . '</a>' : 'Not set' ?></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr><td class="text-muted" style="width:180px;">Created</td><td><?= date('F d, Y', strtotime($channel['created_at'])) ?></td></tr>
                    <tr><td class="text-muted">Partner</td><td><?= !empty($channel['is_partner']) ? '<span class="text-success">Yes</span>' : '<span class="text-muted">No</span>' ?></td></tr>
                    <tr><td class="text-muted">Verified</td><td><?= !empty($channel['is_verified']) ? '<span class="text-success">Yes</span>' : '<span class="text-muted">No</span>' ?></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
