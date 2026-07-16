<?php $__layout = 'layouts.dashboard'; ?>

<h4 class="mb-4">Edit Profile</h4>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item"><a class="nav-link <?= ($activeTab ?? 'profile') === 'profile' ? 'active' : '' ?>" href="<?= url('/viewer/profile/edit') ?>">Profile</a></li>
    <li class="nav-item"><a class="nav-link <?= ($activeTab ?? '') === 'password' ? 'active' : '' ?>" href="<?= url('/viewer/change-password') ?>">Password</a></li>
</ul>

<?php if (($activeTab ?? 'profile') === 'profile'): ?>
<form method="POST" action="<?= url('/viewer/profile/update') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-md-8">
            <div class="mb-3">
                <label class="form-label">Display Name</label>
                <input type="text" class="form-control" name="display_name" value="<?= e($user['display_name'] ?? $user['username']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" value="<?= e($user['username']) ?>" disabled>
                <small class="text-muted">Username cannot be changed.</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Bio</label>
                <textarea class="form-control" name="description" rows="4" maxlength="500"><?= e($user['description'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Avatar</label>
                <input type="file" class="form-control" name="avatar" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
        <div class="col-md-4 text-center">
            <?php if (!empty($user['avatar'])): ?>
                <img src="<?= e($user['avatar']) ?>" class="rounded-circle mb-3" width="120" height="120" alt="">
            <?php else: ?>
                <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:120px;height:120px"><i class="bi bi-person fs-1"></i></div>
            <?php endif; ?>
        </div>
    </div>
</form>

<hr class="my-4">
<h5 class="text-danger">Danger Zone</h5>
<form method="POST" action="<?= url('/viewer/delete-account') ?>" onsubmit="return confirm('Are you sure you want to delete your account? This cannot be undone.')">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-danger">Delete Account</button>
</form>
<?php else: ?>
<form method="POST" action="<?= url('/viewer/change-password') ?>">
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Current Password</label>
                <input type="password" class="form-control" name="current_password" required>
            </div>
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" class="form-control" name="password" required minlength="8">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" name="password_confirmation" required>
            </div>
            <button type="submit" class="btn btn-primary">Change Password</button>
        </div>
    </div>
</form>
<?php endif; ?>
