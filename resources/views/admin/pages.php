@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">CMS Pages</h4>
    <a href="<?= url('/admin/pages/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Page</a>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom">
        <form class="d-flex" method="GET">
            <input type="text" class="form-control form-control-sm me-2" name="search" placeholder="Search pages..." value="<?= e($search) ?>">
            <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i></button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>Title</th><th>Slug</th><th>Published</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    @if(count($pages['data']) > 0)
                        @foreach($pages['data'] as $p)
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td class="fw-semibold"><?= e($p['title'] ?? '') ?></td>
                            <td class="text-muted small"><?= e($p['slug'] ?? '') ?></td>
                            <td>
                                @if(!empty($p['is_published']))
                                    <span class="badge bg-success badge-status">Published</span>
                                @else
                                    <span class="badge bg-secondary badge-status">Draft</span>
                                @endif
                            </td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($p['created_at'] ?? '')) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('/admin/pages/edit/' . $p['id']) ?>" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="<?= url('/admin/pages/delete/' . $p['id']) ?>" class="d-inline" onsubmit="return confirm('Delete this page?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="6" class="text-center text-muted py-4">No pages found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($pages['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-end">
        <nav><ul class="pagination pagination-sm mb-0">
            @if($pages['has_prev_page'])<li class="page-item"><a class="page-link" href="?page=<?= $pages['current_page'] - 1 ?>&search=<?= e($search) ?>">Prev</a></li>@endif
            @for($i = max(1, $pages['current_page'] - 2); $i <= min($pages['last_page'], $pages['current_page'] + 2); $i++)
                <li class="page-item <?= $i === $pages['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= e($search) ?>"><?= $i ?></a></li>
            @endfor
            @if($pages['has_more_pages'])<li class="page-item"><a class="page-link" href="?page=<?= $pages['current_page'] + 1 ?>&search=<?= e($search) ?>">Next</a></li>@endif
        </ul></nav>
    </div>
    @endif
</div>
@endsection
