@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Contact Messages</h4>
    @if(($unread ?? 0) > 0)
        <span class="badge bg-danger fs-6"><?= $unread ?> unread</span>
    @endif
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="unread" <?= $status === 'unread' ? 'selected' : '' ?>>Unread</option>
                    <option value="read" <?= $status === 'read' ? 'selected' : '' ?>>Read</option>
                    <option value="replied" <?= $status === 'replied' ? 'selected' : '' ?>>Replied</option>
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i>Filter</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Subject</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                    @if(count($messages['data']) > 0)
                        @foreach($messages['data'] as $msg)
                        <tr class="<?= ($msg['status'] ?? '') === 'unread' ? 'table-active' : '' ?>">
                            <td><?= $msg['id'] ?></td>
                            <td class="fw-semibold"><?= e($msg['name'] ?? '') ?></td>
                            <td class="text-muted small"><?= e($msg['email'] ?? '') ?></td>
                            <td class="text-truncate" style="max-width:200px;"><?= e($msg['subject'] ?? '') ?></td>
                            <td>
                                @if(($msg['status'] ?? '') === 'unread')
                                    <span class="badge bg-danger badge-status">Unread</span>
                                @elseif(($msg['status'] ?? '') === 'read')
                                    <span class="badge bg-info badge-status">Read</span>
                                @else
                                    <span class="badge bg-success badge-status">Replied</span>
                                @endif
                            </td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($msg['created_at'] ?? '')) ?></td>
                            <td><a href="<?= url('/admin/contact-messages/show/' . $msg['id']) ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="7" class="text-center text-muted py-4">No messages found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($messages['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-end">
        <nav><ul class="pagination pagination-sm mb-0">
            @if($messages['has_prev_page'])<li class="page-item"><a class="page-link" href="?page=<?= $messages['current_page'] - 1 ?>&status=<?= e($status) ?>">Prev</a></li>@endif
            @for($i = max(1, $messages['current_page'] - 2); $i <= min($messages['last_page'], $messages['current_page'] + 2); $i++)
                <li class="page-item <?= $i === $messages['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&status=<?= e($status) ?>"><?= $i ?></a></li>
            @endfor
            @if($messages['has_more_pages'])<li class="page-item"><a class="page-link" href="?page=<?= $messages['current_page'] + 1 ?>&status=<?= e($status) ?>">Next</a></li>@endif
        </ul></nav>
    </div>
    @endif
</div>
@endsection
