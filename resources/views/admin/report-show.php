@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Report #<?= $report['id'] ?? '' ?></h4>
    <a href="<?= url('/admin/reports') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card table-card mb-4">
            <div class="card-header bg-transparent border-bottom"><h6 class="fw-bold mb-0">Report Details</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Reporter</div>
                        <div class="fw-semibold"><?= e($report['reporter_username'] ?? '') ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Type</div>
                        <span class="badge bg-info badge-status"><?= e($report['reportable_type'] ?? '') ?></span>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Status</div>
                        @if(($report['status'] ?? '') === 'pending')
                            <span class="badge bg-warning badge-status">Pending</span>
                        @elseif(($report['status'] ?? '') === 'resolved')
                            <span class="badge bg-success badge-status">Resolved</span>
                        @else
                            <span class="badge bg-secondary badge-status"><?= e($report['status'] ?? '') ?></span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Date</div>
                        <div><?= e($report['created_at'] ?? '') ?></div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Reason</div>
                        <div class="fw-semibold"><?= e($report['reason'] ?? '') ?></div>
                    </div>
                    <?php if (!empty($report['description'])): ?>
                    <div class="col-12">
                        <div class="text-muted small">Description</div>
                        <div><?= nl2br(e($report['description'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($reportable): ?>
        <div class="card table-card mb-4">
            <div class="card-header bg-transparent border-bottom"><h6 class="fw-bold mb-0">Reported <?= e($report['reportable_type'] ?? '') ?></h6></div>
            <div class="card-body">
                @if(($report['reportable_type'] ?? '') === 'video')
                    <div class="fw-semibold"><?= e($reportable['title'] ?? '') ?></div>
                    <div class="text-muted small">Views: <?= number_format($reportable['view_count'] ?? 0) ?></div>
                @elseif(($report['reportable_type'] ?? '') === 'comment')
                    <div><?= e($reportable['content'] ?? '') ?></div>
                    <div class="text-muted small">By: <?= e($reportable['username'] ?? '') ?></div>
                @elseif(($report['reportable_type'] ?? '') === 'channel')
                    <div class="fw-semibold"><?= e($reportable['name'] ?? '') ?></div>
                @else
                    <div class="fw-semibold"><?= e($reportable['username'] ?? '') ?></div>
                @endif
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <div class="card table-card mb-4">
            <div class="card-header bg-transparent border-bottom"><h6 class="fw-bold mb-0">Resolve Report</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('/admin/reports/resolve/' . $report['id']) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Action</label>
                        <select class="form-select" name="status" required>
                            <option value="resolved">Resolve</option>
                            <option value="dismissed">Dismiss</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Admin Notes</label>
                        <textarea class="form-control" name="notes" rows="4" placeholder="Add notes about this report..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
