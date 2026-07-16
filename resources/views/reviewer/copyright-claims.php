<?php $__layout = 'layouts.reviewer'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Copyright Claims - Detail</h4>
    <a href="<?= url('/reviewer/copyright') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Claimant</th>
                        <th>Work</th>
                        <th>Video</th>
                        <th>Channel</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($claims ?? [])): ?>
                        <?php foreach ($claims as $claim): ?>
                        <tr>
                            <td><?= $claim['id'] ?></td>
                            <td>
                                <div class="small"><?= e($claim['claimant_name'] ?? '') ?></div>
                                <small class="text-muted"><?= e($claim['claimant_email'] ?? '') ?></small>
                            </td>
                            <td class="text-truncate small" style="max-width:150px;"><?= e($claim['original_work_title']) ?></td>
                            <td class="text-truncate small" style="max-width:120px;"><?= e($claim['title'] ?? '') ?></td>
                            <td class="small"><?= e($claim['name'] ?? '') ?></td>
                            <td>
                                <?php $sc = ['pending' => 'warning', 'accepted' => 'success', 'rejected' => 'danger', 'counter_notified' => 'info', 'resolved' => 'secondary']; ?>
                                <span class="badge bg-<?= $sc[$claim['status']] ?? 'secondary' ?>"><?= ucfirst(str_replace('_', ' ', $claim['status'])) ?></span>
                            </td>
                            <td class="text-end">
                                <?php if ($claim['status'] === 'pending'): ?>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#resolveModal<?= $claim['id'] ?>">Resolve</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No claims.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php foreach (($claims ?? []) as $claim): ?>
<?php if ($claim['status'] === 'pending'): ?>
<div class="modal fade" id="resolveModal<?= $claim['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/reviewer/copyright/' . $claim['id'] . '/resolve') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Resolve Claim #<?= $claim['id'] ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Work:</strong> <?= e($claim['original_work_title']) ?></p>
                    <p><strong>Description:</strong> <?= e($claim['description']) ?></p>
                    <div class="mb-3">
                        <label class="form-label">Resolution</label>
                        <select name="resolution" class="form-select" required>
                            <option value="accepted">Accept Claim (take down video)</option>
                            <option value="rejected">Reject Claim (keep video)</option>
                            <option value="counter_notified">Counter-notify (restore video)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Resolution notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Resolution</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endforeach; ?>
