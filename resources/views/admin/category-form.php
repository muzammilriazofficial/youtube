@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><?= isset($category) && $category ? 'Edit Category' : 'Create Category' ?></h4>
    <a href="<?= url('/admin/categories') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card table-card">
    <div class="card-body p-4">
        <form method="POST" action="<?= isset($category) && $category ? url('/admin/categories/update/' . $category['id']) : url('/admin/categories/store') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Name *</label>
                    <input type="text" class="form-control" name="name" value="<?= e($category['name'] ?? ($_SESSION['old_input']['name'] ?? '')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Parent Category</label>
                    <select class="form-select" name="parent_id">
                        <option value="0">None (Top Level)</option>
                        @if(isset($parentCategories))
                            @foreach($parentCategories as $pc)
                                <option value="<?= $pc['id'] ?>" <?= ($category['parent_id'] ?? '') == $pc['id'] ? 'selected' : '' ?>><?= e($pc['name']) ?></option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea class="form-control" name="description" rows="3"><?= e($category['description'] ?? ($_SESSION['old_input']['description'] ?? '')) ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= isset($category) && $category ? 'Update' : 'Create' ?> Category</button>
                    <a href="<?= url('/admin/categories') ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
