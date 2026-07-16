<?php $__layout = 'layouts.app'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <h2 class="mb-4"><?= e($pageTitle ?? 'Page') ?></h2>

        <?php if (!empty($showContactForm)): ?>
            <form method="POST" action="<?= url('/contact') ?>">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" value="<?= e(old('name')) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?= e(old('email')) ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Subject</label>
                        <input type="text" class="form-control" name="subject" value="<?= e(old('subject')) ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" name="message" rows="5" required><?= e(old('message')) ?></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <div class="content"><?= nl2br(e($content ?? '')) ?></div>
        <?php endif; ?>
    </div>
</div>
