<?php $__layout = 'layouts.app'; ?>

<style>
    .video-watch { max-width: 1200px; margin: 0 auto; padding: 24px; display: flex; gap: 24px; }
    .video-main { flex: 1; min-width: 0; }
    .video-sidebar { width: 400px; flex-shrink: 0; }

    .video-player {
        position: relative; width: 100%; aspect-ratio: 16/9; background: #000; border-radius: 12px;
        overflow: hidden; margin-bottom: 12px;
        cursor: pointer;
    }
    .video-player video { width: 100%; height: 100%; display: block; }
    .video-player svg { width: 64px; height: 64px; fill: var(--yt-text-muted); opacity: 0.4; }

    .vp-center-btn {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
        width: 68px; height: 48px; background: rgba(0,0,0,0.6); border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        opacity: 0; transition: opacity 0.25s; pointer-events: none;
    }
    .video-player:hover .vp-center-btn.vp-paused { opacity: 1; }
    .video-player:not(:hover) .vp-center-btn.vp-paused { opacity: 1; }
    .vp-center-btn svg { width: 32px; height: 32px; fill: #fff; }

    .vp-bottom {
        position: absolute; bottom: 0; left: 0; right: 0;
        background: linear-gradient(transparent, rgba(0,0,0,0.85));
        padding: 30px 12px 8px; opacity: 0; transition: opacity 0.25s;
    }
    .video-player:hover .vp-bottom { opacity: 1; }

    .vp-progress-wrap {
        width: 100%; height: 4px; background: rgba(255,255,255,0.2);
        border-radius: 2px; cursor: pointer; margin-bottom: 6px; position: relative;
    }
    .vp-progress-wrap:hover { height: 6px; }
    .vp-progress-bar {
        height: 100%; background: #f00; border-radius: 2px; width: 0%; position: relative;
        transition: width 0.1s linear;
    }
    .vp-progress-wrap:hover .vp-progress-bar { background: #f00; }
    .vp-progress-bar::after {
        content: ''; position: absolute; right: -6px; top: 50%; transform: translateY(-50%);
        width: 12px; height: 12px; background: #f00; border-radius: 50%; opacity: 0;
        transition: opacity 0.15s;
    }
    .vp-progress-wrap:hover .vp-progress-bar::after { opacity: 1; }

    .vp-controls {
        display: flex; align-items: center; justify-content: space-between;
    }
    .vp-controls-left, .vp-controls-right { display: flex; align-items: center; gap: 4px; }
    .vp-btn {
        width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;
        border: none; background: transparent; cursor: pointer; border-radius: 50%;
        transition: background 0.15s;
    }
    .vp-btn:hover { background: rgba(255,255,255,0.15); }
    .vp-btn svg { width: 20px; height: 20px; fill: #fff; }
    .vp-time { font-size: 13px; color: #fff; font-family: 'Roboto', Arial, sans-serif; margin-left: 4px; white-space: nowrap; }
    .vp-title { font-size: 13px; color: #fff; font-family: 'Roboto', Arial, sans-serif; font-weight: 500; flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0 12px; }

    .vp-volume-wrap { display: flex; align-items: center; gap: 0; }
    .vp-volume-slider {
        width: 0; height: 4px; -webkit-appearance: none; appearance: none;
        background: #fff; border-radius: 2px; outline: none;
        transition: width 0.2s; cursor: pointer; margin-left: 2px;
    }
    .vp-volume-wrap:hover .vp-volume-slider { width: 60px; }
    .vp-volume-slider::-webkit-slider-thumb {
        -webkit-appearance: none; width: 12px; height: 12px;
        background: #fff; border-radius: 50%; cursor: pointer;
    }
    .vp-volume-slider::-moz-range-thumb {
        width: 12px; height: 12px; background: #fff;
        border-radius: 50%; border: none; cursor: pointer;
    }

    .vp-settings-panel {
        display: none; position: absolute; bottom: 52px; right: 8px;
        background: rgba(28,28,28,0.95); border-radius: 12px;
        min-width: 200px; padding: 8px 0; z-index: 10;
    }
    .vp-settings-panel.show { display: block; }
    .vp-settings-title {
        padding: 8px 16px 6px; font-size: 14px; font-weight: 500;
        color: #fff; border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .vp-settings-item {
        display: flex; justify-content: space-between; align-items: center;
        padding: 8px 16px; cursor: pointer; color: #fff; font-size: 13px;
    }
    .vp-settings-item:hover { background: rgba(255,255,255,0.1); }
    .vp-settings-value { color: #3ea6ff; font-size: 13px; }
    .vp-settings-sub { display: none; }
    .vp-settings-sub.show { display: block; }
    .vp-settings-option {
        padding: 8px 16px 8px 32px; cursor: pointer; color: #aaa; font-size: 13px;
    }
    .vp-settings-option:hover { background: rgba(255,255,255,0.1); color: #fff; }
    .vp-settings-option.active { color: #3ea6ff; }

    .video-title {
        font-size: 20px; font-weight: 600; color: var(--yt-text-primary);
        margin-bottom: 12px; line-height: 1.4;
    }
    .video-meta {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 12px; padding-bottom: 16px;
        border-bottom: 1px solid var(--yt-border); margin-bottom: 16px;
    }
    .video-views { font-size: 14px; color: var(--yt-text-secondary); }
    .video-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .video-action-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 12px; border-radius: 18px;
        background: var(--yt-surface); border: none;
        font-size: 13px; font-weight: 500; color: var(--yt-text-primary);
        cursor: pointer; transition: background 0.2s;
    }
    .video-action-btn:hover { background: var(--yt-bg-hover); }
    .video-action-btn.active { background: var(--yt-text-primary); color: var(--yt-bg); }
    .video-action-btn svg { width: 18px; height: 18px; fill: currentColor; }

    .channel-bar {
        display: flex; align-items: center; justify-content: space-between;
        padding: 12px 0; margin-bottom: 16px;
    }
    .channel-bar-left { display: flex; align-items: center; gap: 12px; }
    .channel-bar-avatar {
        width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
        background: var(--yt-surface); display: flex; align-items: center; justify-content: center;
        font-size: 16px; font-weight: 500; color: var(--yt-text-secondary); overflow: hidden;
    }
    .channel-bar-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .channel-bar-name {
        font-size: 14px; font-weight: 500; color: var(--yt-text-primary);
        display: flex; align-items: center; gap: 4px;
    }
    .channel-bar-subs { font-size: 12px; color: var(--yt-text-secondary); }
    .subscribe-btn {
        padding: 8px 16px; border-radius: 18px;
        font-size: 14px; font-weight: 500; border: none; cursor: pointer;
        transition: all 0.2s;
    }
    .subscribe-btn.not-sub { background: var(--yt-text-primary); color: var(--yt-bg); }
    .subscribe-btn.not-sub:hover { background: #d9d9d9; }
    [data-theme="dark"] .subscribe-btn.not-sub:hover { background: #555; }
    .subscribe-btn.sub { background: var(--yt-surface); color: var(--yt-text-primary); }

    .description-box {
        background: var(--yt-surface); border-radius: 12px;
        padding: 12px; margin-bottom: 24px; cursor: pointer;
        transition: background 0.2s;
    }
    .description-box:hover { background: var(--yt-bg-hover); }
    .description-views { font-size: 14px; font-weight: 500; color: var(--yt-text-primary); margin-bottom: 4px; }
    .description-text {
        font-size: 14px; color: var(--yt-text-secondary); line-height: 1.5;
        white-space: pre-wrap; max-height: 60px; overflow: hidden;
        transition: max-height 0.3s;
    }
    .description-text.expanded { max-height: none; }

    .comment-section { margin-bottom: 24px; }
    .comment-header {
        font-size: 16px; font-weight: 500; color: var(--yt-text-primary);
        margin-bottom: 16px;
    }
    .comment-input-wrap {
        display: flex; gap: 12px; margin-bottom: 24px;
    }
    .comment-input-avatar {
        width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
        background: var(--yt-surface); display: flex; align-items: center; justify-content: center;
        font-size: 14px; color: var(--yt-text-secondary); overflow: hidden;
    }
    .comment-input-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .comment-input-field {
        flex: 1; border: none; border-bottom: 1px solid var(--yt-border);
        background: transparent; color: var(--yt-text-primary);
        font-size: 14px; padding: 8px 0; outline: none;
        font-family: inherit;
    }
    .comment-input-field::placeholder { color: var(--yt-text-muted); }
    .comment-input-field:focus { border-bottom-color: var(--yt-text-primary); }
    .comment-input-actions {
        display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px;
        opacity: 0; max-height: 0; overflow: hidden; transition: all 0.2s;
    }
    .comment-input-wrap.expanded .comment-input-actions { opacity: 1; max-height: 50px; }
    .comment-cancel-btn {
        padding: 8px 16px; border-radius: 18px; border: none; cursor: pointer;
        font-size: 13px; font-weight: 500; background: transparent; color: var(--yt-text-primary);
        transition: background 0.2s;
    }
    .comment-cancel-btn:hover { background: var(--yt-surface-hover); }
    .comment-submit-btn {
        padding: 8px 16px; border-radius: 18px; border: none; cursor: pointer;
        font-size: 13px; font-weight: 500; background: var(--yt-surface); color: var(--yt-text-secondary);
        transition: background 0.2s;
    }
    .comment-submit-btn.has-text { background: #3ea6ff; color: #fff; }

    .related-video {
        display: flex; gap: 8px; margin-bottom: 8px;
        text-decoration: none; color: inherit;
        border-radius: 8px; padding: 4px; transition: background 0.15s;
    }
    .related-video:hover { background: var(--yt-bg-hover); }
    .related-thumb {
        width: 168px; height: 94px; flex-shrink: 0;
        border-radius: 8px; overflow: hidden; background: var(--yt-surface);
        position: relative;
    }
    .related-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .related-thumb .duration {
        position: absolute; bottom: 4px; right: 4px;
        background: rgba(0,0,0,0.8); color: #fff;
        padding: 1px 4px; border-radius: 4px; font-size: 11px;
    }
    .related-info { flex: 1; min-width: 0; padding-top: 2px; }
    .related-title {
        font-size: 14px; font-weight: 500; color: var(--yt-text-primary);
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden; line-height: 1.3; margin-bottom: 4px;
    }
    .related-channel { font-size: 12px; color: var(--yt-text-secondary); }
    .related-stats { font-size: 12px; color: var(--yt-text-secondary); }

    @media (max-width: 1024px) {
        .video-watch { flex-direction: column; }
        .video-sidebar { width: 100%; }
    }
</style>

<div class="yt-skel-page" style="display:flex;gap:24px;padding:16px;">
    <div style="flex:1;min-width:0;">
        <div class="yt-skeleton yt-skeleton-thumb" style="border-radius:12px;"></div>
        <div style="padding:12px 0;">
            <div class="yt-skeleton" style="height:20px;width:80%;border-radius:4px;margin-bottom:10px;"></div>
            <div class="yt-skeleton" style="height:14px;width:50%;border-radius:4px;margin-bottom:16px;"></div>
            <div style="display:flex;align-items:center;gap:12px;">
                <div class="yt-skeleton yt-skeleton-circle" style="width:40px;height:40px;"></div>
                <div>
                    <div class="yt-skeleton" style="height:14px;width:180px;border-radius:4px;margin-bottom:6px;"></div>
                    <div class="yt-skeleton" style="height:11px;width:100px;border-radius:4px;"></div>
                </div>
            </div>
        </div>
    </div>
    <div style="width:400px;flex-shrink:0;">
        <?php for ($i = 0; $i < 4; $i++): ?>
        <div style="display:flex;gap:8px;margin-bottom:12px;">
            <div class="yt-skeleton" style="width:168px;height:94px;border-radius:8px;flex-shrink:0;"></div>
            <div style="flex:1;padding:4px 0;">
                <div class="yt-skeleton" style="height:14px;border-radius:4px;margin-bottom:6px;width:90%;"></div>
                <div class="yt-skeleton" style="height:10px;border-radius:4px;width:50%;"></div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
</div>

<div class="yt-page-content">
<div class="video-watch">
    <div class="video-main">
        <div class="video-player" id="videoPlayer">
            <?php if (!empty($video['file_path'])): ?>
                <video id="vpVideo" preload="metadata">
                    <source src="<?= url(e($video['file_path'])) ?>" type="video/mp4">
                </video>
                <div class="vp-center-btn vp-paused" id="vpCenterBtn">
                    <svg viewBox="0 0 24 24" id="vpCenterIcon"><path d="M8 5v14l11-7z"/></svg>
                </div>
                <div class="vp-bottom" id="vpBottom">
                    <div class="vp-progress-wrap" id="vpProgressWrap">
                        <div class="vp-progress-bar" id="vpProgressBar"></div>
                    </div>
                    <div class="vp-controls">
                        <div class="vp-controls-left">
                            <button class="vp-btn" id="vpPlayBtn" title="Play">
                                <svg viewBox="0 0 24 24" id="vpPlayIcon"><path d="M8 5v14l11-7z"/></svg>
                            </button>
                            <button class="vp-btn" id="vpPrevBtn" title="Rewind 10s">
                                <svg viewBox="0 0 24 24"><path d="M11.99 5V1l-5 5 5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6h-2c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/><text x="12" y="15.5" text-anchor="middle" fill="#fff" font-size="7.5" font-family="Arial" font-weight="bold">10</text></svg>
                            </button>
                            <button class="vp-btn" id="vpNextBtn" title="Forward 10s">
                                <svg viewBox="0 0 24 24"><path d="M12.01 5V1l5 5-5 5V7c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6h2c0 4.42-3.58 8-8 8s-8-3.58-8-8 3.58-8 8-8z"/><text x="12" y="15.5" text-anchor="middle" fill="#fff" font-size="7.5" font-family="Arial" font-weight="bold">10</text></svg>
                            </button>
                            <div class="vp-volume-wrap">
                                <button class="vp-btn" id="vpMuteBtn" title="Mute">
                                    <svg viewBox="0 0 24 24" id="vpVolumeIcon"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>
                                </button>
                                <input type="range" class="vp-volume-slider" id="vpVolume" min="0" max="1" step="0.05" value="1">
                            </div>
                            <span class="vp-time" id="vpTime">0:00 / 0:00</span>
                        </div>
                        <div class="vp-controls-right">
                            <span class="vp-title"><?= e($video['title']) ?></span>
                            <button class="vp-btn" id="vpSettingsBtn" title="Settings">
                                <svg viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
                            </button>
                            <div class="vp-settings-panel" id="vpSettingsPanel">
                                <div class="vp-settings-title">Settings</div>
                                <div class="vp-settings-item" data-setting="speed">
                                    <span>Playback speed</span>
                                    <span class="vp-settings-value" id="vpSpeedValue">Normal</span>
                                </div>
                                <div class="vp-settings-sub" id="vpSpeedSub">
                                    <div class="vp-settings-option" data-speed="0.25">0.25</div>
                                    <div class="vp-settings-option" data-speed="0.5">0.5</div>
                                    <div class="vp-settings-option" data-speed="0.75">0.75</div>
                                    <div class="vp-settings-option active" data-speed="1">Normal</div>
                                    <div class="vp-settings-option" data-speed="1.25">1.25</div>
                                    <div class="vp-settings-option" data-speed="1.5">1.5</div>
                                    <div class="vp-settings-option" data-speed="2">2</div>
                                </div>
                            </div>
                            <button class="vp-btn" id="vpTheaterBtn" title="Theater mode">
                                <svg viewBox="0 0 24 24" id="vpTheaterIcon"><path d="M19 6H5c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 10H5V8h14v8z"/></svg>
                            </button>
                            <button class="vp-btn" id="vpFullscreenBtn" title="Fullscreen">
                                <svg viewBox="0 0 24 24" id="vpFsIcon"><path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            <?php endif; ?>
        </div>

        <h1 class="video-title"><?= e($video['title']) ?></h1>

        <div class="video-meta">
            <div class="video-views">
                <?= format_number((int) ($video['view_count'] ?? 0)) ?> views &middot; <?= time_ago($video['published_at'] ?? $video['created_at'] ?? '') ?>
            </div>
            <div class="video-actions">
                <?php if (!empty($currentUser ?? null) && !empty($userChannel)): ?>
                <button class="video-action-btn <?= ($userReaction && ($userReaction['type'] ?? '') === 'like') ? 'active' : '' ?>" data-like-video="<?= $video['id'] ?>">
                    <svg viewBox="0 0 24 24"><path d="M1 21h4V9H1v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L14.17 2 7.59 8.59C7.22 8.95 7 9.45 7 10v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2z"/></svg>
                    <span class="like-count"><?= format_number((int) ($video['like_count'] ?? 0)) ?></span>
                </button>
                <?php else: ?>
                <button class="video-action-btn" title="<?= !empty($currentUser ?? null) ? 'Create a channel to like' : 'Sign in to like' ?>">
                    <svg viewBox="0 0 24 24"><path d="M1 21h4V9H1v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L14.17 2 7.59 8.59C7.22 8.95 7 9.45 7 10v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2z"/></svg>
                    <span class="like-count"><?= format_number((int) ($video['like_count'] ?? 0)) ?></span>
                </button>
                <?php endif; ?>
                <button class="video-action-btn">
                    <svg viewBox="0 0 24 24"><path d="M15 3H6c-.83 0-1.54.5-1.84 1.22l-3.02 7.05c-.09.23-.14.47-.14.73v2c0 1.1.9 2 2 2h6.31l-.95 4.57-.03.32c0 .41.17.79.44 1.06L9.83 23l6.59-6.59C16.78 16.05 17 15.55 17 15V5c0-1.1-.9-2-2-2zm4 0v12h4V3h-4z"/></svg>
                </button>
                <button class="video-action-btn">
                    <svg viewBox="0 0 24 24"><path d="M14 9V3L22 12L14 21V15C8 15 4 17 1 21C2 15 5 9 14 9Z"/></svg>
                    Share
                </button>
                <button class="video-action-btn" data-watch-later="<?= $video['id'] ?>">
                    <svg viewBox="0 0 24 24"><path d="M14 10H2v2h12v-2zm0-4H2v2h12V6zm4 8v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zM2 16h8v-2H2v2z"/></svg>
                    Save
                </button>
            </div>
        </div>

        <?php if ($channel): ?>
        <div class="channel-bar">
            <div class="channel-bar-left">
                <a href="<?= url('/channel/' . e($channel['custom_url'] ?? $channel['slug'])) ?>">
                    <div class="channel-bar-avatar">
                        <?php if (!empty($channel['avatar'])): ?>
                            <img src="<?= url(e($channel['avatar'])) ?>" alt="">
                        <?php else: ?>
                            <?= strtoupper(substr($channel['name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                </a>
                <div>
                    <a href="<?= url('/channel/' . e($channel['custom_url'] ?? $channel['slug'])) ?>" style="text-decoration:none;">
                        <div class="channel-bar-name">
                            <?= e($channel['name']) ?>
                            <?php if (!empty($channel['is_verified'])): ?>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="var(--yt-text-secondary)"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="channel-bar-subs"><?= format_number((int) ($channel['subscriber_count'] ?? 0)) ?> subscribers</div>
                </div>
            </div>
            <?php if (!empty($currentUser ?? null) && !empty($userChannel)): ?>
            <button class="subscribe-btn not-sub" data-subscribe="<?= (int) $channel['id'] ?>">Subscribe</button>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($video['description'])): ?>
        <div class="description-box" onclick="this.querySelector('.description-text').classList.toggle('expanded')">
            <div class="description-views"><?= format_number((int) ($video['view_count'] ?? 0)) ?> views &middot; <?= time_ago($video['published_at'] ?? $video['created_at'] ?? '') ?></div>
            <div class="description-text"><?= e($video['description']) ?></div>
        </div>
        <?php endif; ?>

        <div class="comment-section">
            <div class="comment-header"><?= format_number((int) ($video['comments_count'] ?? 0)) ?> Comments</div>
            <?php if (!empty($currentUser ?? null) && !empty($userChannel)): ?>
            <div class="comment-input-wrap" id="commentInputWrap">
                    <?php $commentAvatar = $currentUser['avatar'] ?? ($userChannel['avatar'] ?? ''); ?>
                    <?php if (!empty($commentAvatar)): ?>
                        <div class="comment-input-avatar"><img src="<?= url(e($commentAvatar)) ?>" alt=""></div>
                    <?php else: ?>
                        <div class="comment-input-avatar">
                            <?= strtoupper(substr($currentUser['username'] ?? 'U', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                <div style="flex:1">
                    <input class="comment-input-field" id="commentInput" placeholder="Add a comment..." autocomplete="off">
                    <div class="comment-input-actions" id="commentActions">
                        <button class="comment-cancel-btn" id="commentCancelBtn">Cancel</button>
                        <button class="comment-submit-btn" id="commentSubmitBtn" data-video-id="<?= $video['id'] ?>">Comment</button>
                    </div>
                </div>
            </div>
            <?php elseif (!empty($currentUser ?? null)): ?>
            <div style="padding:16px 0;color:var(--yt-text-secondary);font-size:13px;">
                <a href="<?= url('/studio') ?>" style="color:var(--yt-accent);text-decoration:none;">Create a channel</a> to comment.
            </div>
            <?php else: ?>
            <div style="padding:16px 0;color:var(--yt-text-secondary);font-size:13px;">
                <a href="<?= url('/login') ?>" style="color:var(--yt-accent);text-decoration:none;">Sign in</a> to comment.
            </div>
            <?php endif; ?>
            <?php foreach (($comments['data'] ?? []) as $comment): ?>
                <?php include VIEW_PATH . '/partials/comment.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="video-sidebar">
        <?php foreach (($relatedVideos ?? []) as $related): ?>
        <a href="<?= url('/video/' . e($related['slug'])) ?>" class="related-video">
            <div class="related-thumb">
                <?php if (!empty($related['thumbnail_path'])): ?>
                    <img src="<?= url(e($related['thumbnail_path'])) ?>" alt="" loading="lazy">
                <?php else: ?>
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--yt-surface);">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="var(--yt-text-muted)" opacity="0.4"><path d="M8 5v14l11-7z"/></svg>
                    </div>
                <?php endif; ?>
                <?php if (!empty($related['duration'])): ?>
                    <span class="duration"><?= gmdate('i:s', (int) $related['duration']) ?></span>
                <?php endif; ?>
            </div>
            <div class="related-info">
                <div class="related-title"><?= e($related['title']) ?></div>
                <div class="related-channel"><?= e($related['channel_name'] ?? '') ?></div>
                <div class="related-stats"><?= format_number((int) ($related['view_count'] ?? 0)) ?> views &middot; <?= time_ago($related['published_at'] ?? $related['created_at'] ?? '') ?></div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
</div>

<script>
(function() {
    const player = document.getElementById('videoPlayer');
    if (!player) return;
    const video = document.getElementById('vpVideo');
    const playBtn = document.getElementById('vpPlayBtn');
    const playIcon = document.getElementById('vpPlayIcon');
    const centerBtn = document.getElementById('vpCenterBtn');
    const centerIcon = document.getElementById('vpCenterIcon');
    const progressWrap = document.getElementById('vpProgressWrap');
    const progressBar = document.getElementById('vpProgressBar');
    const timeEl = document.getElementById('vpTime');
    const muteBtn = document.getElementById('vpMuteBtn');
    const volumeIcon = document.getElementById('vpVolumeIcon');
    const volumeSlider = document.getElementById('vpVolume');
    const fullscreenBtn = document.getElementById('vpFullscreenBtn');
    const theaterBtn = document.getElementById('vpTheaterBtn');
    const settingsBtn = document.getElementById('vpSettingsBtn');
    const settingsPanel = document.getElementById('vpSettingsPanel');
    const speedValue = document.getElementById('vpSpeedValue');
    const speedSub = document.getElementById('vpSpeedSub');

    const playPath = 'M8 5v14l11-7z';
    const pausePath = 'M6 19h4V5H6v14zm8-14v14h4V5h-4z';
    const volHigh = 'M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z';
    const volMute = 'M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z';

    function formatTime(s) {
        if (isNaN(s)) return '0:00';
        const m = Math.floor(s / 60);
        const sec = Math.floor(s % 60);
        return m + ':' + (sec < 10 ? '0' : '') + sec;
    }

    function updateIcons() {
        const icon = video.paused ? playPath : pausePath;
        playIcon.innerHTML = '<path d="' + icon + '"/>';
        centerIcon.innerHTML = '<path d="' + (video.paused ? playPath : pausePath) + '"/>';
        playBtn.title = video.paused ? 'Play' : 'Pause';
        if (video.paused) {
            centerBtn.classList.add('vp-paused');
            centerIcon.innerHTML = '<path d="' + playPath + '"/>';
        } else {
            centerBtn.classList.remove('vp-paused');
        }
    }

    function updateProgress() {
        if (!video.duration) return;
        const pct = (video.currentTime / video.duration) * 100;
        progressBar.style.width = pct + '%';
        timeEl.textContent = formatTime(video.currentTime) + ' / ' + formatTime(video.duration);
    }

    function togglePlay() {
        if (video.paused) video.play(); else video.pause();
    }

    video.addEventListener('play', updateIcons);
    video.addEventListener('pause', updateIcons);
    video.addEventListener('timeupdate', updateProgress);
    video.addEventListener('loadedmetadata', updateProgress);

    playBtn.addEventListener('click', function(e) { e.stopPropagation(); togglePlay(); });
    centerBtn.addEventListener('click', function(e) { e.stopPropagation(); togglePlay(); });
    player.addEventListener('click', function(e) {
        if (e.target.closest('.vp-btn') || e.target.closest('.vp-volume-slider') || e.target.closest('.vp-progress-wrap')) return;
        togglePlay();
    });

    progressWrap.addEventListener('click', function(e) {
        const rect = this.getBoundingClientRect();
        const pct = (e.clientX - rect.left) / rect.width;
        video.currentTime = pct * video.duration;
    });

    let seeking = false;
    progressWrap.addEventListener('mousedown', function(e) {
        seeking = true;
        const rect = this.getBoundingClientRect();
        const pct = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
        video.currentTime = pct * video.duration;
    });
    document.addEventListener('mousemove', function(e) {
        if (!seeking) return;
        const rect = progressWrap.getBoundingClientRect();
        const pct = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
        video.currentTime = pct * video.duration;
    });
    document.addEventListener('mouseup', function() { seeking = false; });

    let prevVolume = 1;
    muteBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        if (video.muted || video.volume === 0) {
            video.muted = false;
            video.volume = prevVolume || 0.5;
        } else {
            prevVolume = video.volume;
            video.muted = true;
        }
        volumeSlider.value = video.muted ? 0 : video.volume;
        volumeIcon.innerHTML = '<path d="' + ((video.muted || video.volume === 0) ? volMute : volHigh) + '"/>';
    });
    volumeSlider.addEventListener('input', function(e) {
        e.stopPropagation();
        video.volume = parseFloat(this.value);
        video.muted = video.volume === 0;
        volumeIcon.innerHTML = '<path d="' + ((video.muted || video.volume === 0) ? volMute : volHigh) + '"/>';
    });

    fullscreenBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        if (document.fullscreenElement) document.exitFullscreen();
        else player.requestFullscreen();
    });
    player.addEventListener('dblclick', function(e) {
        if (document.fullscreenElement) document.exitFullscreen();
        else player.requestFullscreen();
    });

    theaterBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        player.classList.toggle('theater');
        const is = player.classList.contains('theater');
        player.style.borderRadius = is ? '0' : '12px';
        player.style.maxWidth = is ? '100%' : '';
        player.style.position = is ? 'fixed' : '';
        player.style.inset = is ? '0' : '';
        player.style.zIndex = is ? '9999' : '';
    });

    settingsBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        settingsPanel.classList.toggle('show');
        speedSub.classList.remove('show');
    });

    document.getElementById('vpSpeedValue').parentElement.addEventListener('click', function(e) {
        e.stopPropagation();
        speedSub.classList.toggle('show');
    });

    speedSub.querySelectorAll('.vp-settings-option').forEach(function(opt) {
        opt.addEventListener('click', function(e) {
            e.stopPropagation();
            const spd = parseFloat(this.dataset.speed);
            video.playbackRate = spd;
            speedValue.textContent = this.textContent;
            speedSub.querySelectorAll('.vp-settings-option').forEach(function(o) { o.classList.remove('active'); });
            this.classList.add('active');
        });
    });

    document.addEventListener('click', function() {
        settingsPanel.classList.remove('show');
        speedSub.classList.remove('show');
    });

    var commentInput = document.getElementById('commentInput');
    var commentWrap = document.getElementById('commentInputWrap');
    var commentCancel = document.getElementById('commentCancelBtn');
    var commentSubmit = document.getElementById('commentSubmitBtn');
    if (commentInput && commentWrap) {
        commentInput.addEventListener('focus', function() {
            commentWrap.classList.add('expanded');
        });
        commentCancel.addEventListener('click', function() {
            commentInput.value = '';
            commentInput.blur();
            commentWrap.classList.remove('expanded');
            commentSubmit.classList.remove('has-text');
        });
        commentInput.addEventListener('input', function() {
            if (this.value.trim().length > 0) commentSubmit.classList.add('has-text');
            else commentSubmit.classList.remove('has-text');
        });
        commentSubmit.addEventListener('click', function() {
            var text = commentInput.value.trim();
            if (!text) return;
            var videoId = this.dataset.videoId;
            var token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            fetch(BASE_URL + '/viewer/comment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': token, 'X-Requested-With': 'XMLHttpRequest' },
                body: 'video_id=' + videoId + '&body=' + encodeURIComponent(text) + '&_token=' + encodeURIComponent(token)
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (data.status === 'ok' && data.comment) {
                    var section = document.querySelector('.comment-section');
                    var header = section.querySelector('.comment-header');
                    var wrap = document.getElementById('commentInputWrap');
                    var authorName = data.user.username || 'user';
                    var avatarUrl = data.user.avatar ? (BASE_URL + data.user.avatar) : '';
                    var avatarHtml = avatarUrl
                        ? '<div class="comment-input-avatar" style="width:40px;height:40px;border-radius:50%;flex-shrink:0;overflow:hidden;"><img src="' + avatarUrl + '" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;"></div>'
                        : '<div class="comment-input-avatar" style="width:40px;height:40px;border-radius:50%;background:var(--yt-surface);display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--yt-text-secondary);flex-shrink:0;">' + authorName.charAt(0).toUpperCase() + '</div>';
                    var html = '<div class="comment-item" style="display:flex;gap:12px;margin-bottom:16px;">' +
                        avatarHtml +
                        '<div><div style="font-size:13px;font-weight:500;color:var(--yt-text-primary);margin-bottom:4px;">@' + authorName + ' <span style="font-weight:400;color:var(--yt-text-secondary);font-size:12px;">just now</span></div>' +
                        '<div style="font-size:13px;color:var(--yt-text-primary);line-height:1.5;">' + data.comment.body + '</div></div></div>';
                    wrap.insertAdjacentHTML('afterend', html);
                    commentInput.value = '';
                    commentWrap.classList.remove('expanded');
                    commentSubmit.classList.remove('has-text');
                }
            });
        });
    }

    video.addEventListener('dblclick', function() {});

    document.addEventListener('keydown', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        if (e.key === ' ' || e.key === 'k') { e.preventDefault(); togglePlay(); }
        else if (e.key === 'ArrowLeft') { video.currentTime = Math.max(0, video.currentTime - 5); }
        else if (e.key === 'ArrowRight') { video.currentTime = Math.min(video.duration, video.currentTime + 5); }
        else if (e.key === 'm') { muteBtn.click(); }
        else if (e.key === 'f') { fullscreenBtn.click(); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); video.volume = Math.min(1, video.volume + 0.05); volumeSlider.value = video.volume; }
        else if (e.key === 'ArrowDown') { e.preventDefault(); video.volume = Math.max(0, video.volume - 0.05); volumeSlider.value = video.volume; }
    });
})();
</script>
