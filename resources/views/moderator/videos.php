<?php $__layout = 'layouts.moderator'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Manage Videos</h4>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/moderator/videos') ?>" class="row g-2">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search videos..." value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <?php foreach (['pending', 'processing', 'published', 'rejected'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($status ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
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
                        <th>Video</th>
                        <th>Channel</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th class="text-end">Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($videos ?? [])): ?>
                        <?php foreach ($videos as $video): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2" style="width:80px;height:45px;overflow:hidden;border-radius:4px;background:var(--bs-secondary);">
                                        <?php if (!empty($video['thumbnail_path'])): ?>
                                            <img src="<?= e($video['thumbnail_path']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center h-100"><i class="bi bi-play-circle"></i></div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <span class="fw-medium"><?= e(mb_substr($video['title'], 0, 40)) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="small"><?= e($video['name'] ?? '') ?></td>
                            <td>
                                <?php
                                    $statusColors = ['published' => 'success', 'pending' => 'warning', 'processing' => 'info', 'rejected' => 'danger'];
                                ?>
                                <span class="badge bg-<?= $statusColors[$video['status']] ?? 'secondary' ?>"><?= ucfirst($video['status']) ?></span>
                            </td>
                            <td><?= format_number((int) ($video['views_count'] ?? 0)) ?></td>
                            <td class="text-end text-muted small"><?= date('M d, Y', strtotime($video['created_at'])) ?></td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Action</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><button class="dropdown-item text-success" onclick="takeAction(<?= $video['id'] ?>, 'approve')"><i class="bi bi-check-circle me-2"></i>Approve</button></li>
                                        <li><button class="dropdown-item text-warning" onclick="takeAction(<?= $video['id'] ?>, 'reject')"><i class="bi bi-x-circle me-2"></i>Reject</button></li>
                                        <li><button class="dropdown-item text-danger" onclick="takeAction(<?= $video['id'] ?>, 'remove')"><i class="bi bi-trash me-2"></i>Remove</button></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><button class="dropdown-item" onclick="takeAction(<?= $video['id'] ?>, 'strike')"><i class="bi bi-exclamation-diamond me-2"></i>Strike Channel</button></li>
                                        <li><button class="dropdown-item" onclick="takeAction(<?= $video['id'] ?>, 'warn')"><i class="bi bi-exclamation-triangle me-2"></i>Warn Creator</button></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No videos found.</td></tr>
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
            <a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) - 1 ?>&search=<?= e($search ?? '') ?>&status=<?= e($status ?? '') ?>">Previous</a>
        </li>
        <?php for ($i = max(1, ($pagination['current_page'] ?? 1) - 2); $i <= min($pagination['last_page'] ?? 1, ($pagination['current_page'] ?? 1) + 2); $i++): ?>
            <li class="page-item <?= $i === ($pagination['current_page'] ?? 1) ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&search=<?= e($search ?? '') ?>&status=<?= e($status ?? '') ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= !($pagination['has_more_pages'] ?? false) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) + 1 ?>&search=<?= e($search ?? '') ?>&status=<?= e($status ?? '') ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/moderator/videos/action') ?>" id="actionForm">
                <?= csrf_field() ?>
                <input type="hidden" name="video_id" id="actionVideoId">
                <input type="hidden" name="action" id="actionType">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="actionDescription"></p>
                    <div class="mb-3">
                        <label class="form-label">Reason (optional)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Provide a reason for this action..."></textarea>
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
function takeAction(videoId, action) {
    document.getElementById('actionVideoId').value = videoId;
    document.getElementById('actionType').value = action;
    const descriptions = {
        approve: 'Approve this video and make it published.',
        reject: 'Reject this video. It will not be visible.',
        remove: 'Remove this video permanently.',
        strike: 'Issue a channel strike for this video.',
        warn: 'Send a warning to the creator.'
    };
    document.getElementById('actionDescription').textContent = descriptions[action] || 'Take action on this video.';
    new bootstrap.Modal(document.getElementById('actionModal')).show();
}
</script>
