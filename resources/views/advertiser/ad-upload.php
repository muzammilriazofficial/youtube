<?php $__layout = 'layouts.advertiser'; ?>

<div class="mb-3">
    <a href="<?= url('/advertiser/ads') ?>" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i>Back to Ads</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Upload New Ad</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('/advertiser/ads/store') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Ad Title</label>
                        <input type="text" name="title" class="form-control" required maxlength="255" placeholder="e.g. Summer Sale Banner">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief description of your ad..."></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Ad Type</label>
                            <select name="type" class="form-select" required>
                                <option value="skippable">Skippable Video Ad</option>
                                <option value="non_skippable">Non-Skippable Video Ad</option>
                                <option value="bumper">Bumper Ad (6s)</option>
                                <option value="display">Display Ad</option>
                                <option value="overlay">Overlay Ad</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Target URL</label>
                            <input type="url" name="target_url" class="form-control" placeholder="https://example.com">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ad File (Video or Image)</label>
                        <input type="file" name="ad_file" class="form-control" accept="video/mp4,video/webm,image/jpeg,image/png,image/gif">
                        <div class="form-text">Accepted formats: MP4, WebM (video) or JPEG, PNG, GIF (image). Max 100MB.</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Upload Ad</button>
                        <a href="<?= url('/advertiser/ads') ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
