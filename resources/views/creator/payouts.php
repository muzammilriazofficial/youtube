<?php $__layout = 'layouts.app'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Payouts</h4>
    <a href="<?= url('/creator/monetization') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="text-warning mb-0">$<?= number_format($pendingPayout ?? 0, 2) ?></h3>
                <small class="text-muted">Pending Payout</small>
                <div class="form-text">Minimum payout threshold: $100.00</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0">$<?= number_format(array_sum(array_map(fn($p) => (float) ($p['amount'] ?? 0), $payouts ?? [])), 2) ?></h3>
                <small class="text-muted">Total Paid Out</small>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($payouts ?? [])): ?>
<div class="card">
    <div class="card-header"><h6 class="mb-0">Payout History</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Period</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payouts as $payout): ?>
                    <tr>
                        <td><small><?= date('M d, Y', strtotime($payout['created_at'])) ?></small></td>
                        <td><small class="text-muted"><?= e($payout['period'] ?? 'N/A') ?></small></td>
                        <td><small><?= e($payout['method'] ?? 'Bank Transfer') ?></small></td>
                        <td>
                            <?php
                            $pStatusColors = ['completed' => 'success', 'processing' => 'warning', 'pending' => 'info', 'failed' => 'danger'];
                            $pColor = $pStatusColors[$payout['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $pColor ?>"><?= ucfirst($payout['status']) ?></span>
                        </td>
                        <td class="text-end fw-medium">$<?= number_format((float) $payout['amount'], 2) ?></td>
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
        <li class="page-item <?= !($pagination['has_more_pages'] ?? false) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) + 1 ?>">Next</a></li>
    </ul>
</nav>
<?php endif; ?>

<?php else: ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-wallet2 display-4 text-muted mb-3"></i>
        <h5>No payouts yet</h5>
        <p class="text-muted">Payouts are processed monthly once you reach the minimum threshold of $100.</p>
    </div>
</div>
<?php endif; ?>
