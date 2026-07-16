<?php $__layout = 'layouts.app'; ?>

<h4 class="mb-4"><i class="bi bi-lightning-fill text-warning"></i> Shorts</h4>
<div class="row g-3">
    <?php foreach (($shorts['data'] ?? []) as $short): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="video-card">
                <a href="<?= url('/video/' . e($short['slug'])) ?>" class="text-decoration-none">
                    <div class="thumbnail" style="aspect-ratio: 9/16; max-height: 400px;">
                        <?php if (!empty($short['thumbnail'])): ?>
                            <img src="<?= e($short['thumbnail']) ?>" alt="<?= e($short['title']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center h-100 bg-secondary"><i class="bi bi-play-circle fs-1"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="mt-2">
                        <h6 class="mb-1 text-truncate" style="color: var(--bs-body-color)"><?= e($short['title']) ?></h6>
                        <small class="text-muted"><?= format_number((int) ($short['view_count'] ?? 0)) ?> views</small>
                    </div>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($shorts['data'] ?? [])): ?>
        <div class="col-12 text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No shorts available.</div>
    <?php endif; ?>
</div>
