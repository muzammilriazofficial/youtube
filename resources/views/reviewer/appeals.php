<?php $__layout = 'layouts.reviewer'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Appeals</h4>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th class="text-end">Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($appeals ?? [])): ?>
                        <?php foreach ($appeals as $appeal): ?>
                        <tr>
                            <td><?= $appeal['id'] ?></td>
                            <td class="small"><?= e($appeal['username'] ?? 'User') ?></td>
                            <td><span class="badge bg-info text-capitalize"><?= e($appeal['reportable_type']) ?></span></td>
                            <td><span class="badge bg-danger text-capitalize"><?= e($appeal['reason']) ?></span></td>
                            <td>
                                <?php $sc = ['pending' => 'warning', 'resolved' => 'success', 'dismissed' => 'secondary']; ?>
                                <span class="badge bg-<?= $sc[$appeal['status']] ?? 'secondary' ?>"><?= ucfirst($appeal['status']) ?></span>
                            </td>
                            <td class="text-end text-muted small"><?= time_ago($appeal['created_at']) ?></td>
                            <td class="text-end">
                                <?php if ($appeal['status'] === 'pending'): ?>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#resolveModal<?= $appeal['id'] ?>">Resolve</button>
                                <?php else: ?>
                                <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No appeals to review.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php foreach (($appeals ?? []) as $appeal): ?>
<?php if ($appeal['status'] === 'pending'): ?>
<div class="modal fade" id="resolveModal<?= $appeal['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/reviewer/appeals/' . $appeal['id'] . '/resolve') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Resolve Appeal #<?= $appeal['id'] ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Type:</strong> <?= e($appeal['reportable_type']) ?></p>
                    <p><strong>Reason:</strong> <?= e($appeal['description'] ?? $appeal['reason']) ?></p>
                    <div class="mb-3">
                        <label class="form-label">Decision</label>
                        <select name="decision" class="form-select" required>
                            <option value="uphold">Uphold Original Decision</option>
                            <option value="overturn">Overturn Decision</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Explain your decision..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Decision</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
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
