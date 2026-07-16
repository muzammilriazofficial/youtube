<?php $__layout = 'layouts.app'; ?>

<h4 class="mb-4">Categories</h4>
<div class="row g-3">
    <?php foreach (($categories ?? []) as $cat): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <a href="<?= url('/category/' . e($cat['slug'])) ?>" class="text-decoration-none">
                <div class="card bg-secondary bg-opacity-25 h-100">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-folder fs-1 mb-2" style="color: var(--bs-primary)"></i>
                        <h5 class="card-title mb-1" style="color: var(--bs-body-color)"><?= e($cat['name']) ?></h5>
                        <?php if (!empty($cat['description'])): ?>
                            <p class="card-text small text-muted"><?= e(truncate($cat['description'], 60)) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
    <?php if (empty($categories ?? [])): ?>
        <div class="col-12 text-center py-5 text-muted"><i class="bi bi-grid fs-1 d-block mb-2"></i>No categories available.</div>
    <?php endif; ?>
</div>
