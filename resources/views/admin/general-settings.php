@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">General Settings</h4>
</div>

<?php $s = $settings ?? []; ?>

<div class="card table-card">
    <div class="card-body p-4">
        <form method="POST" action="<?= url('/admin/general-settings/update') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Site Name</label><input type="text" class="form-control" name="site_name" value="<?= e($s['site_name'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Site URL</label><input type="url" class="form-control" name="site_url" value="<?= e($s['site_url'] ?? '') ?>"></div>
                <div class="col-12"><label class="form-label fw-semibold">Site Description</label><textarea class="form-control" name="site_description" rows="3"><?= e($s['site_description'] ?? '') ?></textarea></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Contact Email</label><input type="email" class="form-control" name="contact_email" value="<?= e($s['contact_email'] ?? '') ?>"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Default Timezone</label><input type="text" class="form-control" name="default_timezone" value="<?= e($s['default_timezone'] ?? 'UTC') ?>"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Default Language</label><input type="text" class="form-control" name="default_language" value="<?= e($s['default_language'] ?? 'en') ?>"></div>
                <div class="col-12"><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Settings</button></div>
            </div>
        </form>
    </div>
</div>
@endsection
