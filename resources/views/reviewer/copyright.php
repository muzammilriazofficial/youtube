<?php $__layout = 'layouts.reviewer'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Copyright Claims</h4>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/reviewer/copyright') ?>" class="row g-2">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <?php foreach (['pending', 'accepted', 'rejected', 'counter_notified', 'resolved'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($status ?? '') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
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
                        <th>Claimant</th>
                        <th>Original Work</th>
                        <th>Video</th>
                        <th>Status</th>
                        <th class="text-end">Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($claims ?? [])): ?>
                        <?php foreach ($claims as $claim): ?>
                        <tr>
                            <td><?= $claim['id'] ?></td>
                            <td class="small"><?= e($claim['claimant_name'] ?? '') ?></td>
                            <td class="text-truncate small" style="max-width:150px;"><?= e($claim['original_work_title']) ?></td>
                            <td class="text-truncate small" style="max-width:150px;"><?= e($claim['title'] ?? '') ?></td>
                            <td>
                                <?php
                                    $statusColors = ['pending' => 'warning', 'accepted' => 'success', 'rejected' => 'danger', 'counter_notified' => 'info', 'resolved' => 'secondary'];
                                ?>
                                <span class="badge bg-<?= $statusColors[$claim['status']] ?? 'secondary' ?>"><?= ucfirst(str_replace('_', ' ', $claim['status'])) ?></span>
                            </td>
                            <td class="text-end text-muted small"><?= time_ago($claim['created_at']) ?></td>
                            <td class="text-end">
                                <a href="<?= url('/reviewer/copyright/claims') ?>" class="btn btn-sm btn-outline-primary">Details</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No copyright claims.</td></tr>
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
            <a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) - 1 ?>&status=<?= e($status ?? '') ?>">Previous</a>
        </li>
        <?php for ($i = max(1, ($pagination['current_page'] ?? 1) - 2); $i <= min($pagination['last_page'] ?? 1, ($pagination['current_page'] ?? 1) + 2); $i++): ?>
            <li class="page-item <?= $i === ($pagination['current_page'] ?? 1) ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&status=<?= e($status ?? '') ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= !($pagination['has_more_pages'] ?? false) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) + 1 ?>&status=<?= e($status ?? '') ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
