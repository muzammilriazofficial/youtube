<?php $__layout = 'layouts.auth'; ?>

<h5 class="card-title text-center mb-4">Sign In</h5>
<form method="POST" action="<?= url('/login') ?>">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" value="<?= e(old('email')) ?>" required autofocus>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" name="password" required>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="remember" id="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>
        <a href="<?= url('/forgot-password') ?>" class="text-decoration-none small">Forgot password?</a>
    </div>
    <button type="submit" class="btn btn-primary w-100 mb-3">Sign In</button>
    <p class="text-center mb-0 small">Don't have an account? <a href="<?= url('/register') ?>">Register</a></p>
</form>
