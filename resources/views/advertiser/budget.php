<?php $__layout = 'layouts.advertiser'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Budget Overview</h4>
    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addFundsModal"><i class="bi bi-plus-circle me-1"></i>Add Funds</button>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 bg-primary bg-opacity-10">
            <div class="card-body text-center">
                <h3 class="mb-0">$<?= number_format($totalBudget ?? 0, 2) ?></h3>
                <small class="text-muted">Total Budget</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-warning bg-opacity-10">
            <div class="card-body text-center">
                <h3 class="mb-0">$<?= number_format($totalSpent ?? 0, 2) ?></h3>
                <small class="text-muted">Total Spent</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-success bg-opacity-10">
            <div class="card-body text-center">
                <h3 class="mb-0">$<?= number_format($remaining ?? 0, 2) ?></h3>
                <small class="text-muted">Remaining</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-info bg-opacity-10">
            <div class="card-body text-center">
                <h3 class="mb-0">$<?= number_format($adSpend ?? 0, 2) ?></h3>
                <small class="text-muted">Ad Spend</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h6 class="mb-0">Spending History</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Campaign</th>
                        <th>Budget</th>
                        <th>Spent</th>
                        <th>Remaining</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($spendingHistory ?? [])): ?>
                        <?php foreach ($spendingHistory as $h): ?>
                        <tr>
                            <td class="fw-medium"><?= e($h['name']) ?></td>
                            <td>$<?= number_format((float) $h['budget'], 2) ?></td>
                            <td>$<?= number_format((float) $h['spent'], 2) ?></td>
                            <td>$<?= number_format((float) $h['budget'] - (float) $h['spent'], 2) ?></td>
                            <td>
                                <?php $sc = ['active' => 'success', 'draft' => 'secondary', 'paused' => 'warning', 'completed' => 'info', 'cancelled' => 'danger']; ?>
                                <span class="badge bg-<?= $sc[$h['status']] ?? 'secondary' ?>"><?= ucfirst($h['status']) ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No spending history.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (($pagination['last_page'] ?? 1) > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= !($pagination['has_prev_page'] ?? false) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) - 1 ?>">Previous</a>
        </li>
        <?php for ($i = max(1, ($pagination['current_page'] ?? 1) - 2); $i <= min($pagination['last_page'] ?? 1, ($pagination['current_page'] ?? 1) + 2); $i++): ?>
            <li class="page-item <?= $i === ($pagination['current_page'] ?? 1) ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= !($pagination['has_more_pages'] ?? false) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) + 1 ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<div class="modal fade" id="addFundsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/advertiser/budget/add') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Add Funds</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount ($)</label>
                        <input type="number" name="amount" class="form-control" required step="0.01" min="1" placeholder="0.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Funds</button>
                </div>
            </form>
        </div>
    </div>
</div>
