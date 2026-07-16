<?php $__layout = 'layouts.support'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-speedometer2 me-2 text-purple"></i>Support Dashboard</h4>
    <small class="text-muted"><?= date('l, F j, Y') ?></small>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 bg-opacity-10 stat-card" style="background: rgba(37,99,235,0.1); border-color: #2563eb !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Open Tickets</h6>
                        <h3 class="mb-0 text-primary"><?= format_number($openTickets ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-ticket-detailed fs-2 text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 stat-card" style="background: rgba(234,179,8,0.1); border-color: #eab308 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">In Progress</h6>
                        <h3 class="mb-0 text-warning"><?= format_number($inProgressTickets ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-arrow-repeat fs-2 text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 stat-card" style="background: rgba(22,163,74,0.1); border-color: #16a34a !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Resolved Today</h6>
                        <h3 class="mb-0 text-success"><?= format_number($resolvedToday ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-check-circle fs-2 text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 stat-card" style="background: rgba(124,58,237,0.1); border-color: #7c3aed !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Avg Response</h6>
                        <h3 class="mb-0" style="color: #a78bfa;"><?= ($avgResponseHours ?? 0) ?>h</h3>
                    </div>
                    <i class="bi bi-clock-history fs-2 opacity-50" style="color: #a78bfa;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Ticket Categories</h6>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-flag me-2"></i>Priority Breakdown</h6>
            </div>
            <div class="card-body">
                <canvas id="priorityChart" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Quick Stats</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <span class="text-muted">Total Tickets</span>
                    <strong><?= format_number($totalTickets ?? 0) ?></strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <span class="text-muted">Open + In Progress</span>
                    <strong class="text-warning"><?= format_number(($openTickets ?? 0) + ($inProgressTickets ?? 0)) ?></strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <span class="text-muted">Resolved Today</span>
                    <strong class="text-success"><?= format_number($resolvedToday ?? 0) ?></strong>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Resolution Rate</span>
                    <strong class="text-primary"><?= ($totalTickets ?? 0) > 0 ? round((($resolvedToday ?? 0) / max(($totalTickets ?? 1), 1)) * 100, 1) : 0 ?>%</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Tickets</h6>
        <a href="<?= url('/support/tickets') ?>" class="btn btn-sm btn-outline-secondary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subject</th>
                        <th>User</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th class="text-end">Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentTickets ?? [])): ?>
                        <?php foreach ($recentTickets as $t): ?>
                        <tr>
                            <td class="text-muted">#<?= $t['id'] ?></td>
                            <td><a href="<?= url('/support/tickets/' . $t['id']) ?>" class="text-decoration-none"><?= e(mb_substr($t['subject'], 0, 40)) ?><?= strlen($t['subject']) > 40 ? '...' : '' ?></a></td>
                            <td><?= e($t['username'] ?? 'User') ?></td>
                            <td><span class="badge bg-secondary text-capitalize"><?= e($t['category'] ?? 'general') ?></span></td>
                            <td><span class="badge badge-priority-<?= $t['priority'] ?? 'low' ?> text-capitalize"><?= e($t['priority'] ?? 'low') ?></span></td>
                            <td><span class="badge badge-status-<?= $t['status'] ?? 'open' ?> text-capitalize"><?= str_replace('_', ' ', $t['status'] ?? 'open') ?></span></td>
                            <td class="text-end text-muted small"><?= time_ago($t['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No tickets yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= asset('vendor/chart.min.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryLabels = <?= json_encode(array_column($categoryBreakdown ?? [], 'category')) ?>;
    const categoryData = <?= json_encode(array_map('intval', array_column($categoryBreakdown ?? [], 'count'))) ?>;
    const priorityLabels = <?= json_encode(array_column($priorityBreakdown ?? [], 'priority')) ?>;
    const priorityData = <?= json_encode(array_map('intval', array_column($priorityBreakdown ?? [], 'count'))) ?>;

    const colors = ['#7c3aed', '#2563eb', '#16a34a', '#eab308', '#f97316', '#dc2626', '#6b7280', '#ec4899'];

    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: categoryLabels.map(c => c.charAt(0).toUpperCase() + c.slice(1)),
            datasets: [{ data: categoryData, backgroundColor: colors, borderWidth: 0 }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { color: '#9ca3af', boxWidth: 12 } } } }
    });

    new Chart(document.getElementById('priorityChart'), {
        type: 'doughnut',
        data: {
            labels: priorityLabels.map(p => p.charAt(0).toUpperCase() + p.slice(1)),
            datasets: [{ data: priorityData, backgroundColor: ['#6b7280', '#2563eb', '#f59e0b', '#dc2626'], borderWidth: 0 }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { color: '#9ca3af', boxWidth: 12 } } } }
    });
});
</script>
