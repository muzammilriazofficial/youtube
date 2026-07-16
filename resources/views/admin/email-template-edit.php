@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Edit Email Template: <?= e($template['name'] ?? '') ?></h4>
    <a href="<?= url('/admin/email-templates') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card table-card">
    <div class="card-body p-4">
        <form method="POST" action="<?= url('/admin/email-templates/update/' . $template['id']) ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">Subject</label>
                <input type="text" class="form-control" name="subject" value="<?= e($template['subject'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Body (HTML)</label>
                <textarea class="form-control font-monospace" name="body" rows="20"><?= e($template['body'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <small class="text-muted">Available variables: {name}, {email}, {site_name}, {link}, {date}</small>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Template</button>
        </form>
    </div>
</div>
@endsection
