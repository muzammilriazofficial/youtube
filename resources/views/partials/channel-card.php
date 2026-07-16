<?php
$channelName = $channel['name'] ?? 'Channel';
$channelSlug = $channel['custom_url'] ?? $channel['slug'] ?? '#';
$channelAvatar = $channel['avatar'] ?? '';
$subscriberCount = format_number((int) ($channel['subscriber_count'] ?? 0));
$videoCount = $channel['video_count'] ?? '';
?>
<a href="<?= url('/channel/' . e($channelSlug)) ?>" class="yt-video-card" style="text-align:center">
    <div style="padding:24px 16px;display:flex;flex-direction:column;align-items:center">
        <?php if ($channelAvatar): ?>
            <img src="<?= url(e($channelAvatar)) ?>" alt="<?= e($channelName) ?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin-bottom:12px" loading="lazy">
        <?php else: ?>
            <div style="width:80px;height:80px;border-radius:50%;background:var(--yt-accent);color:#fff;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:600;margin-bottom:12px">
                <?= strtoupper(substr($channelName, 0, 1)) ?>
            </div>
        <?php endif; ?>
        <div class="video-title" style="text-align:center;-webkit-line-clamp:1;margin-bottom:4px"><?= e($channelName) ?></div>
        <div class="video-stats"><?= $subscriberCount ?> subscribers</div>
        <?php if ($videoCount !== ''): ?>
            <div class="video-stats"><?= format_number((int)$videoCount) ?> videos</div>
        <?php endif; ?>
    </div>
</a>
