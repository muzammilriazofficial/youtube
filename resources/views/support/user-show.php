<?php $__layout = 'layouts.support'; ?>

<div class="mb-3">
    <a href="<?= url('/support/users') ?>" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i>Back to Users</a>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 mb-3" style="width:80px;height:80px;">
                    <i class="bi bi-person fs-1 text-primary"></i>
                </div>
                <h5 class="mb-1"><?= e($user['username'] ?? '') ?></h5>
                <p class="text-muted mb-2"><?= e($user['email'] ?? '') ?></p>
                <?php if (!empty($user['is_banned'])): ?>
                    <span class="badge bg-danger">Banned</span>
                <?php else: ?>
                    <span class="badge bg-success">Active</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Account Info</h6></div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted d-block">Joined</small>
                    <span><?= e($user['created_at'] ?? '') ?></span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Account Age</small>
                    <span><?= time_ago($user['created_at'] ?? date('Y-m-d')) ?></span>
                </div>
                <?php if (!empty($user['is_admin'])): ?>
                    <div><span class="badge bg-warning">Admin</span></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($channels ?? [])): ?>
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Channels</h6></div>
            <div class="card-body">
                <?php foreach ($channels as $ch): ?>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-broadcast text-primary me-2"></i>
                        <span><?= e($ch['name'] ?? 'Channel') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-8">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 bg-primary bg-opacity-10">
                    <div class="card-body text-center">
                        <h4 class="mb-0"><?= format_number($videoCount ?? 0) ?></h4>
                        <small class="text-muted">Videos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-info bg-opacity-10">
                    <div class="card-body text-center">
                        <h4 class="mb-0"><?= format_number($commentCount ?? 0) ?></h4>
                        <small class="text-muted">Comments</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-danger bg-opacity-10">
                    <div class="card-body text-center">
                        <h4 class="mb-0"><?= format_number($reportsReceived ?? 0) ?></h4>
                        <small class="text-muted">Reports Received</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-warning bg-opacity-10">
                    <div class="card-body text-center">
                        <h4 class="mb-0"><?= format_number($reportsFiled ?? 0) ?></h4>
                        <small class="text-muted">Reports Filed</small>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($recentVideos ?? [])): ?>
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-play-btn me-2"></i>Recent Videos</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Title</th><th>Status</th><th class="text-end">Created</th></tr></thead>
                        <tbody>
                            <?php foreach ($recentVideos as $v): ?>
                            <tr>
                                <td><?= e(mb_substr($v['title'] ?? '', 0, 50)) ?></td>
                                <td><span class="badge bg-secondary text-capitalize"><?= e($v['status'] ?? '') ?></span></td>
                                <td class="text-end text-muted small"><?= time_ago($v['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($recentComments ?? [])): ?>
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Recent Comments</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Comment</th><th class="text-end">Created</th></tr></thead>
                        <tbody>
                            <?php foreach ($recentComments as $c): ?>
                            <tr>
                                <td class="text-truncate" style="max-width:400px;"><?= e($c['body'] ?? $c['comment'] ?? '') ?></td>
                                <td class="text-end text-muted small"><?= time_ago($c['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($tickets ?? [])): ?>
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-ticket-detailed me-2"></i>Support Tickets</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>#</th><th>Subject</th><th>Status</th><th class="text-end">Created</th></tr></thead>
                        <tbody>
                            <?php foreach ($tickets as $tk): ?>
                            <tr>
                                <td class="text-muted">#<?= $tk['id'] ?></td>
                                <td><a href="<?= url('/support/tickets/' . $tk['id']) ?>" class="text-decoration-none"><?= e(mb_substr($tk['subject'], 0, 40)) ?></a></td>
                                <td><span class="badge badge-status-<?= $tk['status'] ?> text-capitalize"><?= str_replace('_', ' ', $tk['status']) ?></span></td>
                                <td class="text-end text-muted small"><?= time_ago($tk['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
