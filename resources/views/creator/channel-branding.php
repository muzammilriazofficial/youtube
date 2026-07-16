<?php $__layout = 'layouts.app'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Channel Branding</h4>
    <a href="<?= url('/creator/channel') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Profile Picture (Avatar)</h6></div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <?php if (!empty($channel['avatar'])): ?>
                        <img src="<?= url(e($channel['avatar'])) ?>" alt="Avatar" class="rounded-circle" width="120" height="120" style="object-fit:cover;">
                    <?php else: ?>
                        <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width:120px;height:120px;"><i class="bi bi-person fs-1 text-muted"></i></div>
                    <?php endif; ?>
                </div>
                <form method="POST" action="<?= url('/creator/channel/branding/update') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3 text-start">
                        <label for="avatar" class="form-label">Upload new avatar</label>
                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp">
                        <div class="form-text">Recommended: 800x800px. JPEG, PNG, GIF or WebP. Max 4MB.</div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Upload Avatar</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Banner Image</h6></div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <?php if (!empty($channel['banner'])): ?>
                        <img src="<?= url(e($channel['banner'])) ?>" alt="Banner" class="rounded" style="width:100%;max-width:600px;height:140px;object-fit:cover;">
                    <?php else: ?>
                        <div class="bg-secondary rounded d-inline-flex align-items-center justify-content-center" style="width:100%;max-width:600px;height:140px;"><i class="bi bi-image fs-1 text-muted"></i></div>
                    <?php endif; ?>
                </div>
                <form method="POST" action="<?= url('/creator/channel/branding/update') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3 text-start">
                        <label for="banner" class="form-label">Upload new banner</label>
                        <input type="file" class="form-control" id="banner" name="banner" accept="image/jpeg,image/png,image/gif,image/webp">
                        <div class="form-text">Recommended: 2560x1440px. JPEG, PNG, GIF or WebP. Max 6MB.</div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Upload Banner</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header"><h6 class="mb-0">Branding Preview</h6></div>
    <div class="card-body">
        <div class="border rounded p-3">
            <div class="mb-3">
                <?php if (!empty($channel['banner'])): ?>
                    <img src="<?= url(e($channel['banner'])) ?>" alt="Banner" class="w-100 rounded" style="height:200px;object-fit:cover;">
                <?php else: ?>
                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="height:200px;"><i class="bi bi-image fs-1 text-muted"></i></div>
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center">
                <?php if (!empty($channel['avatar'])): ?>
                    <img src="<?= url(e($channel['avatar'])) ?>" alt="" class="rounded-circle me-3" width="64" height="64" style="object-fit:cover;margin-top:-32px;">
                <?php else: ?>
                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" style="width:64px;height:64px;margin-top:-32px;"><i class="bi bi-person fs-4"></i></div>
                <?php endif; ?>
                <div>
                    <h5 class="mb-0"><?= e($channel['name']) ?> <?php if (!empty($channel['is_verified'])): ?><i class="bi bi-patch-check-fill text-primary small"></i><?php endif; ?></h5>
                    <small class="text-muted"><?= format_number((int) $channel['subscriber_count']) ?> subscribers &middot; <?= format_number((int) $channel['video_count']) ?> videos</small>
                </div>
            </div>
        </div>
    </div>
</div>
