@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Reported Videos</h4>
    <a href="<?= url('/admin/videos') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>All Videos</a>
</div>

<div class="card table-card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>Title</th><th>Channel</th><th>Views</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @if(count($videos['data']) > 0)
                        @foreach($videos['data'] as $v)
                        <tr>
                            <td><?= $v['id'] ?></td>
                            <td class="fw-semibold text-truncate" style="max-width:250px;"><?= e($v['title'] ?? '') ?></td>
                            <td class="text-muted small"><?= e($v['channel_name'] ?? '') ?></td>
                            <td><?= number_format($v['view_count'] ?? 0) ?></td>
                            <td><span class="badge bg-danger badge-status">Reported</span></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <form method="POST" action="<?= url('/admin/videos/action/' . $v['id']) ?>"><?= csrf_field() ?>
                                        <input type="hidden" name="action" value="approve">
                                        <button class="btn btn-outline-success" title="Approve"><i class="bi bi-check-lg"></i></button>
                                    </form>
                                    <form method="POST" action="<?= url('/admin/videos/action/' . $v['id']) ?>"><?= csrf_field() ?>
                                        <input type="hidden" name="action" value="remove">
                                        <button class="btn btn-outline-danger confirm-action" data-confirm="Remove this reported video?" title="Remove"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="6" class="text-center text-muted py-4">No reported videos</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($videos['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-end">
        <nav><ul class="pagination pagination-sm mb-0">
            @if($videos['has_prev_page'])
                <li class="page-item"><a class="page-link" href="?page=<?= $videos['current_page'] - 1 ?>">Prev</a></li>
            @endif
            @for($i = max(1, $videos['current_page'] - 2); $i <= min($videos['last_page'], $videos['current_page'] + 2); $i++)
                <li class="page-item <?= $i === $videos['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
            @endfor
            @if($videos['has_more_pages'])
                <li class="page-item"><a class="page-link" href="?page=<?= $videos['current_page'] + 1 ?>">Next</a></li>
            @endif
        </ul></nav>
    </div>
    @endif
</div>
@endsection
