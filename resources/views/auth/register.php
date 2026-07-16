<?php $__layout = 'layouts.auth'; ?>

<h5 class="card-title text-center mb-4">Create Account</h5>
<form method="POST" action="<?= url('/register') ?>">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" class="form-control" name="username" value="<?= e(old('username')) ?>" required minlength="3" maxlength="30">
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" value="<?= e(old('email')) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" name="password" required minlength="8">
    </div>
    <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" class="form-control" name="password_confirmation" required>
    </div>
    <button type="submit" class="btn btn-primary w-100 mb-3">Register</button>
    <p class="text-center mb-0 small">Already have an account? <a href="<?= url('/login') ?>">Sign In</a></p>
</form>
