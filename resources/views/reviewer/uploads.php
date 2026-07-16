<?php $__layout = 'layouts.reviewer'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Pending Uploads</h4>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Video</th>
                        <th>Channel</th>
                        <th>Category</th>
                        <th>Duration</th>
                        <th class="text-end">Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($uploads ?? [])): ?>
                        <?php foreach ($uploads as $video): ?>
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
                                    <span class="fw-medium small"><?= e(mb_substr($video['title'], 0, 35)) ?></span>
                                </div>
                            </td>
                            <td class="small"><?= e($video['name'] ?? '') ?></td>
                            <td class="small"><?= e($video['category_name'] ?? 'N/A') ?></td>
                            <td class="small"><?= !empty($video['duration']) ? gmdate('i:s', (int) $video['duration']) : 'N/A' ?></td>
                            <td class="text-end text-muted small"><?= date('M d', strtotime($video['created_at'])) ?></td>
                            <td class="text-end">
                                <a href="<?= url('/reviewer/uploads/' . $video['id']) ?>" class="btn btn-sm btn-primary">Review</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No pending uploads. All clear!</td></tr>
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
