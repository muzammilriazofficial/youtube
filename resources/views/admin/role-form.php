@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><?= isset($role) && $role ? 'Edit Role: ' . e($role['name']) : 'Create Role' ?></h4>
    <a href="<?= url('/admin/roles') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card table-card">
            <div class="card-body p-4">
                <form method="POST" action="<?= isset($role) && $role ? url('/admin/roles/update/' . $role['id']) : url('/admin/roles/store') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name *</label>
                        <input type="text" class="form-control" name="name" value="<?= e($role['name'] ?? ($_SESSION['old_input']['name'] ?? '')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" name="description" rows="3"><?= e($role['description'] ?? ($_SESSION['old_input']['description'] ?? '')) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= isset($role) && $role ? 'Update' : 'Create' ?> Role</button>
                </form>
            </div>
        </div>
    </div>

    @if(isset($role) && $role)
    <div class="col-lg-7">
        <div class="card table-card">
            <div class="card-header bg-transparent border-bottom"><h6 class="fw-bold mb-0">Assign Permissions</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('/admin/roles/permissions/' . $role['id']) ?>">
                    <?= csrf_field() ?>
                    <?php
                    $groupedPerms = [];
                    if (isset($permissions)) {
                        foreach ($permissions as $p) {
                            $g = $p['group_name'] ?? 'general';
                            $groupedPerms[$g][] = $p;
                        }
                    }
                    $assigned = $assignedPermissionIds ?? [];
                    ?>
                    @foreach($groupedPerms as $group => $perms)
                    <div class="mb-3">
                        <h6 class="text-uppercase small fw-bold text-muted"><?= e(ucfirst($group)) ?></h6>
                        <div class="row">
                            @foreach($perms as $perm)
                            <div class="col-md-6 col-lg-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $perm['id'] ?>" id="perm-<?= $perm['id'] ?>" <?= in_array($perm['id'], $assigned) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="perm-<?= $perm['id'] ?>"><?= e($perm['name']) ?></label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Permissions</button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
