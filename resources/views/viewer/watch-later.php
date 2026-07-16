<?php $__layout = 'layouts.dashboard'; ?>

<h4 class="mb-4">Watch Later</h4>
<div class="row g-3">
    <?php foreach (($watchLater['data'] ?? []) as $item): ?>
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
                        <?php if (!empty($item['video']['duration'])): ?>
                            <span class="duration"><?= gmdate('i:s', (int) $item['video']['duration']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-2"><h6 class="mb-1 text-truncate" style="color: var(--bs-body-color)"><?= e($item['video']['title']) ?></h6><small class="text-muted"><?= format_number((int) ($item['video']['view_count'] ?? 0)) ?> views</small></div>
                </a>
                <form method="POST" action="<?= url('/viewer/watch-later/remove') ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="video_id" value="<?= $item['video_id'] ?>">
                    <button type="submit" class="btn btn-link btn-sm text-muted p-0"><i class="bi bi-x-lg"></i> Remove</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if (empty($watchLater['data'] ?? [])): ?>
        <div class="col-12 text-center py-5 text-muted"><i class="bi bi-clock fs-1 d-block mb-2"></i>Watch Later list is empty.</div>
    <?php endif; ?>
</div>
