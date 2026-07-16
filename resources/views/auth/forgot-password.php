<?php $__layout = 'layouts.auth'; ?>

<h5 class="card-title text-center mb-4">Forgot Password</h5>
<p class="text-muted small text-center mb-4">Enter your email and we'll send you a reset link.</p>
<form method="POST" action="<?= url('/forgot-password') ?>">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" value="<?= e(old('email')) ?>" required autofocus>
    </div>
    <button type="submit" class="btn btn-primary w-100 mb-3">Send Reset Link</button>
    <p class="text-center mb-0 small"><a href="<?= url('/login') ?>">Back to Sign In</a></p>
</form>
