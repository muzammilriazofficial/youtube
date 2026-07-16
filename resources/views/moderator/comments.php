<?php $__layout = 'layouts.moderator'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Manage Comments</h4>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/moderator/comments') ?>" class="row g-2">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" placeholder="Search comments..." value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Search</button>
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
                        <th>User</th>
                        <th>Comment</th>
                        <th>Video</th>
                        <th>Status</th>
                        <th class="text-end">Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($comments ?? [])): ?>
                        <?php foreach ($comments as $comment): ?>
                        <tr>
                            <td class="small"><?= e($comment['username'] ?? 'User') ?></td>
                            <td class="text-truncate" style="max-width:250px;"><?= e(mb_substr($comment['body'], 0, 60)) ?></td>
                            <td class="text-truncate small" style="max-width:150px;"><?= e(mb_substr($comment['title'] ?? '', 0, 30)) ?></td>
                            <td>
                                <?php
                                    $statusColors = ['visible' => 'success', 'hidden' => 'warning', 'deleted' => 'danger'];
                                ?>
                                <span class="badge bg-<?= $statusColors[$comment['status']] ?? 'secondary' ?>"><?= ucfirst($comment['status']) ?></span>
                            </td>
                            <td class="text-end text-muted small"><?= time_ago($comment['created_at']) ?></td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Action</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><button class="dropdown-item text-success" onclick="takeAction(<?= $comment['id'] ?>, 'approve')"><i class="bi bi-check-circle me-2"></i>Approve</button></li>
                                        <li><button class="dropdown-item text-warning" onclick="takeAction(<?= $comment['id'] ?>, 'hide')"><i class="bi bi-eye-slash me-2"></i>Hide</button></li>
                                        <li><button class="dropdown-item text-danger" onclick="takeAction(<?= $comment['id'] ?>, 'delete')"><i class="bi bi-trash me-2"></i>Delete</button></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><button class="dropdown-item" onclick="takeAction(<?= $comment['id'] ?>, 'warn')"><i class="bi bi-exclamation-triangle me-2"></i>Warn User</button></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No comments found.</td></tr>
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
            <a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) - 1 ?>&search=<?= e($search ?? '') ?>">Previous</a>
        </li>
        <?php for ($i = max(1, ($pagination['current_page'] ?? 1) - 2); $i <= min($pagination['last_page'] ?? 1, ($pagination['current_page'] ?? 1) + 2); $i++): ?>
            <li class="page-item <?= $i === ($pagination['current_page'] ?? 1) ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&search=<?= e($search ?? '') ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= !($pagination['has_more_pages'] ?? false) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) + 1 ?>&search=<?= e($search ?? '') ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/moderator/comments/action') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="comment_id" id="actionCommentId">
                <input type="hidden" name="action" id="actionType">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason (optional)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Provide a reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function takeAction(commentId, action) {
    document.getElementById('actionCommentId').value = commentId;
    document.getElementById('actionType').value = action;
    new bootstrap.Modal(document.getElementById('actionModal')).show();
}
</script>
