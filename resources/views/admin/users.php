@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">User Management</h4>
    <a href="<?= url('/admin/users/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add User</a>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" name="search" placeholder="Search users..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="role">
                    <option value="">All Roles</option>
                    <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="banned" <?= $status === 'banned' ? 'selected' : '' ?>>Banned</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
            <div class="col-md-2">
                <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th><th>User</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($users['data']) > 0)
                        @foreach($users['data'] as $u)
                        <tr id="user-row-<?= $u['id'] ?>">
                            <td><?= $u['id'] ?></td>
                            <td class="d-flex align-items-center">
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;font-size:12px;">
                                    <?= strtoupper(substr($u['username'] ?? 'U', 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?= e($u['username'] ?? '') ?></div>
                                    <div class="text-muted small"><?= e($u['display_name'] ?? '') ?></div>
                                </div>
                            </td>
                            <td class="text-muted small"><?= e($u['email'] ?? '') ?></td>
                            <td>
                                @if(!empty($u['is_admin']))
                                    <span class="badge bg-danger badge-status">Admin</span>
                                @else
                                    <span class="badge bg-primary badge-status">User</span>
                                @endif
                            </td>
                            <td>
                                @if(($u['status'] ?? '') === 'active')
                                    <span class="badge bg-success badge-status">Active</span>
                                @elseif(($u['status'] ?? '') === 'banned')
                                    <span class="badge bg-danger badge-status">Banned</span>
                                @else
                                    <span class="badge bg-secondary badge-status">Unknown</span>
                                @endif
                            </td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($u['created_at'] ?? '')) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('/admin/users/edit/' . $u['id']) ?>" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <button class="btn btn-outline-warning toggle-status-btn" data-id="<?= $u['id'] ?>" title="Toggle Status">
                                        <i class="bi bi-toggle2-on"></i>
                                    </button>
                                    <form method="POST" action="<?= url('/admin/users/delete/' . $u['id']) ?>" class="d-inline" onsubmit="return confirm('Delete this user?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="7" class="text-center text-muted py-4">No users found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($users['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing <?= count($users['data']) ?> of <?= number_format($users['total']) ?> users</small>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                @if($users['has_prev_page'])
                    <li class="page-item"><a class="page-link" href="?page=<?= $users['current_page'] - 1 ?>&search=<?= e($search) ?>&role=<?= e($role) ?>&status=<?= e($status) ?>">Prev</a></li>
                @endif
                @for($i = max(1, $users['current_page'] - 2); $i <= min($users['last_page'], $users['current_page'] + 2); $i++)
                    <li class="page-item <?= $i === $users['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= e($search) ?>&role=<?= e($role) ?>&status=<?= e($status) ?>"><?= $i ?></a></li>
                @endfor
                @if($users['has_more_pages'])
                    <li class="page-item"><a class="page-link" href="?page=<?= $users['current_page'] + 1 ?>&search=<?= e($search) ?>&role=<?= e($role) ?>&status=<?= e($status) ?>">Next</a></li>
                @endif
            </ul>
        </nav>
    </div>
    @endif
</div>

<script>
document.querySelectorAll('.toggle-status-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        ajaxPost('<?= url("/admin/users/toggle-status/") ?>' + id, {}, function(err, res) {
            if (err) { alert('Error occurred.'); return; }
            if (res.success) { location.reload(); }
        });
    });
});
</script>
@endsection
