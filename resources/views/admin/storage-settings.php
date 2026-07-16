@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Storage Settings</h4>
</div>

<?php $s = $settings ?? []; ?>

<div class="card table-card">
    <div class="card-body p-4">
        <form method="POST" action="<?= url('/admin/storage-settings/update') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label fw-semibold">Storage Driver</label>
                    <select class="form-select" name="storage_driver"><option value="local" <?= ($s['storage_driver'] ?? '') === 'local' ? 'selected' : '' ?>>Local</option><option value="s3" <?= ($s['storage_driver'] ?? '') === 's3' ? 'selected' : '' ?>>Amazon S3</option></select>
                </div>
                <div class="col-md-4"><label class="form-label fw-semibold">S3 Key</label><input type="text" class="form-control" name="s3_key" value="<?= e($s['s3_key'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">S3 Secret</label><input type="password" class="form-control" name="s3_secret" value="<?= e($s['s3_secret'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">S3 Region</label><input type="text" class="form-control" name="s3_region" value="<?= e($s['s3_region'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">S3 Bucket</label><input type="text" class="form-control" name="s3_bucket" value="<?= e($s['s3_bucket'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">S3 Endpoint</label><input type="text" class="form-control" name="s3_endpoint" value="<?= e($s['s3_endpoint'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Max Upload Size (MB)</label><input type="number" class="form-control" name="max_upload_size" value="<?= e($s['max_upload_size'] ?? '500') ?>"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Allowed Video Formats</label><input type="text" class="form-control" name="allowed_video_formats" value="<?= e($s['allowed_video_formats'] ?? 'mp4,mkv,avi,mov') ?>"></div>
                <div class="col-12"><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Storage Settings</button></div>
            </div>
        </form>
    </div>
</div>
@endsection
