<?php $__layout = 'layouts.support'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-camera-video me-2"></i>Creator Management</h4>
    <span class="badge bg-secondary fs-6"><?= format_number($creators['total'] ?? 0) ?> channels</span>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/support/creators') ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted">Search Channel</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Channel name..." value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Monetization</label>
                <select name="monetization" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="active" <?= ($filters['monetization'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="pending" <?= ($filters['monetization'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="suspended" <?= ($filters['monetization'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    <option value="rejected" <?= ($filters['monetization'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Partner</label>
                <select name="partner" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="yes" <?= ($filters['partner'] ?? '') === 'yes' ? 'selected' : '' ?>>Partner</option>
                    <option value="no" <?= ($filters['partner'] ?? '') === 'no' ? 'selected' : '' ?>>Non-Partner</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>Search</button>
                <a href="<?= url('/support/creators') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
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
                        <th>ID</th>
                        <th>Channel</th>
                        <th>Owner</th>
                        <th>Subscribers</th>
                        <th>Videos</th>
                        <th>Partner</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($creators['data'] ?? [])): ?>
                        <?php foreach ($creators['data'] as $c): ?>
                        <tr>
                            <td class="text-muted">#<?= $c['id'] ?></td>
                            <td class="fw-semibold">
                                <i class="bi bi-broadcast text-primary me-1"></i><?= e($c['name'] ?? '') ?>
                            </td>
                            <td>
                                <a href="<?= url('/support/users/' . $c['user_id']) ?>" class="text-decoration-none"><?= e($c['username'] ?? '') ?></a>
                            </td>
                            <td><?= format_number((int) ($c['subscribers'] ?? 0)) ?></td>
                            <td><?= format_number((int) ($c['videos_count'] ?? 0)) ?></td>
                            <td>
                                <?php if (!empty($c['is_partner'])): ?>
                                    <span class="badge bg-success">Partner</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Standard</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= time_ago($c['created_at']) ?></td>
                            <td class="text-end">
                                <a href="<?= url('/support/users/' . $c['user_id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>View Owner</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No creators found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (($creators['last_page'] ?? 1) > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-center">
        <li class="page-item <?= ($creators['has_prev_page'] ?? false) ? '' : 'disabled' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => ($creators['current_page'] ?? 1) - 1])) ?>">Previous</a>
        </li>
        <?php for ($i = max(1, ($creators['current_page'] ?? 1) - 2); $i <= min($creators['last_page'] ?? 1, ($creators['current_page'] ?? 1) + 2); $i++): ?>
        <li class="page-item <?= $i === ($creators['current_page'] ?? 1) ? 'active' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= ($creators['has_more_pages'] ?? false) ? '' : 'disabled' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => ($creators['current_page'] ?? 1) + 1])) ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
