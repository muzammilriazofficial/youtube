<?php $__layout = 'layouts.dashboard'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><?= e($playlist['title']) ?></h4>
        <small class="text-muted"><?= $playlist['video_count'] ?? 0 ?> videos &middot; <?= e($playlist['visibility'] ?? 'private') ?></small>
    </div>
    <div>
        <a href="<?= url('/viewer/playlists/edit/' . $playlist['id']) ?>" class="btn btn-outline-primary btn-sm">Edit</a>
    </div>
</div>

<?php if (!empty($playlist['description'])): ?>
    <p class="text-muted mb-4"><?= e($playlist['description']) ?></p>
<?php endif; ?>

<div class="row g-3">
    <?php foreach ($videos as $i => $vid): ?>
        <div class="col-12">
            <div class="d-flex align-items-center">
                <span class="text-muted me-3"><?= $i + 1 ?></span>
                <a href="<?= url('/video/' . e($vid['slug'])) ?>" class="flex-shrink-0" style="width:160px;">
                    <div class="thumbnail" style="aspect-ratio:16/9;">
                        <?php if (!empty($vid['thumbnail'])): ?>
                            <img src="<?= e($vid['thumbnail']) ?>" alt="" loading="lazy" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center h-100 bg-secondary rounded"><i class="bi bi-play-circle"></i></div>
                        <?php endif; ?>
                    </div>
                </a>
                <div class="ms-3 flex-grow-1">
                    <h6 class="mb-0"><a href="<?= url('/video/' . e($vid['slug'])) ?>" class="text-decoration-none" style="color:var(--bs-body-color)"><?= e($vid['title']) ?></a></h6>
                    <small class="text-muted"><?= format_number((int) ($vid['view_count'] ?? 0)) ?> views</small>
                </div>
                <form method="POST" action="<?= url('/viewer/playlists/remove-video') ?>" class="ms-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="playlist_id" value="<?= $playlist['id'] ?>">
                    <input type="hidden" name="video_id" value="<?= $vid['id'] ?>">
                    <button type="submit" class="btn btn-link text-muted"><i class="bi bi-x-lg"></i></button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($videos)): ?>
        <div class="col-12 text-center py-5 text-muted">No videos in this playlist.</div>
    <?php endif; ?>
</div>
