<?php $__layout = 'layouts.app'; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <h4 style="font-size:20px;font-weight:500;margin:0;color:var(--yt-text-primary);"><?= e($pageTitle ?? 'Videos') ?></h4>
    <select class="form-select" style="width:auto;font-size:13px;background:var(--yt-surface);color:var(--yt-text-primary);border:1px solid var(--yt-chip-bg);" onchange="window.location.href=this.value">
        <option value="?sort=latest<?= ($currentCategory ?? '') ? '&category=' . e($currentCategory) : '' ?>" <?= ($currentSort ?? '') === 'latest' ? 'selected' : '' ?>>Latest</option>
        <option value="?sort=popular<?= ($currentCategory ?? '') ? '&category=' . e($currentCategory) : '' ?>" <?= ($currentSort ?? '') === 'popular' ? 'selected' : '' ?>>Most Popular</option>
        <option value="?sort=oldest<?= ($currentCategory ?? '') ? '&category=' . e($currentCategory) : '' ?>" <?= ($currentSort ?? '') === 'oldest' ? 'selected' : '' ?>>Oldest</option>
    </select>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
    <?php foreach (($videos['data'] ?? []) as $video): ?>
        <?php include VIEW_PATH . '/partials/video-card.php'; ?>
    <?php endforeach; ?>
    <?php if (empty($videos['data'] ?? [])): ?>
        <div style="grid-column:1/-1;text-align:center;padding:64px 16px;color:var(--yt-text-secondary);">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="var(--yt-text-muted)" style="margin-bottom:12px;opacity:0.4;"><path d="M19 3H4.99c-1.11 0-1.98.89-1.98 2L3 19c0 1.1.88 2 1.99 2H19c1.1 0 2-.9 2-2V5c0-1.11-.9-2-2-2zm0 12h-4c0 1.66-1.35 3-3 3s-3-1.34-3-3H4.99V5H19v10z"/></svg>
            <div>No videos found.</div>
        </div>
    <?php endif; ?>
</div>

<?php if (($videos['last_page'] ?? 1) > 1): ?>
<nav class="mt-4"><ul class="pagination justify-content-center">
    <?php for ($p = 1; $p <= ($videos['last_page'] ?? 1); $p++): ?>
        <li class="page-item <?= $p == ($videos['current_page'] ?? 1) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $p ?>&sort=<?= e($currentSort ?? 'latest') ?><?= ($currentCategory ?? '') ? '&category=' . e($currentCategory) : '' ?>"><?= $p ?></a>
        </li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
