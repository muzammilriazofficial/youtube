<?php $__layout = 'layouts.moderator'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">All Reports</h4>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/moderator/reports') ?>" class="row g-2">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <?php foreach (['pending', 'reviewed', 'resolved', 'dismissed'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($status ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <?php foreach (['video', 'comment', 'channel'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($type ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Reporter</th>
                        <th>Type</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th class="text-end">Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reports ?? [])): ?>
                        <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><?= $report['id'] ?></td>
                            <td class="small"><?= e($report['username'] ?? 'User') ?></td>
                            <td><span class="badge bg-info text-capitalize"><?= e($report['reportable_type']) ?></span></td>
                            <td><span class="badge bg-danger text-capitalize"><?= e($report['reason']) ?></span></td>
                            <td>
                                <?php
                                    $statusColors = ['pending' => 'warning', 'reviewed' => 'info', 'resolved' => 'success', 'dismissed' => 'secondary'];
                                ?>
                                <span class="badge bg-<?= $statusColors[$report['status']] ?? 'secondary' ?>"><?= ucfirst($report['status']) ?></span>
                            </td>
                            <td class="text-end text-muted small"><?= time_ago($report['created_at']) ?></td>
                            <td class="text-end">
                                <a href="<?= url('/moderator/reports/' . $report['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No reports found.</td></tr>
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
            <a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) - 1 ?>&status=<?= e($status ?? '') ?>&type=<?= e($type ?? '') ?>">Previous</a>
        </li>
        <?php for ($i = max(1, ($pagination['current_page'] ?? 1) - 2); $i <= min($pagination['last_page'] ?? 1, ($pagination['current_page'] ?? 1) + 2); $i++): ?>
            <li class="page-item <?= $i === ($pagination['current_page'] ?? 1) ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&status=<?= e($status ?? '') ?>&type=<?= e($type ?? '') ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= !($pagination['has_more_pages'] ?? false) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) + 1 ?>&status=<?= e($status ?? '') ?>&type=<?= e($type ?? '') ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
