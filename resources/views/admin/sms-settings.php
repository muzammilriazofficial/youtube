@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">SMS Settings</h4>
</div>

<?php $s = $settings ?? []; ?>

<div class="card table-card">
    <div class="card-body p-4">
        <form method="POST" action="<?= url('/admin/sms-settings/update') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label fw-semibold">SMS Provider</label>
                    <select class="form-select" name="sms_provider"><option value="twilio" <?= ($s['sms_provider'] ?? '') === 'twilio' ? 'selected' : '' ?>>Twilio</option><option value="nexmo" <?= ($s['sms_provider'] ?? '') === 'nexmo' ? 'selected' : '' ?>>Nexmo</option></select>
                </div>
                <div class="col-md-4"><label class="form-label fw-semibold">API Key</label><input type="text" class="form-control" name="sms_api_key" value="<?= e($s['sms_api_key'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">API Secret</label><input type="password" class="form-control" name="sms_api_secret" value="<?= e($s['sms_api_secret'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">From Number</label><input type="text" class="form-control" name="sms_from_number" value="<?= e($s['sms_from_number'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Enabled</label>
                    <select class="form-select" name="sms_enabled"><option value="1" <?= ($s['sms_enabled'] ?? '') === '1' ? 'selected' : '' ?>>Yes</option><option value="0" <?= ($s['sms_enabled'] ?? '') !== '1' ? 'selected' : '' ?>>No</option></select>
                </div>
                <div class="col-12"><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save SMS Settings</button></div>
            </div>
        </form>
    </div>
</div>
@endsection
