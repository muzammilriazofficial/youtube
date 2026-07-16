<?php $__layout = 'layouts.support'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Copyright Help</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 bg-danger bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Total DMCA Claims</h6>
                        <h3 class="mb-0 text-danger"><?= format_number($dmcaClaims ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-shield-exclamation fs-2 text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 bg-warning bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Pending DMCA</h6>
                        <h3 class="mb-0 text-warning"><?= format_number($pendingDmca ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-hourglass-split fs-2 text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 bg-info bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Open Copyright Tickets</h6>
                        <h3 class="mb-0 text-info"><?= format_number($pendingCopyright ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-ticket-detailed fs-2 text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Copyright Tickets</h6>
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
                            <?php if (!empty($copyrightTickets ?? [])): ?>
                                <?php foreach ($copyrightTickets as $t): ?>
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
                                <tr><td colspan="6" class="text-center text-muted py-4">No copyright tickets yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Recent DMCA Claims</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Claimant</th>
                                <th>Status</th>
                                <th class="text-end">Filed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentClaims ?? [])): ?>
                                <?php foreach ($recentClaims as $cl): ?>
                                <tr>
                                    <td class="text-muted">#<?= $cl['id'] ?></td>
                                    <td><?= e($cl['username'] ?? 'User') ?></td>
                                    <td><span class="badge badge-status-<?= $cl['status'] ?? 'pending' ?> text-capitalize"><?= str_replace('_', ' ', $cl['status'] ?? 'pending') ?></span></td>
                                    <td class="text-end text-muted small"><?= time_ago($cl['created_at']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">No DMCA claims filed yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>DMCA Process Overview</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="small fw-bold">1. Filing a Claim</h6>
                    <p class="small text-muted mb-0">Rights holders submit a DMCA takedown notice with proof of ownership, identification of the infringing content, and contact information.</p>
                </div>
                <div class="mb-3">
                    <h6 class="small fw-bold">2. Review Process</h6>
                    <p class="small text-muted mb-0">Our team reviews the claim for completeness and validity. The content creator is notified and may file a counter-notification.</p>
                </div>
                <div class="mb-3">
                    <h6 class="small fw-bold">3. Counter-Notification</h6>
                    <p class="small text-muted mb-0">If the creator believes the claim is mistaken, they can file a counter-notification. The original claimant then has 10-14 business days to take legal action.</p>
                </div>
                <div>
                    <h6 class="small fw-bold">4. Resolution</h6>
                    <p class="small text-muted mb-0">Content is either restored or permanently removed based on the outcome. Repeated violations result in channel termination.</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-journal-text me-2"></i>Copyright Policies</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="small fw-bold">Fair Use</h6>
                    <p class="small text-muted mb-0">Limited use of copyrighted material may qualify as fair use for purposes such as commentary, criticism, news reporting, teaching, and research.</p>
                </div>
                <div class="mb-3">
                    <h6 class="small fw-bold">Content ID</h6>
                    <p class="small text-muted mb-0">Automated Content ID matches help rights holders manage their content. Claims can result in blocking, monetization, or tracking of the video.</p>
                </div>
                <div>
                    <h6 class="small fw-bold">Repeat Infringers</h6>
                    <p class="small text-muted mb-0">Channels that receive multiple copyright strikes may be subject to termination. 3 strikes = channel termination per YouTube policy.</p>
                </div>
            </div>
        </div>
    </div>
</div>
