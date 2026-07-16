@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><?= isset($page_data) && $page_data ? 'Edit Page' : 'Create Page' ?></h4>
    <a href="<?= url('/admin/pages') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card table-card">
    <div class="card-body p-4">
        <form method="POST" action="<?= isset($page_data) && $page_data ? url('/admin/pages/update/' . $page_data['id']) : url('/admin/pages/store') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Title *</label>
                    <input type="text" class="form-control" name="title" value="<?= e($page_data['title'] ?? ($_SESSION['old_input']['title'] ?? '')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Slug *</label>
                    <input type="text" class="form-control" name="slug" value="<?= e($page_data['slug'] ?? ($_SESSION['old_input']['slug'] ?? '')) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Content</label>
                    <textarea class="form-control" name="content" rows="12"><?= e($page_data['content'] ?? ($_SESSION['old_input']['content'] ?? '')) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Meta Title</label>
                    <input type="text" class="form-control" name="meta_title" value="<?= e($page_data['meta_title'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Meta Description</label>
                    <input type="text" class="form-control" name="meta_description" value="<?= e($page_data['meta_description'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_published" value="1" id="isPublished" <?= !empty($page_data['is_published']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isPublished">Published</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= isset($page_data) && $page_data ? 'Update' : 'Create' ?> Page</button>
                    <a href="<?= url('/admin/pages') ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
