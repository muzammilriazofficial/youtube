<?php $__layout = 'layouts.app'; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <h4 style="font-size:20px;font-weight:500;margin:0;color:var(--yt-text-primary);">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="red" style="vertical-align:middle;margin-right:6px;"><path d="M17.53 11.2c-.23-.3-.51-.56-.77-.82-.67-.6-1.43-1.03-2.07-1.66C13.3 7.26 13 5.72 13.73 4c-2.36.6-4.28 2.09-5.43 4.05C7.27 9.23 7 11.08 7.38 12.84c.37 1.75 1.56 3.22 3.13 3.95 1.57.73 3.37.67 4.93-.13 1.56-.8 2.69-2.38 3.16-4.07.29-1.04.19-2.12-.13-3.1l-.81.7z"/><path d="M13.13 22.19l-1.63-3.83c1.57-.65 2.74-1.9 3.13-3.46.37-1.75-.11-3.56-1.28-4.96-1.18-1.41-2.9-2.26-4.74-2.26-.46 0-.91.06-1.35.18L3.49 9.86C5.31 8.18 7.75 7.2 10.25 7.2c3.24 0 6.14 1.52 8.01 3.92 1.87 2.4 2.68 5.72 2.17 8.94-.51 3.23-2.48 6.02-5.22 7.73l-2.08 4.43z"/></svg>
        Trending
    </h4>
    <div style="display:flex;flex-wrap:wrap;gap:6px;">
        <a href="?category=" class="chip <?= empty($currentCategory ?? '') ? 'active' : '' ?>">All</a>
        <?php foreach (($categories ?? []) as $cat): ?>
            <a href="?category=<?= e($cat['slug']) ?>" class="chip <?= ($currentCategory ?? '') === $cat['slug'] ? 'active' : '' ?>"><?= e($cat['name']) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div>
    <?php foreach (($trending['data'] ?? []) as $i => $video): ?>
        <div style="display:flex;gap:12px;margin-bottom:20px;padding:8px 0;">
            <span style="font-size:24px;font-weight:500;color:var(--yt-text-secondary);min-width:40px;text-align:center;line-height:1;align-self:center;"><?= $i + 1 ?></span>
            <a href="<?= url('/video/' . e($video['slug'])) ?>" class="flex-shrink-0" style="width:240px;text-decoration:none;">
                <div style="position:relative;padding-top:56.25%;border-radius:12px;overflow:hidden;background:var(--yt-surface);">
                    <?php if (!empty($video['thumbnail_path'])): ?>
                        <img src="<?= url(e($video['thumbnail_path'])) ?>" alt="" loading="lazy" style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <div style="position:absolute;top:0;left:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="var(--yt-text-muted)" opacity="0.4"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($video['duration'])): ?>
                        <span class="duration"><?= gmdate('i:s', (int) $video['duration']) ?></span>
                    <?php endif; ?>
                </div>
            </a>
            <div style="flex:1;min-width:0;padding-top:2px;">
                <div style="font-size:16px;font-weight:400;color:var(--yt-text-primary);margin-bottom:4px;">
                    <a href="<?= url('/video/' . e($video['slug'])) ?>" class="yt-link" style="text-decoration:none;"><?= e($video['title']) ?></a>
                </div>
                <div style="font-size:12px;color:var(--yt-text-secondary);margin-bottom:4px;"><?= format_number((int) ($video['view_count'] ?? 0)) ?> views &middot; <?= time_ago($video['published_at'] ?? '') ?></div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <a href="<?= url('/channel/' . e($video['channel_custom_url'] ?? $video['channel_slug'] ?? '')) ?>" style="text-decoration:none;">
                        <?php if (!empty($video['channel_avatar'])): ?>
                            <img src="<?= url(e($video['channel_avatar'])) ?>" style="width:20px;height:20px;border-radius:50%;object-fit:cover;" alt="">
                        <?php else: ?>
                            <div style="width:20px;height:20px;border-radius:50%;background:var(--yt-surface);display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:500;color:var(--yt-text-secondary);"><?= strtoupper(substr($video['channel_name'] ?? 'C', 0, 1)) ?></div>
                        <?php endif; ?>
                    </a>
                    <a href="<?= url('/channel/' . e($video['channel_custom_url'] ?? $video['channel_slug'] ?? '')) ?>" style="font-size:12px;color:var(--yt-text-secondary);text-decoration:none;"><?= e($video['channel_name'] ?? 'Channel') ?></a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($trending['data'] ?? [])): ?>
        <div style="text-align:center;padding:64px 16px;color:var(--yt-text-secondary);">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="var(--yt-text-muted)" style="margin-bottom:12px;opacity:0.4;"><path d="M17.53 11.2c-.23-.3-.51-.56-.77-.82-.67-.6-1.43-1.03-2.07-1.66C13.3 7.26 13 5.72 13.73 4c-2.36.6-4.28 2.09-5.43 4.05C7.27 9.23 7 11.08 7.38 12.84c.37 1.75 1.56 3.22 3.13 3.95 1.57.73 3.37.67 4.93-.13 1.56-.8 2.69-2.38 3.16-4.07.29-1.04.19-2.12-.13-3.1l-.81.7z"/></svg>
            <div>No trending videos.</div>
        </div>
    <?php endif; ?>
</div>

<?php if (($trending['last_page'] ?? 1) > 1): ?>
<nav class="mt-4"><ul class="pagination justify-content-center">
    <?php for ($p = 1; $p <= ($trending['last_page'] ?? 1); $p++): ?>
        <li class="page-item <?= $p == ($trending['current_page'] ?? 1) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $p ?>&category=<?= e($currentCategory ?? '') ?>"><?= $p ?></a>
        </li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
