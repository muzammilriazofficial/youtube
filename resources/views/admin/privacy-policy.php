@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Privacy Policy</h4>
</div>

<div class="card table-card">
    <div class="card-body p-4">
        <form method="POST" action="<?= url('/admin/privacy-policy/update') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">Privacy Policy Content</label>
                <textarea class="form-control" name="content" rows="20" placeholder="Enter privacy policy content..."><?= e($policy['content'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Privacy Policy</button>
        </form>
    </div>
</div>
@endsection
