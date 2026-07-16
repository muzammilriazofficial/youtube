<?php $__layout = 'layouts.moderator'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Moderator Dashboard</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 bg-warning bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Pending Videos</h6>
                        <h3 class="mb-0"><?= format_number($pendingVideos ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-hourglass-split fs-2 text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-danger bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Reported Videos</h6>
                        <h3 class="mb-0"><?= format_number($reportedVideos ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-flag fs-2 text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-info bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Reported Comments</h6>
                        <h3 class="mb-0"><?= format_number($reportedComments ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-chat-left-dots fs-2 text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-primary bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Total Reports</h6>
                        <h3 class="mb-0"><?= format_number($totalReports ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-2 text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= url('/moderator/videos?status=pending') ?>" class="btn btn-warning btn-sm"><i class="bi bi-hourglass-split me-1"></i>Review Pending Videos</a>
                    <a href="<?= url('/moderator/videos/reported') ?>" class="btn btn-danger btn-sm"><i class="bi bi-flag me-1"></i>Review Reported Videos</a>
                    <a href="<?= url('/moderator/comments/reported') ?>" class="btn btn-info btn-sm"><i class="bi bi-chat-left-dots me-1"></i>Review Reported Comments</a>
                    <a href="<?= url('/moderator/channels/reported') ?>" class="btn btn-secondary btn-sm"><i class="bi bi-broadcast me-1"></i>Review Reported Channels</a>
                    <a href="<?= url('/moderator/reports') ?>" class="btn btn-primary btn-sm"><i class="bi bi-exclamation-triangle me-1"></i>All Reports</a>
                    <a href="<?= url('/moderator/violations') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-journal-x me-1"></i>Violation Log</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Recent Violations</h6>
                <a href="<?= url('/moderator/violations') ?>" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Moderator</th>
                                <th class="text-end">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentViolations ?? [])): ?>
                                <?php foreach ($recentViolations as $v): ?>
                                <tr>
                                    <td><span class="badge bg-<?= $v['type'] === 'warning' ? 'warning' : ($v['type'] === 'ban' ? 'danger' : 'secondary') ?>"><?= e(ucfirst($v['type'])) ?></span></td>
                                    <td class="text-truncate" style="max-width:300px;"><?= e($v['description']) ?></td>
                                    <td><?= e($v['username'] ?? 'Mod') ?></td>
                                    <td class="text-end text-muted small"><?= time_ago($v['created_at']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">No violations logged yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">Report Reasons</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($reportReasons ?? [])): ?>
                    <?php foreach ($reportReasons as $reason): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-capitalize"><?= e($reason['reason']) ?></span>
                        <span class="badge bg-danger"><?= $reason['count'] ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">No pending reports.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
