@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Payment History</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-12"><div class="card stat-card p-3"><div class="text-muted small">Total Revenue</div><h4 class="fw-bold text-success mb-0">$<?= number_format($totalRevenue, 2) ?></h4></div></div>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-4"><input type="text" class="form-control form-control-sm" name="search" placeholder="Search by transaction ID or username..." value="<?= e($search) ?>"></div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="failed" <?= $status === 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="refunded" <?= $status === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filter</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>User</th><th>Amount</th><th>Method</th><th>Transaction</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @if(count($payments['data']) > 0)
                        @foreach($payments['data'] as $pay)
                        <tr>
                            <td><?= $pay['id'] ?></td>
                            <td class="text-muted small"><?= e($pay['username'] ?? '') ?></td>
                            <td class="fw-bold">$<?= number_format((float)($pay['amount'] ?? 0), 2) ?></td>
                            <td><span class="badge bg-info badge-status"><?= e($pay['method'] ?? 'N/A') ?></span></td>
                            <td class="text-muted small text-truncate" style="max-width:150px;"><?= e($pay['transaction_id'] ?? '') ?></td>
                            <td>
                                @if(($pay['status'] ?? '') === 'completed')
                                    <span class="badge bg-success badge-status">Completed</span>
                                @elseif(($pay['status'] ?? '') === 'pending')
                                    <span class="badge bg-warning badge-status">Pending</span>
                                @elseif(($pay['status'] ?? '') === 'failed')
                                    <span class="badge bg-danger badge-status">Failed</span>
                                @else
                                    <span class="badge bg-secondary badge-status"><?= e($pay['status'] ?? '') ?></span>
                                @endif
                            </td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($pay['created_at'] ?? '')) ?></td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="7" class="text-center text-muted py-4">No payments found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($payments['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-end">
        <nav><ul class="pagination pagination-sm mb-0">
            @if($payments['has_prev_page'])<li class="page-item"><a class="page-link" href="?page=<?= $payments['current_page'] - 1 ?>&search=<?= e($search) ?>&status=<?= e($status) ?>">Prev</a></li>@endif
            @for($i = max(1, $payments['current_page'] - 2); $i <= min($payments['last_page'], $payments['current_page'] + 2); $i++)
                <li class="page-item <?= $i === $payments['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= e($search) ?>&status=<?= e($status) ?>"><?= $i ?></a></li>
            @endfor
            @if($payments['has_more_pages'])<li class="page-item"><a class="page-link" href="?page=<?= $payments['current_page'] + 1 ?>&search=<?= e($search) ?>&status=<?= e($status) ?>">Next</a></li>@endif
        </ul></nav>
    </div>
    @endif
</div>
@endsection
