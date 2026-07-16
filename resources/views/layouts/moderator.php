<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Moderator Panel') ?></title>
    <link href="<?= asset('vendor/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/bootstrap-icons.css') ?>" rel="stylesheet">
    <style>
        body { padding-top: 56px; }
        .sidebar { min-height: calc(100vh - 56px); position: sticky; top: 56px; overflow-y: auto; max-height: calc(100vh - 56px); width: 260px; }
        .sidebar .nav-link { color: var(--bs-body-color); padding: 8px 16px; border-radius: 6px; margin: 1px 8px; font-size: 14px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: var(--bs-secondary-bg); }
        .sidebar .nav-link i { width: 20px; text-align: center; margin-right: 8px; }
        .sidebar .nav-header { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--bs-secondary-color); padding: 12px 16px 4px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg fixed-top border-bottom">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?= url('/moderator/dashboard') ?>">
                <i class="bi bi-shield-check text-warning me-2"></i>Moderator Panel
            </a>
            <div class="d-flex align-items-center ms-auto">
                <a href="<?= url('/') ?>" class="btn btn-link text-decoration-none me-2" title="View Site"><i class="bi bi-globe fs-5"></i></a>
                <button class="btn btn-link text-decoration-none me-2" id="themeToggle"><i class="bi bi-moon-stars-fill fs-5"></i></button>
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-5"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= url('/viewer/profile') ?>"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="<?= url('/logout') ?>"><?= csrf_field() ?>
                                <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="d-flex">
        <aside class="sidebar border-end p-0 d-none d-lg-block">
            <nav class="nav flex-column py-2">
                <div class="nav-header">Overview</div>
                <a class="nav-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>" href="<?= url('/moderator/dashboard') ?>"><i class="bi bi-speedometer2"></i>Dashboard</a>
                <div class="nav-header">Content Moderation</div>
                <a class="nav-link <?= ($activeMenu ?? '') === 'videos' ? 'active' : '' ?>" href="<?= url('/moderator/videos') ?>"><i class="bi bi-play-btn"></i>Videos</a>
                <a class="nav-link <?= ($activeMenu ?? '') === 'video-reports' ? 'active' : '' ?>" href="<?= url('/moderator/videos/reported') ?>"><i class="bi bi-flag"></i>Reported Videos</a>
                <a class="nav-link <?= ($activeMenu ?? '') === 'comments' ? 'active' : '' ?>" href="<?= url('/moderator/comments') ?>"><i class="bi bi-chat-dots"></i>Comments</a>
                <a class="nav-link <?= ($activeMenu ?? '') === 'comment-reports' ? 'active' : '' ?>" href="<?= url('/moderator/comments/reported') ?>"><i class="bi bi-flag"></i>Reported Comments</a>
                <div class="nav-header">Channel Management</div>
                <a class="nav-link <?= ($activeMenu ?? '') === 'channels' ? 'active' : '' ?>" href="<?= url('/moderator/channels') ?>"><i class="bi bi-broadcast"></i>Channels</a>
                <a class="nav-link <?= ($activeMenu ?? '') === 'channel-reports' ? 'active' : '' ?>" href="<?= url('/moderator/channels/reported') ?>"><i class="bi bi-flag"></i>Reported Channels</a>
                <div class="nav-header">Enforcement</div>
                <a class="nav-link <?= ($activeMenu ?? '') === 'reports' ? 'active' : '' ?>" href="<?= url('/moderator/reports') ?>"><i class="bi bi-exclamation-triangle"></i>All Reports</a>
                <a class="nav-link <?= ($activeMenu ?? '') === 'violations' ? 'active' : '' ?>" href="<?= url('/moderator/violations') ?>"><i class="bi bi-journal-x"></i>Violations</a>
            </nav>
        </aside>

        <main class="flex-grow-1 p-4">
            <?php if (($success ?? null) !== null): ?>
                <div class="alert alert-success alert-dismissible fade show"><?= e($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if (($error ?? null) !== null): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?= e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?= $__content ?>
        </main>
    </div>

    <script src="<?= asset('vendor/bootstrap.bundle.min.js') ?>"></script>
    <script>
        document.getElementById('themeToggle')?.addEventListener('click', () => {
            const html = document.documentElement;
            const next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', next);
            localStorage.setItem('theme', next);
        });
        const saved = localStorage.getItem('theme');
        if (saved) document.documentElement.setAttribute('data-bs-theme', saved);
    </script>
</body>
</html>
