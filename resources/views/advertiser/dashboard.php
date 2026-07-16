<?php $__layout = 'layouts.advertiser'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Advertiser Dashboard</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 bg-primary bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Total Impressions</h6>
                        <h3 class="mb-0"><?= format_number($totalImpressions ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-eye fs-2 text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-success bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Total Clicks</h6>
                        <h3 class="mb-0"><?= format_number($totalClicks ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-cursor fs-2 text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-warning bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Total Spend</h6>
                        <h3 class="mb-0">$<?= number_format($totalSpend ?? 0, 2) ?></h3>
                    </div>
                    <i class="bi bi-cash-coin fs-2 text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-info bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">CTR</h6>
                        <h3 class="mb-0"><?= $ctr ?? 0 ?>%</h3>
                    </div>
                    <i class="bi bi-graph-up fs-2 text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 bg-secondary bg-opacity-10">
            <div class="card-body text-center">
                <h3 class="mb-0"><?= $totalCampaigns ?? 0 ?></h3>
                <small class="text-muted">Total Campaigns</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-secondary bg-opacity-10">
            <div class="card-body text-center">
                <h3 class="mb-0"><?= $activeCampaigns ?? 0 ?></h3>
                <small class="text-muted">Active Campaigns</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-secondary bg-opacity-10">
            <div class="card-body text-center">
                <h3 class="mb-0"><?= $totalAds ?? 0 ?></h3>
                <small class="text-muted">Total Ads</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-secondary bg-opacity-10">
            <div class="card-body text-center">
                <h3 class="mb-0"><?= $activeAds ?? 0 ?></h3>
                <small class="text-muted">Active Ads</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Quick Actions</h6></div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= url('/advertiser/campaigns/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>New Campaign</a>
                    <a href="<?= url('/advertiser/ads/upload') ?>" class="btn btn-success btn-sm"><i class="bi bi-upload me-1"></i>Upload Ad</a>
                    <a href="<?= url('/advertiser/budget') ?>" class="btn btn-warning btn-sm"><i class="bi bi-wallet2 me-1"></i>Add Budget</a>
                    <a href="<?= url('/advertiser/analytics') ?>" class="btn btn-info btn-sm"><i class="bi bi-graph-up me-1"></i>Analytics</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Spend - Last 30 Days</h6>
                <a href="<?= url('/advertiser/analytics') ?>" class="btn btn-sm btn-outline-secondary">View Details</a>
            </div>
            <div class="card-body">
                <canvas id="spendChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Recent Campaigns</h6></div>
            <div class="card-body p-0">
                <?php if (!empty($recentCampaigns ?? [])): ?>
                    <?php foreach ($recentCampaigns as $c): ?>
                    <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small fw-medium"><?= e($c['name']) ?></div>
                            <small class="text-muted"><?= '$' . number_format((float) $c['spent'], 2) ?> / $<?= number_format((float) $c['budget'], 2) ?></small>
                        </div>
                        <?php
                            $cColors = ['active' => 'success', 'draft' => 'secondary', 'paused' => 'warning', 'completed' => 'info', 'cancelled' => 'danger'];
                        ?>
                        <span class="badge bg-<?= $cColors[$c['status']] ?? 'secondary' ?>"><?= ucfirst($c['status']) ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-4">No campaigns yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('vendor/chart.min.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('spendChart');
    if (ctx) {
        const labels = <?= json_encode(array_column($spendChart ?? [], 'date')) ?>;
        const values = <?= json_encode(array_map('floatval', array_column($spendChart ?? [], 'daily_spend'))) ?>;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.map(d => { const dt = new Date(d); return dt.toLocaleDateString('en-US', {month:'short', day:'numeric'}); }),
                datasets: [{
                    label: 'Spend ($)',
                    data: values,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25,135,84,0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2,
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
