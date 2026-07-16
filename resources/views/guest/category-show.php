<?php $__layout = 'layouts.app'; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <h4 style="font-size:20px;font-weight:500;margin:0;color:var(--yt-text-primary);"><?= e($category['name']) ?></h4>
    <select class="form-select" style="width:auto;font-size:13px;background:var(--yt-surface);color:var(--yt-text-primary);border:1px solid var(--yt-chip-bg);" onchange="window.location.href='?sort='+this.value">
        <option value="latest" <?= ($currentSort ?? '') === 'latest' ? 'selected' : '' ?>>Latest</option>
        <option value="popular" <?= ($currentSort ?? '') === 'popular' ? 'selected' : '' ?>>Most Popular</option>
    </select>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
    <?php foreach (($videos['data'] ?? []) as $video): ?>
        <?php include VIEW_PATH . '/partials/video-card.php'; ?>
    <?php endforeach; ?>
    <?php if (empty($videos['data'] ?? [])): ?>
        <div style="grid-column:1/-1;text-align:center;padding:64px 16px;color:var(--yt-text-secondary);">No videos in this category.</div>
    <?php endif; ?>
</div>

<?php if (($videos['last_page'] ?? 1) > 1): ?>
<nav class="mt-4"><ul class="pagination justify-content-center">
    <?php for ($p = 1; $p <= ($videos['last_page'] ?? 1); $p++): ?>
        <li class="page-item <?= $p == ($videos['current_page'] ?? 1) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>&sort=<?= e($currentSort ?? 'latest') ?>"><?= $p ?></a></li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
