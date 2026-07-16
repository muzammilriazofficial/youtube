<?php $__layout = 'layouts.app'; ?>

<div class="yt-skel-page">
    <div style="height:200px;background:var(--yt-surface);border-radius:12px;margin-bottom:16px;" class="yt-skeleton"></div>
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
        <div class="yt-skeleton yt-skeleton-circle" style="width:80px;height:80px;flex-shrink:0;"></div>
        <div style="flex:1">
            <div class="yt-skeleton" style="height:24px;width:30%;border-radius:4px;margin-bottom:8px;"></div>
            <div class="yt-skeleton" style="height:14px;width:50%;border-radius:4px;"></div>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
        <?php for ($i = 0; $i < 6; $i++): ?>
        <div class="yt-skel-card">
            <div class="skel-thumb yt-skeleton yt-skeleton-thumb"></div>
            <div class="skel-info">
                <div class="skel-lines">
                    <div class="skel-title"></div>
                    <div class="skel-meta"></div>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
</div>

<div class="yt-page-content">

<div class="yt-channel-banner">
    <?php if (!empty($channel['banner'])): ?>
        <img src="<?= url(e($channel['banner'])) ?>" alt="Channel Banner">
    <?php else: ?>
        <div style="width:100%;height:100%;background:linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);"></div>
    <?php endif; ?>
</div>

<div class="yt-channel-header">
    <div class="channel-avatar">
        <?php if (!empty($channel['avatar'])): ?>
            <img src="<?= url(e($channel['avatar'])) ?>" alt="<?= e($channel['name']) ?>" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
        <?php else: ?>
            <?= strtoupper(substr($channel['name'], 0, 1)) ?>
        <?php endif; ?>
    </div>
    <div class="channel-info">
        <div class="channel-name">
            <?= e($channel['name']) ?>
            <?php if (!empty($channel['is_verified'])): ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#aaa"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            <?php endif; ?>
        </div>
        <div class="channel-handle">@<?= e($channel['custom_url'] ?? $channel['slug']) ?></div>
        <div class="channel-stats">
            <?= format_number((int) ($totalVideos ?? 0)) ?> videos
            &middot;
            <?= format_number((int) ($channel['subscriber_count'] ?? 0)) ?> subscribers
            &middot;
            <?= format_number((int) ($totalViews ?? 0)) ?> views
        </div>
        <?php if (!empty($channel['description'])): ?>
            <div class="channel-description" style="margin-top:12px;font-size:14px;color:var(--yt-text-secondary);max-width:600px;line-height:1.5;">
                <?= e(truncate($channel['description'], 300)) ?>
            </div>
        <?php endif; ?>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
        <?php if ($isSubscribed ?? false): ?>
            <button class="yt-subscribe-btn subscribed" data-subscribe="<?= (int) $channel['id'] ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-right:6px"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                Subscribed
            </button>
        <?php else: ?>
            <button class="yt-subscribe-btn not-subscribed" data-subscribe="<?= (int) $channel['id'] ?>">
                Subscribe
            </button>
        <?php endif; ?>
        <button class="yt-subscribe-btn" style="background:transparent;border:1px solid var(--yt-border);color:var(--yt-text-primary);" onclick="navigator.share?.({title:'<?= e($channel['name']) ?>',url:window.location.href})">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-right:4px"><path d="M14 9V3L22 12L14 21V15C8 15 4 17 1 21C2 15 5 9 14 9Z"/></svg>
            Share
        </button>
    </div>
</div>

<ul class="yt-channel-tabs" style="list-style:none;padding:0;margin:0;overflow:hidden;">
    <li><a class="yt-channel-tab <?= ($currentTab ?? 'videos') === 'videos' ? 'active' : '' ?>" href="?tab=videos">Videos</a></li>
    <li><a class="yt-channel-tab <?= ($currentTab ?? '') === 'playlists' ? 'active' : '' ?>" href="?tab=playlists">Playlists</a></li>
</ul>

<?php if (($currentTab ?? 'videos') === 'videos'): ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;padding-bottom:32px;">
        <?php foreach (($videos['data'] ?? []) as $video): ?>
            <div class="yt-card" style="cursor:pointer;" onclick="window.location='<?= url('/video/' . e($video['slug'])) ?>'">
                <div style="position:relative;padding-top:56.25%;background:var(--yt-surface);border-radius:var(--yt-radius-md);overflow:hidden;">
                    <?php if (!empty($video['thumbnail'])): ?>
                        <img src="<?= e($video['thumbnail']) ?>" alt="" loading="lazy" style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <div style="position:absolute;top:0;left:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--yt-surface);">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="var(--yt-text-muted)"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($video['duration'])): ?>
                        <span style="position:absolute;bottom:4px;right:4px;background:rgba(0,0,0,0.8);color:#fff;padding:2px 4px;border-radius:4px;font-size:12px;font-weight:500;"><?= gmdate('i:s', (int) $video['duration']) ?></span>
                    <?php endif; ?>
                </div>
                <div style="padding:8px 0;">
                    <div style="display:flex;gap:8px;">
                        <div style="flex:1;min-width:0;">
                            <h3 style="font-size:14px;font-weight:500;line-height:1.4;margin:0;color:var(--yt-text-primary);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?= e($video['title']) ?></h3>
                            <div style="font-size:12px;color:var(--yt-text-secondary);margin-top:4px;"><?= format_number((int) ($video['view_count'] ?? 0)) ?> views</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($videos['data'] ?? [])): ?>
            <div style="grid-column:1/-1;text-align:center;padding:64px 16px;color:var(--yt-text-secondary);">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="var(--yt-text-muted)" style="margin-bottom:16px;"><path d="M18 4l2 4h-3l-2-4h-2l2 4h-3l-2-4H8l2 4H7L5 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4h-4z"/></svg>
                <div style="font-size:16px;font-weight:500;margin-bottom:8px;">No videos yet</div>
                <div style="font-size:14px;">This channel hasn't uploaded any videos.</div>
            </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;padding-bottom:32px;">
        <?php foreach (($playlists['data'] ?? []) as $pl): ?>
            <a href="<?= url('/viewer/playlists/' . $pl['id']) ?>" style="text-decoration:none;color:inherit;" class="yt-card">
                <div style="position:relative;padding-top:56.25%;background:var(--yt-surface);border-radius:var(--yt-radius-md);overflow:hidden;">
                    <div style="position:absolute;top:0;left:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:8px;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="var(--yt-text-muted)"><path d="M15 6H3v2h12V6zm0 4H3v2h12v-2zM3 16h8v-2H3v2zM17 6v8.18c-.31-.11-.65-.18-1-.18-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3V8h3V6h-5z"/></svg>
                        <span style="font-size:13px;color:var(--yt-text-secondary);"><?= $pl['video_count'] ?? 0 ?> videos</span>
                    </div>
                </div>
                <div style="padding:8px 0;">
                    <h3 style="font-size:14px;font-weight:500;margin:0;color:var(--yt-text-primary);"><?= e($pl['title']) ?></h3>
                </div>
            </a>
        <?php endforeach; ?>
        <?php if (empty($playlists['data'] ?? [])): ?>
            <div style="grid-column:1/-1;text-align:center;padding:64px 16px;color:var(--yt-text-secondary);">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="var(--yt-text-muted)" style="margin-bottom:16px;"><path d="M15 6H3v2h12V6zm0 4H3v2h12v-2zM3 16h8v-2H3v2zM17 6v8.18c-.31-.11-.65-.18-1-.18-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3V8h3V6h-5z"/></svg>
                <div style="font-size:16px;font-weight:500;margin-bottom:8px;">No playlists</div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

</div>
