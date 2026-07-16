<?php $__layout = 'layouts.advertiser'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Payment History</h4>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Transaction ID</th>
                        <th class="text-end">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments ?? [])): ?>
                        <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td class="fw-medium">$<?= number_format((float) $p['amount'], 2) ?></td>
                            <td class="small text-capitalize"><?= e(str_replace('_', ' ', $p['payment_method'])) ?></td>
                            <td>
                                <?php $sc = ['pending' => 'warning', 'processing' => 'info', 'completed' => 'success', 'failed' => 'danger']; ?>
                                <span class="badge bg-<?= $sc[$p['status']] ?? 'secondary' ?>"><?= ucfirst($p['status']) ?></span>
                            </td>
                            <td class="small text-muted"><?= e($p['transaction_id'] ?? '-') ?></td>
                            <td class="text-end text-muted small"><?= date('M d, Y h:i A', strtotime($p['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No payment history.</td></tr>
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
