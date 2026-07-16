<?php $__layout = 'layouts.app'; ?>

<div class="yt-skel-page">
    <div style="display:flex;gap:8px;margin-bottom:24px;padding:8px 0;">
        <?php for ($i = 0; $i < 8; $i++): ?>
            <div class="yt-skeleton" style="width:80px;height:32px;border-radius:20px;flex-shrink:0;"></div>
        <?php endfor; ?>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
        <?php for ($i = 0; $i < 8; $i++): ?>
            <div class="yt-skel-card">
                <div class="skel-thumb yt-skeleton yt-skeleton-thumb"></div>
                <div class="skel-info">
                    <div class="skel-avatar yt-skeleton yt-skeleton-circle"></div>
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

<?php if (!empty($categories)): ?>
<div style="margin-bottom:20px;overflow-x:auto;white-space:nowrap;scrollbar-width:none;-ms-overflow-style:none;padding:8px 0;">
        <div style="display:inline-flex;gap:8px;">
            <a href="<?= url('/videos') ?>" style="display:inline-block;padding:6px 14px;border-radius:20px;background:var(--yt-surface);color:var(--yt-text-primary);font-size:13px;font-weight:500;text-decoration:none;transition:background 0.2s;flex-shrink:0;border:1px solid var(--yt-border);" onmouseover="this.style.background='var(--yt-surface-hover)'" onmouseout="this.style.background='var(--yt-surface)'">All</a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= url('/category/' . e($cat['slug'])) ?>" style="display:inline-block;padding:6px 14px;border-radius:20px;background:var(--yt-surface);color:var(--yt-text-primary);font-size:13px;font-weight:500;text-decoration:none;transition:background 0.2s;flex-shrink:0;border:1px solid var(--yt-border);" onmouseover="this.style.background='var(--yt-surface-hover)'" onmouseout="this.style.background='var(--yt-surface)'"><?= e($cat['name']) ?></a>
        <?php endforeach; ?>
        </div>
</div>
<style>.yt-main > div:first-child > div[style*="overflow-x"]::-webkit-scrollbar{display:none;}</style>
<?php endif; ?>

<?php if (!empty($subscriptionVideos)): ?>
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0" style="font-size:16px;font-weight:500;">From Your Subscriptions</h4>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
        <?php foreach ($subscriptionVideos as $video): ?>
            <?php include VIEW_PATH . '/partials/video-card.php'; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($continueWatching)): ?>
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0" style="font-size:16px;font-weight:500;">Continue Watching</h4>
        <a href="<?= url('/viewer/history') ?>" style="font-size:13px;font-weight:500;color:#3ea6ff;">See all <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg></a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
        <?php foreach ($continueWatching as $item): ?>
            <?php if (!empty($item['video'])): ?>
            <?php $video = $item['video']; ?>
            <?php include VIEW_PATH . '/partials/video-card.php'; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="mb-4">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
        <?php foreach (($featuredVideos ?? []) as $video): ?>
            <?php include VIEW_PATH . '/partials/video-card.php'; ?>
        <?php endforeach; ?>
        <?php if (empty($featuredVideos)): ?>
            <div style="grid-column:1/-1;text-align:center;padding:64px 16px;color:var(--yt-text-secondary);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="var(--yt-text-muted)" style="margin-bottom:12px;opacity:0.4;"><path d="M19 3H4.99c-1.11 0-1.98.89-1.98 2L3 19c0 1.1.88 2 1.99 2H19c1.1 0 2-.9 2-2V5c0-1.11-.9-2-2-2zm0 12h-4c0 1.66-1.35 3-3 3s-3-1.34-3-3H4.99V5H19v10z"/></svg>
                <div>No featured videos yet.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($trendingVideos)): ?>
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0" style="font-size:16px;font-weight:500;">Trending</h4>
        <a href="<?= url('/trending') ?>" style="font-size:13px;font-weight:500;color:#3ea6ff;">See all <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg></a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
        <?php foreach ($trendingVideos as $video): ?>
            <?php include VIEW_PATH . '/partials/video-card.php'; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0" style="font-size:16px;font-weight:500;">Latest Videos</h4>
        <a href="<?= url('/videos') ?>" style="font-size:13px;font-weight:500;color:#3ea6ff;">See all <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg></a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
        <?php foreach (($latestVideos ?? []) as $video): ?>
            <?php include VIEW_PATH . '/partials/video-card.php'; ?>
        <?php endforeach; ?>
        <?php if (empty($latestVideos)): ?>
            <div style="grid-column:1/-1;text-align:center;padding:64px 16px;color:var(--yt-text-secondary);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="var(--yt-text-muted)" style="margin-bottom:12px;opacity:0.4;"><path d="M19 3H4.99c-1.11 0-1.98.89-1.98 2L3 19c0 1.1.88 2 1.99 2H19c1.1 0 2-.9 2-2V5c0-1.11-.9-2-2-2zm0 12h-4c0 1.66-1.35 3-3 3s-3-1.34-3-3H4.99V5H19v10z"/></svg>
                <div>No videos yet.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

</div>
