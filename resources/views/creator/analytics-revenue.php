<?php $__layout = 'layouts.app'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Revenue Analytics</h4>
    <div>
        <a href="<?= url('/creator/analytics') ?>" class="btn btn-outline-secondary btn-sm me-2"><i class="bi bi-arrow-left me-1"></i>Overview</a>
        <div class="btn-group btn-group-sm">
            <?php foreach ([7 => '7d', 28 => '28d', 90 => '90d'] as $d => $label): ?>
                <a href="<?= url('/creator/analytics/revenue', ['days' => $d]) ?>" class="btn <?= ($days ?? 28) == $d ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="text-success mb-0">$<?= number_format($totalRevenue ?? 0, 2) ?></h3>
                <small class="text-muted">Total Revenue (<?= $days ?> days)</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0">$<?= number_format(!empty($revenueByVideo) ? array_sum(array_column($revenueByVideo, 'revenue')) / count($revenueByVideo) : 0, 2) ?></h3>
                <small class="text-muted">Avg. per Video</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0"><?= count($revenueByVideo ?? []) ?></h3>
                <small class="text-muted">Monetized Videos</small>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0">Revenue Over Time</h6></div>
    <div class="card-body">
        <canvas id="revenueChart" height="280"></canvas>
    </div>
</div>

<div class="card">
    <div class="card-header"><h6 class="mb-0">Revenue by Video</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Video</th>
                        <th class="text-end">Revenue</th>
                        <th class="text-end">Share</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $totalRev = array_sum(array_column($revenueByVideo ?? [], 'revenue')); ?>
                    <?php foreach (($revenueByVideo ?? []) as $rv): ?>
                    <tr>
                        <td class="fw-medium"><?= e($rv['title']) ?></td>
                        <td class="text-end">$<?= number_format((float) $rv['revenue'], 2) ?></td>
                        <td class="text-end"><?= $totalRev > 0 ? number_format(((float) $rv['revenue'] / $totalRev) * 100, 1) : 0 ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($revenueByVideo)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">No revenue data available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= asset('vendor/chart.min.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        const labels = <?= json_encode(array_column($revenueOverTime ?? [], 'date')) ?>;
        const values = <?= json_encode(array_map('floatval', array_column($revenueOverTime ?? [], 'value'))) ?>;
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels.map(d => { const dt = new Date(d); return dt.toLocaleDateString('en-US', {month:'short', day:'numeric'}); }),
                datasets: [{
                    label: 'Revenue ($)',
                    data: values,
                    backgroundColor: 'rgba(40, 167, 69, 0.6)',
                    borderColor: '#28a745',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { callback: v => '$' + v } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
