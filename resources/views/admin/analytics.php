@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Analytics</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3">
            <div class="text-muted small mb-1">Total Views</div>
            <h4 class="fw-bold mb-0"><?= number_format($totalViews) ?></h4>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3">
            <div class="text-muted small mb-1">Total Likes</div>
            <h4 class="fw-bold mb-0"><?= number_format($totalLikes) ?></h4>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3">
            <div class="text-muted small mb-1">Total Comments</div>
            <h4 class="fw-bold mb-0"><?= number_format($totalComments) ?></h4>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3">
            <div class="text-muted small mb-1">Total Subscriptions</div>
            <h4 class="fw-bold mb-0"><?= number_format($totalSubscriptions) ?></h4>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3 border-start border-primary border-3">
            <div class="text-muted small">Today's Views</div>
            <h5 class="fw-bold mb-0"><?= number_format($todayViews) ?></h5>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3 border-start border-success border-3">
            <div class="text-muted small">Today's Signups</div>
            <h5 class="fw-bold mb-0"><?= number_format($todaySignups) ?></h5>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <div class="card table-card">
            <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0"><h6 class="fw-bold mb-0"><i class="bi bi-eye me-2"></i>Views (30 Days)</h6></div>
            <div class="card-body"><div class="chart-container"><canvas id="viewsChart"></canvas></div></div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card table-card">
            <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0"><h6 class="fw-bold mb-0"><i class="bi bi-person-plus me-2"></i>Signups (30 Days)</h6></div>
            <div class="card-body"><div class="chart-container"><canvas id="signupsChart"></canvas></div></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <div class="card table-card">
            <div class="card-header bg-transparent border-bottom"><h6 class="fw-bold mb-0">Top Videos by Views</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Title</th><th>Views</th><th>Likes</th></tr></thead>
                        <tbody>
                            @foreach($topVideos as $tv)
                            <tr><td class="text-truncate" style="max-width:250px;"><?= e($tv['title'] ?? '') ?></td><td><?= number_format($tv['view_count'] ?? 0) ?></td><td><?= number_format($tv['like_count'] ?? 0) ?></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card table-card">
            <div class="card-header bg-transparent border-bottom"><h6 class="fw-bold mb-0">Top Channels by Subscribers</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Channel</th><th>Subscribers</th><th>Videos</th></tr></thead>
                        <tbody>
                            @foreach($topChannels as $tc)
                            <tr><td class="fw-semibold"><?= e($tc['name'] ?? '') ?></td><td><?= number_format($tc['subscriber_count'] ?? 0) ?></td><td><?= number_format($tc['video_count'] ?? 0) ?></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const base = '<?= url("/admin/analytics/data") ?>';
    function loadChart(type, canvasId, label, color) {
        fetch(base + '?type=' + type, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
        .then(r => r.json()).then(res => {
            new Chart(document.getElementById(canvasId).getContext('2d'), {
                type: 'line', data: { labels: res.labels, datasets: [{ label, data: res.data || res.likes || [], borderColor: color, backgroundColor: color + '20', fill: true, tension: 0.4, pointRadius: 0 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { maxTicksLimit: 7, font: { size: 10 } } }, y: { beginAtZero: true, grid: { color: 'rgba(128,128,128,0.1)' } } } }
            });
        });
    }
    loadChart('views', 'viewsChart', 'Views', '#0d6efd');
    loadChart('signups', 'signupsChart', 'Signups', '#198754');
});
</script>
@endsection
