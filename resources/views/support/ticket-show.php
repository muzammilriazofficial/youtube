<?php $__layout = 'layouts.support'; ?>

<div class="mb-3">
    <a href="<?= url('/support/tickets') ?>" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i>Back to Tickets</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">#<?= $ticket['id'] ?> — <?= e($ticket['subject']) ?></h5>
                    <small class="text-muted">Opened <?= time_ago($ticket['created_at']) ?> by <a href="<?= url('/support/users/' . $ticket['user_id']) ?>"><?= e($ticket['username'] ?? 'User') ?></a></small>
                </div>
                <div>
                    <span class="badge badge-status-<?= $ticket['status'] ?> text-capitalize fs-6"><?= str_replace('_', ' ', $ticket['status']) ?></span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Category</small>
                        <span class="badge bg-secondary text-capitalize"><?= e($ticket['category'] ?? 'general') ?></span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Priority</small>
                        <span class="badge badge-priority-<?= $ticket['priority'] ?? 'low' ?> text-capitalize"><?= e($ticket['priority'] ?? 'low') ?></span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Created</small>
                        <span><?= e($ticket['created_at']) ?></span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Last Updated</small>
                        <span><?= e($ticket['updated_at'] ?? $ticket['created_at']) ?></span>
                    </div>
                </div>

                <?php if (!empty($ticket['description'])): ?>
                    <div class="border-top pt-3">
                        <p class="mb-0"><?= nl2br(e($ticket['description'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($relatedVideo)): ?>
                    <div class="border-top pt-3 mt-3">
                        <small class="text-muted d-block mb-2"><i class="bi bi-play-btn me-1"></i>Related Video</small>
                        <div class="d-flex align-items-center p-2 bg-body-secondary rounded">
                            <div class="me-3">
                                <i class="bi bi-play-circle fs-3 text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-semibold"><?= e($relatedVideo['title'] ?? 'Video') ?></div>
                                <small class="text-muted"><?= format_number((int) ($relatedVideo['views'] ?? 0)) ?> views</small>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($relatedChannel)): ?>
                    <div class="border-top pt-3 mt-3">
                        <small class="text-muted d-block mb-2"><i class="bi bi-broadcast me-1"></i>Related Channel</small>
                        <div class="d-flex align-items-center p-2 bg-body-secondary rounded">
                            <div class="me-3">
                                <i class="bi bi-person-video2 fs-3 text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-semibold"><?= e($relatedChannel['name'] ?? 'Channel') ?></div>
                                <small class="text-muted"><?= format_number((int) ($relatedChannel['subscribers'] ?? 0)) ?> subscribers</small>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Conversation Thread</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($replies ?? [])): ?>
                    <?php foreach ($replies as $r): ?>
                        <?php $isAgent = !empty($r['is_agent']); ?>
                        <div class="d-flex <?= $isAgent ? 'justify-content-end' : 'justify-content-start' ?> mb-3">
                            <div class="d-flex <?= $isAgent ? 'flex-row-reverse' : '' ?>" style="max-width: 80%;">
                                <div class="me-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center <?= $isAgent ? 'bg-primary' : 'bg-secondary' ?>" style="width:36px;height:36px;">
                                        <i class="bi bi-<?= $isAgent ? 'headset' : 'person' ?> text-white small"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center mb-1 <?= $isAgent ? 'justify-content-end' : '' ?>">
                                        <small class="fw-semibold <?= $isAgent ? 'text-primary' : '' ?>"><?= $isAgent ? 'Agent' : e($ticket['username'] ?? 'User') ?></small>
                                        <small class="text-muted ms-2"><?= time_ago($r['created_at']) ?></small>
                                    </div>
                                    <div class="p-3 rounded <?= $isAgent ? 'bg-primary bg-opacity-10 border border-primary border-opacity-25' : 'bg-body-secondary' ?>">
                                        <?= nl2br(e($r['message'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted py-3 mb-0">No replies yet. Start the conversation below.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-reply me-2"></i>Reply to Ticket</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('/support/tickets/' . $ticket['id'] . '/reply') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <textarea name="message" class="form-control" rows="4" placeholder="Type your reply..." required></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Send Reply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Ticket Controls</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label small text-muted">Update Status</label>
                    <form method="POST" action="<?= url('/support/tickets/' . $ticket['id'] . '/status') ?>" class="d-flex gap-2">
                        <?= csrf_field() ?>
                        <select name="status" class="form-select form-select-sm flex-grow-1">
                            <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                            <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="waiting_on_user" <?= $ticket['status'] === 'waiting_on_user' ? 'selected' : '' ?>>Waiting on User</option>
                            <option value="resolved" <?= $ticket['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                            <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-check-lg"></i></button>
                    </form>
                </div>

                <div class="mb-3">
                    <label class="form-label small text-muted">Assign To</label>
                    <form method="POST" action="<?= url('/support/tickets/' . $ticket['id'] . '/assign') ?>" class="d-flex gap-2">
                        <?= csrf_field() ?>
                        <select name="assigned_to" class="form-select form-select-sm flex-grow-1">
                            <option value="unassign">Unassigned</option>
                            <option value="self">Myself</option>
                            <?php foreach ($agents ?? [] as $agent): ?>
                                <option value="<?= $agent['id'] ?>" <?= (int) ($ticket['assigned_to'] ?? 0) === (int) $agent['id'] ? 'selected' : '' ?>><?= e($agent['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-check-lg"></i></button>
                    </form>
                </div>

                <hr>

                <div class="mb-2">
                    <small class="text-muted d-block">Current Agent</small>
                    <span class="fw-semibold">
                        <?php if (!empty($ticket['assigned_to'])): ?>
                            <i class="bi bi-person-check text-success me-1"></i><?= e($ticket['assignee_username'] ?? 'Agent') ?>
                        <?php else: ?>
                            <i class="bi bi-person-dash text-muted me-1"></i><span class="text-muted">Unassigned</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">User</small>
                    <a href="<?= url('/support/users/' . $ticket['user_id']) ?>" class="text-decoration-none fw-semibold"><?= e($ticket['username'] ?? 'User') ?></a>
                </div>
                <div>
                    <small class="text-muted d-block">Ticket ID</small>
                    <span class="text-muted">#<?= $ticket['id'] ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
