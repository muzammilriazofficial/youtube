@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Email Settings</h4>
</div>

<?php $s = $settings ?? []; ?>

<div class="card table-card">
    <div class="card-body p-4">
        <form method="POST" action="<?= url('/admin/email-settings/update') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label fw-semibold">Mail Driver</label>
                    <select class="form-select" name="mail_driver"><option value="smtp" <?= ($s['mail_driver'] ?? '') === 'smtp' ? 'selected' : '' ?>>SMTP</option><option value="mail" <?= ($s['mail_driver'] ?? '') === 'mail' ? 'selected' : '' ?>>PHP Mail</option><option value="sendmail" <?= ($s['mail_driver'] ?? '') === 'sendmail' ? 'selected' : '' ?>>Sendmail</option></select>
                </div>
                <div class="col-md-4"><label class="form-label fw-semibold">SMTP Host</label><input type="text" class="form-control" name="mail_host" value="<?= e($s['mail_host'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">SMTP Port</label><input type="number" class="form-control" name="mail_port" value="<?= e($s['mail_port'] ?? '587') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">SMTP Username</label><input type="text" class="form-control" name="mail_username" value="<?= e($s['mail_username'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">SMTP Password</label><input type="password" class="form-control" name="mail_password" value="<?= e($s['mail_password'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Encryption</label>
                    <select class="form-select" name="mail_encryption"><option value="tls" <?= ($s['mail_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option><option value="ssl" <?= ($s['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option></select>
                </div>
                <div class="col-md-6"><label class="form-label fw-semibold">From Address</label><input type="email" class="form-control" name="mail_from_address" value="<?= e($s['mail_from_address'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">From Name</label><input type="text" class="form-control" name="mail_from_name" value="<?= e($s['mail_from_name'] ?? '') ?>"></div>
                <div class="col-12"><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Email Settings</button></div>
            </div>
        </form>
    </div>
</div>
@endsection
