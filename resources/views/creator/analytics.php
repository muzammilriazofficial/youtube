<?php $__layout = 'layouts.app'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Analytics Overview</h4>
    <div class="btn-group btn-group-sm">
        <?php foreach ([7 => '7 days', 28 => '28 days', 90 => '90 days', 365 => '12 months'] as $d => $label): ?>
            <a href="<?= url('/creator/analytics', ['days' => $d]) ?>" class="btn <?= ($days ?? 28) == $d ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0"><?= format_number($totalViews ?? 0) ?></h3>
                <small class="text-muted">Views (<?= $days ?> days)</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0"><?= number_format($totalWatchTime ?? 0, 1) ?>h</h3>
                <small class="text-muted">Watch Time</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0"><?= format_number($newSubscribers ?? 0) ?></h3>
                <small class="text-muted">New Subscribers</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0">$<?= number_format($estimatedRevenue ?? 0, 2) ?></h3>
                <small class="text-muted">Est. Revenue</small>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Views Over Time</h6>
        <a href="<?= url('/creator/analytics') ?>?days=<?= $days ?>" class="btn btn-sm btn-outline-secondary">Refresh</a>
    </div>
    <div class="card-body">
        <canvas id="mainChart" height="300"></canvas>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Top Performing Videos</h6>
        <a href="<?= url('/creator/analytics/videos') ?>" class="btn btn-sm btn-outline-secondary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Video</th>
                        <th class="text-end">Views</th>
                        <th class="text-end">Likes</th>
                        <th class="text-end">Comments</th>
                        <th>Published</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($topVideos ?? []) as $vid): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="width:80px;height:45px;overflow:hidden;border-radius:4px;background:var(--bs-secondary);flex-shrink:0;">
                                    <?php if (!empty($vid['thumbnail'])): ?>
                                        <img src="<?= e($vid['thumbnail']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center h-100"><i class="bi bi-play-circle"></i></div>
                                    <?php endif; ?>
                                </div>
                                <small class="fw-medium"><?= e(mb_substr($vid['title'], 0, 40)) ?></small>
                            </div>
                        </td>
                        <td class="text-end"><?= format_number((int) $vid['view_count']) ?></td>
                        <td class="text-end"><?= format_number((int) $vid['like_count']) ?></td>
                        <td class="text-end"><?= format_number((int) $vid['comment_count']) ?></td>
                        <td><small class="text-muted"><?= date('M d', strtotime($vid['published_at'] ?? $vid['created_at'])) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= asset('vendor/chart.min.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('mainChart');
    if (ctx) {
        const labels = <?= json_encode(array_column($viewsOverTime ?? [], 'date')) ?>;
        const values = <?= json_encode(array_map('intval', array_column($viewsOverTime ?? [], 'value'))) ?>;
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.map(d => { const dt = new Date(d); return dt.toLocaleDateString('en-US', {month:'short', day:'numeric'}); }),
                datasets: [{
                    label: 'Views',
                    data: values,
                    borderColor: '#ff0000',
                    backgroundColor: 'rgba(255,0,0,0.08)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                    pointBackgroundColor: '#ff0000',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
