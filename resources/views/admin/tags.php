@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Tag Management</h4>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <form class="d-flex" method="GET">
                    <input type="text" class="form-control form-control-sm me-2" name="search" placeholder="Search tags..." value="<?= e($search) ?>">
                    <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i></button>
                </form>
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="newTagName" placeholder="New tag name...">
                    <button class="btn btn-primary" id="addTagBtn"><i class="bi bi-plus-lg me-1"></i>Add</button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>Name</th><th>Slug</th><th>Usage Count</th><th>Actions</th></tr></thead>
                <tbody id="tagsBody">
                    @if(count($tags['data']) > 0)
                        @foreach($tags['data'] as $tag)
                        <tr>
                            <td><?= $tag['id'] ?></td>
                            <td class="fw-semibold"><?= e($tag['name'] ?? '') ?></td>
                            <td class="text-muted small"><?= e($tag['slug'] ?? '') ?></td>
                            <td><span class="badge bg-primary badge-status"><?= $tag['usage_count'] ?? 0 ?></span></td>
                            <td>
                                <form method="POST" action="<?= url('/admin/tags/delete/' . $tag['id']) ?>" class="d-inline" onsubmit="return confirm('Delete this tag?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="5" class="text-center text-muted py-4">No tags found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($tags['last_page'] > 1)
    <div class="card-footer bg-transparent border-top d-flex justify-content-end">
        <nav><ul class="pagination pagination-sm mb-0">
            @if($tags['has_prev_page'])<li class="page-item"><a class="page-link" href="?page=<?= $tags['current_page'] - 1 ?>&search=<?= e($search) ?>">Prev</a></li>@endif
            @for($i = max(1, $tags['current_page'] - 2); $i <= min($tags['last_page'], $tags['current_page'] + 2); $i++)
                <li class="page-item <?= $i === $tags['current_page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= e($search) ?>"><?= $i ?></a></li>
            @endfor
            @if($tags['has_more_pages'])<li class="page-item"><a class="page-link" href="?page=<?= $tags['current_page'] + 1 ?>&search=<?= e($search) ?>">Next</a></li>@endif
        </ul></nav>
    </div>
    @endif
</div>

<script>
document.getElementById('addTagBtn')?.addEventListener('click', function() {
    const name = document.getElementById('newTagName').value.trim();
    if (!name) return;
    ajaxPost('<?= url("/admin/tags/store") ?>', { name: name }, function(err, res) {
        if (err) { alert('Error occurred.'); return; }
        if (res.success) { location.reload(); }
        else { alert(res.error || 'Failed to add tag.'); }
    });
});
document.getElementById('newTagName')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('addTagBtn').click(); }
});
</script>
@endsection
