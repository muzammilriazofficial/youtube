@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Payout Management</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6"><div class="card stat-card p-3"><div class="text-muted small">Pending Payouts</div><h4 class="fw-bold text-warning mb-0">$<?= number_format($totalPending, 2) ?></h4></div></div>
    <div class="col-md-6"><div class="card stat-card p-3"><div class="text-muted small">Total Paid</div><h4 class="fw-bold text-success mb-0">$<?= number_format($totalPaid, 2) ?></h4></div></div>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i>Filter</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>User</th><th>Amount</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    @if(count($payouts['data']) > 0)
                        @foreach($payouts['data'] as $p)
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td class="text-muted small"><?= e($p['username'] ?? '') ?></td>
                            <td class="fw-bold">$<?= number_format((float)($p['amount'] ?? 0), 2) ?></td>
                            <td>
                                @if(($p['status'] ?? '') === 'completed')
                                    <span class="badge bg-success badge-status">Completed</span>
                                @else
                                    <span class="badge bg-warning badge-status">Pending</span>
                                @endif
                            </td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($p['created_at'] ?? '')) ?></td>
                            <td>
                                @if(($p['status'] ?? '') === 'pending')
                                <form method="POST" action="<?= url('/admin/payouts/process/' . $p['id']) ?>" class="d-inline" onsubmit="return confirm('Mark as processed?')">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-success"><i class="bi bi-check-lg me-1"></i>Process</button>
                                </form>
                                @else
                                <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="6" class="text-center text-muted py-4">No payouts found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($payouts['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-end">
        <nav><ul class="pagination pagination-sm mb-0">
            @if($payouts['has_prev_page'])<li class="page-item"><a class="page-link" href="?page=<?= $payouts['current_page'] - 1 ?>&status=<?= e($status) ?>">Prev</a></li>@endif
            @for($i = max(1, $payouts['current_page'] - 2); $i <= min($payouts['last_page'], $payouts['current_page'] + 2); $i++)
                <li class="page-item <?= $i === $payouts['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&status=<?= e($status) ?>"><?= $i ?></a></li>
            @endfor
            @if($payouts['has_more_pages'])<li class="page-item"><a class="page-link" href="?page=<?= $payouts['current_page'] + 1 ?>&status=<?= e($status) ?>">Next</a></li>@endif
        </ul></nav>
    </div>
    @endif
</div>
@endsection
