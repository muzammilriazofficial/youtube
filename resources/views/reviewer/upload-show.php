<?php $__layout = 'layouts.reviewer'; ?>

<div class="mb-3">
    <a href="<?= url('/reviewer/uploads') ?>" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i>Back to Uploads</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Video Preview</h6></div>
            <div class="card-body">
                <?php if (!empty($video['file_path'])): ?>
                    <div class="ratio ratio-16x9 mb-3 bg-dark rounded overflow-hidden">
                        <video controls class="w-100 h-100">
                            <source src="<?= e($video['file_path']) ?>" type="video/mp4">
                        </video>
                    </div>
                <?php else: ?>
                    <div class="ratio ratio-16x9 mb-3 bg-dark rounded d-flex align-items-center justify-content-center">
                        <i class="bi bi-play-circle fs-1 text-muted"></i>
                    </div>
                <?php endif; ?>

                <h5><?= e($video['title']) ?></h5>
                <p class="text-muted small mb-0"><?= e($video['description'] ?? 'No description.') ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Metadata</h6></div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-sm-3 text-muted">Category</div>
                    <div class="col-sm-9"><?= e($video['category_name'] ?? 'N/A') ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-3 text-muted">Visibility</div>
                    <div class="col-sm-9"><span class="badge bg-info text-capitalize"><?= e($video['visibility']) ?></span></div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-3 text-muted">Duration</div>
                    <div class="col-sm-9"><?= !empty($video['duration']) ? gmdate('i:s', (int) $video['duration']) : 'N/A' ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-3 text-muted">File Size</div>
                    <div class="col-sm-9"><?= !empty($video['file_size']) ? human_file_size((int) $video['file_size']) : 'N/A' ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-3 text-muted">Tags</div>
                    <div class="col-sm-9"><?= e($video['seo_keywords'] ?? 'None') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Channel Info</h6></div>
            <div class="card-body">
                <h6><?= e($video['name'] ?? '') ?></h6>
                <p class="text-muted small mb-1">Owner: <?= e($video['username'] ?? '') ?></p>
                <p class="text-muted small mb-1">Subscribers: <?= format_number((int) ($channelStats['subscriber_count'] ?? 0)) ?></p>
                <p class="text-muted small mb-0">Videos: <?= format_number((int) ($channelStats['video_count'] ?? 0)) ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Review Actions</h6></div>
            <div class="card-body">
                <p class="text-muted small mb-3">Review this upload and decide whether to publish or reject it.</p>
                <div class="d-grid gap-2">
                    <form method="POST" action="<?= url('/reviewer/uploads/' . $video['id'] . '/approve') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-success w-100"><i class="bi bi-check-circle me-1"></i>Approve & Publish</button>
                    </form>
                    <button class="btn btn-danger w-100" data-bs-toggle="collapse" data-bs-target="#rejectForm"><i class="bi bi-x-circle me-1"></i>Reject</button>
                </div>
                <div class="collapse mt-3" id="rejectForm">
                    <form method="POST" action="<?= url('/reviewer/uploads/' . $video['id'] . '/reject') ?>">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason</label>
                            <textarea name="reason" class="form-control" rows="3" required placeholder="Explain why this video is being rejected..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Confirm Rejection</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
