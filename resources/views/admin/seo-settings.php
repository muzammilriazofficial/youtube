@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">SEO Settings</h4>
</div>

<?php $s = $settings ?? []; ?>

<div class="card table-card">
    <div class="card-body p-4">
        <form method="POST" action="<?= url('/admin/seo-settings/update') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Meta Title</label><input type="text" class="form-control" name="meta_title" value="<?= e($s['meta_title'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Meta Keywords</label><input type="text" class="form-control" name="meta_keywords" value="<?= e($s['meta_keywords'] ?? '') ?>"></div>
                <div class="col-12"><label class="form-label fw-semibold">Meta Description</label><textarea class="form-control" name="meta_description" rows="3"><?= e($s['meta_description'] ?? '') ?></textarea></div>
                <div class="col-md-6"><label class="form-label fw-semibold">OG Image URL</label><input type="url" class="form-control" name="og_image" value="<?= e($s['og_image'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Google Analytics ID</label><input type="text" class="form-control" name="google_analytics_id" value="<?= e($s['google_analytics_id'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Sitemap</label><select class="form-select" name="enable_sitemap"><option value="1" <?= ($s['enable_sitemap'] ?? '') === '1' ? 'selected' : '' ?>>Enabled</option><option value="0" <?= ($s['enable_sitemap'] ?? '') !== '1' ? 'selected' : '' ?>>Disabled</option></select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Robots.txt</label><select class="form-select" name="enable_robots_txt"><option value="1" <?= ($s['enable_robots_txt'] ?? '') === '1' ? 'selected' : '' ?>>Enabled</option><option value="0" <?= ($s['enable_robots_txt'] ?? '') !== '1' ? 'selected' : '' ?>>Disabled</option></select></div>
                <div class="col-12"><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save SEO Settings</button></div>
            </div>
        </form>
    </div>
</div>
@endsection
