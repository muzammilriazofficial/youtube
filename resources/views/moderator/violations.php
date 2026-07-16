<?php $__layout = 'layouts.moderator'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Violations Log</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addViolationModal"><i class="bi bi-plus me-1"></i>Log Violation</button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Action Taken</th>
                        <th>Moderator</th>
                        <th class="text-end">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($violations ?? [])): ?>
                        <?php foreach ($violations as $v): ?>
                        <tr>
                            <td><?= $v['id'] ?></td>
                            <td><span class="badge bg-<?= $v['type'] === 'ban' ? 'danger' : ($v['type'] === 'warning' ? 'warning' : 'secondary') ?> text-capitalize"><?= e($v['type']) ?></span></td>
                            <td class="text-truncate" style="max-width:300px;"><?= e($v['description']) ?></td>
                            <td class="small"><?= e($v['action_taken']) ?></td>
                            <td class="small"><?= e($v['username'] ?? 'Mod') ?></td>
                            <td class="text-end text-muted small"><?= time_ago($v['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No violations logged.</td></tr>
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

<div class="modal fade" id="addViolationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/moderator/violations/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Log New Violation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <input type="text" name="type" class="form-control" required placeholder="e.g. spam, harassment, copyright">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Action Taken</label>
                        <input type="text" name="action_taken" class="form-control" required placeholder="e.g. Warning issued, Video removed">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Video ID (optional)</label>
                            <input type="number" name="video_id" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Comment ID (optional)</label>
                            <input type="number" name="comment_id" class="form-control">
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Channel ID (optional)</label>
                            <input type="number" name="channel_id" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Target User ID (optional)</label>
                            <input type="number" name="target_user_id" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Log Violation</button>
                </div>
            </form>
        </div>
    </div>
</div>
