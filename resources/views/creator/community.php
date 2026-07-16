<?php $__layout = 'layouts.app'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Community Posts</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPostModal"><i class="bi bi-plus-lg me-1"></i>New Post</button>
</div>

<?php if (!empty($posts ?? [])): ?>
<div class="row g-3">
    <?php foreach ($posts as $post): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <?php if (!empty($channel['avatar'])): ?>
                        <img src="<?= url(e($channel['avatar'])) ?>" alt="" class="rounded-circle me-2" width="36" height="36" style="object-fit:cover;">
                    <?php else: ?>
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;"><i class="bi bi-person small"></i></div>
                    <?php endif; ?>
                    <div>
                        <small class="fw-medium"><?= e($channel['name']) ?></small><br>
                        <small class="text-muted"><?= date('M d, Y', strtotime($post['created_at'])) ?></small>
                    </div>
                </div>
                <p class="mb-2"><?= nl2br(e($post['content'])) ?></p>
                <?php if (!empty($post['image_path'])): ?>
                    <img src="<?= e($post['image_path']) ?>" alt="" class="rounded mb-2" style="width:100%;max-height:200px;object-fit:cover;">
                <?php endif; ?>
                <?php if (!empty($post['poll_options'])): ?>
                    <div class="border rounded p-2 mb-2">
                        <small class="text-muted d-block mb-1">Poll:</small>
                        <?php foreach (json_decode($post['poll_options'], true) ?? [] as $option): ?>
                            <div class="border rounded px-2 py-1 mb-1 small"><?= e($option) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <small class="text-muted"><i class="bi bi-hand-thumbs-up me-1"></i><?= $post['like_count'] ?? 0 ?></small>
                <small class="text-muted"><i class="bi bi-chat me-1"></i><?= $post['comment_count'] ?? 0 ?></small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (($pagination['last_page'] ?? 1) > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= !($pagination['has_prev_page'] ?? false) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) - 1 ?>">Prev</a></li>
        <li class="page-item <?= !($pagination['has_more_pages'] ?? false) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) + 1 ?>">Next</a></li>
    </ul>
</nav>
<?php endif; ?>

<?php else: ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-megaphone display-4 text-muted mb-3"></i>
        <h5>No community posts yet</h5>
        <p class="text-muted">Engage with your audience through community posts.</p>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal"><i class="bi bi-plus-lg me-1"></i>Create Post</button>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="createPostModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/creator/community') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Create Community Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="postType" class="form-label">Post Type</label>
                        <select class="form-select" id="postType" name="post_type">
                            <option value="text">Text</option>
                            <option value="image">Image</option>
                            <option value="poll">Poll</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="postContent" class="form-label">Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="postContent" name="content" rows="4" maxlength="10000" required placeholder="What do you want to share?"></textarea>
                        <div class="form-text">Max 10,000 characters.</div>
                    </div>
                    <div class="mb-3" id="imageUploadWrap" style="display:none;">
                        <label for="postImage" class="form-label">Image</label>
                        <input type="file" class="form-control" id="postImage" name="post_image" accept="image/jpeg,image/png,image/gif,image/webp">
                    </div>
                    <div class="mb-3" id="pollOptionsWrap" style="display:none;">
                        <label for="pollOptions" class="form-label">Poll Options (one per line)</label>
                        <textarea class="form-control" id="pollOptions" name="poll_options" rows="4" placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Post</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('postType')?.addEventListener('change', function() {
    document.getElementById('imageUploadWrap').style.display = this.value === 'image' ? 'block' : 'none';
    document.getElementById('pollOptionsWrap').style.display = this.value === 'poll' ? 'block' : 'none';
});
</script>
