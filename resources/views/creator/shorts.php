<?php $__layout = 'layouts.app'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Shorts</h4>
    <a href="<?= url('/creator/shorts/upload') ?>" class="btn btn-primary btn-sm"><i class="bi bi-lightning me-1"></i>Upload Short</a>
</div>

<?php if (!empty($shorts ?? [])): ?>
<div class="row g-3">
    <?php foreach ($shorts as $short): ?>
    <div class="col-6 col-md-4 col-lg-3">
        <div class="card h-100">
            <div class="position-relative" style="aspect-ratio:9/16;background:var(--bs-secondary);overflow:hidden;border-radius:0.375rem 0.375rem 0 0;">
                <?php if (!empty($short['thumbnail'])): ?>
                    <img src="<?= e($short['thumbnail']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center h-100"><i class="bi bi-lightning fs-1 text-muted"></i></div>
                <?php endif; ?>
                <div class="position-absolute bottom-0 start-0 end-0 bg-gradient-to-top p-2" style="background:linear-gradient(transparent,rgba(0,0,0,0.8));">
                    <small class="text-white"><i class="bi bi-eye me-1"></i><?= format_number((int) $short['view_count']) ?></small>
                </div>
            </div>
            <div class="card-body p-2">
                <h6 class="card-title mb-1 small text-truncate"><?= e($short['title']) ?></h6>
                <div class="d-flex justify-content-between">
                    <small class="text-muted"><?= ucfirst($short['visibility']) ?></small>
                    <small class="text-muted"><?= date('M d', strtotime($short['created_at'])) ?></small>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (($pagination['last_page'] ?? 1) > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= !($pagination['has_prev_page'] ?? false) ? 'disabled' : '' ?>"><a class="page-link" href="<?= url('/creator/shorts', ['page' => ($pagination['current_page'] ?? 1) - 1]) ?>">Prev</a></li>
        <li class="page-item <?= !($pagination['has_more_pages'] ?? false) ? 'disabled' : '' ?>"><a class="page-link" href="<?= url('/creator/shorts', ['page' => ($pagination['current_page'] ?? 1) + 1]) ?>">Next</a></li>
    </ul>
</nav>
<?php endif; ?>

<?php else: ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-lightning display-4 text-muted mb-3"></i>
        <h5>No shorts yet</h5>
        <p class="text-muted">Upload your first short to get started.</p>
        <a href="<?= url('/creator/shorts/upload') ?>" class="btn btn-primary"><i class="bi bi-lightning me-1"></i>Upload Short</a>
    </div>
</div>
<?php endif; ?>
