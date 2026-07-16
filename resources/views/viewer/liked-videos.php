<?php $__layout = 'layouts.dashboard'; ?>

<h4 class="mb-4">Liked Videos</h4>
<div class="row g-3">
    <?php foreach (($likedVideos['data'] ?? []) as $item): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="video-card">
                <a href="<?= url('/video/' . e($item['slug'])) ?>" class="text-decoration-none">
                    <div class="thumbnail">
                        <?php if (!empty($item['thumbnail'])): ?>
                            <img src="<?= e($item['thumbnail']) ?>" alt="" loading="lazy">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center h-100 bg-secondary"><i class="bi bi-play-circle fs-1"></i></div>
                        <?php endif; ?>
                        <?php if (!empty($item['duration'])): ?>
                            <span class="duration"><?= gmdate('i:s', (int) $item['duration']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-2"><h6 class="mb-1 text-truncate" style="color: var(--bs-body-color)"><?= e($item['title']) ?></h6><small class="text-muted"><?= format_number((int) ($item['view_count'] ?? 0)) ?> views</small></div>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($likedVideos['data'] ?? [])): ?>
        <div class="col-12 text-center py-5 text-muted"><i class="bi bi-hand-thumbs-up fs-1 d-block mb-2"></i>No liked videos.</div>
    <?php endif; ?>
</div>

<?php if (($likedVideos['last_page'] ?? 1) > 1): ?>
<nav class="mt-4"><ul class="pagination justify-content-center">
    <?php for ($p = 1; $p <= ($likedVideos['last_page'] ?? 1); $p++): ?>
        <li class="page-item <?= $p == ($likedVideos['current_page'] ?? 1) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a></li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
