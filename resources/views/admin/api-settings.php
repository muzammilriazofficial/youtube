@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">API Settings</h4>
</div>

<?php $s = $settings ?? []; ?>

<div class="card table-card">
    <div class="card-body p-4">
        <form method="POST" action="<?= url('/admin/api-settings/update') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label fw-semibold">Rate Limit (req/min)</label><input type="number" class="form-control" name="api_rate_limit" value="<?= e($s['api_rate_limit'] ?? '60') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">API Key</label><input type="text" class="form-control font-monospace" name="api_key" value="<?= e($s['api_key'] ?? bin2hex(random_bytes(32))) ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">API Logging</label>
                    <select class="form-select" name="enable_api_logging"><option value="1" <?= ($s['enable_api_logging'] ?? '') === '1' ? 'selected' : '' ?>>Enabled</option><option value="0" <?= ($s['enable_api_logging'] ?? '') !== '1' ? 'selected' : '' ?>>Disabled</option></select>
                </div>
                <div class="col-md-6"><label class="form-label fw-semibold">CORS Origins</label><input type="text" class="form-control" name="cors_origins" value="<?= e($s['cors_origins'] ?? '*') ?>" placeholder="*"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Webhooks</label>
                    <select class="form-select" name="enable_webhooks"><option value="1" <?= ($s['enable_webhooks'] ?? '') === '1' ? 'selected' : '' ?>>Enabled</option><option value="0" <?= ($s['enable_webhooks'] ?? '') !== '1' ? 'selected' : '' ?>>Disabled</option></select>
                </div>
                <div class="col-md-3"><label class="form-label fw-semibold">Webhook Secret</label><input type="text" class="form-control" name="webhook_secret" value="<?= e($s['webhook_secret'] ?? '') ?>"></div>
                <div class="col-12"><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save API Settings</button></div>
            </div>
        </form>
    </div>
</div>
@endsection
