<?php $__layout = 'layouts.dashboard'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Watch History</h4>
    <form method="POST" action="<?= url('/viewer/history/clear') ?>" onsubmit="return confirm('Clear all watch history?')">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-danger btn-sm">Clear All</button>
    </form>
</div>

<div class="row g-3">
    <?php foreach (($history['data'] ?? []) as $item): ?>
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
                        <div class="position-absolute bottom-0 start-0 w-100" style="height:3px;background:#333">
                            <div style="height:100%;width:<?= (int) ($item['progress'] ?? 0) ?>%;background:var(--bs-danger)"></div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <h6 class="mb-1 text-truncate" style="color: var(--bs-body-color)"><?= e($item['video']['title']) ?></h6>
                        <small class="text-muted">Watched <?= time_ago($item['last_watched_at'] ?? $item['created_at'] ?? '') ?></small>
                    </div>
                </a>
                <form method="POST" action="<?= url('/viewer/history/remove') ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="video_id" value="<?= $item['video_id'] ?>">
                    <button type="submit" class="btn btn-link btn-sm text-muted p-0"><i class="bi bi-x-lg"></i> Remove</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if (empty($history['data'] ?? [])): ?>
        <div class="col-12 text-center py-5 text-muted"><i class="bi bi-clock-history fs-1 d-block mb-2"></i>No watch history.</div>
    <?php endif; ?>
</div>

<?php if (($history['last_page'] ?? 1) > 1): ?>
<nav class="mt-4"><ul class="pagination justify-content-center">
    <?php for ($p = 1; $p <= ($history['last_page'] ?? 1); $p++): ?>
        <li class="page-item <?= $p == ($history['current_page'] ?? 1) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a></li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
