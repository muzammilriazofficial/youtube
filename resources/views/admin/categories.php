@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Category Management</h4>
    <a href="<?= url('/admin/categories/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Category</a>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-6"><input type="text" class="form-control form-control-sm" name="search" placeholder="Search categories..." value="<?= e($search) ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Search</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>Name</th><th>Slug</th><th>Parent</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    @if(count($categories['data']) > 0)
                        @foreach($categories['data'] as $cat)
                        <tr>
                            <td><?= $cat['id'] ?></td>
                            <td class="fw-semibold"><?= e($cat['name'] ?? '') ?></td>
                            <td class="text-muted small"><?= e($cat['slug'] ?? '') ?></td>
                            <td class="text-muted small"><?= $cat['parent_id'] ? '#' . $cat['parent_id'] : '-' ?></td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($cat['created_at'] ?? '')) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('/admin/categories/edit/' . $cat['id']) ?>" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="<?= url('/admin/categories/delete/' . $cat['id']) ?>" class="d-inline" onsubmit="return confirm('Delete this category?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="6" class="text-center text-muted py-4">No categories found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($categories['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-end">
        <nav><ul class="pagination pagination-sm mb-0">
            @if($categories['has_prev_page'])<li class="page-item"><a class="page-link" href="?page=<?= $categories['current_page'] - 1 ?>&search=<?= e($search) ?>">Prev</a></li>@endif
            @for($i = max(1, $categories['current_page'] - 2); $i <= min($categories['last_page'], $categories['current_page'] + 2); $i++)
                <li class="page-item <?= $i === $categories['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= e($search) ?>"><?= $i ?></a></li>
            @endfor
            @if($categories['has_more_pages'])<li class="page-item"><a class="page-link" href="?page=<?= $categories['current_page'] + 1 ?>&search=<?= e($search) ?>">Next</a></li>@endif
        </ul></nav>
    </div>
    @endif
</div>
@endsection
