<?php $__layout = 'layouts.app'; ?>

<?php if (!empty($managing ?? false)): ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-broadcast text-danger me-2"></i>Managing Live Stream</h4>
    <form method="POST" action="<?= url('/creator/live/' . $stream['id'] . '/end') ?>" onsubmit="return confirm('End this live stream?')">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-stop-fill me-1"></i>End Stream</button>
    </form>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Stream Preview</h6>
                <span class="badge bg-danger"><i class="bi bi-broadcast me-1"></i>LIVE</span>
            </div>
            <div class="card-body p-0">
                <div class="bg-dark d-flex align-items-center justify-content-center" style="height:400px;">
                    <div class="text-center">
                        <i class="bi bi-broadcast fs-1 text-danger mb-2"></i>
                        <p class="text-muted mb-0">Stream is active</p>
                        <small class="text-muted">Use OBS or streaming software to broadcast</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Stream Info</h6></div>
            <div class="card-body">
                <h5><?= e($stream['title']) ?></h5>
                <p class="text-muted"><?= e($stream['description'] ?? '') ?></p>
                <div class="row">
                    <div class="col-md-6"><small class="text-muted">Viewers:</small> <strong><?= format_number((int) $stream['view_count']) ?></strong></div>
                    <div class="col-md-6"><small class="text-muted">Started:</small> <strong><?= date('H:i:s', strtotime($stream['published_at'] ?? $stream['created_at'])) ?></strong></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Stream Settings</h6></div>
            <div class="card-body">
                <label class="form-label small text-muted">Stream URL</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control form-control-sm" value="rtmp://localhost/live" readonly>
                    <button class="btn btn-outline-secondary btn-sm" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)"><i class="bi bi-clipboard"></i></button>
                </div>
                <label class="form-label small text-muted">Stream Key</label>
                <div class="input-group mb-3">
                    <input type="password" class="form-control form-control-sm" id="streamKey" value="<?= e($stream['stream_key'] ?? '') ?>" readonly>
                    <button class="btn btn-outline-secondary btn-sm" onclick="toggleKey()"><i class="bi bi-eye" id="keyIcon"></i></button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('streamKey').value)"><i class="bi bi-clipboard"></i></button>
                </div>
                <div class="form-text">Enter these into your streaming software (OBS, etc.)</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Live Chat</h6></div>
            <div class="card-body p-0">
                <div id="chatBox" style="height:300px;overflow-y:auto;padding:12px;">
                    <?php if (!empty($chatMessages ?? [])): ?>
                        <?php foreach ($chatMessages as $msg): ?>
                        <div class="mb-2">
                            <strong class="small"><?= e($msg['username'] ?? 'User') ?>:</strong>
                            <span class="small"><?= e($msg['message']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">No messages yet</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleKey() {
    const input = document.getElementById('streamKey');
    const icon = document.getElementById('keyIcon');
    if (input.type === 'password') { input.type = 'text'; icon.className = 'bi bi-eye-slash'; }
    else { input.type = 'password'; icon.className = 'bi bi-eye'; }
}
</script>

<?php else: ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Live Streams</h4>
    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#startLiveModal"><i class="bi bi-broadcast me-1"></i>Start Live Stream</button>
</div>

<?php if (!empty($streams ?? [])): ?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Stream</th>
                        <th>Status</th>
                        <th class="text-end">Viewers</th>
                        <th>Started</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($streams as $stream): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if (!empty($stream['thumbnail'])): ?>
                                    <img src="<?= e($stream['thumbnail']) ?>" alt="" class="rounded me-2" style="width:80px;height:45px;object-fit:cover;">
                                <?php else: ?>
                                    <div class="bg-secondary rounded me-2 d-flex align-items-center justify-content-center" style="width:80px;height:45px;"><i class="bi bi-broadcast"></i></div>
                                <?php endif; ?>
                                <span class="fw-medium"><?= e($stream['title']) ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-<?= ($stream['status'] === 'live') ? 'danger' : (($stream['status'] === 'published') ? 'success' : 'secondary') ?>">
                                <?= ($stream['status'] === 'live') ? '<i class="bi bi-broadcast me-1"></i>LIVE' : ucfirst($stream['status']) ?>
                            </span>
                        </td>
                        <td class="text-end"><?= format_number((int) $stream['view_count']) ?></td>
                        <td><small class="text-muted"><?= date('M d, H:i', strtotime($stream['published_at'] ?? $stream['created_at'])) ?></small></td>
                        <td class="text-end">
                            <?php if ($stream['status'] === 'live'): ?>
                                <a href="<?= url('/creator/live/' . $stream['id'] . '/manage') ?>" class="btn btn-sm btn-danger">Manage</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-broadcast display-4 text-muted mb-3"></i>
        <h5>No live streams yet</h5>
        <p class="text-muted">Start your first live stream to engage with your audience in real-time.</p>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#startLiveModal"><i class="bi bi-broadcast me-1"></i>Start Live Stream</button>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="startLiveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/creator/live/start') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-broadcast text-danger me-2"></i>Start Live Stream</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="liveTitle" class="form-label">Stream Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="liveTitle" name="title" required maxlength="100" placeholder="What's your stream about?">
                    </div>
                    <div class="mb-3">
                        <label for="liveDesc" class="form-label">Description</label>
                        <textarea class="form-control" id="liveDesc" name="description" rows="3" maxlength="5000"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="liveVis" class="form-label">Visibility</label>
                        <select class="form-select" id="liveVis" name="visibility">
                            <option value="public">Public</option>
                            <option value="unlisted">Unlisted</option>
                            <option value="private">Private</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-broadcast me-1"></i>Start Stream</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php endif; ?>
