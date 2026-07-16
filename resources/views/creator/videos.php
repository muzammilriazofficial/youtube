<?php $__layout = 'layouts.app'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Videos</h4>
    <a href="<?= url('/creator/videos/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-upload me-1"></i>Upload Video</a>
</div>

<ul class="nav nav-tabs mb-3">
    <?php
    $filters = [
        'all' => 'All',
        'published' => 'Published',
        'processing' => 'Processing',
        'private' => 'Private',
        'unlisted' => 'Unlisted',
        'error' => 'Errors',
    ];
    foreach ($filters as $key => $label):
    ?>
        <li class="nav-item">
            <a class="nav-link <?= ($filter ?? 'all') === $key ? 'active' : '' ?>" href="<?= url('/creator/videos', ['filter' => $key]) ?>"><?= $label ?></a>
        </li>
    <?php endforeach; ?>
</ul>

<?php if (!empty($videos ?? [])): ?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:40px;"><input type="checkbox" class="form-check-input" id="selectAll"></th>
                        <th>Video</th>
                        <th>Status</th>
                        <th>Visibility</th>
                        <th class="text-end">Views</th>
                        <th class="text-end">Comments</th>
                        <th class="text-end">Likes</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($videos as $video): ?>
                    <tr>
                        <td><input type="checkbox" class="form-check-input video-checkbox" value="<?= $video['id'] ?>"></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-2 position-relative" style="width:100px;height:56px;overflow:hidden;border-radius:4px;background:var(--bs-secondary);flex-shrink:0;">
                                    <?php if (!empty($video['thumbnail'])): ?>
                                        <img src="<?= e($video['thumbnail']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center h-100"><i class="bi bi-play-circle"></i></div>
                                    <?php endif; ?>
                                    <?php if (!empty($video['duration'])): ?>
                                        <span class="position-absolute bottom-0 end-0 bg-dark text-white px-1" style="font-size:0.65rem;"><?= gmdate('i:s', (int) $video['duration']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <a href="<?= url('/creator/videos/' . $video['id'] . '/edit') ?>" class="text-decoration-none text-body fw-medium d-block" style="max-width:300px;"><?= e($video['title']) ?></a>
                                    <small class="text-muted">ID: <?= $video['id'] ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php
                            $statusColors = ['published' => 'success', 'draft' => 'secondary', 'processing' => 'warning', 'live' => 'danger', 'error' => 'danger'];
                            $color = $statusColors[$video['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $color ?>"><?= ucfirst($video['status']) ?></span>
                        </td>
                        <td><span class="text-muted small"><?= ucfirst($video['visibility']) ?></span></td>
                        <td class="text-end"><?= format_number((int) $video['view_count']) ?></td>
                        <td class="text-end"><?= format_number((int) $video['comment_count']) ?></td>
                        <td class="text-end"><?= format_number((int) $video['like_count']) ?></td>
                        <td><small class="text-muted"><?= date('M d, Y', strtotime($video['created_at'])) ?></small></td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="<?= url('/creator/videos/' . $video['id'] . '/edit') ?>"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                    <?php if ($video['status'] === 'published'): ?>
                                        <li><a class="dropdown-item" href="<?= url('/video/' . e($video['slug'])) ?>" target="_blank"><i class="bi bi-eye me-2"></i>View</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="<?= url('/creator/videos/' . $video['id'] . '/delete') ?>" onsubmit="return confirm('Move this video to trash?')">
                                            <?= csrf_field() ?>
                                            <button class="dropdown-item text-danger" type="submit"><i class="bi bi-trash me-2"></i>Delete</button>
                                        </form>
                                    </li>
                                </ul>
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
        <li class="page-item <?= !($pagination['has_prev_page'] ?? false) ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= url('/creator/videos', ['filter' => $filter, 'page' => ($pagination['current_page'] ?? 1) - 1]) ?>">Previous</a>
        </li>
        <?php for ($i = max(1, ($pagination['current_page'] ?? 1) - 2); $i <= min($pagination['last_page'] ?? 1, ($pagination['current_page'] ?? 1) + 2); $i++): ?>
            <li class="page-item <?= $i === ($pagination['current_page'] ?? 1) ? 'active' : '' ?>">
                <a class="page-link" href="<?= url('/creator/videos', ['filter' => $filter, 'page' => $i]) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= !($pagination['has_more_pages'] ?? false) ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= url('/creator/videos', ['filter' => $filter, 'page' => ($pagination['current_page'] ?? 1) + 1]) ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php else: ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-play-circle display-4 text-muted mb-3"></i>
        <h5>No videos found</h5>
        <p class="text-muted">Upload your first video to get started.</p>
        <a href="<?= url('/creator/videos/create') ?>" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Upload Video</a>
    </div>
</div>
<?php endif; ?>
