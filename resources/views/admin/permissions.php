@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Permission Management</h4>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card table-card mb-4">
            <div class="card-body p-0">
                @foreach($grouped as $group => $perms)
                <div class="p-3 border-bottom">
                    <h6 class="text-uppercase small fw-bold text-muted mb-2"><?= e(ucfirst($group)) ?></h6>
                    <div class="row">
                        @foreach($perms as $perm)
                        <div class="col-md-4 col-lg-3 mb-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small"><?= e($perm['name']) ?></span>
                                <form method="POST" action="<?= url('/admin/permissions/delete/' . $perm['id']) ?>" class="d-inline" onsubmit="return confirm('Delete this permission?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1"><i class="bi bi-x"></i></button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
                @if(empty($grouped))
                    <div class="p-4 text-center text-muted">No permissions defined yet</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card table-card">
            <div class="card-header bg-transparent border-bottom"><h6 class="fw-bold mb-0">Add Permission</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('/admin/permissions/store') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3"><label class="form-label fw-semibold">Name *</label><input type="text" class="form-control" name="name" required placeholder="e.g. videos.delete"></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Group *</label><input type="text" class="form-control" name="group_name" required placeholder="e.g. videos" value="general"></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Description</label><input type="text" class="form-control" name="description"></div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg me-1"></i>Add Permission</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
