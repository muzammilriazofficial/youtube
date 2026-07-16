@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Backup Management</h4>
    <form method="POST" action="<?= url('/admin/backup/create') ?>" class="d-inline" onsubmit="return confirm('Create a new backup? This may take a while.')">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Create Backup</button>
    </form>
</div>

<div class="card table-card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Filename</th><th>Size</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    @if(count($backups) > 0)
                        @foreach($backups as $b)
                        <tr>
                            <td class="fw-semibold font-monospace small"><?= e($b['filename']) ?></td>
                            <td class="text-muted"><?= e($b['size']) ?></td>
                            <td class="text-muted small"><?= e($b['created_at']) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('/admin/backup/download/' . $b['filename']) ?>" class="btn btn-outline-primary"><i class="bi bi-download me-1"></i>Download</a>
                                    <form method="POST" action="<?= url('/admin/backup/restore') ?>" class="d-inline" onsubmit="return confirm('Restore from this backup? This will overwrite the current database.')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="filename" value="<?= e($b['filename']) ?>">
                                        <button type="submit" class="btn btn-outline-warning"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="4" class="text-center text-muted py-4">No backups found. Create your first backup.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card table-card">
    <div class="card-body">
        <h6 class="fw-bold"><i class="bi bi-info-circle me-2"></i>Backup Information</h6>
        <ul class="text-muted small mb-0">
            <li>Backups are stored in the <code>storage/backups</code> directory</li>
            <li>Backups contain SQL dumps of the entire database</li>
            <li>Restoring a backup will <strong>overwrite</strong> the current database</li>
            <li>Always download a backup before restoring</li>
        </ul>
    </div>
</div>
@endsection
