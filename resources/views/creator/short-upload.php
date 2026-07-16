<?php $__layout = 'layouts.app'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Upload Short</h4>
    <a href="<?= url('/creator/shorts') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Shorts</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Short Details</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('/creator/shorts') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Short Video <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="short_video" accept="video/mp4,video/webm,video/quicktime" required>
                        <div class="form-text">MP4, WebM, MOV. Max 100MB. Max 60 seconds.</div>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required maxlength="100" placeholder="Give your short a title">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" maxlength="5000" placeholder="Describe your short..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="visibility" class="form-label">Visibility</label>
                        <select class="form-select" id="visibility" name="visibility">
                            <option value="public">Public</option>
                            <option value="unlisted">Unlisted</option>
                            <option value="private">Private</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="thumbnail" class="form-label">Custom Thumbnail</label>
                        <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/gif,image/webp">
                        <div class="form-text">Leave empty to auto-generate.</div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-lightning me-1"></i>Upload Short</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Shorts Guidelines</h6></div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Max 60 seconds long</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Vertical format (9:16) recommended</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>MP4, WebM, or MOV format</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Max file size: 100MB</li>
                    <li><i class="bi bi-check-circle text-success me-2"></i>Add an engaging title</li>
                </ul>
            </div>
        </div>
    </div>
</div>
