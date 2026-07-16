<?php $__layout = 'layouts.app'; ?>

<h4 class="mb-4">Monetization</h4>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Monetization Status</h6></div>
            <div class="card-body">
                <?php if (!empty($monetization)): ?>
                    <div class="d-flex align-items-center mb-3">
                        <?php
                        $statusConfig = [
                            'approved' => ['icon' => 'check-circle-fill', 'color' => 'success', 'text' => 'Approved'],
                            'pending' => ['icon' => 'clock-fill', 'color' => 'warning', 'text' => 'Pending Review'],
                            'rejected' => ['icon' => 'x-circle-fill', 'color' => 'danger', 'text' => 'Rejected'],
                        ];
                        $st = $statusConfig[$monetization['status']] ?? ['icon' => 'question-circle', 'color' => 'secondary', 'text' => ucfirst($monetization['status'])];
                        ?>
                        <i class="bi bi-<?= $st['icon'] ?> text-<?= $st['color'] ?> fs-3 me-2"></i>
                        <div>
                            <h5 class="mb-0"><?= $st['text'] ?></h5>
                            <small class="text-muted">Applied <?= date('M d, Y', strtotime($monetization['applied_at'] ?? $monetization['created_at'])) ?></small>
                        </div>
                    </div>
                    <?php if ($monetization['status'] === 'approved'): ?>
                        <div class="alert alert-success mb-0">
                            <i class="bi bi-cash-coin me-2"></i>Your channel is monetized. You're earning revenue from ads shown on your videos.
                            <a href="<?= url('/creator/monetization/earnings') ?>" class="alert-link ms-2">View Earnings &rarr;</a>
                        </div>
                    <?php elseif ($monetization['status'] === 'pending'): ?>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-hourglass-split me-2"></i>Your application is being reviewed. This typically takes 1-2 weeks.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-x-circle me-2"></i>Your application was not approved. Please review the requirements and reapply.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <h5>Apply for Monetization</h5>
                    <p class="text-muted">Join the Partner Program to earn money from your content.</p>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center <?= $subscriberCount >= 1000 ? 'border-success' : '' ?>">
                                <h4 class="mb-1 <?= $subscriberCount >= 1000 ? 'text-success' : '' ?>"><?= format_number($subscriberCount) ?> / 1,000</h4>
                                <small class="text-muted">Subscribers</small>
                                <div class="progress mt-2" style="height:4px;">
                                    <div class="progress-bar bg-<?= $subscriberCount >= 1000 ? 'success' : 'primary' ?>" style="width:<?= min(100, ($subscriberCount / 1000) * 100) ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center <?= $watchHours >= 4000 ? 'border-success' : '' ?>">
                                <h4 class="mb-1 <?= $watchHours >= 4000 ? 'text-success' : '' ?>"><?= format_number((int) $watchHours) ?> / 4,000</h4>
                                <small class="text-muted">Watch Hours</small>
                                <div class="progress mt-2" style="height:4px;">
                                    <div class="progress-bar bg-<?= $watchHours >= 4000 ? 'success' : 'primary' ?>" style="width:<?= min(100, ($watchHours / 4000) * 100) ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center <?= $totalViews >= 1000000 ? 'border-success' : '' ?>">
                                <h4 class="mb-1 <?= $totalViews >= 1000000 ? 'text-success' : '' ?>"><?= format_number($totalViews) ?> / 10M</h4>
                                <small class="text-muted">Public Views (90 days)</small>
                                <div class="progress mt-2" style="height:4px;">
                                    <div class="progress-bar bg-<?= $totalViews >= 1000000 ? 'success' : 'primary' ?>" style="width:<?= min(100, ($totalViews / 10000000) * 100) ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ($eligible): ?>
                        <form method="POST" action="<?= url('/creator/monetization/apply') ?>" onsubmit="return confirm('Submit monetization application?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success"><i class="bi bi-cash-coin me-1"></i>Apply for Monetization</button>
                        </form>
                    <?php else: ?>
                        <div class="text-muted"><i class="bi bi-info-circle me-1"></i>You need to meet the eligibility requirements to apply.</div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Quick Links</h6></div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="<?= url('/creator/monetization/earnings') ?>" class="list-group-item list-group-item-action d-flex align-items-center"><i class="bi bi-graph-up-arrow text-success me-2"></i>Earnings History</a>
                    <a href="<?= url('/creator/monetization/payouts') ?>" class="list-group-item list-group-item-action d-flex align-items-center"><i class="bi bi-wallet2 text-primary me-2"></i>Payout History</a>
                    <a href="<?= url('/creator/analytics/revenue') ?>" class="list-group-item list-group-item-action d-flex align-items-center"><i class="bi bi-bar-chart text-info me-2"></i>Revenue Analytics</a>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Revenue Requirements</h6></div>
            <div class="card-body small">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i>1,000+ subscribers</li>
                    <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i>4,000+ public watch hours</li>
                    <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i>AdSense account linked</li>
                    <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i>No community guideline strikes</li>
                    <li><i class="bi bi-check-lg text-success me-2"></i>Comply with monetization policies</li>
                </ul>
            </div>
        </div>
    </div>
</div>
