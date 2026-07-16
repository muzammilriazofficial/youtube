@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Activity Logs</h4>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="action">
                    <option value="">All Actions</option>
                    @foreach($actions as $a)
                        <option value="<?= e($a) ?>" <?= ($action ?? '') === $a ? 'selected' : '' ?>><?= e($a) ?></option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input type="number" class="form-control form-control-sm" name="user_id" placeholder="User ID" value="<?= e($userId ?? '') ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i>Filter</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>User</th><th>Action</th><th>Description</th><th>IP</th><th>Date</th></tr></thead>
                <tbody>
                    @if(count($logs['data']) > 0)
                        @foreach($logs['data'] as $log)
                        <tr>
                            <td><?= $log['id'] ?></td>
                            <td class="text-muted small"><?= e($log['username'] ?? 'System') ?></td>
                            <td><span class="badge bg-info badge-status"><?= e($log['action'] ?? '') ?></span></td>
                            <td class="text-truncate" style="max-width:300px;"><?= e($log['description'] ?? $log['properties'] ?? '') ?></td>
                            <td class="text-muted small font-monospace"><?= e($log['ip_address'] ?? '') ?></td>
                            <td class="text-muted small"><?= date('M d H:i', strtotime($log['created_at'] ?? '')) ?></td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="6" class="text-center text-muted py-4">No activity logs found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($logs['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-end">
        <nav><ul class="pagination pagination-sm mb-0">
            @if($logs['has_prev_page'])<li class="page-item"><a class="page-link" href="?page=<?= $logs['current_page'] - 1 ?>&action=<?= e($action ?? '') ?>&user_id=<?= e($userId ?? '') ?>">Prev</a></li>@endif
            @for($i = max(1, $logs['current_page'] - 2); $i <= min($logs['last_page'], $logs['current_page'] + 2); $i++)
                <li class="page-item <?= $i === $logs['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&action=<?= e($action ?? '') ?>&user_id=<?= e($userId ?? '') ?>"><?= $i ?></a></li>
            @endfor
            @if($logs['has_more_pages'])<li class="page-item"><a class="page-link" href="?page=<?= $logs['current_page'] + 1 ?>&action=<?= e($action ?? '') ?>&user_id=<?= e($userId ?? '') ?>">Next</a></li>@endif
        </ul></nav>
    </div>
    @endif
</div>
@endsection
