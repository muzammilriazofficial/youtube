<?php $__layout = 'layouts.dashboard'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Notifications <?php if (($unreadCount ?? 0) > 0): ?><span class="badge bg-danger"><?= $unreadCount ?></span><?php endif; ?></h4>
    <div>
        <form method="POST" action="<?= url('/viewer/notifications/read-all') ?>" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-secondary btn-sm">Mark All Read</button>
        </form>
        <form method="POST" action="<?= url('/viewer/notifications/delete') ?>" class="d-inline" onsubmit="return confirm('Delete all notifications?')">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm">Clear All</button>
        </form>
    </div>
</div>

<div class="list-group">
    <?php foreach (($notifications['data'] ?? []) as $notif): ?>
        <div class="list-group-item list-group-item-action <?= empty($notif['read_at'] ?? null) ? 'border-start border-primary border-3' : '' ?>">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-1"><?= e($notif['title'] ?? 'Notification') ?></h6>
                    <p class="mb-1 small text-muted"><?= e($notif['message'] ?? '') ?></p>
                    <small class="text-muted"><?= time_ago($notif['created_at'] ?? '') ?></small>
                </div>
                <?php if (empty($notif['read_at'] ?? null)): ?>
                    <form method="POST" action="<?= url('/viewer/notifications/read') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="notification_id" value="<?= $notif['id'] ?>">
                        <button type="submit" class="btn btn-link btn-sm p-0"><i class="bi bi-check-lg"></i></button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($notifications['data'] ?? [])): ?>
        <div class="text-center py-5 text-muted"><i class="bi bi-bell fs-1 d-block mb-2"></i>No notifications.</div>
    <?php endif; ?>
</div>

<?php if (($notifications['last_page'] ?? 1) > 1): ?>
<nav class="mt-4"><ul class="pagination justify-content-center">
    <?php for ($p = 1; $p <= ($notifications['last_page'] ?? 1); $p++): ?>
        <li class="page-item <?= $p == ($notifications['current_page'] ?? 1) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a></li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
