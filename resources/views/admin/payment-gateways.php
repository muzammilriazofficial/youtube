@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Payment Gateway Settings</h4>
</div>

<?php $s = $settings ?? []; ?>

<div class="card table-card">
    <div class="card-body p-4">
        <form method="POST" action="<?= url('/admin/payment-gateways/update') ?>">
            <?= csrf_field() ?>
            <h6 class="fw-bold mb-3">Stripe</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3"><label class="form-label fw-semibold">Enabled</label>
                    <select class="form-select" name="stripe_enabled"><option value="1" <?= ($s['stripe_enabled'] ?? '') === '1' ? 'selected' : '' ?>>Yes</option><option value="0" <?= ($s['stripe_enabled'] ?? '') !== '1' ? 'selected' : '' ?>>No</option></select>
                </div>
                <div class="col-md-3"><label class="form-label fw-semibold">Publishable Key</label><input type="text" class="form-control" name="stripe_key" value="<?= e($s['stripe_key'] ?? '') ?>"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Secret Key</label><input type="password" class="form-control" name="stripe_secret" value="<?= e($s['stripe_secret'] ?? '') ?>"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Webhook Secret</label><input type="password" class="form-control" name="stripe_webhook" value="<?= e($s['stripe_webhook'] ?? '') ?>"></div>
            </div>
            <h6 class="fw-bold mb-3">PayPal</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4"><label class="form-label fw-semibold">Enabled</label>
                    <select class="form-select" name="paypal_enabled"><option value="1" <?= ($s['paypal_enabled'] ?? '') === '1' ? 'selected' : '' ?>>Yes</option><option value="0" <?= ($s['paypal_enabled'] ?? '') !== '1' ? 'selected' : '' ?>>No</option></select>
                </div>
                <div class="col-md-4"><label class="form-label fw-semibold">Client ID</label><input type="text" class="form-control" name="paypal_client_id" value="<?= e($s['paypal_client_id'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Secret</label><input type="password" class="form-control" name="paypal_secret" value="<?= e($s['paypal_secret'] ?? '') ?>"></div>
            </div>
            <h6 class="fw-bold mb-3">General</h6>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Currency</label><input type="text" class="form-control" name="currency" value="<?= e($s['currency'] ?? 'USD') ?>"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Minimum Payout ($)</label><input type="number" class="form-control" name="minimum_payout" step="0.01" value="<?= e($s['minimum_payout'] ?? '100') ?>"></div>
                <div class="col-12"><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Payment Settings</button></div>
            </div>
        </form>
    </div>
</div>
@endsection
