<?php $__layout = 'layouts.app'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Video</h4>
    <a href="<?= url('/creator/videos') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Videos</a>
</div>

<?php
$statusColors = ['published' => 'success', 'draft' => 'secondary', 'processing' => 'warning', 'live' => 'danger', 'error' => 'danger'];
$color = $statusColors[$video['status']] ?? 'secondary';
?>

<div class="alert alert-info d-flex align-items-center mb-4">
    <i class="bi bi-info-circle me-2"></i>
    Status: <span class="badge bg-<?= $color ?> ms-2"><?= ucfirst($video['status']) ?></span>
    <span class="ms-3">Visibility: <strong><?= ucfirst($video['visibility']) ?></strong></span>
    <?php if (!empty($video['file_path'])): ?>
        <span class="ms-3">File: <strong><?= e(basename($video['file_path'])) ?></strong></span>
    <?php endif; ?>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Video Details</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('/creator/videos/' . $video['id'] . '/update') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= e($video['title']) ?>" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="8" maxlength="5000"><?= e($video['description'] ?? '') ?></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select...</option>
                                <?php foreach (($categories ?? []) as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (int) ($video['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="visibility" class="form-label">Visibility</label>
                            <select class="form-select" id="visibility" name="visibility">
                                <?php foreach (['public' => 'Public', 'unlisted' => 'Unlisted', 'private' => 'Private'] as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= ($video['visibility'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags</label>
                        <input type="text" class="form-control" id="tags" name="tags" value="<?= e(implode(', ', json_decode($video['tags'] ?? '[]', true) ?? [])) ?>" placeholder="tag1, tag2">
                        <div class="form-text">Separate tags with commas.</div>
                    </div>
                    <div class="mb-3">
                        <label for="thumbnail" class="form-label">Update Thumbnail</label>
                        <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/gif,image/webp">
                        <div class="form-text">Leave empty to keep current thumbnail.</div>
                    </div>
                    <div id="thumbPreviewWrap" class="mb-3" style="display:none;">
                        <img id="thumbPrev" src="" alt="" class="rounded" style="max-width:300px;max-height:170px;">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                </form>
            </div>
        </div>

        <?php if (!empty($video['file_path'])): ?>
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Video Preview</h6></div>
            <div class="card-body">
                <video controls class="w-100" style="max-height:400px;background:#000;">
                    <source src="<?= e($video['file_path']) ?>" type="video/mp4">
                    Your browser does not support video.
                </video>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Current Thumbnail</h6></div>
            <div class="card-body text-center">
                <?php if (!empty($video['thumbnail'])): ?>
                    <img src="<?= e($video['thumbnail']) ?>" alt="" class="rounded" style="width:100%;max-width:300px;">
                <?php else: ?>
                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="height:170px;"><i class="bi bi-image fs-1 text-muted"></i></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Video Stats</h6></div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Views</span><strong><?= format_number((int) $video['view_count']) ?></strong></li>
                    <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Likes</span><strong><?= format_number((int) $video['like_count']) ?></strong></li>
                    <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Comments</span><strong><?= format_number((int) $video['comment_count']) ?></strong></li>
                    <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Duration</span><strong><?= !empty($video['duration']) ? gmdate('i:s', (int) $video['duration']) : 'N/A' ?></strong></li>
                    <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">File Size</span><strong><?= !empty($video['file_size']) ? round((int) $video['file_size'] / 1024 / 1024, 1) . ' MB' : 'N/A' ?></strong></li>
                    <li class="d-flex justify-content-between py-2"><span class="text-muted">Published</span><strong><?= !empty($video['published_at']) ? date('M d, Y', strtotime($video['published_at'])) : 'N/A' ?></strong></li>
                </ul>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Danger Zone</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('/creator/videos/' . $video['id'] . '/delete') ?>" onsubmit="return confirm('Are you sure you want to delete this video? This action cannot be undone.')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-trash me-1"></i>Delete Video</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('thumbnail')?.addEventListener('change', function(e) {
    if (e.target.files.length > 0) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('thumbPrev').src = ev.target.result;
            document.getElementById('thumbPreviewWrap').style.display = 'block';
        };
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>
