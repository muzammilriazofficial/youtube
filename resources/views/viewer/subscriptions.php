<?php $__layout = 'layouts.dashboard'; ?>

<h4 class="mb-4">Subscriptions <small class="text-muted">(<?= $subscriptionCount ?? 0 ?>)</small></h4>

<?php if (!empty($subscriptions ?? [])): ?>
<div class="mb-4">
    <div class="d-flex flex-wrap gap-3 mb-4">
        <?php foreach ($subscriptions as $sub): ?>
            <a href="<?= url('/channel/' . e($sub['custom_url'] ?? $sub['slug'])) ?>" class="text-decoration-none">
                <div class="text-center" style="width: 80px;">
                    <?php if (!empty($sub['avatar'])): ?>
                        <img src="<?= e($sub['avatar']) ?>" class="rounded-circle mb-1" width="56" height="56" alt="">
                    <?php else: ?>
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1" style="width:56px;height:56px"><i class="bi bi-person"></i></div>
                    <?php endif; ?>
                    <small class="text-truncate d-block" style="max-width: 80px; color: var(--bs-body-color)"><?= e($sub['name']) ?></small>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($latestVideos['data'] ?? [])): ?>
<h5>Latest from Subscriptions</h5>
<div class="row g-3">
    <?php foreach ($latestVideos['data'] as $video): ?>
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
<?php else: ?>
    <div class="text-center py-5 text-muted"><i class="bi bi-collection-play fs-1 d-block mb-2"></i>No videos from your subscriptions.</div>
<?php endif; ?>
