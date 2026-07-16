<?php $__layout = 'layouts.reviewer'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Reviewer Dashboard</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 bg-warning bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Pending Uploads</h6>
                        <h3 class="mb-0"><?= format_number($pendingUploads ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-hourglass-split fs-2 text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-info bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Copyright Claims</h6>
                        <h3 class="mb-0"><?= format_number($pendingCopyright ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-shield-lock fs-2 text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-success bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Monetization Apps</h6>
                        <h3 class="mb-0"><?= format_number($monetizationApps ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-cash-coin fs-2 text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-danger bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Open Appeals</h6>
                        <h3 class="mb-0"><?= format_number($totalAppeals ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-arrow-return-left fs-2 text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Quick Actions</h6></div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= url('/reviewer/uploads') ?>" class="btn btn-warning btn-sm"><i class="bi bi-upload me-1"></i>Review Pending Uploads</a>
                    <a href="<?= url('/reviewer/copyright') ?>" class="btn btn-info btn-sm"><i class="bi bi-shield-lock me-1"></i>Copyright Claims</a>
                    <a href="<?= url('/reviewer/monetization') ?>" class="btn btn-success btn-sm"><i class="bi bi-cash-coin me-1"></i>Monetization Reviews</a>
                    <a href="<?= url('/reviewer/appeals') ?>" class="btn btn-danger btn-sm"><i class="bi bi-arrow-return-left me-1"></i>Appeals</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Pending Uploads</h6>
                <a href="<?= url('/reviewer/uploads') ?>" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($recentUploads ?? [])): ?>
                    <?php foreach ($recentUploads as $upload): ?>
                    <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <a href="<?= url('/reviewer/uploads/' . $upload['id']) ?>" class="text-decoration-none fw-medium"><?= e(mb_substr($upload['title'], 0, 40)) ?></a>
                            <br><small class="text-muted"><?= e($upload['username'] ?? '') ?> &middot; <?= time_ago($upload['created_at']) ?></small>
                        </div>
                        <div>
                            <a href="<?= url('/reviewer/uploads/' . $upload['id']) ?>" class="btn btn-sm btn-outline-primary">Review</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-4">No pending uploads.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Copyright Claims</h6>
                <a href="<?= url('/reviewer/copyright') ?>" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($recentCopyright ?? [])): ?>
                    <?php foreach ($recentCopyright as $claim): ?>
                    <div class="px-3 py-2 border-bottom">
                        <div class="fw-medium small"><?= e($claim['original_work_title'] ?? '') ?></div>
                        <small class="text-muted">Claimed on: <?= e(mb_substr($claim['title'] ?? '', 0, 30)) ?> &middot; <?= time_ago($claim['created_at']) ?></small>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-4">No pending claims.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
