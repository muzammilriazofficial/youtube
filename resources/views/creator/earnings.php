<?php $__layout = 'layouts.app'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Earnings</h4>
    <a href="<?= url('/creator/monetization') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="text-success mb-0">$<?= number_format($totalEarnings ?? 0, 2) ?></h3>
                <small class="text-muted">Total Earnings</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0">$<?= number_format($thisMonth ?? 0, 2) ?></h3>
                <small class="text-muted">This Month</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0">$<?= number_format($lastMonth ?? 0, 2) ?></h3>
                <small class="text-muted">Last Month</small>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($earnings ?? [])): ?>
<div class="card">
    <div class="card-header"><h6 class="mb-0">Earnings History</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Video</th>
                        <th>Source</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($earnings as $earning): ?>
                    <tr>
                        <td><small><?= date('M d, Y', strtotime($earning['earned_at'] ?? $earning['created_at'])) ?></small></td>
                        <td><small class="text-muted"><?= e($earning['video_title'] ?? 'N/A') ?></small></td>
                        <td><span class="badge bg-secondary"><?= e($earning['source'] ?? 'Ad Revenue') ?></span></td>
                        <td class="text-end fw-medium text-success">$<?= number_format((float) $earning['amount'], 2) ?></td>
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
        <i class="bi bi-cash-coin display-4 text-muted mb-3"></i>
        <h5>No earnings yet</h5>
        <p class="text-muted">Earnings will appear here once your channel is monetized.</p>
    </div>
</div>
<?php endif; ?>
