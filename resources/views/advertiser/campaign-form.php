<?php $__layout = 'layouts.advertiser'; ?>

<div class="mb-3">
    <a href="<?= url('/advertiser/campaigns') ?>" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i>Back to Campaigns</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?= $campaign ? 'Edit Campaign' : 'Create Campaign' ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $campaign ? url('/advertiser/campaigns/' . $campaign['id'] . '/update') : url('/advertiser/campaigns/store') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Campaign Name</label>
                        <input type="text" name="name" class="form-control" required value="<?= e($campaign['name'] ?? old('name', '')) ?>" placeholder="e.g. Summer Sale 2026">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Budget ($)</label>
                            <input type="number" name="budget" class="form-control" required step="0.01" min="1" value="<?= e((string) ($campaign['budget'] ?? old('budget', ''))) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php foreach (['draft', 'active', 'paused'] as $s): ?>
                                    <option value="<?= $s ?>" <?= ($campaign['status'] ?? 'draft') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" required value="<?= e($campaign['start_date'] ?? old('start_date', '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" required value="<?= e($campaign['end_date'] ?? old('end_date', '')) ?>">
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i><?= $campaign ? 'Update Campaign' : 'Create Campaign' ?></button>
                        <a href="<?= url('/advertiser/campaigns') ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
