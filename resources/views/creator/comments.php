<?php $__layout = 'layouts.app'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><?= ($showReported ?? false) ? 'Reported Comments' : 'Comments' ?></h4>
    <div>
        <a href="<?= url('/creator/comments') ?>" class="btn btn-sm <?= !($showReported ?? false) ? 'btn-primary' : 'btn-outline-primary' ?>">All Comments</a>
        <a href="<?= url('/creator/comments/reported') ?>" class="btn btn-sm <?= ($showReported ?? false) ? 'btn-warning' : 'btn-outline-warning' ?>"><i class="bi bi-flag me-1"></i>Reported</a>
    </div>
</div>

<?php if (!empty($comments ?? [])): ?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Comment</th>
                        <th>Video</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;flex-shrink:0;">
                                    <i class="bi bi-person small"></i>
                                </div>
                                <small class="fw-medium"><?= e($comment['username'] ?? 'User') ?></small>
                            </div>
                        </td>
                        <td style="max-width:300px;">
                            <p class="mb-0 small"><?= e(mb_substr($comment['body'], 0, 100)) ?></p>
                            <?php if (!empty($comment['like_count'])): ?>
                                <small class="text-muted"><i class="bi bi-hand-thumbs-up"></i> <?= $comment['like_count'] ?></small>
                            <?php endif; ?>
                        </td>
                        <td><small class="text-muted"><?= e(mb_substr($comment['video_title'] ?? '', 0, 30)) ?></small></td>
                        <td><small class="text-muted"><?= date('M d, H:i', strtotime($comment['created_at'])) ?></small></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <form method="POST" action="<?= url('/creator/comments/' . $comment['id'] . '/moderate') ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-outline-success" title="Approve"><i class="bi bi-check-lg"></i></button>
                                </form>
                                <form method="POST" action="<?= url('/creator/comments/' . $comment['id'] . '/moderate') ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="hide">
                                    <button type="submit" class="btn btn-outline-warning" title="Hide"><i class="bi bi-eye-slash"></i></button>
                                </form>
                                <form method="POST" action="<?= url('/creator/comments/' . $comment['id'] . '/moderate') ?>" class="d-inline" onsubmit="return confirm('Delete this comment?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (($pagination['last_page'] ?? 1) > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= !($pagination['has_prev_page'] ?? false) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) - 1 ?>">Prev</a></li>
        <?php for ($i = max(1, ($pagination['current_page'] ?? 1) - 2); $i <= min($pagination['last_page'] ?? 1, ($pagination['current_page'] ?? 1) + 2); $i++): ?>
            <li class="page-item <?= $i === ($pagination['current_page'] ?? 1) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
        <?php endfor; ?>
        <li class="page-item <?= !($pagination['has_more_pages'] ?? false) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) + 1 ?>">Next</a></li>
    </ul>
</nav>
<?php endif; ?>

<?php else: ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-chat-dots display-4 text-muted mb-3"></i>
        <h5>No comments found</h5>
        <p class="text-muted">Comments on your videos will appear here.</p>
    </div>
</div>
<?php endif; ?>
