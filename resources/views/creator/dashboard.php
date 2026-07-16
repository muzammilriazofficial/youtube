<?php $__layout = 'layouts.app'; ?>

<style>
    .studio-dash { max-width: 1200px; margin: 0 auto; padding: 24px; }

    .dash-welcome {
        display: flex; align-items: center; gap: 16px;
        margin-bottom: 28px; padding-bottom: 20px;
        border-bottom: 1px solid var(--yt-border);
    }
    .dash-welcome-avatar {
        width: 48px; height: 48px; border-radius: 50%; flex-shrink: 0;
        background: var(--yt-surface); display: flex; align-items: center; justify-content: center;
        font-size: 20px; font-weight: 500; color: var(--yt-text-primary);
        overflow: hidden;
    }
    .dash-welcome-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .dash-welcome-text h2 { font-size: 18px; font-weight: 500; color: var(--yt-text-primary); margin: 0; }
    .dash-welcome-text p { font-size: 14px; color: var(--yt-text-secondary); margin: 2px 0 0; }
    .dash-welcome-actions { margin-left: auto; display: flex; gap: 10px; }
    .yt-btn-upload {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px; border-radius: 18px;
        background: transparent; border: none;
        font-size: 14px; font-weight: 500;
        color: var(--yt-text-primary); cursor: pointer;
        transition: background 0.2s;
    }
    .yt-btn-upload:hover { background: var(--yt-bg-hover); }
    .yt-btn-upload svg { width: 20px; height: 20px; fill: currentColor; }

    .dash-section { margin-bottom: 28px; }
    .dash-section-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 16px;
    }
    .dash-section-title {
        font-size: 16px; font-weight: 500; color: var(--yt-text-primary);
        display: flex; align-items: center; gap: 8px;
    }
    .dash-section-link {
        font-size: 13px; font-weight: 500; color: #3ea6ff;
        text-decoration: none; display: flex; align-items: center; gap: 4px;
    }
    .dash-section-link:hover { color: #65b8ff; }

    .dash-summary {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }
    .dash-summary-card {
        background: var(--yt-bg-elevated);
        border: 1px solid var(--yt-border);
        border-radius: 8px;
        padding: 16px;
        display: flex; flex-direction: column; gap: 8px;
    }
    .dash-summary-card .label {
        font-size: 13px; color: var(--yt-text-secondary);
    }
    .dash-summary-card .value {
        font-size: 24px; font-weight: 500; color: var(--yt-text-primary);
    }
    .dash-summary-card .change {
        font-size: 12px; display: flex; align-items: center; gap: 4px;
    }
    .dash-summary-card .change.up { color: var(--yt-success); }
    .dash-summary-card .change.down { color: #f44336; }
    .dash-summary-card .change.neutral { color: var(--yt-text-muted); }

    .dash-chart-card {
        background: var(--yt-bg-elevated);
        border: 1px solid var(--yt-border);
        border-radius: 8px;
        overflow: hidden;
    }
    .dash-chart-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid var(--yt-border);
    }
    .dash-chart-header h3 {
        font-size: 14px; font-weight: 500; color: var(--yt-text-primary); margin: 0;
    }
    .dash-chart-body { padding: 20px; position: relative; height: 280px; }

    .dash-latest-video {
        background: var(--yt-bg-elevated);
        border: 1px solid var(--yt-border);
        border-radius: 8px;
        overflow: hidden;
    }
    .dash-latest-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--yt-border);
    }
    .dash-latest-header h3 {
        font-size: 14px; font-weight: 500; color: var(--yt-text-primary); margin: 0;
    }
    .dash-latest-body { display: flex; gap: 20px; padding: 20px; }
    .dash-latest-thumb {
        width: 360px; flex-shrink: 0;
        border-radius: 8px; overflow: hidden;
        background: var(--yt-surface); position: relative;
        aspect-ratio: 16/9;
    }
    .dash-latest-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .dash-latest-thumb .duration {
        position: absolute; bottom: 4px; right: 4px;
        background: rgba(0,0,0,0.8); color: #fff;
        padding: 2px 4px; border-radius: 4px; font-size: 12px;
    }
    .dash-latest-info { flex: 1; display: flex; flex-direction: column; }
    .dash-latest-info h4 {
        font-size: 16px; font-weight: 500; color: var(--yt-text-primary);
        margin: 0 0 12px; line-height: 1.4;
    }
    .dash-latest-stats {
        display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: auto;
    }
    .dash-latest-stat { display: flex; flex-direction: column; }
    .dash-latest-stat .stat-label { font-size: 12px; color: var(--yt-text-muted); }
    .dash-latest-stat .stat-value { font-size: 14px; font-weight: 500; color: var(--yt-text-primary); }

    .dash-content-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
    }

    .dash-comments-card {
        background: var(--yt-bg-elevated);
        border: 1px solid var(--yt-border);
        border-radius: 8px;
        overflow: hidden;
    }
    .dash-comment-item {
        display: flex; gap: 12px; padding: 12px 20px;
        border-bottom: 1px solid var(--yt-border);
        transition: background 0.15s;
    }
    .dash-comment-item:last-child { border-bottom: none; }
    .dash-comment-item:hover { background: var(--yt-bg-hover); }
    .dash-comment-avatar {
        width: 32px; height: 32px; border-radius: 50%;
        background: var(--yt-surface); flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 500; color: var(--yt-text-secondary);
        overflow: hidden;
    }
    .dash-comment-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .dash-comment-body { flex: 1; min-width: 0; }
    .dash-comment-meta {
        display: flex; align-items: center; gap: 8px;
        font-size: 12px; margin-bottom: 2px;
    }
    .dash-comment-meta .name { font-weight: 500; color: var(--yt-text-primary); }
    .dash-comment-meta .time { color: var(--yt-text-muted); }
    .dash-comment-text {
        font-size: 13px; color: var(--yt-text-secondary);
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .dash-comment-video {
        font-size: 11px; color: var(--yt-text-muted); margin-top: 2px;
    }

    .empty-state {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        padding: 48px 16px; color: var(--yt-text-muted); text-align: center;
    }
    .empty-state svg { width: 48px; height: 48px; fill: var(--yt-text-muted); margin-bottom: 12px; opacity: 0.5; }
    .empty-state p { font-size: 14px; margin: 0; }
    .empty-state a { color: #3ea6ff; text-decoration: none; font-weight: 500; }

    .dash-no-video {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        aspect-ratio: 16/9; background: var(--yt-surface); border-radius: 8px;
    }
    .dash-no-video svg { width: 48px; height: 48px; fill: var(--yt-text-muted); opacity: 0.4; }

    @media (max-width: 768px) {
        .dash-summary { grid-template-columns: 1fr 1fr; }
        .dash-content-grid { grid-template-columns: 1fr; }
        .dash-latest-body { flex-direction: column; }
        .dash-latest-thumb { width: 100%; }
        .dash-welcome { flex-wrap: wrap; }
    }
</style>

<div class="studio-dash">
    <div class="dash-welcome">
        <div class="dash-welcome-avatar">
            <?php if (!empty($channel['avatar'])): ?>
                <img src="<?= url(e($channel['avatar'])) ?>" alt="">
            <?php else: ?>
                <?= strtoupper(substr($channel['name'], 0, 1)) ?>
            <?php endif; ?>
        </div>
        <div class="dash-welcome-text">
            <h2>Channel dashboard</h2>
            <p><?= e($channel['name']) ?></p>
        </div>
        <div class="dash-welcome-actions">
            <a href="<?= url('/creator/videos/create') ?>" class="yt-btn-upload">
                <svg viewBox="0 0 24 24"><path d="M14 13h-3v3H9v-3H6v-2h3V8h2v3h3v2zm3-6v12H3V7h14zm1-1H2v14h16V6zM21 3v14h-1V4H6V3h15z"/></svg>
                Upload video
            </a>
        </div>
    </div>

    <div class="dash-summary">
        <div class="dash-summary-card">
            <span class="label">Views</span>
            <span class="value"><?= format_number($totalViews ?? 0) ?></span>
            <span class="change neutral">Last 28 days</span>
        </div>
        <div class="dash-summary-card">
            <span class="label">Subscribers</span>
            <span class="value"><?= format_number($totalSubscribers ?? 0) ?></span>
            <span class="change neutral">Last 28 days</span>
        </div>
        <div class="dash-summary-card">
            <span class="label">Videos</span>
            <span class="value"><?= format_number($totalVideos ?? 0) ?></span>
            <span class="change neutral">Total uploaded</span>
        </div>
        <div class="dash-summary-card">
            <span class="label">Est. revenue</span>
            <span class="value">$<?= number_format((float) ($revenue ?? 0), 2) ?></span>
            <span class="change neutral">Last 28 days</span>
        </div>
    </div>

    <div class="dash-section" style="margin-top:28px;">
        <div class="dash-chart-card">
            <div class="dash-chart-header">
                <h3>Views</h3>
                <a href="<?= url('/creator/analytics') ?>" class="dash-section-link">
                    Go to analytics
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
                </a>
            </div>
            <div class="dash-chart-body">
                <canvas id="viewsChart"></canvas>
            </div>
        </div>
    </div>

    <?php if (!empty($recentVideos)): ?>
    <div class="dash-section">
        <div class="dash-latest-video">
            <div class="dash-latest-header">
                <h3>Latest video performance</h3>
            </div>
            <?php $latest = $recentVideos[0]; ?>
            <div class="dash-latest-body">
                <div class="dash-latest-thumb">
                    <?php if (!empty($latest['thumbnail'])): ?>
                        <img src="<?= url(e($latest['thumbnail'])) ?>" alt="">
                    <?php else: ?>
                        <div class="dash-no-video">
                            <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($latest['duration'])): ?>
                        <span class="duration"><?= gmdate('i:s', (int) $latest['duration']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="dash-latest-info">
                    <h4><?= e($latest['title']) ?></h4>
                    <div class="dash-latest-stats">
                        <div class="dash-latest-stat">
                            <span class="stat-label">Views</span>
                            <span class="stat-value"><?= format_number((int) $latest['view_count']) ?></span>
                        </div>
                        <div class="dash-latest-stat">
                            <span class="stat-label">Likes</span>
                            <span class="stat-value"><?= format_number((int) $latest['like_count']) ?></span>
                        </div>
                        <div class="dash-latest-stat">
                            <span class="stat-label">Published</span>
                            <span class="stat-value"><?= date('M d, Y', strtotime($latest['created_at'])) ?></span>
                        </div>
                        <div class="dash-latest-stat">
                            <span class="stat-label">Status</span>
                            <span class="stat-value" style="text-transform:capitalize"><?= e($latest['status']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="dash-section">
        <div class="dash-latest-video">
            <div class="empty-state">
                <svg viewBox="0 0 24 24"><path d="M18 4l2 4h-3l-2-4h-2l2 4h-3l-2-4H8l2 4H7L5 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4h-4z"/></svg>
                <p>No videos yet. <a href="<?= url('/creator/videos/create') ?>">Upload your first video</a></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="dash-content-grid">
        <div class="dash-comments-card">
            <div class="dash-chart-header">
                <h3>Comments</h3>
                <a href="<?= url('/creator/comments') ?>" class="dash-section-link">
                    View all
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
                </a>
            </div>
            <?php if (!empty($recentComments)): ?>
                <?php foreach ($recentComments as $comment): ?>
                <div class="dash-comment-item">
                    <div class="dash-comment-avatar">
                        <?= strtoupper(substr($comment['username'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="dash-comment-body">
                        <div class="dash-comment-meta">
                            <span class="name"><?= e($comment['username'] ?? 'User') ?></span>
                            <span class="time"><?= time_ago($comment['created_at'] ?? '') ?></span>
                        </div>
                        <div class="dash-comment-text"><?= e($comment['body']) ?></div>
                        <?php if (!empty($comment['video_title'])): ?>
                        <div class="dash-comment-video">on <?= e(mb_substr($comment['video_title'], 0, 40)) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state" style="padding:32px 16px;">
                    <p>No comments yet</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="dash-comments-card">
            <div class="dash-chart-header">
                <h3>Channel stats</h3>
                <a href="<?= url('/creator/analytics') ?>" class="dash-section-link">
                    Analytics
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
                </a>
            </div>
            <div class="dash-comment-item">
                <div style="flex:1;">
                    <div class="dash-latest-stat">
                        <span class="stat-label">Total comments</span>
                        <span class="stat-value"><?= format_number($totalComments ?? 0) ?></span>
                    </div>
                </div>
            </div>
            <div class="dash-comment-item">
                <div style="flex:1;">
                    <div class="dash-latest-stat">
                        <span class="stat-label">Channel created</span>
                        <span class="stat-value"><?= date('M d, Y', strtotime($channel['created_at'] ?? 'now')) ?></span>
                    </div>
                </div>
            </div>
            <div class="dash-comment-item">
                <div style="flex:1;">
                    <div class="dash-latest-stat">
                        <span class="stat-label">Total view count</span>
                        <span class="stat-value"><?= format_number((int) ($channel['total_view_count'] ?? 0)) ?></span>
                    </div>
                </div>
            </div>
            <div class="dash-comment-item">
                <div style="flex:1;">
                    <div class="dash-latest-stat">
                        <span class="stat-label">Verified</span>
                        <span class="stat-value"><?= !empty($channel['is_verified']) ? 'Yes' : 'No' ?></span>
                    </div>
                </div>
            </div>
            <div class="dash-comment-item">
                <div style="flex:1;">
                    <div class="dash-latest-stat">
                        <span class="stat-label">Partner</span>
                        <span class="stat-value"><?= !empty($channel['is_partner']) ? 'Yes' : 'No' ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('viewsChart');
    if (!ctx) return;
    const labels = <?= json_encode(array_column($viewsChart ?? [], 'date')) ?>;
    const values = <?= json_encode(array_map('intval', array_column($viewsChart ?? [], 'views'))) ?>;
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const textColor = isDark ? '#aaa' : '#666';
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels.map(d => { const dt = new Date(d); return dt.toLocaleDateString('en-US', {month:'short', day:'numeric'}); }),
            datasets: [{
                data: values,
                borderColor: '#ff0000',
                backgroundColor: isDark ? 'rgba(255,0,0,0.08)' : 'rgba(255,0,0,0.05)',
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 5,
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#3a3a3a' : '#fff',
                    titleColor: isDark ? '#f1f1f1' : '#0f0f0f',
                    bodyColor: isDark ? '#f1f1f1' : '#0f0f0f',
                    borderColor: isDark ? '#555' : '#e0e0e0',
                    borderWidth: 1,
                    padding: 10,
                    displayColors: false,
                    callbacks: { label: (c) => c.parsed.y + ' views' }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor, drawBorder: false },
                    ticks: { color: textColor, font: { size: 11 }, padding: 8 },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: textColor, font: { size: 11 }, maxRotation: 0, autoSkipPadding: 20 },
                    border: { display: false }
                }
            }
        }
    });
});
</script>
