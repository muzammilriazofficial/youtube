<?php $__layout = 'layouts.moderator'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('/moderator/reports') ?>" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i>Back to Reports</a>
        <h4 class="mb-0 mt-1">Report #<?= e((string) $report['id']) ?></h4>
    </div>
    <span class="badge bg-<?= ['pending' => 'warning', 'resolved' => 'success', 'dismissed' => 'secondary'][$report['status']] ?? 'secondary' ?> fs-6"><?= ucfirst($report['status']) ?></span>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Report Details</h6></div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3 text-muted">Reporter</div>
                    <div class="col-sm-9"><?= e($report['username'] ?? 'User') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 text-muted">Type</div>
                    <div class="col-sm-9"><span class="badge bg-info text-capitalize"><?= e($report['reportable_type']) ?></span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 text-muted">Reason</div>
                    <div class="col-sm-9"><span class="badge bg-danger text-capitalize"><?= e($report['reason']) ?></span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 text-muted">Description</div>
                    <div class="col-sm-9"><?= e($report['description'] ?? 'No description provided.') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 text-muted">Date</div>
                    <div class="col-sm-9"><?= date('M d, Y h:i A', strtotime($report['created_at'])) ?></div>
                </div>
                <?php if ($report['resolution'] !== null): ?>
                <div class="row mb-3">
                    <div class="col-sm-3 text-muted">Resolution</div>
                    <div class="col-sm-9"><?= e($report['resolution']) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($reportedContent !== null): ?>
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Reported Content</h6></div>
            <div class="card-body">
                <?php if ($report['reportable_type'] === 'video'): ?>
                    <div class="d-flex">
                        <?php if (!empty($reportedContent['thumbnail_path'])): ?>
                            <div class="me-3" style="width:200px;height:112px;overflow:hidden;border-radius:8px;background:var(--bs-secondary);">
                                <img src="<?= e($reportedContent['thumbnail_path']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                            </div>
                        <?php endif; ?>
                        <div>
                            <h5><?= e($reportedContent['title'] ?? '') ?></h5>
                            <p class="text-muted mb-1">Channel: <?= e($reportedContent['name'] ?? '') ?></p>
                            <p class="text-muted mb-1">Status: <span class="badge bg-<?= $reportedContent['status'] === 'published' ? 'success' : 'warning' ?>"><?= e($reportedContent['status']) ?></span></p>
                            <p class="text-muted mb-0">Views: <?= format_number((int) ($reportedContent['views_count'] ?? 0)) ?></p>
                        </div>
                    </div>
                <?php elseif ($report['reportable_type'] === 'comment'): ?>
                    <div class="border-start border-3 ps-3">
                        <p class="mb-1"><?= e($reportedContent['body'] ?? '') ?></p>
                        <small class="text-muted">By <?= e($reportedContent['username'] ?? 'User') ?> on <?= e($reportedContent['title'] ?? 'video') ?></small>
                    </div>
                <?php elseif ($report['reportable_type'] === 'channel'): ?>
                    <h5><?= e($reportedContent['name'] ?? '') ?></h5>
                    <p class="text-muted">Owner: <?= e($reportedContent['username'] ?? '') ?></p>
                    <p class="text-muted">Subscribers: <?= format_number((int) ($reportedContent['subscriber_count'] ?? 0)) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <?php if ($report['status'] === 'pending'): ?>
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Resolve Report</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('/moderator/reports/' . $report['id'] . '/resolve') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Decision</label>
                        <select name="status" class="form-select" required>
                            <option value="resolved">Resolve</option>
                            <option value="dismissed">Dismiss</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Resolution Notes</label>
                        <textarea name="resolution" class="form-control" rows="4" required placeholder="Describe the resolution..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Submit Resolution</button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Resolution Info</h6></div>
            <div class="card-body">
                <p class="mb-1"><strong>Status:</strong> <?= ucfirst($report['status']) ?></p>
                <p class="mb-1"><strong>Reviewed by:</strong> <?= e((string) $report['reviewed_by']) ?></p>
                <p class="mb-0"><strong>Resolution:</strong> <?= e($report['resolution'] ?? 'N/A') ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
