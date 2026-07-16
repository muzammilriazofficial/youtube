@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Create User</h4>
    <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card table-card">
    <div class="card-body p-4">
        <form method="POST" action="<?= url('/admin/users/store') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Username *</label>
                    <input type="text" class="form-control" name="username" value="<?= e($_SESSION['old_input']['username'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Display Name *</label>
                    <input type="text" class="form-control" name="display_name" value="<?= e($_SESSION['old_input']['display_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email *</label>
                    <input type="email" class="form-control" name="email" value="<?= e($_SESSION['old_input']['email'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Password *</label>
                    <input type="password" class="form-control" name="password" required minlength="6">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Role</label>
                    <select class="form-select" name="role">
                        <option value="">No Role</option>
                        @if(isset($roles))
                            @foreach($roles as $role)
                                <option value="<?= $role['id'] ?>"><?= e($role['name']) ?></option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Admin</label>
                    <select class="form-select" name="is_admin">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea class="form-control" name="description" rows="3"><?= e($_SESSION['old_input']['description'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create User</button>
                    <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
