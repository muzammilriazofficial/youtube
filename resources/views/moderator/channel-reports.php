<?php $__layout = 'layouts.moderator'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Reported Channels</h4>
    <a href="<?= url('/moderator/reports') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>All Reports</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Reporter</th>
                        <th>Channel</th>
                        <th>Owner</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reports ?? [])): ?>
                        <?php foreach ($reports as $report): ?>
                        <tr>
                            <td class="small"><?= e($report['username'] ?? 'User') ?></td>
                            <td class="fw-medium"><?= e($report['name'] ?? 'Deleted') ?></td>
                            <td class="small"><?= e($report['channel_user.username'] ?? '') ?></td>
                            <td><span class="badge bg-danger text-capitalize"><?= e($report['reason']) ?></span></td>
                            <td>
                                <?php $statusColors = ['pending' => 'warning', 'resolved' => 'success', 'dismissed' => 'secondary']; ?>
                                <span class="badge bg-<?= $statusColors[$report['status']] ?? 'secondary' ?>"><?= ucfirst($report['status']) ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('/moderator/reports/' . $report['id']) ?>" class="btn btn-sm btn-outline-primary">Review</a>
                                <button class="btn btn-sm btn-warning" onclick="takeChannelAction(<?= $report['reportable_id'] ?>, 'warn')">Warn</button>
                                <button class="btn btn-sm btn-danger" onclick="takeChannelAction(<?= $report['reportable_id'] ?>, 'ban')">Ban</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No reported channels.</td></tr>
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

<div class="modal fade" id="channelActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/moderator/channels/action') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="channel_id" id="actionChannelId">
                <input type="hidden" name="action" id="actionType">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
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
function takeChannelAction(channelId, action) {
    document.getElementById('actionChannelId').value = channelId;
    document.getElementById('actionType').value = action;
    new bootstrap.Modal(document.getElementById('channelActionModal')).show();
}
</script>
