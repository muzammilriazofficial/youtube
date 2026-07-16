<?php $__layout = 'layouts.support'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Monetization Help</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 bg-success bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Pending Applications</h6>
                        <h3 class="mb-0 text-success"><?= format_number($pendingApplications ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-hourglass-split fs-2 text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 bg-warning bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Open Monetization Tickets</h6>
                        <h3 class="mb-0 text-warning"><?= format_number($pendingMonetization ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-ticket-detailed fs-2 text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 bg-info bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Total Monetization Tickets</h6>
                        <h3 class="mb-0 text-info"><?= format_number($totalMonetizationTickets ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-cash-stack fs-2 text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Monetization Tickets</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Subject</th>
                                <th>User</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th class="text-end">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($monetizationTickets ?? [])): ?>
                                <?php foreach ($monetizationTickets as $t): ?>
                                <tr>
                                    <td class="text-muted">#<?= $t['id'] ?></td>
                                    <td><a href="<?= url('/support/tickets/' . $t['id']) ?>" class="text-decoration-none fw-semibold"><?= e(mb_substr($t['subject'], 0, 45)) ?></a></td>
                                    <td><?= e($t['username'] ?? 'User') ?></td>
                                    <td><span class="badge badge-priority-<?= $t['priority'] ?? 'low' ?> text-capitalize"><?= e($t['priority'] ?? 'low') ?></span></td>
                                    <td><span class="badge badge-status-<?= $t['status'] ?? 'open' ?> text-capitalize"><?= str_replace('_', ' ', $t['status'] ?? 'open') ?></span></td>
                                    <td class="text-end text-muted small"><?= time_ago($t['created_at']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No monetization tickets yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Common Issues</h6></div>
            <div class="card-body">
                <?php if (!empty($commonIssues ?? [])): ?>
                    <?php foreach ($commonIssues as $issue): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small"><?= e($issue['subject']) ?></span>
                        <span class="badge bg-warning"><?= $issue['count'] ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted mb-0 small">No common issues tracked yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-book me-2"></i>Monetization Guidelines</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="small fw-bold">Eligibility Requirements</h6>
                    <ul class="small text-muted mb-0">
                        <li>1,000+ subscribers</li>
                        <li>4,000+ public watch hours (12 months)</li>
                        <li>Comply with all YouTube monetization policies</li>
                        <li>Linked AdSense account</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <h6 class="small fw-bold">Revenue Share</h6>
                    <p class="small text-muted mb-0">Creators receive 55% of ad revenue, with 45% retained by the platform. Payment is issued monthly once the $100 threshold is reached.</p>
                </div>
                <div>
                    <h6 class="small fw-bold">Common Rejection Reasons</h6>
                    <ul class="small text-muted mb-0">
                        <li>Insufficient watch hours or subscribers</li>
                        <li>Reused or duplicate content</li>
                        <li>Content not suitable for advertisers</li>
                        <li>Community Guidelines violations</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
