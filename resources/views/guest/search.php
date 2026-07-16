<?php $__layout = 'layouts.app'; ?>

<div class="yt-skel-page">
    <div style="height:40px;border-radius:8px;margin-bottom:24px;" class="yt-skeleton"></div>
    <div style="display:flex;gap:8px;margin-bottom:24px;">
        <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="yt-skeleton" style="width:80px;height:32px;border-radius:20px;flex-shrink:0;"></div>
        <?php endfor; ?>
    </div>
    <?php for ($i = 0; $i < 5; $i++): ?>
    <div class="yt-skel-row">
        <div class="skel-thumb yt-skeleton"></div>
        <div class="skel-body">
            <div class="skel-title yt-skeleton"></div>
            <div class="skel-channel yt-skeleton"></div>
            <div class="skel-views yt-skeleton"></div>
        </div>
    </div>
    <?php endfor; ?>
</div>

<div class="yt-page-content">

<div class="row">
    <div class="col-lg-8">
        <form class="d-flex mb-4" action="<?= url('/search') ?>" method="GET">
            <div class="input-group">
                <input type="search" class="form-control" name="q" placeholder="Search videos, channels..." value="<?= e($query ?? '') ?>">
                <button class="btn btn-primary" type="submit"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0016 9.5 6.5 6.5 0 109.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg></button>
            </div>
        </form>

        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="?q=<?= urlencode($query ?? '') ?>&type=all" class="chip <?= ($currentType ?? 'all') === 'all' ? 'active' : '' ?>">All</a>
            <a href="?q=<?= urlencode($query ?? '') ?>&type=videos" class="chip <?= ($currentType ?? '') === 'videos' ? 'active' : '' ?>">Videos</a>
            <a href="?q=<?= urlencode($query ?? '') ?>&type=channels" class="chip <?= ($currentType ?? '') === 'channels' ? 'active' : '' ?>">Channels</a>
            <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='?q=<?= urlencode($query ?? '') ?>&type=<?= e($currentType ?? 'all') ?>&sort='+this.value">
                <option value="relevance" <?= ($currentSort ?? '') === 'relevance' ? 'selected' : '' ?>>Relevance</option>
                <option value="upload_date" <?= ($currentSort ?? '') === 'upload_date' ? 'selected' : '' ?>>Upload Date</option>
                <option value="view_count" <?= ($currentSort ?? '') === 'view_count' ? 'selected' : '' ?>>View Count</option>
            </select>
        </div>

        <?php if (!empty($query)): ?>
            <p style="font-size:13px;color:var(--yt-text-secondary);margin-bottom:16px;">About <?= format_number($totalResults ?? 0) ?> results</p>
        <?php endif; ?>

        <?php if (!empty($results['videos']['data'] ?? [])): ?>
            <?php foreach ($results['videos']['data'] as $video): ?>
                <div style="display:flex;gap:12px;margin-bottom:20px;">
                    <a href="<?= url('/video/' . e($video['slug'])) ?>" class="flex-shrink-0" style="width:320px;text-decoration:none;">
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
                        <div style="font-size:18px;font-weight:400;line-height:1.4;color:var(--yt-text-primary);margin-bottom:4px;">
                            <a href="<?= url('/video/' . e($video['slug'])) ?>" class="yt-link" style="text-decoration:none;"><?= e($video['title']) ?></a>
                        </div>
                        <div style="font-size:12px;color:var(--yt-text-secondary);margin-bottom:8px;"><?= format_number((int) ($video['view_count'] ?? 0)) ?> views &middot; <?= time_ago($video['published_at'] ?? '') ?></div>
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                            <a href="<?= url('/channel/' . e($video['channel_custom_url'] ?? $video['channel_slug'] ?? '')) ?>" style="text-decoration:none;">
                                <?php if (!empty($video['channel_avatar'])): ?>
                                    <img src="<?= url(e($video['channel_avatar'])) ?>" style="width:24px;height:24px;border-radius:50%;object-fit:cover;" alt="">
                                <?php else: ?>
                                    <div style="width:24px;height:24px;border-radius:50%;background:var(--yt-surface);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:500;color:var(--yt-text-secondary);"><?= strtoupper(substr($video['channel_name'] ?? 'C', 0, 1)) ?></div>
                                <?php endif; ?>
                            </a>
                            <a href="<?= url('/channel/' . e($video['channel_custom_url'] ?? $video['channel_slug'] ?? '')) ?>" style="font-size:12px;color:var(--yt-text-secondary);text-decoration:none;"><?= e($video['channel_name'] ?? 'Channel') ?></a>
                        </div>
                        <?php if (!empty($video['description'])): ?>
                            <div style="font-size:12px;color:var(--yt-text-secondary);line-height:1.4;"><?= e(truncate($video['description'], 150)) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (($results['videos']['last_page'] ?? 1) > 1): ?>
                <nav class="mt-4"><ul class="pagination justify-content-center">
                    <?php for ($p = 1; $p <= ($results['videos']['last_page'] ?? 1); $p++): ?>
                        <li class="page-item <?= $p == ($results['videos']['current_page'] ?? 1) ? 'active' : '' ?>">
                            <a class="page-link" href="?q=<?= urlencode($query ?? '') ?>&type=<?= e($currentType ?? 'all') ?>&page=<?= $p ?>&sort=<?= e($currentSort ?? 'relevance') ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                </ul></nav>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($results['channels'] ?? [])): ?>
            <h5 style="font-size:16px;font-weight:500;margin-bottom:16px;margin-top:24px;">Channels</h5>
            <?php foreach ($results['channels'] as $ch): ?>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                    <a href="<?= url('/channel/' . e($ch['custom_url'] ?? $ch['slug'])) ?>">
                        <?php if (!empty($ch['avatar'])): ?>
                            <img src="<?= url(e($ch['avatar'])) ?>" style="width:48px;height:48px;border-radius:50%;object-fit:cover;" alt="">
                        <?php else: ?>
                            <div style="width:48px;height:48px;border-radius:50%;background:var(--yt-surface);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:500;color:var(--yt-text-secondary);"><?= strtoupper(substr($ch['name'], 0, 1)) ?></div>
                        <?php endif; ?>
                    </a>
                    <div>
                        <div style="font-size:16px;font-weight:500;color:var(--yt-text-primary);"><a href="<?= url('/channel/' . e($ch['custom_url'] ?? $ch['slug'])) ?>" class="yt-link" style="text-decoration:none;"><?= e($ch['name']) ?></a></div>
                        <div style="font-size:12px;color:var(--yt-text-secondary);"><?= format_number((int) ($ch['subscriber_count'] ?? 0)) ?> subscribers</div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (empty($results['videos']['data'] ?? []) && empty($results['channels'] ?? [])): ?>
            <div style="text-align:center;padding:64px 16px;color:var(--yt-text-secondary);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="var(--yt-text-muted)" style="margin-bottom:12px;opacity:0.4;"><path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0016 9.5 6.5 6.5 0 109.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                <div><?= $query ? 'No results found.' : 'Start typing to search.' ?></div>
            </div>
        <?php endif; ?>
    </div>
</div>

</div>
