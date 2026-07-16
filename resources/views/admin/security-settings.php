@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Security Settings</h4>
</div>

<?php $s = $settings ?? []; ?>

<div class="card table-card">
    <div class="card-body p-4">
        <form method="POST" action="<?= url('/admin/security-settings/update') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label fw-semibold">Two-Factor Auth</label>
                    <select class="form-select" name="two_factor_enabled"><option value="1" <?= ($s['two_factor_enabled'] ?? '') === '1' ? 'selected' : '' ?>>Enabled</option><option value="0" <?= ($s['two_factor_enabled'] ?? '') !== '1' ? 'selected' : '' ?>>Disabled</option></select>
                </div>
                <div class="col-md-4"><label class="form-label fw-semibold">Max Login Attempts</label><input type="number" class="form-control" name="max_login_attempts" value="<?= e($s['max_login_attempts'] ?? '5') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Lockout Duration (min)</label><input type="number" class="form-control" name="lockout_duration" value="<?= e($s['lockout_duration'] ?? '15') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Min Password Length</label><input type="number" class="form-control" name="password_min_length" value="<?= e($s['password_min_length'] ?? '8') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Email Verification</label>
                    <select class="form-select" name="require_email_verification"><option value="1" <?= ($s['require_email_verification'] ?? '') === '1' ? 'selected' : '' ?>>Required</option><option value="0" <?= ($s['require_email_verification'] ?? '') !== '1' ? 'selected' : '' ?>>Not Required</option></select>
                </div>
                <div class="col-md-4"><label class="form-label fw-semibold">reCAPTCHA</label>
                    <select class="form-select" name="enable_recaptcha"><option value="1" <?= ($s['enable_recaptcha'] ?? '') === '1' ? 'selected' : '' ?>>Enabled</option><option value="0" <?= ($s['enable_recaptcha'] ?? '') !== '1' ? 'selected' : '' ?>>Disabled</option></select>
                </div>
                <div class="col-md-6"><label class="form-label fw-semibold">reCAPTCHA Site Key</label><input type="text" class="form-control" name="recaptcha_site_key" value="<?= e($s['recaptcha_site_key'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">reCAPTCHA Secret Key</label><input type="text" class="form-control" name="recaptcha_secret_key" value="<?= e($s['recaptcha_secret_key'] ?? '') ?>"></div>
                <div class="col-12"><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Security Settings</button></div>
            </div>
        </form>
    </div>
</div>
@endsection
