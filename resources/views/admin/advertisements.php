@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Advertisement Management</h4>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-4"><input type="text" class="form-control form-control-sm" name="search" placeholder="Search ads..." value="<?= e($search) ?>"></div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="paused" <?= $status === 'paused' ? 'selected' : '' ?>>Paused</option>
                    <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filter</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>Title</th><th>User</th><th>Type</th><th>Status</th><th>Budget</th><th>Actions</th></tr></thead>
                <tbody>
                    @if(count($ads['data']) > 0)
                        @foreach($ads['data'] as $ad)
                        <tr>
                            <td><?= $ad['id'] ?></td>
                            <td class="fw-semibold text-truncate" style="max-width:200px;"><?= e($ad['title'] ?? '') ?></td>
                            <td class="text-muted small"><?= e($ad['username'] ?? '') ?></td>
                            <td><span class="badge bg-info badge-status"><?= e($ad['type'] ?? 'standard') ?></span></td>
                            <td>
                                @if(($ad['status'] ?? '') === 'active')
                                    <span class="badge bg-success badge-status">Active</span>
                                @elseif(($ad['status'] ?? '') === 'pending')
                                    <span class="badge bg-warning badge-status">Pending</span>
                                @elseif(($ad['status'] ?? '') === 'paused')
                                    <span class="badge bg-secondary badge-status">Paused</span>
                                @else
                                    <span class="badge bg-danger badge-status"><?= e($ad['status'] ?? '') ?></span>
                                @endif
                            </td>
                            <td>$<?= number_format((float)($ad['budget'] ?? 0), 2) ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Actions</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @if(($ad['status'] ?? '') !== 'active')
                                        <li><form method="POST" action="<?= url('/admin/advertisements/action/' . $ad['id']) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="approve"><button class="dropdown-item text-success" type="submit"><i class="bi bi-check-circle me-2"></i>Approve</button></form></li>
                                        @endif
                                        @if(($ad['status'] ?? '') !== 'paused')
                                        <li><form method="POST" action="<?= url('/admin/advertisements/action/' . $ad['id']) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="pause"><button class="dropdown-item text-warning" type="submit"><i class="bi bi-pause-circle me-2"></i>Pause</button></form></li>
                                        @endif
                                        <li><form method="POST" action="<?= url('/admin/advertisements/action/' . $ad['id']) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="reject"><button class="dropdown-item text-danger" type="submit"><i class="bi bi-x-circle me-2"></i>Reject</button></form></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><form method="POST" action="<?= url('/admin/advertisements/action/' . $ad['id']) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><button class="dropdown-item text-danger confirm-action" data-confirm="Delete this ad?" type="submit"><i class="bi bi-trash me-2"></i>Delete</button></form></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="7" class="text-center text-muted py-4">No advertisements found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($ads['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-end">
        <nav><ul class="pagination pagination-sm mb-0">
            @if($ads['has_prev_page'])<li class="page-item"><a class="page-link" href="?page=<?= $ads['current_page'] - 1 ?>&search=<?= e($search) ?>&status=<?= e($status) ?>">Prev</a></li>@endif
            @for($i = max(1, $ads['current_page'] - 2); $i <= min($ads['last_page'], $ads['current_page'] + 2); $i++)
                <li class="page-item <?= $i === $ads['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= e($search) ?>&status=<?= e($status) ?>"><?= $i ?></a></li>
            @endfor
            @if($ads['has_more_pages'])<li class="page-item"><a class="page-link" href="?page=<?= $ads['current_page'] + 1 ?>&search=<?= e($search) ?>&status=<?= e($status) ?>">Next</a></li>@endif
        </ul></nav>
    </div>
    @endif
</div>
@endsection
