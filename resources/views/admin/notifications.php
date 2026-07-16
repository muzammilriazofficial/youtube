@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Notification Management</h4>
    <a href="<?= url('/admin/notifications/send') ?>" class="btn btn-primary btn-sm"><i class="bi bi-send me-1"></i>Send Broadcast</a>
</div>

<div class="card table-card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>User</th><th>Title</th><th>Message</th><th>Read</th><th>Date</th></tr></thead>
                <tbody>
                    @if(count($notifications['data']) > 0)
                        @foreach($notifications['data'] as $n)
                        <tr>
                            <td><?= $n['id'] ?></td>
                            <td class="text-muted small"><?= e($n['username'] ?? '') ?></td>
                            <td class="fw-semibold text-truncate" style="max-width:200px;"><?= e($n['title'] ?? '') ?></td>
                            <td class="text-truncate" style="max-width:250px;"><?= e($n['message'] ?? '') ?></td>
                            <td>
                                @if(!empty($n['is_read']))
                                    <span class="badge bg-success badge-status">Read</span>
                                @else
                                    <span class="badge bg-warning badge-status">Unread</span>
                                @endif
                            </td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($n['created_at'] ?? '')) ?></td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="6" class="text-center text-muted py-4">No notifications found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($notifications['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-end">
        <nav><ul class="pagination pagination-sm mb-0">
            @if($notifications['has_prev_page'])<li class="page-item"><a class="page-link" href="?page=<?= $notifications['current_page'] - 1 ?>">Prev</a></li>@endif
            @for($i = max(1, $notifications['current_page'] - 2); $i <= min($notifications['last_page'], $notifications['current_page'] + 2); $i++)
                <li class="page-item <?= $i === $notifications['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
            @endfor
            @if($notifications['has_more_pages'])<li class="page-item"><a class="page-link" href="?page=<?= $notifications['current_page'] + 1 ?>">Next</a></li>@endif
        </ul></nav>
    </div>
    @endif
</div>
@endsection
