<?php $__layout = 'layouts.advertiser'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">My Ads</h4>
    <a href="<?= url('/advertiser/ads/upload') ?>" class="btn btn-primary btn-sm"><i class="bi bi-upload me-1"></i>Upload Ad</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Ad</th>
                        <th>Type</th>
                        <th>Impressions</th>
                        <th>Clicks</th>
                        <th>CTR</th>
                        <th>Spend</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ads ?? [])): ?>
                        <?php foreach ($ads as $ad): ?>
                        <tr>
                            <td class="fw-medium"><?= e($ad['title']) ?></td>
                            <td><span class="badge bg-info text-capitalize"><?= e(str_replace('_', ' ', $ad['type'])) ?></span></td>
                            <td><?= format_number((int) $ad['impressions']) ?></td>
                            <td><?= format_number((int) $ad['clicks']) ?></td>
                            <td><?= $ad['impressions'] > 0 ? round(($ad['clicks'] / $ad['impressions']) * 100, 2) . '%' : '0%' ?></td>
                            <td>$<?= number_format((float) $ad['spend'], 2) ?></td>
                            <td>
                                <?php $sc = ['pending' => 'warning', 'active' => 'success', 'paused' => 'info', 'rejected' => 'danger', 'expired' => 'secondary']; ?>
                                <span class="badge bg-<?= $sc[$ad['status']] ?? 'secondary' ?>"><?= ucfirst($ad['status']) ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No ads yet. <a href="<?= url('/advertiser/ads/upload') ?>">Upload your first ad</a></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (($pagination['last_page'] ?? 1) > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= !($pagination['has_prev_page'] ?? false) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) - 1 ?>">Previous</a>
        </li>
        <?php for ($i = max(1, ($pagination['current_page'] ?? 1) - 2); $i <= min($pagination['last_page'] ?? 1, ($pagination['current_page'] ?? 1) + 2); $i++): ?>
            <li class="page-item <?= $i === ($pagination['current_page'] ?? 1) ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= !($pagination['has_more_pages'] ?? false) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) + 1 ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
