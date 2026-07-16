@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Channel Management</h4>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-6"><input type="text" class="form-control form-control-sm" name="search" placeholder="Search channels..." value="<?= e($search) ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Search</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>Channel</th><th>Owner</th><th>Subscribers</th><th>Videos</th><th>Verified</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @if(count($channels['data']) > 0)
                        @foreach($channels['data'] as $ch)
                        <tr>
                            <td><?= $ch['id'] ?></td>
                            <td class="fw-semibold"><?= e($ch['name'] ?? '') ?></td>
                            <td class="text-muted small"><?= e($ch['username'] ?? '') ?></td>
                            <td><?= number_format($ch['subscriber_count'] ?? 0) ?></td>
                            <td><?= number_format($ch['video_count'] ?? 0) ?></td>
                            <td>
                                @if(!empty($ch['is_verified']))
                                    <span class="badge bg-success badge-status">Verified</span>
                                @else
                                    <span class="badge bg-secondary badge-status">No</span>
                                @endif
                            </td>
                            <td>
                                @if(($ch['status'] ?? '') === 'suspended')
                                    <span class="badge bg-danger badge-status">Suspended</span>
                                @else
                                    <span class="badge bg-success badge-status">Active</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Actions</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @if(empty($ch['is_verified']))
                                        <li><form method="POST" action="<?= url('/admin/channels/action/' . $ch['id']) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="verify"><button class="dropdown-item text-success" type="submit"><i class="bi bi-patch-check me-2"></i>Verify</button></form></li>
                                        @else
                                        <li><form method="POST" action="<?= url('/admin/channels/action/' . $ch['id']) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="unverify"><button class="dropdown-item text-warning" type="submit"><i class="bi bi-x-circle me-2"></i>Unverify</button></form></li>
                                        @endif
                                        @if(($ch['status'] ?? '') === 'suspended')
                                        <li><form method="POST" action="<?= url('/admin/channels/action/' . $ch['id']) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="activate"><button class="dropdown-item text-success" type="submit"><i class="bi bi-check-circle me-2"></i>Activate</button></form></li>
                                        @else
                                        <li><form method="POST" action="<?= url('/admin/channels/action/' . $ch['id']) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="suspend"><button class="dropdown-item text-danger confirm-action" data-confirm="Suspend this channel?" type="submit"><i class="bi bi-slash-circle me-2"></i>Suspend</button></form></li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="8" class="text-center text-muted py-4">No channels found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($channels['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-end">
        <nav><ul class="pagination pagination-sm mb-0">
            @if($channels['has_prev_page'])<li class="page-item"><a class="page-link" href="?page=<?= $channels['current_page'] - 1 ?>&search=<?= e($search) ?>">Prev</a></li>@endif
            @for($i = max(1, $channels['current_page'] - 2); $i <= min($channels['last_page'], $channels['current_page'] + 2); $i++)
                <li class="page-item <?= $i === $channels['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= e($search) ?>"><?= $i ?></a></li>
            @endfor
            @if($channels['has_more_pages'])<li class="page-item"><a class="page-link" href="?page=<?= $channels['current_page'] + 1 ?>&search=<?= e($search) ?>">Next</a></li>@endif
        </ul></nav>
    </div>
    @endif
</div>
@endsection
