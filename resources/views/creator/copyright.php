<?php $__layout = 'layouts.app'; ?>

<h4 class="mb-4">Copyright Claims</h4>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0"><?= format_number($totalClaims ?? 0) ?></h3>
                <small class="text-muted">Total Claims</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="text-danger mb-0"><?= format_number($activeClaims ?? 0) ?></h3>
                <small class="text-muted">Active Claims</small>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($claims ?? [])): ?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Video</th>
                        <th>Claimant</th>
                        <th>Content</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($claims as $claim): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="width:80px;height:45px;overflow:hidden;border-radius:4px;background:var(--bs-secondary);flex-shrink:0;">
                                    <?php if (!empty($claim['thumbnail'])): ?>
                                        <img src="<?= e($claim['thumbnail']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center h-100"><i class="bi bi-play-circle"></i></div>
                                    <?php endif; ?>
                                </div>
                                <small class="fw-medium"><?= e(mb_substr($claim['video_title'] ?? '', 0, 30)) ?></small>
                            </div>
                        </td>
                        <td><small><?= e($claim['claimant'] ?? 'Unknown') ?></small></td>
                        <td><small class="text-muted"><?= e($claim['content_type'] ?? 'Audio/Video') ?></small></td>
                        <td>
                            <?php
                            $cColors = ['active' => 'danger', 'disputed' => 'warning', 'resolved' => 'success', 'expired' => 'secondary'];
                            $cColor = $cColors[$claim['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $cColor ?>"><?= ucfirst($claim['status']) ?></span>
                        </td>
                        <td><small class="text-muted"><?= date('M d, Y', strtotime($claim['created_at'])) ?></small></td>
                        <td class="text-end">
                            <?php if ($claim['status'] === 'active'): ?>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-secondary" title="Dispute" onclick="alert('Dispute form coming soon.')"><i class="bi bi-flag"></i></button>
                                    <button class="btn btn-outline-secondary" title="Remove Content" onclick="alert('Content removal coming soon.')"><i class="bi bi-trash"></i></button>
                                </div>
                            <?php else: ?>
                                <small class="text-muted">-</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (($pagination['last_page'] ?? 1) > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= !($pagination['has_prev_page'] ?? false) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) - 1 ?>">Prev</a></li>
        <?php for ($i = max(1, ($pagination['current_page'] ?? 1) - 2); $i <= min($pagination['last_page'] ?? 1, ($pagination['current_page'] ?? 1) + 2); $i++): ?>
            <li class="page-item <?= $i === ($pagination['current_page'] ?? 1) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
        <?php endfor; ?>
        <li class="page-item <?= !($pagination['has_more_pages'] ?? false) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) + 1 ?>">Next</a></li>
    </ul>
</nav>
<?php endif; ?>

<?php else: ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-shield-lock display-4 text-muted mb-3"></i>
        <h5>No copyright claims</h5>
        <p class="text-muted">Your channel has no copyright claims. Keep creating original content!</p>
    </div>
</div>
<?php endif; ?>

<div class="card mt-4">
    <div class="card-header"><h6 class="mb-0">About Copyright Claims</h6></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>What is a Copyright Claim?</h6>
                <p class="small text-muted">A copyright claim (also known as a Content ID claim) occurs when YouTube's Content ID system detects content in your video that matches copyrighted material. The copyright owner may choose to block, track, or monetize your video.</p>
            </div>
            <div class="col-md-6">
                <h6>What can you do?</h6>
                <ul class="small text-muted">
                    <li>Review the claim details to understand what content was flagged.</li>
                    <li>If you have rights to the content, you can dispute the claim.</li>
                    <li>Remove the claimed content from your video.</li>
                    <li>Accept the claim and let the copyright owner monetize your video.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
