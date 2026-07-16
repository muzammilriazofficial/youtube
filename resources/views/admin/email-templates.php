@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Email Templates</h4>
</div>

<div class="card table-card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>Name</th><th>Subject</th><th>Updated</th><th>Actions</th></tr></thead>
                <tbody>
                    @if(isset($templates) && count($templates) > 0)
                        @foreach($templates as $t)
                        <tr>
                            <td><?= $t['id'] ?></td>
                            <td class="fw-semibold"><?= e($t['name'] ?? '') ?></td>
                            <td class="text-muted small"><?= e($t['subject'] ?? '') ?></td>
                            <td class="text-muted small"><?= isset($t['updated_at']) ? date('M d, Y', strtotime($t['updated_at'])) : '-' ?></td>
                            <td><a href="<?= url('/admin/email-templates/edit/' . $t['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a></td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="5" class="text-center text-muted py-4">No templates found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
