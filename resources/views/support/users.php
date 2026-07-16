<?php $__layout = 'layouts.support'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-people me-2"></i>User Management</h4>
    <span class="badge bg-secondary fs-6"><?= format_number($users['total'] ?? 0) ?> users</span>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/support/users') ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Username..." value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="banned" <?= ($filters['status'] ?? '') === 'banned' ? 'selected' : '' ?>>Banned</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Role</label>
                <select name="role" class="form-select form-select-sm">
                    <option value="">All Roles</option>
                    <option value="viewer" <?= ($filters['role'] ?? '') === 'viewer' ? 'selected' : '' ?>>Viewer</option>
                    <option value="creator" <?= ($filters['role'] ?? '') === 'creator' ? 'selected' : '' ?>>Creator</option>
                    <option value="moderator" <?= ($filters['role'] ?? '') === 'moderator' ? 'selected' : '' ?>>Moderator</option>
                    <option value="support" <?= ($filters['role'] ?? '') === 'support' ? 'selected' : '' ?>>Support</option>
                    <option value="admin" <?= ($filters['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>Search</button>
                <a href="<?= url('/support/users') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
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
                        <th>Username</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users['data'] ?? [])): ?>
                        <?php foreach ($users['data'] as $u): ?>
                        <tr>
                            <td class="text-muted">#<?= $u['id'] ?></td>
                            <td class="fw-semibold"><?= e($u['username'] ?? '') ?></td>
                            <td class="text-muted"><?= e($u['email'] ?? '') ?></td>
                            <td>
                                <?php if (!empty($u['is_banned'])): ?>
                                    <span class="badge bg-danger">Banned</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= time_ago($u['created_at']) ?></td>
                            <td class="text-end">
                                <a href="<?= url('/support/users/' . $u['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (($users['last_page'] ?? 1) > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-center">
        <li class="page-item <?= ($users['has_prev_page'] ?? false) ? '' : 'disabled' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => ($users['current_page'] ?? 1) - 1])) ?>">Previous</a>
        </li>
        <?php for ($i = max(1, ($users['current_page'] ?? 1) - 2); $i <= min($users['last_page'] ?? 1, ($users['current_page'] ?? 1) + 2); $i++): ?>
        <li class="page-item <?= $i === ($users['current_page'] ?? 1) ? 'active' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= ($users['has_more_pages'] ?? false) ? '' : 'disabled' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => ($users['current_page'] ?? 1) + 1])) ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
