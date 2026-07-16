@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Video Management</h4>
    <div>
        <a href="<?= url('/admin/videos/pending') ?>" class="btn btn-outline-warning btn-sm me-1"><i class="bi bi-hourglass-split me-1"></i>Pending</a>
        <a href="<?= url('/admin/videos/reported') ?>" class="btn btn-outline-danger btn-sm"><i class="bi bi-flag me-1"></i>Reported</a>
    </div>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" name="search" placeholder="Search videos..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    <option value="reported" <?= $status === 'reported' ? 'selected' : '' ?>>Reported</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
            <div class="col-md-2">
                <a href="<?= url('/admin/videos') ?>" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>Title</th><th>Channel</th><th>Views</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    @if(count($videos['data']) > 0)
                        @foreach($videos['data'] as $v)
                        <tr>
                            <td><?= $v['id'] ?></td>
                            <td class="fw-semibold text-truncate" style="max-width:250px;"><?= e($v['title'] ?? '') ?></td>
                            <td class="text-muted small"><?= e($v['channel_name'] ?? '') ?></td>
                            <td><?= number_format($v['view_count'] ?? 0) ?></td>
                            <td>
                                @if(($v['status'] ?? '') === 'published')
                                    <span class="badge bg-success badge-status">Published</span>
                                @elseif(($v['status'] ?? '') === 'pending')
                                    <span class="badge bg-warning badge-status">Pending</span>
                                @elseif(($v['status'] ?? '') === 'rejected')
                                    <span class="badge bg-danger badge-status">Rejected</span>
                                @else
                                    <span class="badge bg-secondary badge-status"><?= e($v['status'] ?? '') ?></span>
                                @endif
                            </td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($v['created_at'] ?? '')) ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Actions</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @if(($v['status'] ?? '') !== 'published')
                                        <li>
                                            <form method="POST" action="<?= url('/admin/videos/action/' . $v['id']) ?>"><?= csrf_field() ?>
                                                <input type="hidden" name="action" value="approve">
                                                <button class="dropdown-item text-success" type="submit"><i class="bi bi-check-circle me-2"></i>Approve</button>
                                            </form>
                                        </li>
                                        @endif
                                        @if(($v['status'] ?? '') !== 'rejected')
                                        <li>
                                            <form method="POST" action="<?= url('/admin/videos/action/' . $v['id']) ?>"><?= csrf_field() ?>
                                                <input type="hidden" name="action" value="reject">
                                                <button class="dropdown-item text-warning" type="submit"><i class="bi bi-x-circle me-2"></i>Reject</button>
                                            </form>
                                        </li>
                                        @endif
                                        <li>
                                            <form method="POST" action="<?= url('/admin/videos/action/' . $v['id']) ?>"><?= csrf_field() ?>
                                                <input type="hidden" name="action" value="feature">
                                                <button class="dropdown-item text-info" type="submit"><i class="bi bi-star me-2"></i>Feature</button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="<?= url('/admin/videos/action/' . $v['id']) ?>"><?= csrf_field() ?>
                                                <input type="hidden" name="action" value="remove">
                                                <button class="dropdown-item text-danger confirm-action" data-confirm="Remove this video?" type="submit"><i class="bi bi-trash me-2"></i>Remove</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="7" class="text-center text-muted py-4">No videos found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($videos['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-between align-items-center">
        <small class="text-muted"><?= number_format($videos['total']) ?> total videos</small>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                @if($videos['has_prev_page'])
                    <li class="page-item"><a class="page-link" href="?page=<?= $videos['current_page'] - 1 ?>&search=<?= e($search) ?>&status=<?= e($status) ?>">Prev</a></li>
                @endif
                @for($i = max(1, $videos['current_page'] - 2); $i <= min($videos['last_page'], $videos['current_page'] + 2); $i++)
                    <li class="page-item <?= $i === $videos['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= e($search) ?>&status=<?= e($status) ?>"><?= $i ?></a></li>
                @endfor
                @if($videos['has_more_pages'])
                    <li class="page-item"><a class="page-link" href="?page=<?= $videos['current_page'] + 1 ?>&search=<?= e($search) ?>&status=<?= e($status) ?>">Next</a></li>
                @endif
            </ul>
        </nav>
    </div>
    @endif
</div>
@endsection
