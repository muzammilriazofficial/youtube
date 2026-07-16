<?php $__layout = 'layouts.dashboard'; ?>

<h4 class="mb-4">Dashboard</h4>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card bg-primary bg-opacity-10 border-0">
            <div class="card-body"><h6 class="text-muted mb-1">Subscriptions</h6><h3 class="mb-0"><?= $subscriptionCount ?? 0 ?></h3></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success bg-opacity-10 border-0">
            <div class="card-body"><h6 class="text-muted mb-1">Videos in Feed</h6><h3 class="mb-0"><?= count($subscriptionVideos ?? []) ?></h3></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning bg-opacity-10 border-0">
            <div class="card-body"><h6 class="text-muted mb-1">Continue Watching</h6><h3 class="mb-0"><?= count($continueWatching ?? []) ?></h3></div>
        </div>
    </div>
</div>

<?php if (!empty($continueWatching ?? [])): ?>
<div class="mb-4">
    <h5>Continue Watching</h5>
    <div class="row g-3">
        <?php foreach ($continueWatching as $item): ?>
            <?php if (!empty($item['video'])): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="video-card">
                    <a href="<?= url('/video/' . e($item['video']['slug'])) ?>" class="text-decoration-none">
                        <div class="thumbnail">
                            <?php if (!empty($item['video']['thumbnail'])): ?>
                                <img src="<?= e($item['video']['thumbnail']) ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center h-100 bg-secondary"><i class="bi bi-play-circle fs-1"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2"><h6 class="mb-0 text-truncate" style="color: var(--bs-body-color)"><?= e($item['video']['title']) ?></h6></div>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($subscriptionVideos ?? [])): ?>
<div class="mb-4">
    <h5>From Your Subscriptions</h5>
    <div class="row g-3">
        <?php foreach ($subscriptionVideos as $video): ?>
            <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                <div class="video-card">
                    <a href="<?= url('/video/' . e($video['slug'])) ?>" class="text-decoration-none">
                        <div class="thumbnail">
                            <?php if (!empty($video['thumbnail'])): ?>
                                <img src="<?= e($video['thumbnail']) ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center h-100 bg-secondary"><i class="bi bi-play-circle fs-1"></i></div>
                            <?php endif; ?>
                            <?php if (!empty($video['duration'])): ?>
                                <span class="duration"><?= gmdate('i:s', (int) $video['duration']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2"><h6 class="mb-1 text-truncate" style="color: var(--bs-body-color)"><?= e($video['title']) ?></h6><small class="text-muted"><?= format_number((int) ($video['view_count'] ?? 0)) ?> views</small></div>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="mb-4">
    <h5>Recommended For You</h5>
    <div class="row g-3">
        <?php foreach (($recommendations ?? []) as $video): ?>
            <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                <div class="video-card">
                    <a href="<?= url('/video/' . e($video['slug'])) ?>" class="text-decoration-none">
                        <div class="thumbnail">
                            <?php if (!empty($video['thumbnail'])): ?>
                                <img src="<?= e($video['thumbnail']) ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center h-100 bg-secondary"><i class="bi bi-play-circle fs-1"></i></div>
                            <?php endif; ?>
                            <?php if (!empty($video['duration'])): ?>
                                <span class="duration"><?= gmdate('i:s', (int) $video['duration']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2"><h6 class="mb-1 text-truncate" style="color: var(--bs-body-color)"><?= e($video['title']) ?></h6><small class="text-muted"><?= format_number((int) ($video['view_count'] ?? 0)) ?> views</small></div>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
