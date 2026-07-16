<?php $__layout = 'layouts.auth'; ?>

<h5 class="card-title text-center mb-4">Reset Password</h5>
<form method="POST" action="<?= url('/reset-password/' . e($token ?? '')) ?>">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label">New Password</label>
        <input type="password" class="form-control" name="password" required minlength="8">
    </div>
    <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" class="form-control" name="password_confirmation" required>
    </div>
    <button type="submit" class="btn btn-primary w-100 mb-3">Reset Password</button>
    <p class="text-center mb-0 small"><a href="<?= url('/login') ?>">Back to Sign In</a></p>
</form>
