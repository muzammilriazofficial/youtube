@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Copyright Claims</h4>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="accepted" <?= $status === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                    <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    <option value="counter_notified" <?= $status === 'counter_notified' ? 'selected' : '' ?>>Counter-Notified</option>
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i>Filter</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>Claimant</th><th>Video</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                    @if(count($claims['data']) > 0)
                        @foreach($claims['data'] as $cl)
                        <tr>
                            <td><?= $cl['id'] ?></td>
                            <td class="text-muted small"><?= e($cl['claimant_username'] ?? '') ?></td>
                            <td class="text-truncate" style="max-width:200px;"><?= e($cl['video_title'] ?? '') ?></td>
                            <td>
                                @if(($cl['status'] ?? '') === 'pending')
                                    <span class="badge bg-warning badge-status">Pending</span>
                                @elseif(($cl['status'] ?? '') === 'accepted')
                                    <span class="badge bg-success badge-status">Accepted</span>
                                @elseif(($cl['status'] ?? '') === 'rejected')
                                    <span class="badge bg-danger badge-status">Rejected</span>
                                @else
                                    <span class="badge bg-info badge-status"><?= e($cl['status'] ?? '') ?></span>
                                @endif
                            </td>
                            <td class="text-muted small"><?= date('M d', strtotime($cl['created_at'] ?? '')) ?></td>
                            <td>
                                @if(($cl['status'] ?? '') === 'pending')
                                <div class="btn-group btn-group-sm">
                                    <form method="POST" action="<?= url('/admin/copyright/action/' . $cl['id']) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="accept"><button class="btn btn-outline-success confirm-action" data-confirm="Accept claim and remove video?"><i class="bi bi-check-lg"></i></button></form>
                                    <form method="POST" action="<?= url('/admin/copyright/action/' . $cl['id']) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="reject"><button class="btn btn-outline-danger"><i class="bi bi-x-lg"></i></button></form>
                                    <form method="POST" action="<?= url('/admin/copyright/action/' . $cl['id']) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="counter_notify"><button class="btn btn-outline-info"><i class="bi bi-arrow-return-right"></i></button></form>
                                </div>
                                @else
                                <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="6" class="text-center text-muted py-4">No copyright claims found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($claims['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-end">
        <nav><ul class="pagination pagination-sm mb-0">
            @if($claims['has_prev_page'])<li class="page-item"><a class="page-link" href="?page=<?= $claims['current_page'] - 1 ?>&status=<?= e($status) ?>">Prev</a></li>@endif
            @for($i = max(1, $claims['current_page'] - 2); $i <= min($claims['last_page'], $claims['current_page'] + 2); $i++)
                <li class="page-item <?= $i === $claims['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&status=<?= e($status) ?>"><?= $i ?></a></li>
            @endfor
            @if($claims['has_more_pages'])<li class="page-item"><a class="page-link" href="?page=<?= $claims['current_page'] + 1 ?>&status=<?= e($status) ?>">Next</a></li>@endif
        </ul></nav>
    </div>
    @endif
</div>
@endsection
