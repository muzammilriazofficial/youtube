<?php $__layout = 'layouts.dashboard'; ?>

<h4 class="mb-4">Downloads</h4>
<div class="row g-3">
    <?php foreach (($downloads['data'] ?? []) as $dl): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="video-card">
                <div class="thumbnail bg-secondary d-flex align-items-center justify-content-center">
                    <i class="bi bi-file-earmark-play fs-1"></i>
                </div>
                <div class="mt-2">
                    <h6 class="mb-0 text-truncate" style="color: var(--bs-body-color)"><?= e($dl['title'] ?? 'Video') ?></h6>
                    <small class="text-muted"><?= time_ago($dl['created_at'] ?? '') ?></small>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($downloads['data'] ?? [])): ?>
        <div class="col-12 text-center py-5 text-muted"><i class="bi bi-download fs-1 d-block mb-2"></i>No downloads.</div>
    <?php endif; ?>
</div>
