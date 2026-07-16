<?php $__layout = 'layouts.app'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Audience Analytics</h4>
    <a href="<?= url('/creator/analytics') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Overview</a>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Age Demographics</h6></div>
            <div class="card-body">
                <canvas id="ageChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Gender Split</h6></div>
            <div class="card-body">
                <canvas id="genderChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Top Countries</h6></div>
            <div class="card-body">
                <canvas id="geoChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Device Types</h6></div>
            <div class="card-body">
                <canvas id="deviceChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h6 class="mb-0">Geography Details</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Country</th><th class="text-end">Views</th><th class="text-end">Share</th></tr>
                </thead>
                <tbody>
                    <?php $totalGeo = array_sum(array_column($geography ?? [], 'views')); ?>
                    <?php foreach (($geography ?? []) as $geo): ?>
                    <tr>
                        <td><?= e($geo['country']) ?></td>
                        <td class="text-end"><?= format_number((int) $geo['views']) ?></td>
                        <td class="text-end"><?= $totalGeo > 0 ? number_format(((int) $geo['views'] / $totalGeo) * 100, 1) : 0 ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($geography)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">No data available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= asset('vendor/chart.min.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = ['#ff6384','#36a2eb','#ffce56','#4bc0c0','#9966ff','#ff9f40','#c9cbcf','#7bc043','#ee5253','#0abde3'];
    const colorAlpha = colors.map(c => c + '99');

    const ageCtx = document.getElementById('ageChart');
    if (ageCtx) {
        const ageData = <?= json_encode($demographics ?? []) ?>;
        new Chart(ageCtx, {
            type: 'bar',
            data: {
                labels: ageData.map(d => d.age_range),
                datasets: [{ label: 'Viewers', data: ageData.map(d => parseInt(d.count)), backgroundColor: colorAlpha, borderColor: colors, borderWidth: 1, borderRadius: 4 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true }, x: { grid: { display: false } } } }
        });
    }

    const genderCtx = document.getElementById('genderChart');
    if (genderCtx) {
        const genderData = <?= json_encode($genderSplit ?? []) ?>;
        new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: genderData.map(d => d.gender || 'Unknown'),
                datasets: [{ data: genderData.map(d => parseInt(d.count)), backgroundColor: ['#36a2eb','#ff6384','#ffce56','#c9cbcf'], borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    const geoCtx = document.getElementById('geoChart');
    if (geoCtx) {
        const geoData = <?= json_encode($geography ?? []) ?>;
        new Chart(geoCtx, {
            type: 'bar',
            data: {
                labels: geoData.map(d => d.country),
                datasets: [{ label: 'Views', data: geoData.map(d => parseInt(d.views)), backgroundColor: colorAlpha, borderColor: colors, borderWidth: 1, borderRadius: 4 }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
        });
    }

    const devCtx = document.getElementById('deviceChart');
    if (devCtx) {
        const devData = <?= json_encode($devices ?? []) ?>;
        new Chart(devCtx, {
            type: 'pie',
            data: {
                labels: devData.map(d => d.device_type || 'Unknown'),
                datasets: [{ data: devData.map(d => parseInt(d.count)), backgroundColor: ['#36a2eb','#ff6384','#ffce56','#4bc0c0','#9966ff'], borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }
});
</script>
