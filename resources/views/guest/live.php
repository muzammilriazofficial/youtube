<?php $__layout = 'layouts.app'; ?>

<h4 class="mb-4"><i class="bi bi-broadcast text-danger"></i> Live Streams</h4>
<div class="row g-3">
    <?php foreach (($liveStreams['data'] ?? []) as $stream): ?>
        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            <div class="video-card">
                <a href="<?= url('/video/' . e($stream['slug'])) ?>" class="text-decoration-none">
                    <div class="thumbnail">
                        <?php if (!empty($stream['thumbnail'])): ?>
                            <img src="<?= e($stream['thumbnail']) ?>" alt="<?= e($stream['title']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center h-100 bg-secondary"><i class="bi bi-play-circle fs-1"></i></div>
                        <?php endif; ?>
                        <span class="badge bg-danger position-absolute top-0 start-0 m-2">LIVE</span>
                    </div>
                    <div class="mt-2">
                        <h6 class="mb-1 text-truncate" style="color: var(--bs-body-color)"><?= e($stream['title']) ?></h6>
                        <small class="text-muted"><?= format_number((int) ($stream['view_count'] ?? 0)) ?> watching</small>
                    </div>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($liveStreams['data'] ?? [])): ?>
        <div class="col-12 text-center py-5 text-muted"><i class="bi bi-broadcast fs-1 d-block mb-2"></i>No live streams right now.</div>
    <?php endif; ?>
</div>
