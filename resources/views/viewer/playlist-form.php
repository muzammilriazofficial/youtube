<?php $__layout = 'layouts.dashboard'; ?>

<h4 class="mb-4"><?= ($playlist ?? null) ? 'Edit Playlist' : 'Create Playlist' ?></h4>

<div class="row">
    <div class="col-md-8">
        <form method="POST" action="<?= ($playlist ?? null) ? url('/viewer/playlists/update/' . $playlist['id']) : url('/viewer/playlists/store') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" class="form-control" name="title" value="<?= e($playlist['title'] ?? '') ?>" required maxlength="150">
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3" maxlength="500"><?= e($playlist['description'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Visibility</label>
                <select class="form-select" name="visibility">
                    <option value="private" <?= ($playlist['visibility'] ?? 'private') === 'private' ? 'selected' : '' ?>>Private</option>
                    <option value="unlisted" <?= ($playlist['visibility'] ?? '') === 'unlisted' ? 'selected' : '' ?>>Unlisted</option>
                    <option value="public" <?= ($playlist['visibility'] ?? '') === 'public' ? 'selected' : '' ?>>Public</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><?= ($playlist ?? null) ? 'Update' : 'Create' ?></button>
            <a href="<?= url('/viewer/playlists') ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
