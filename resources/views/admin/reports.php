@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Report Management</h4>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="resolved" <?= $status === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                    <option value="dismissed" <?= $status === 'dismissed' ? 'selected' : '' ?>>Dismissed</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="type">
                    <option value="">All Types</option>
                    <option value="video" <?= $type === 'video' ? 'selected' : '' ?>>Video</option>
                    <option value="comment" <?= $type === 'comment' ? 'selected' : '' ?>>Comment</option>
                    <option value="channel" <?= $type === 'channel' ? 'selected' : '' ?>>Channel</option>
                    <option value="user" <?= $type === 'user' ? 'selected' : '' ?>>User</option>
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i>Filter</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>Reporter</th><th>Type</th><th>Reason</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                    @if(count($reports['data']) > 0)
                        @foreach($reports['data'] as $r)
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td class="text-muted small"><?= e($r['reporter_username'] ?? '') ?></td>
                            <td><span class="badge bg-info badge-status"><?= e($r['reportable_type'] ?? '') ?></span></td>
                            <td class="text-truncate" style="max-width:200px;"><?= e($r['reason'] ?? '') ?></td>
                            <td>
                                @if(($r['status'] ?? '') === 'pending')
                                    <span class="badge bg-warning badge-status">Pending</span>
                                @elseif(($r['status'] ?? '') === 'resolved')
                                    <span class="badge bg-success badge-status">Resolved</span>
                                @else
                                    <span class="badge bg-secondary badge-status"><?= e($r['status'] ?? '') ?></span>
                                @endif
                            </td>
                            <td class="text-muted small"><?= date('M d', strtotime($r['created_at'] ?? '')) ?></td>
                            <td><a href="<?= url('/admin/reports/show/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="7" class="text-center text-muted py-4">No reports found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($reports['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-end">
        <nav><ul class="pagination pagination-sm mb-0">
            @if($reports['has_prev_page'])<li class="page-item"><a class="page-link" href="?page=<?= $reports['current_page'] - 1 ?>&status=<?= e($status) ?>&type=<?= e($type) ?>">Prev</a></li>@endif
            @for($i = max(1, $reports['current_page'] - 2); $i <= min($reports['last_page'], $reports['current_page'] + 2); $i++)
                <li class="page-item <?= $i === $reports['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&status=<?= e($status) ?>&type=<?= e($type) ?>"><?= $i ?></a></li>
            @endfor
            @if($reports['has_more_pages'])<li class="page-item"><a class="page-link" href="?page=<?= $reports['current_page'] + 1 ?>&status=<?= e($status) ?>&type=<?= e($type) ?>">Next</a></li>@endif
        </ul></nav>
    </div>
    @endif
</div>
@endsection
