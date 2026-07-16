@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Login Logs</h4>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="success" <?= $status === 'success' ? 'selected' : '' ?>>Success</option>
                    <option value="failed" <?= $status === 'failed' ? 'selected' : '' ?>>Failed</option>
                </select>
            </div>
            <div class="col-md-2"><input type="number" class="form-control form-control-sm" name="user_id" placeholder="User ID" value="<?= e($userId ?? '') ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i>Filter</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>User</th><th>Email</th><th>IP</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @if(count($logs['data']) > 0)
                        @foreach($logs['data'] as $log)
                        <tr>
                            <td><?= $log['id'] ?></td>
                            <td class="text-muted small"><?= e($log['username'] ?? '') ?></td>
                            <td class="text-muted small"><?= e($log['email'] ?? '') ?></td>
                            <td class="text-muted small font-monospace"><?= e($log['ip_address'] ?? '') ?></td>
                            <td>
                                @if(($log['status'] ?? '') === 'success')
                                    <span class="badge bg-success badge-status">Success</span>
                                @else
                                    <span class="badge bg-danger badge-status">Failed</span>
                                @endif
                            </td>
                            <td class="text-muted small"><?= date('M d H:i', strtotime($log['created_at'] ?? '')) ?></td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="6" class="text-center text-muted py-4">No login logs found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($logs['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-end">
        <nav><ul class="pagination pagination-sm mb-0">
            @if($logs['has_prev_page'])<li class="page-item"><a class="page-link" href="?page=<?= $logs['current_page'] - 1 ?>&status=<?= e($status) ?>&user_id=<?= e($userId ?? '') ?>">Prev</a></li>@endif
            @for($i = max(1, $logs['current_page'] - 2); $i <= min($logs['last_page'], $logs['current_page'] + 2); $i++)
                <li class="page-item <?= $i === $logs['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&status=<?= e($status) ?>&user_id=<?= e($userId ?? '') ?>"><?= $i ?></a></li>
            @endfor
            @if($logs['has_more_pages'])<li class="page-item"><a class="page-link" href="?page=<?= $logs['current_page'] + 1 ?>&status=<?= e($status) ?>&user_id=<?= e($userId ?? '') ?>">Next</a></li>@endif
        </ul></nav>
    </div>
    @endif
</div>
@endsection
