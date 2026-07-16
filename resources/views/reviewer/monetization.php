<?php $__layout = 'layouts.reviewer'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Monetization Applications</h4>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Channel</th>
                        <th>Owner</th>
                        <th>Subscribers</th>
                        <th>Total Earnings</th>
                        <th>Applied</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($applications ?? [])): ?>
                        <?php foreach ($applications as $app): ?>
                        <tr>
                            <td class="fw-medium"><?= e($app['name'] ?? '') ?></td>
                            <td class="small"><?= e($app['username'] ?? '') ?></td>
                            <td><?= format_number((int) ($app['subscriber_count'] ?? 0)) ?></td>
                            <td>$<?= number_format((float) ($app['total_earnings'] ?? 0), 2) ?></td>
                            <td class="text-muted small"><?= $app['application_date'] ? date('M d, Y', strtotime($app['application_date'])) : 'N/A' ?></td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <form method="POST" action="<?= url('/reviewer/monetization/' . $app['id'] . '/approve') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-success me-1"><i class="bi bi-check-circle me-1"></i>Approve</button>
                                    </form>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $app['id'] ?>"><i class="bi bi-x-circle me-1"></i>Reject</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No pending monetization applications.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php foreach (($applications ?? []) as $app): ?>
<div class="modal fade" id="rejectModal<?= $app['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/reviewer/monetization/' . $app['id'] . '/reject') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Reject Monetization</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Reject monetization for <strong><?= e($app['name'] ?? '') ?></strong>?</p>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Explain why this application is being rejected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Application</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

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
