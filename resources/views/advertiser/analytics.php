<?php $__layout = 'layouts.advertiser'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Ad Analytics</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card border-0 bg-primary bg-opacity-10">
            <div class="card-body text-center">
                <h4 class="mb-0"><?= format_number($totalImpressions ?? 0) ?></h4>
                <small class="text-muted">Impressions</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 bg-success bg-opacity-10">
            <div class="card-body text-center">
                <h4 class="mb-0"><?= format_number($totalClicks ?? 0) ?></h4>
                <small class="text-muted">Clicks</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 bg-warning bg-opacity-10">
            <div class="card-body text-center">
                <h4 class="mb-0">$<?= number_format($totalSpend ?? 0, 2) ?></h4>
                <small class="text-muted">Spend</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 bg-info bg-opacity-10">
            <div class="card-body text-center">
                <h4 class="mb-0"><?= $ctr ?? 0 ?>%</h4>
                <small class="text-muted">CTR</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 bg-secondary bg-opacity-10">
            <div class="card-body text-center">
                <h4 class="mb-0">$<?= number_format($cpc ?? 0, 2) ?></h4>
                <small class="text-muted">CPC</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 bg-danger bg-opacity-10">
            <div class="card-body text-center">
                <h4 class="mb-0">$<?= number_format($cpm ?? 0, 2) ?></h4>
                <small class="text-muted">CPM</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">Performance Over Time</h6>
            </div>
            <div class="card-body">
                <canvas id="analyticsChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Performance by Type</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th class="text-end">Impr.</th>
                                <th class="text-end">Clicks</th>
                                <th class="text-end">Spend</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($byType ?? [])): ?>
                                <?php foreach ($byType as $t): ?>
                                <tr>
                                    <td class="small text-capitalize"><?= e(str_replace('_', ' ', $t['type'])) ?></td>
                                    <td class="text-end small"><?= format_number((int) $t['impressions']) ?></td>
                                    <td class="text-end small"><?= format_number((int) $t['clicks']) ?></td>
                                    <td class="text-end small">$<?= number_format((float) $t['spend'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No data.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h6 class="mb-0">Top Performing Ads</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Ad</th>
                        <th>Type</th>
                        <th class="text-end">Impressions</th>
                        <th class="text-end">Clicks</th>
                        <th class="text-end">CTR</th>
                        <th class="text-end">Spend</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($topAds ?? [])): ?>
                        <?php foreach ($topAds as $ad): ?>
                        <tr>
                            <td class="fw-medium"><?= e($ad['title']) ?></td>
                            <td><span class="badge bg-info text-capitalize small"><?= e(str_replace('_', ' ', $ad['type'])) ?></span></td>
                            <td class="text-end"><?= format_number((int) $ad['impressions']) ?></td>
                            <td class="text-end"><?= format_number((int) $ad['clicks']) ?></td>
                            <td class="text-end"><?= $ad['impressions'] > 0 ? round(($ad['clicks'] / $ad['impressions']) * 100, 2) . '%' : '0%' ?></td>
                            <td class="text-end">$<?= number_format((float) $ad['spend'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No ad data yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= asset('vendor/chart.min.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('<?= url('/advertiser/analytics/data') ?>?days=30', {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const ctx = document.getElementById('analyticsChart');
        if (ctx && data.labels) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels.map(d => { const dt = new Date(d); return dt.toLocaleDateString('en-US', {month:'short', day:'numeric'}); }),
                    datasets: [
                        { label: 'Impressions', data: data.impressions, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,0.1)', fill: true, tension: 0.3, pointRadius: 2 },
                        { label: 'Clicks', data: data.clicks, borderColor: '#198754', backgroundColor: 'rgba(25,135,84,0.1)', fill: true, tension: 0.3, pointRadius: 2 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    })
    .catch(() => {});
});
</script>
