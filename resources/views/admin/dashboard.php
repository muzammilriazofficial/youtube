@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Dashboard</h4>
    <span class="text-muted small"><?= date('l, F j, Y') ?></span>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small mb-1">Total Users</div>
                    <h4 class="fw-bold mb-0"><?= number_format($totalUsers) ?></h4>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-people"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small mb-1">Total Videos</div>
                    <h4 class="fw-bold mb-0"><?= number_format($totalVideos) ?></h4>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-play-btn"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small mb-1">Total Channels</div>
                    <h4 class="fw-bold mb-0"><?= number_format($totalChannels) ?></h4>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-broadcast"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small mb-1">Revenue This Month</div>
                    <h4 class="fw-bold mb-0">$<?= number_format($revenueThisMonth, 2) ?></h4>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-cash-coin"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3 border-start border-warning border-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-hourglass-split text-warning fs-4 me-3"></i>
                <div>
                    <div class="text-muted small">Pending Videos</div>
                    <h5 class="mb-0 fw-bold"><?= $pendingVideos ?></h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3 border-start border-danger border-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-flag text-danger fs-4 me-3"></i>
                <div>
                    <div class="text-muted small">Pending Reports</div>
                    <h5 class="mb-0 fw-bold"><?= $pendingReports ?></h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3 border-start border-info border-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-wallet2 text-info fs-4 me-3"></i>
                <div>
                    <div class="text-muted small">Pending Payouts</div>
                    <h5 class="mb-0 fw-bold"><?= $pendingPayouts ?></h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3 border-start border-success border-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-database text-success fs-4 me-3"></i>
                <div>
                    <div class="text-muted small">Database Size</div>
                    <h5 class="mb-0 fw-bold"><?= e($dbSize) ?></h5>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-4">
        <div class="card table-card">
            <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-people me-2"></i>User Growth (30 Days)</h6>
            </div>
            <div class="card-body">
                <div class="chart-container"><canvas id="userGrowthChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card table-card">
            <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-play-btn me-2"></i>Video Uploads (30 Days)</h6>
            </div>
            <div class="card-body">
                <div class="chart-container"><canvas id="videoUploadsChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card table-card">
            <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-graph-up me-2"></i>Revenue (12 Months)</h6>
            </div>
            <div class="card-body">
                <div class="chart-container"><canvas id="revenueChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <div class="card table-card">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Recent Users</h6>
                <a href="<?= url('/admin/users') ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>User</th><th>Email</th><th>Joined</th><th>Status</th></tr></thead>
                        <tbody>
                            @if(count($recentUsers) > 0)
                                @foreach($recentUsers as $u)
                                <tr>
                                    <td class="d-flex align-items-center">
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;font-size:12px;">
                                            <?= strtoupper(substr($u['username'] ?? 'U', 0, 1)) ?>
                                        </div>
                                        <?= e($u['username'] ?? '') ?>
                                    </td>
                                    <td class="text-muted small"><?= e($u['email'] ?? '') ?></td>
                                    <td class="text-muted small"><?= date('M d', strtotime($u['created_at'] ?? '')) ?></td>
                                    <td>
                                        @if(($u['status'] ?? '') === 'active')
                                            <span class="badge bg-success badge-status">Active</span>
                                        @elseif(($u['status'] ?? '') === 'banned')
                                            <span class="badge bg-danger badge-status">Banned</span>
                                        @else
                                            <span class="badge bg-secondary badge-status">Unknown</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr><td colspan="4" class="text-center text-muted py-3">No users yet</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card table-card">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Recent Videos</h6>
                <a href="<?= url('/admin/videos') ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Title</th><th>Channel</th><th>Views</th><th>Status</th></tr></thead>
                        <tbody>
                            @if(count($recentVideos) > 0)
                                @foreach($recentVideos as $v)
                                <tr>
                                    <td class="text-truncate" style="max-width:200px;"><?= e($v['title'] ?? '') ?></td>
                                    <td class="text-muted small"><?= e($v['channel_name'] ?? $v['username'] ?? '') ?></td>
                                    <td class="text-muted small"><?= number_format($v['view_count'] ?? 0) ?></td>
                                    <td>
                                        @if(($v['status'] ?? '') === 'published')
                                            <span class="badge bg-success badge-status">Published</span>
                                        @elseif(($v['status'] ?? '') === 'pending')
                                            <span class="badge bg-warning badge-status">Pending</span>
                                        @elseif(($v['status'] ?? '') === 'rejected')
                                            <span class="badge bg-danger badge-status">Rejected</span>
                                        @else
                                            <span class="badge bg-secondary badge-status"><?= e($v['status'] ?? '') ?></span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr><td colspan="4" class="text-center text-muted py-3">No videos yet</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom">
        <h6 class="fw-bold mb-0"><i class="bi bi-heart-pulse me-2"></i>System Status</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="d-flex align-items-center">
                    <span class="badge bg-success me-2 p-2"><i class="bi bi-check-lg"></i></span>
                    <div>
                        <div class="small fw-bold">PHP <?= phpversion() ?></div>
                        <div class="text-muted small">Runtime</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex align-items-center">
                    <span class="badge bg-success me-2 p-2"><i class="bi bi-check-lg"></i></span>
                    <div>
                        <div class="small fw-bold">Database</div>
                        <div class="text-muted small">Connected</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex align-items-center">
                    <span class="badge bg-info me-2 p-2"><i class="bi bi-info-lg"></i></span>
                    <div>
                        <div class="small fw-bold">Disk Free</div>
                        <div class="text-muted small"><?= e($diskFree) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex align-items-center">
                    <span class="badge bg-success me-2 p-2"><i class="bi bi-check-lg"></i></span>
                    <div>
                        <div class="small fw-bold">Memory</div>
                        <div class="text-muted small"><?= ini_get('memory_limit') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?= url("/admin/chart-data") ?>';
    const csrf = csrfToken();

    function loadChart(type, canvasId, config) {
        fetch(baseUrl + '?type=' + type, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(res => {
            const ctx = document.getElementById(canvasId).getContext('2d');
            new Chart(ctx, {
                type: config.type || 'line',
                data: {
                    labels: res.labels,
                    datasets: [{
                        label: config.label,
                        data: res.data,
                        borderColor: config.color,
                        backgroundColor: config.bgColor || config.color + '20',
                        borderWidth: 2,
                        fill: config.fill !== false,
                        tension: 0.4,
                        pointRadius: config.pointRadius || 0,
                        pointHoverRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { maxTicksLimit: 7, font: { size: 10 } } },
                        y: { beginAtZero: true, grid: { color: 'rgba(128,128,128,0.1)' }, ticks: { font: { size: 10 } } }
                    }
                }
            });
        });
    }

    loadChart('user_growth', 'userGrowthChart', { label: 'Users', color: '#0d6efd', type: 'line', fill: true });
    loadChart('video_uploads', 'videoUploadsChart', { label: 'Videos', color: '#198754', type: 'bar', bgColor: '#19875480' });
    loadChart('revenue', 'revenueChart', { label: 'Revenue ($)', color: '#ffc107', type: 'line', fill: true });
});
</script>
@endsection
