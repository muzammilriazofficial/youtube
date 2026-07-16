<?php $__layout = 'layouts.app'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Video Analytics</h4>
    <div>
        <a href="<?= url('/creator/analytics') ?>" class="btn btn-outline-secondary btn-sm me-2"><i class="bi bi-arrow-left me-1"></i>Overview</a>
        <a href="<?= url('/creator/analytics/revenue') ?>" class="btn btn-outline-secondary btn-sm me-2">Revenue</a>
        <a href="<?= url('/creator/analytics/audience') ?>" class="btn btn-outline-secondary btn-sm">Audience</a>
    </div>
</div>

<?php if (!empty($videos ?? [])): ?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Video</th>
                        <th class="text-end">Views</th>
                        <th class="text-end">Avg. View Duration</th>
                        <th class="text-end">CTR</th>
                        <th class="text-end">Likes</th>
                        <th class="text-end">Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($videos as $video): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="width:100px;height:56px;overflow:hidden;border-radius:4px;background:var(--bs-secondary);flex-shrink:0;">
                                    <?php if (!empty($video['thumbnail'])): ?>
                                        <img src="<?= e($video['thumbnail']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center h-100"><i class="bi bi-play-circle"></i></div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <span class="fw-medium d-block" style="max-width:300px;"><?= e(mb_substr($video['title'], 0, 50)) ?></span>
                                    <small class="text-muted">Published <?= date('M d, Y', strtotime($video['published_at'] ?? $video['created_at'])) ?></small>
                                </div>
                            </div>
                        </td>
                        <td class="text-end"><strong><?= format_number((int) $video['view_count']) ?></strong></td>
                        <td class="text-muted text-end"><?= number_format((float) ($video['avg_view_duration'] ?? 0), 1) ?>s</td>
                        <td class="text-end"><?= number_format((float) ($video['ctr'] ?? 0), 1) ?>%</td>
                        <td class="text-end"><?= format_number((int) $video['like_count']) ?></td>
                        <td class="text-end"><?= format_number((int) $video['comment_count']) ?></td>
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
        <i class="bi bi-graph-up display-4 text-muted mb-3"></i>
        <h5>No video analytics available</h5>
        <p class="text-muted">Publish videos to start seeing analytics data.</p>
    </div>
</div>
<?php endif; ?>
