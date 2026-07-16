@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Monetization Overview</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card stat-card p-3"><div class="text-muted small">Approved</div><h4 class="fw-bold text-success mb-0"><?= $totalApproved ?></h4></div></div>
    <div class="col-md-4"><div class="card stat-card p-3"><div class="text-muted small">Pending</div><h4 class="fw-bold text-warning mb-0"><?= $totalPending ?></h4></div></div>
    <div class="col-md-4"><div class="card stat-card p-3"><div class="text-muted small">Rejected</div><h4 class="fw-bold text-danger mb-0"><?= $totalRejected ?></h4></div></div>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i>Filter</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>User</th><th>Channel</th><th>Status</th><th>Created</th></tr></thead>
                <tbody>
                    @if(count($monetizations['data']) > 0)
                        @foreach($monetizations['data'] as $m)
                        <tr>
                            <td><?= $m['id'] ?></td>
                            <td class="text-muted small"><?= e($m['username'] ?? '') ?></td>
                            <td class="fw-semibold"><?= e($m['channel_name'] ?? '') ?></td>
                            <td>
                                @if(($m['status'] ?? '') === 'approved')
                                    <span class="badge bg-success badge-status">Approved</span>
                                @elseif(($m['status'] ?? '') === 'pending')
                                    <span class="badge bg-warning badge-status">Pending</span>
                                @else
                                    <span class="badge bg-danger badge-status"><?= e($m['status'] ?? '') ?></span>
                                @endif
                            </td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($m['created_at'] ?? '')) ?></td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="5" class="text-center text-muted py-4">No monetization records found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($monetizations['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-end">
        <nav><ul class="pagination pagination-sm mb-0">
            @if($monetizations['has_prev_page'])<li class="page-item"><a class="page-link" href="?page=<?= $monetizations['current_page'] - 1 ?>&status=<?= e($status) ?>">Prev</a></li>@endif
            @for($i = max(1, $monetizations['current_page'] - 2); $i <= min($monetizations['last_page'], $monetizations['current_page'] + 2); $i++)
                <li class="page-item <?= $i === $monetizations['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&status=<?= e($status) ?>"><?= $i ?></a></li>
            @endfor
            @if($monetizations['has_more_pages'])<li class="page-item"><a class="page-link" href="?page=<?= $monetizations['current_page'] + 1 ?>&status=<?= e($status) ?>">Next</a></li>@endif
        </ul></nav>
    </div>
    @endif
</div>
@endsection
