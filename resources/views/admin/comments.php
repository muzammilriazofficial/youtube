@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Comment Management</h4>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-4"><input type="text" class="form-control form-control-sm" name="search" placeholder="Search comments..." value="<?= e($search) ?>"></div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="hidden" <?= $status === 'hidden' ? 'selected' : '' ?>>Hidden</option>
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filter</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>Comment</th><th>User</th><th>Video</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                    @if(count($comments['data']) > 0)
                        @foreach($comments['data'] as $c)
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td class="text-truncate" style="max-width:250px;"><?= e($c['content'] ?? '') ?></td>
                            <td class="text-muted small"><?= e($c['username'] ?? '') ?></td>
                            <td class="text-muted small text-truncate" style="max-width:150px;"><?= e($c['video_title'] ?? '') ?></td>
                            <td>
                                @if(($c['status'] ?? '') === 'approved')
                                    <span class="badge bg-success badge-status">Approved</span>
                                @elseif(($c['status'] ?? '') === 'pending')
                                    <span class="badge bg-warning badge-status">Pending</span>
                                @elseif(($c['status'] ?? '') === 'hidden')
                                    <span class="badge bg-secondary badge-status">Hidden</span>
                                @else
                                    <span class="badge bg-secondary badge-status"><?= e($c['status'] ?? '') ?></span>
                                @endif
                            </td>
                            <td class="text-muted small"><?= date('M d', strtotime($c['created_at'] ?? '')) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if(($c['status'] ?? '') !== 'approved')
                                    <form method="POST" action="<?= url('/admin/comments/action/' . $c['id']) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="approve"><button class="btn btn-outline-success" title="Approve"><i class="bi bi-check-lg"></i></button></form>
                                    @endif
                                    <form method="POST" action="<?= url('/admin/comments/action/' . $c['id']) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="hide"><button class="btn btn-outline-warning" title="Hide"><i class="bi bi-eye-slash"></i></button></form>
                                    <form method="POST" action="<?= url('/admin/comments/action/' . $c['id']) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><button class="btn btn-outline-danger confirm-action" data-confirm="Delete this comment?" title="Delete"><i class="bi bi-trash"></i></button></form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="7" class="text-center text-muted py-4">No comments found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($comments['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-end">
        <nav><ul class="pagination pagination-sm mb-0">
            @if($comments['has_prev_page'])<li class="page-item"><a class="page-link" href="?page=<?= $comments['current_page'] - 1 ?>&search=<?= e($search) ?>&status=<?= e($status) ?>">Prev</a></li>@endif
            @for($i = max(1, $comments['current_page'] - 2); $i <= min($comments['last_page'], $comments['current_page'] + 2); $i++)
                <li class="page-item <?= $i === $comments['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= e($search) ?>&status=<?= e($status) ?>"><?= $i ?></a></li>
            @endfor
            @if($comments['has_more_pages'])<li class="page-item"><a class="page-link" href="?page=<?= $comments['current_page'] + 1 ?>&search=<?= e($search) ?>&status=<?= e($status) ?>">Next</a></li>@endif
        </ul></nav>
    </div>
    @endif
</div>
@endsection
