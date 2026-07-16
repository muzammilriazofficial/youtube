@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Creator Management</h4>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-6">
                <input type="text" class="form-control form-control-sm" name="search" placeholder="Search creators..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Search</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>Channel</th><th>Owner</th><th>Subscribers</th><th>Videos</th><th>Verified</th><th>Created</th></tr></thead>
                <tbody>
                    @if(count($creators['data']) > 0)
                        @foreach($creators['data'] as $c)
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td class="fw-semibold"><?= e($c['name'] ?? '') ?></td>
                            <td class="text-muted"><?= e($c['username'] ?? '') ?></td>
                            <td><?= number_format($c['subscriber_count'] ?? 0) ?></td>
                            <td><?= number_format($c['video_count'] ?? 0) ?></td>
                            <td>
                                @if(!empty($c['is_verified']))
                                    <span class="badge bg-success badge-status">Verified</span>
                                @else
                                    <span class="badge bg-secondary badge-status">No</span>
                                @endif
                            </td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($c['created_at'] ?? '')) ?></td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="7" class="text-center text-muted py-4">No creators found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($creators['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-between align-items-center">
        <small class="text-muted"><?= number_format($creators['total']) ?> total creators</small>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                @if($creators['has_prev_page'])
                    <li class="page-item"><a class="page-link" href="?page=<?= $creators['current_page'] - 1 ?>&search=<?= e($search) ?>">Prev</a></li>
                @endif
                @for($i = max(1, $creators['current_page'] - 2); $i <= min($creators['last_page'], $creators['current_page'] + 2); $i++)
                    <li class="page-item <?= $i === $creators['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= e($search) ?>"><?= $i ?></a></li>
                @endfor
                @if($creators['has_more_pages'])
                    <li class="page-item"><a class="page-link" href="?page=<?= $creators['current_page'] + 1 ?>&search=<?= e($search) ?>">Next</a></li>
                @endif
            </ul>
        </nav>
    </div>
    @endif
</div>
@endsection
