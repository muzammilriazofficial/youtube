<?php $__layout = 'layouts.advertiser'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">My Campaigns</h4>
    <a href="<?= url('/advertiser/campaigns/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus me-1"></i>New Campaign</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/advertiser/campaigns') ?>" class="row g-2">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <?php foreach (['draft', 'active', 'paused', 'completed', 'cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($status ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
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
                        <th>Campaign</th>
                        <th>Budget</th>
                        <th>Spent</th>
                        <th>Dates</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($campaigns ?? [])): ?>
                        <?php foreach ($campaigns as $c): ?>
                        <tr>
                            <td class="fw-medium"><?= e($c['name']) ?></td>
                            <td>$<?= number_format((float) $c['budget'], 2) ?></td>
                            <td>
                                $<?= number_format((float) $c['spent'], 2) ?>
                                <?php $pct = $c['budget'] > 0 ? round(($c['spent'] / $c['budget']) * 100) : 0; ?>
                                <div class="progress mt-1" style="height:4px;width:80px;">
                                    <div class="progress-bar bg-<?= $pct > 90 ? 'danger' : ($pct > 70 ? 'warning' : 'success') ?>" style="width:<?= min($pct, 100) ?>%"></div>
                                </div>
                            </td>
                            <td class="small text-muted"><?= date('M d', strtotime($c['start_date'])) ?> - <?= date('M d', strtotime($c['end_date'])) ?></td>
                            <td>
                                <?php $cColors = ['active' => 'success', 'draft' => 'secondary', 'paused' => 'warning', 'completed' => 'info', 'cancelled' => 'danger']; ?>
                                <span class="badge bg-<?= $cColors[$c['status']] ?? 'secondary' ?>"><?= ucfirst($c['status']) ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('/advertiser/campaigns/' . $c['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="<?= url('/advertiser/campaigns/' . $c['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Cancel this campaign?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No campaigns yet. <a href="<?= url('/advertiser/campaigns/create') ?>">Create your first campaign</a></td></tr>
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
