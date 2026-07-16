@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Revenue Dashboard</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3"><div class="text-muted small mb-1">Total Revenue</div><h4 class="fw-bold text-success mb-0">$<?= number_format($totalRevenue, 2) ?></h4></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3"><div class="text-muted small mb-1">This Month</div><h4 class="fw-bold text-primary mb-0">$<?= number_format($thisMonth, 2) ?></h4></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3"><div class="text-muted small mb-1">Last Month</div><h4 class="fw-bold text-info mb-0">$<?= number_format($lastMonth, 2) ?></h4></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3"><div class="text-muted small mb-1">Pending Payouts</div><h4 class="fw-bold text-warning mb-0">$<?= number_format($pendingPayouts, 2) ?></h4></div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3"><div class="text-muted small mb-1">Total Paid Out</div><h4 class="fw-bold mb-0">$<?= number_format($totalPayouts, 2) ?></h4></div>
    </div>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0"><h6 class="fw-bold mb-0"><i class="bi bi-graph-up me-2"></i>Monthly Revenue (12 Months)</h6></div>
    <div class="card-body"><div class="chart-container"><canvas id="monthlyRevenueChart"></canvas></div></div>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom"><h6 class="fw-bold mb-0">Recent Payments</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>User</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @if(count($recentPayments) > 0)
                        @foreach($recentPayments as $p)
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td class="text-muted small"><?= e($p['username'] ?? '') ?></td>
                            <td class="fw-bold">$<?= number_format((float)($p['amount'] ?? 0), 2) ?></td>
                            <td><span class="badge bg-info badge-status"><?= e($p['method'] ?? '') ?></span></td>
                            <td>
                                @if(($p['status'] ?? '') === 'completed')
                                    <span class="badge bg-success badge-status">Completed</span>
                                @else
                                    <span class="badge bg-secondary badge-status"><?= e($p['status'] ?? '') ?></span>
                                @endif
                            </td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($p['created_at'] ?? '')) ?></td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="6" class="text-center text-muted py-4">No payments yet</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const data = <?= json_encode($monthlyRevenue) ?>;
    new Chart(document.getElementById('monthlyRevenueChart').getContext('2d'), {
        type: 'bar', data: {
            labels: data.map(d => d.month),
            datasets: [{ label: 'Revenue', data: data.map(d => d.revenue), backgroundColor: '#19875480', borderColor: '#198754', borderWidth: 1 }]
        }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(128,128,128,0.1)' } }, x: { grid: { display: false } } } }
    });
});
</script>
@endsection
