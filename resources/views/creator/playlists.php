<?php $__layout = 'layouts.app'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Playlists</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPlaylistModal"><i class="bi bi-plus-lg me-1"></i>Create Playlist</button>
</div>

<?php if (!empty($playlists ?? [])): ?>
<div class="row g-3">
    <?php foreach ($playlists as $playlist): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="position-relative" style="height:140px;background:var(--bs-secondary);overflow:hidden;">
                <?php if (!empty($playlist['thumbnail'])): ?>
                    <img src="<?= e($playlist['thumbnail']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center h-100"><i class="bi bi-collection-play fs-1 text-muted"></i></div>
                <?php endif; ?>
                <div class="position-absolute bottom-0 end-0 bg-dark text-white px-2 py-1" style="font-size:0.75rem;">
                    <?= $playlist['video_count'] ?? 0 ?> videos
                </div>
            </div>
            <div class="card-body">
                <h6 class="card-title mb-1"><?= e($playlist['title']) ?></h6>
                <p class="card-text small text-muted mb-2"><?= e(mb_substr($playlist['description'] ?? '', 0, 80)) ?></p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-secondary"><?= ucfirst($playlist['visibility']) ?></span>
                    <small class="text-muted"><?= date('M d, Y', strtotime($playlist['updated_at'])) ?></small>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (($pagination['last_page'] ?? 1) > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= !($pagination['has_prev_page'] ?? false) ? 'disabled' : '' ?>"><a class="page-link" href="<?= url('/creator/playlists', ['page' => ($pagination['current_page'] ?? 1) - 1]) ?>">Prev</a></li>
        <li class="page-item <?= !($pagination['has_more_pages'] ?? false) ? 'disabled' : '' ?>"><a class="page-link" href="<?= url('/creator/playlists', ['page' => ($pagination['current_page'] ?? 1) + 1]) ?>">Next</a></li>
    </ul>
</nav>
<?php endif; ?>

<?php else: ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-collection-play display-4 text-muted mb-3"></i>
        <h5>No playlists yet</h5>
        <p class="text-muted">Create playlists to organize your videos.</p>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPlaylistModal"><i class="bi bi-plus-lg me-1"></i>Create Playlist</button>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="createPlaylistModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/creator/playlists') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Create Playlist</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="plTitle" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="plTitle" name="title" required maxlength="150" placeholder="Playlist title">
                    </div>
                    <div class="mb-3">
                        <label for="plDesc" class="form-label">Description</label>
                        <textarea class="form-control" id="plDesc" name="description" rows="3" maxlength="5000"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="plVis" class="form-label">Visibility</label>
                        <select class="form-select" id="plVis" name="visibility">
                            <option value="private">Private</option>
                            <option value="public">Public</option>
                            <option value="unlisted">Unlisted</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
