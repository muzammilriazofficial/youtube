@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Send Broadcast Notification</h4>
    <a href="<?= url('/admin/notifications') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card table-card">
    <div class="card-body p-4">
        <form method="POST" action="<?= url('/admin/notifications/send') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Title *</label>
                    <input type="text" class="form-control" name="title" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Target *</label>
                    <select class="form-select" name="target" id="notifTarget">
                        <option value="all">All Users</option>
                        <option value="role">Specific Role</option>
                    </select>
                </div>
                <div class="col-md-6" id="roleSelect" style="display:none;">
                    <label class="form-label fw-semibold">Role</label>
                    <select class="form-select" name="role_id">
                        @if(isset($roles))
                            @foreach($roles as $role)
                                <option value="<?= $role['id'] ?>"><?= e($role['name']) ?></option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Message *</label>
                    <textarea class="form-control" name="message" rows="5" required placeholder="Write your notification message..."></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Send Notification</button>
                    <a href="<?= url('/admin/notifications') ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('notifTarget')?.addEventListener('change', function() {
    document.getElementById('roleSelect').style.display = this.value === 'role' ? 'block' : 'none';
});
</script>
@endsection
