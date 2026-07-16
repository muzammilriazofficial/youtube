<?php $__layout = 'layouts.support'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-ticket-detailed me-2"></i>Support Tickets</h4>
    <span class="badge bg-secondary fs-6"><?= format_number($tickets['total'] ?? 0) ?> total</span>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/support/tickets') ?>" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="open" <?= ($filters['status'] ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
                    <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="waiting_on_user" <?= ($filters['status'] ?? '') === 'waiting_on_user' ? 'selected' : '' ?>>Waiting on User</option>
                    <option value="resolved" <?= ($filters['status'] ?? '') === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                    <option value="closed" <?= ($filters['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Priority</label>
                <select name="priority" class="form-select form-select-sm">
                    <option value="">All Priorities</option>
                    <option value="low" <?= ($filters['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                    <option value="medium" <?= ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                    <option value="urgent" <?= ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Category</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    <option value="general" <?= ($filters['category'] ?? '') === 'general' ? 'selected' : '' ?>>General</option>
                    <option value="account" <?= ($filters['category'] ?? '') === 'account' ? 'selected' : '' ?>>Account</option>
                    <option value="monetization" <?= ($filters['category'] ?? '') === 'monetization' ? 'selected' : '' ?>>Monetization</option>
                    <option value="copyright" <?= ($filters['category'] ?? '') === 'copyright' ? 'selected' : '' ?>>Copyright</option>
                    <option value="technical" <?= ($filters['category'] ?? '') === 'technical' ? 'selected' : '' ?>>Technical</option>
                    <option value="content" <?= ($filters['category'] ?? '') === 'content' ? 'selected' : '' ?>>Content</option>
                    <option value="billing" <?= ($filters['category'] ?? '') === 'billing' ? 'selected' : '' ?>>Billing</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Assignment</label>
                <select name="assigned" class="form-select form-select-sm">
                    <option value="">All Agents</option>
                    <option value="me" <?= ($filters['assigned'] ?? '') === 'me' ? 'selected' : '' ?>>Assigned to Me</option>
                    <option value="unassigned" <?= ($filters['assigned'] ?? '') === 'unassigned' ? 'selected' : '' ?>>Unassigned</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Subject or user..." value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>Filter</button>
                <a href="<?= url('/support/tickets') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
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
                        <th>Subject</th>
                        <th>User</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Last Reply</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tickets['data'] ?? [])): ?>
                        <?php foreach ($tickets['data'] as $t): ?>
                        <tr>
                            <td class="text-muted">#<?= $t['id'] ?></td>
                            <td>
                                <a href="<?= url('/support/tickets/' . $t['id']) ?>" class="text-decoration-none fw-semibold">
                                    <?= e(mb_substr($t['subject'], 0, 50)) ?><?= strlen($t['subject']) > 50 ? '...' : '' ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?= url('/support/users/' . $t['user_id']) ?>" class="text-decoration-none"><?= e($t['username'] ?? 'User') ?></a>
                            </td>
                            <td><span class="badge bg-secondary text-capitalize"><?= e($t['category'] ?? 'general') ?></span></td>
                            <td><span class="badge badge-priority-<?= $t['priority'] ?? 'low' ?> text-capitalize"><?= e($t['priority'] ?? 'low') ?></span></td>
                            <td><span class="badge badge-status-<?= $t['status'] ?? 'open' ?> text-capitalize"><?= str_replace('_', ' ', $t['status'] ?? 'open') ?></span></td>
                            <td>
                                <?php if (!empty($t['assigned_to'])): ?>
                                    <span class="text-success"><i class="bi bi-person-check me-1"></i><?= e($t['assignee_username'] ?? 'Agent') ?></span>
                                <?php else: ?>
                                    <span class="text-muted"><i class="bi bi-person-dash me-1"></i>Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= time_ago($t['updated_at'] ?? $t['created_at']) ?></td>
                            <td class="text-end">
                                <a href="<?= url('/support/tickets/' . $t['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No tickets found matching your filters.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (($tickets['last_page'] ?? 1) > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-center">
        <li class="page-item <?= ($tickets['has_prev_page'] ?? false) ? '' : 'disabled' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => ($tickets['current_page'] ?? 1) - 1])) ?>">Previous</a>
        </li>
        <?php for ($i = max(1, ($tickets['current_page'] ?? 1) - 2); $i <= min($tickets['last_page'] ?? 1, ($tickets['current_page'] ?? 1) + 2); $i++): ?>
        <li class="page-item <?= $i === ($tickets['current_page'] ?? 1) ? 'active' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= ($tickets['has_more_pages'] ?? false) ? '' : 'disabled' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => ($tickets['current_page'] ?? 1) + 1])) ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
