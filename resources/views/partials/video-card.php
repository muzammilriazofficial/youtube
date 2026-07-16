<?php
$channelName = $video['channel_name'] ?? 'Channel';
$channelAvatar = $video['channel_avatar'] ?? '';
$channelSlug = $video['channel_custom_url'] ?? $video['channel_slug'] ?? '';
?>
<div class="video-card" onclick="window.location.href='<?= url('/video/' . e($video['slug'])) ?>'" role="link" tabindex="0">
    <div class="thumbnail">
        <?php if (!empty($video['thumbnail_path'])): ?>
            <img src="<?= url(e($video['thumbnail_path'])) ?>" alt="<?= e($video['title']) ?>" loading="lazy">
        <?php else: ?>
            <div style="position:absolute;top:0;left:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--yt-surface);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="var(--yt-text-muted)" opacity="0.4"><path d="M8 5v14l11-7z"/></svg>
            </div>
        <?php endif; ?>
        <?php if (!empty($video['duration'])): ?>
            <span class="duration"><?= gmdate('i:s', (int) $video['duration']) ?></span>
        <?php endif; ?>
    </div>
    <div class="video-info">
        <div class="video-channel-avatar">
            <a href="<?= url('/channel/' . e($channelSlug)) ?>" onclick="event.stopPropagation();">
                <?php if (!empty($channelAvatar)): ?>
                    <img src="<?= url(e($channelAvatar)) ?>" alt="">
                <?php else: ?>
                    <div class="avatar-initial"><?= strtoupper(substr($channelName, 0, 1)) ?></div>
                <?php endif; ?>
            </a>
        </div>
        <div class="video-text">
            <div class="video-title"><?= e($video['title']) ?></div>
            <div class="video-channel-name">
                <a href="<?= url('/channel/' . e($channelSlug)) ?>" onclick="event.stopPropagation();"><?= e($channelName) ?></a>
            </div>
            <div class="video-stats"><?= format_number((int) ($video['view_count'] ?? 0)) ?> views &middot; <?= time_ago($video['published_at'] ?? $video['created_at'] ?? '') ?></div>
        </div>
    </div>
</div>
