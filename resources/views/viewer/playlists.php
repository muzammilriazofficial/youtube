<?php $__layout = 'layouts.dashboard'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">My Playlists</h4>
    <a href="<?= url('/viewer/playlists/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> New Playlist</a>
</div>
<div class="row g-3">
    <?php foreach (($playlists ?? []) as $pl): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <a href="<?= url('/viewer/playlists/' . $pl['id']) ?>" class="text-decoration-none">
                <div class="card bg-secondary bg-opacity-25 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-collection-play fs-4 me-2"></i>
                            <h6 class="mb-0" style="color: var(--bs-body-color)"><?= e($pl['title']) ?></h6>
                        </div>
                        <small class="text-muted"><?= $pl['video_count'] ?? 0 ?> videos &middot; <?= e($pl['visibility'] ?? 'private') ?></small>
                        <div class="mt-2">
                            <a href="<?= url('/viewer/playlists/edit/' . $pl['id']) ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                            <form method="POST" action="<?= url('/viewer/playlists/delete/' . $pl['id']) ?>" class="d-inline" onsubmit="return confirm('Delete this playlist?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
    <?php if (empty($playlists ?? [])): ?>
        <div class="col-12 text-center py-5 text-muted"><i class="bi bi-collection-play fs-1 d-block mb-2"></i>No playlists yet.</div>
    <?php endif; ?>
</div>
