@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Social Login Settings</h4>
</div>

<?php $s = $settings ?? []; ?>

<div class="card table-card">
    <div class="card-body p-4">
        <form method="POST" action="<?= url('/admin/social-login/update') ?>">
            <?= csrf_field() ?>
            <h6 class="fw-bold mb-3">Google</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4"><label class="form-label fw-semibold">Enabled</label><select class="form-select" name="google_enabled"><option value="1" <?= ($s['google_enabled'] ?? '') === '1' ? 'selected' : '' ?>>Yes</option><option value="0" <?= ($s['google_enabled'] ?? '') !== '1' ? 'selected' : '' ?>>No</option></select></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Client ID</label><input type="text" class="form-control" name="google_client_id" value="<?= e($s['google_client_id'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Client Secret</label><input type="password" class="form-control" name="google_client_secret" value="<?= e($s['google_client_secret'] ?? '') ?>"></div>
            </div>
            <h6 class="fw-bold mb-3">Facebook</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4"><label class="form-label fw-semibold">Enabled</label><select class="form-select" name="facebook_enabled"><option value="1" <?= ($s['facebook_enabled'] ?? '') === '1' ? 'selected' : '' ?>>Yes</option><option value="0" <?= ($s['facebook_enabled'] ?? '') !== '1' ? 'selected' : '' ?>>No</option></select></div>
                <div class="col-md-4"><label class="form-label fw-semibold">App ID</label><input type="text" class="form-control" name="facebook_app_id" value="<?= e($s['facebook_app_id'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">App Secret</label><input type="password" class="form-control" name="facebook_app_secret" value="<?= e($s['facebook_app_secret'] ?? '') ?>"></div>
            </div>
            <h6 class="fw-bold mb-3">GitHub</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4"><label class="form-label fw-semibold">Enabled</label><select class="form-select" name="github_enabled"><option value="1" <?= ($s['github_enabled'] ?? '') === '1' ? 'selected' : '' ?>>Yes</option><option value="0" <?= ($s['github_enabled'] ?? '') !== '1' ? 'selected' : '' ?>>No</option></select></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Client ID</label><input type="text" class="form-control" name="github_client_id" value="<?= e($s['github_client_id'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Client Secret</label><input type="password" class="form-control" name="github_client_secret" value="<?= e($s['github_client_secret'] ?? '') ?>"></div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Social Login Settings</button>
        </form>
    </div>
</div>
@endsection
