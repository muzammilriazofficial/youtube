<?php $__layout = 'layouts.app'; ?>

<h4 class="mb-4">Channels</h4>
<div class="row g-3">
    <?php foreach (($channels['data'] ?? []) as $ch): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <a href="<?= url('/channel/' . e($ch['custom_url'] ?? $ch['slug'])) ?>" class="text-decoration-none">
                <div class="card bg-secondary bg-opacity-25 h-100 channel-card">
                    <div class="card-body text-center py-4">
                        <?php if (!empty($ch['avatar'])): ?>
                            <img src="<?= e($ch['avatar']) ?>" class="avatar mb-3" alt="">
                        <?php else: ?>
                            <div class="avatar mx-auto mb-3 bg-secondary d-flex align-items-center justify-content-center"><i class="bi bi-person fs-3"></i></div>
                        <?php endif; ?>
                        <h6 class="mb-1" style="color: var(--bs-body-color)"><?= e($ch['name']) ?></h6>
                        <small class="text-muted"><?= format_number((int) ($ch['subscriber_count'] ?? 0)) ?> subscribers</small>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
    <?php if (empty($channels['data'] ?? [])): ?>
        <div class="col-12 text-center py-5 text-muted">No channels found.</div>
    <?php endif; ?>
</div>

<?php if (($channels['last_page'] ?? 1) > 1): ?>
<nav class="mt-4"><ul class="pagination justify-content-center">
    <?php for ($p = 1; $p <= ($channels['last_page'] ?? 1); $p++): ?>
        <li class="page-item <?= $p == ($channels['current_page'] ?? 1) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>&sort=<?= e($currentSort ?? 'popular') ?>"><?= $p ?></a></li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
