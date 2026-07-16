@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Role Management</h4>
    <a href="<?= url('/admin/roles/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Role</a>
</div>

<div class="card table-card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Permissions</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    @if(count($roles) > 0)
                        @foreach($roles as $r)
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td class="fw-semibold"><?= e($r['name'] ?? '') ?></td>
                            <td class="text-muted small"><?= e($r['description'] ?? '') ?></td>
                            <td><span class="badge bg-primary badge-status"><?= $r['permission_count'] ?? 0 ?></span></td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($r['created_at'] ?? '')) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('/admin/roles/edit/' . $r['id']) ?>" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="<?= url('/admin/roles/delete/' . $r['id']) ?>" class="d-inline" onsubmit="return confirm('Delete this role?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="6" class="text-center text-muted py-4">No roles found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
