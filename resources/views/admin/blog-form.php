@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><?= isset($post) && $post ? 'Edit Blog Post' : 'Create Blog Post' ?></h4>
    <a href="<?= url('/admin/blog') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card table-card">
    <div class="card-body p-4">
        <form method="POST" action="<?= isset($post) && $post ? url('/admin/blog/update/' . $post['id']) : url('/admin/blog/store') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Title *</label>
                    <input type="text" class="form-control" name="title" value="<?= e($post['title'] ?? ($_SESSION['old_input']['title'] ?? '')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Status</label>
                    <select class="form-select" name="status">
                        <option value="draft" <?= ($post['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Slug *</label>
                    <input type="text" class="form-control" name="slug" value="<?= e($post['slug'] ?? ($_SESSION['old_input']['slug'] ?? '')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Excerpt</label>
                    <input type="text" class="form-control" name="excerpt" value="<?= e($post['excerpt'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Content *</label>
                    <textarea class="form-control" name="content" rows="15" required><?= e($post['content'] ?? ($_SESSION['old_input']['content'] ?? '')) ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= isset($post) && $post ? 'Update' : 'Create' ?> Post</button>
                    <a href="<?= url('/admin/blog') ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
