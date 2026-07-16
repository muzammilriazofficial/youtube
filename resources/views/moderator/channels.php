<?php $__layout = 'layouts.moderator'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Manage Channels</h4>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/moderator/channels') ?>" class="row g-2">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" placeholder="Search channels..." value="<?= e($search ?? '') ?>">
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
                        <th>Channel</th>
                        <th>Owner</th>
                        <th>Subscribers</th>
                        <th>Videos</th>
                        <th>Verified</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($channels ?? [])): ?>
                        <?php foreach ($channels as $channel): ?>
                        <tr>
                            <td class="fw-medium"><?= e($channel['name']) ?></td>
                            <td class="small"><?= e($channel['username'] ?? '') ?></td>
                            <td><?= format_number((int) ($channel['subscriber_count'] ?? 0)) ?></td>
                            <td><?= format_number((int) ($channel['video_count'] ?? 0)) ?></td>
                            <td>
                                <?= !empty($channel['is_verified']) ? '<span class="badge bg-success">Verified</span>' : '<span class="badge bg-secondary">No</span>' ?>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Action</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <?php if (empty($channel['is_verified'])): ?>
                                            <li><button class="dropdown-item" onclick="takeChannelAction(<?= $channel['id'] ?>, 'verify')"><i class="bi bi-patch-check me-2"></i>Verify</button></li>
                                        <?php else: ?>
                                            <li><button class="dropdown-item" onclick="takeChannelAction(<?= $channel['id'] ?>, 'unverify')"><i class="bi bi-x-octagon me-2"></i>Unverify</button></li>
                                        <?php endif; ?>
                                        <li><button class="dropdown-item text-warning" onclick="takeChannelAction(<?= $channel['id'] ?>, 'warn')"><i class="bi bi-exclamation-triangle me-2"></i>Warn</button></li>
                                        <li><button class="dropdown-item text-danger" onclick="takeChannelAction(<?= $channel['id'] ?>, 'suspend')"><i class="bi bi-pause-circle me-2"></i>Suspend</button></li>
                                        <li><button class="dropdown-item text-danger" onclick="takeChannelAction(<?= $channel['id'] ?>, 'ban')"><i class="bi bi-slash-circle me-2"></i>Ban</button></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No channels found.</td></tr>
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

<div class="modal fade" id="channelActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/moderator/channels/action') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="channel_id" id="actionChannelId">
                <input type="hidden" name="action" id="actionType">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason (required for ban/suspend)</label>
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
