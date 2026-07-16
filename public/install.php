<?php

declare(strict_types=1);

/**
 * YouTube Clone - Web Installer
 *
 * This script creates the database, runs migrations, and seeds initial data.
 * It can only be run once. Delete the lock file at storage/.installed to re-run.
 */

$lockFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . '.installed';
$rootPath = dirname(__DIR__);

if (file_exists($lockFile)) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Already Installed</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background: #0f0f0f; color: #ccc; }
            .box { background: #1a1a2e; padding: 40px; border-radius: 12px; text-align: center; max-width: 500px; }
            h1 { color: #ff4444; margin-bottom: 10px; }
            p { color: #888; line-height: 1.6; }
            a { color: #4488ff; text-decoration: none; }
            a:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <div class="box">
            <h1>Already Installed</h1>
            <p>This application has already been installed. To reinstall, delete the file <code>storage/.installed</code> and refresh this page.</p>
            <p><a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/">&#8592; Go to Homepage</a></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$success = [];
$errors = [];
$step = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = (int) ($_POST['step'] ?? 1);

    if ($step === 2) {
        $dbHost = trim($_POST['db_host'] ?? '127.0.0.1');
        $dbPort = trim($_POST['db_port'] ?? '3306');
        $dbUser = trim($_POST['db_user'] ?? 'root');
        $dbPass = $_POST['db_pass'] ?? '';
        $dbName = trim($_POST['db_name'] ?? 'youtube_clone');

        $adminEmail    = trim($_POST['admin_email'] ?? 'admin@youtube.com');
        $adminPassword = $_POST['admin_password'] ?? 'Admin@123';
        $adminFirst    = trim($_POST['admin_first_name'] ?? 'Super');
        $adminLast     = trim($_POST['admin_last_name'] ?? 'Admin');
        $adminUsername = trim($_POST['admin_username'] ?? 'admin');

        try {
            $dsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $success[] = "Connected to MySQL server.";

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$dbName}`");
            $success[] = "Database '{$dbName}' is ready.";

            $migration = require $rootPath . '/database/migrations/001_create_all_tables.php';
            $migrationSql = preg_replace('/--.*$/m', '', $migration['up']);
            $migrationSql = preg_replace('/CREATE TABLE\s+/i', 'CREATE TABLE IF NOT EXISTS ', $migrationSql);
            $statements = array_filter(array_map('trim', explode(';', $migrationSql)));
            foreach ($statements as $sql) {
                $sql = trim($sql);
                if ($sql !== '') {
                    $pdo->exec($sql);
                }
            }
            $success[] = "Migration completed. All tables created.";

            $seeder = require $rootPath . '/database/seeders/001_seed_roles_permissions.php';
            $seederSql = preg_replace('/--.*$/m', '', $seeder['up']);
            $seederSql = preg_replace('/INSERT INTO\s+/i', 'INSERT IGNORE INTO ', $seederSql);
            $seederStatements = array_filter(array_map('trim', explode(';', $seederSql)));
            foreach ($seederStatements as $sql) {
                $sql = trim($sql);
                if ($sql !== '') {
                    $pdo->exec($sql);
                }
            }
            $success[] = "Seeder completed. Roles, permissions, and default admin user seeded.";

            $columnFixes = [
                "ALTER TABLE `videos` CHANGE COLUMN `views_count` `view_count` BIGINT UNSIGNED NOT NULL DEFAULT 0",
                "ALTER TABLE `videos` CHANGE COLUMN `likes_count` `like_count` BIGINT UNSIGNED NOT NULL DEFAULT 0",
                "ALTER TABLE `videos` CHANGE COLUMN `dislikes_count` `dislike_count` BIGINT UNSIGNED NOT NULL DEFAULT 0",
                "ALTER TABLE `videos` CHANGE COLUMN `shares_count` `share_count` BIGINT UNSIGNED NOT NULL DEFAULT 0",
                "ALTER TABLE `comments` CHANGE COLUMN `likes_count` `like_count` BIGINT UNSIGNED NOT NULL DEFAULT 0",
                "ALTER TABLE `playlists` CHANGE COLUMN `views_count` `view_count` BIGINT UNSIGNED NOT NULL DEFAULT 0",
                "ALTER TABLE `blog_posts` CHANGE COLUMN `views_count` `view_count` BIGINT UNSIGNED NOT NULL DEFAULT 0",
            ];
            foreach ($columnFixes as $fix) {
                try { $pdo->exec($fix); } catch (\Throwable $e) {}
            }
            $success[] = "Column names normalized.";

            $adminHash = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare("UPDATE `users` SET `email` = ?, `password` = ?, `username` = ?, `first_name` = ?, `last_name` = ? WHERE `email` = 'admin@youtube.com'");
            $stmt->execute([$adminEmail, $adminHash, $adminUsername, $adminFirst, $adminLast]);
            $success[] = "Admin user updated: {$adminEmail}";

            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $success[] = "Total tables: " . count($tables);

            $envContent = file_get_contents($rootPath . '/.env');
            $envContent = str_replace('DB_HOST=127.0.0.1', "DB_HOST={$dbHost}", $envContent);
            $envContent = str_replace('DB_PORT=3306', "DB_PORT={$dbPort}", $envContent);
            $envContent = str_replace('DB_DATABASE=youtube_clone', "DB_DATABASE={$dbName}", $envContent);
            $envContent = str_replace('DB_USERNAME=root', "DB_USERNAME={$dbUser}", $envContent);
            $envContent = str_replace("DB_PASSWORD=\n", "DB_PASSWORD={$dbPass}\n", $envContent);
            file_put_contents($rootPath . '/.env', $envContent);
            $success[] = "Environment configuration updated.";

            file_put_contents($lockFile, json_encode([
                'installed_at' => date('Y-m-d H:i:s'),
                'database'     => $dbName,
            ]));
            $success[] = "Installation complete!";

            $step = 3;

        } catch (PDOException $e) {
            $errors[] = "Database Error: " . $e->getMessage();
            $step = 2;
        } catch (\Throwable $e) {
            $errors[] = "Error: " . $e->getMessage();
            $step = 2;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouTube Clone - Installer</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f0f0f; color: #e0e0e0; min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .installer { background: #1a1a2e; border-radius: 16px; padding: 40px; max-width: 600px; width: 100%; box-shadow: 0 8px 32px rgba(0,0,0,0.4); }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo svg { width: 48px; height: 48px; fill: #FF0000; }
        .logo h1 { font-size: 24px; margin-top: 10px; color: #fff; }
        .logo p { color: #888; font-size: 14px; margin-top: 5px; }
        .steps { display: flex; justify-content: center; gap: 8px; margin-bottom: 30px; }
        .step-dot { width: 12px; height: 12px; border-radius: 50%; background: #333; }
        .step-dot.active { background: #FF0000; }
        .step-dot.done { background: #44bb44; }
        h2 { font-size: 20px; margin-bottom: 20px; color: #fff; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 13px; color: #aaa; margin-bottom: 6px; font-weight: 500; }
        input[type="text"], input[type="password"], input[type="email"], input[type="number"] { width: 100%; padding: 10px 14px; background: #16213e; border: 1px solid #333; border-radius: 8px; color: #fff; font-size: 14px; outline: none; transition: border-color 0.2s; }
        input:focus { border-color: #FF0000; }
        .row { display: flex; gap: 16px; }
        .row .form-group { flex: 1; }
        .section-label { font-size: 12px; text-transform: uppercase; color: #FF0000; letter-spacing: 1px; margin: 24px 0 12px; padding-bottom: 8px; border-bottom: 1px solid #333; }
        .btn { display: inline-block; padding: 12px 28px; background: #FF0000; color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 500; cursor: pointer; transition: background 0.2s; width: 100%; }
        .btn:hover { background: #cc0000; }
        .btn-success { background: #44bb44; }
        .btn-success:hover { background: #339933; }
        .success-list { list-style: none; padding: 0; }
        .success-list li { padding: 8px 0; border-bottom: 1px solid #333; font-size: 14px; }
        .success-list li::before { content: "✓ "; color: #44bb44; font-weight: bold; }
        .error-list { list-style: none; padding: 0; margin-top: 16px; }
        .error-list li { padding: 8px 12px; background: #3d1111; border: 1px solid #662222; border-radius: 8px; margin-bottom: 8px; font-size: 14px; color: #ff6666; }
        .info-box { background: #16213e; border-left: 4px solid #4488ff; padding: 16px; border-radius: 0 8px 8px 0; margin-bottom: 20px; font-size: 14px; line-height: 1.6; color: #aaa; }
        .credentials { background: #16213e; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .credentials .field { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #333; }
        .credentials .field:last-child { border-bottom: none; }
        .credentials .label { color: #888; }
        .credentials .value { color: #fff; font-family: monospace; }
        .warning { background: #3d2a11; border: 1px solid #664422; border-radius: 8px; padding: 12px 16px; margin-top: 16px; font-size: 13px; color: #ffaa44; }
    </style>
</head>
<body>
    <div class="installer">
        <div class="logo">
            <svg viewBox="0 0 90 20" xmlns="http://www.w3.org/2000/svg"><g fill="currentColor"><path d="M27.9727 3.12324C27.6435 1.89323 26.6768 0.926623 25.4468 0.597366C23.2197 2.24288e-07 14.285 0 14.285 0C14.285 0 5.35042 2.24288e-07 3.12323 0.597366C1.89323 0.926623 0.926623 1.89323 0.597366 3.12324C2.24288e-07 5.35042 0 10 0 10C0 10 2.24288e-07 14.6496 0.597366 16.8768C0.926623 18.1068 1.89323 19.0734 3.12323 19.4026C5.35042 20 14.285 20 14.285 20C14.285 20 23.2197 20 25.4468 19.4026C26.6768 19.0734 27.6435 18.1068 27.9727 16.8768C28.5701 14.6496 28.5701 10 28.5701 10C28.5701 10 28.5677 5.35042 27.9727 3.12324Z" fill="#FF0000"/><path d="M11.4253 14.2854L18.8477 10.0004L11.4253 5.71533V14.2854Z" fill="white"/></g></svg>
            <h1>YouTube Clone Installer</h1>
            <p>Set up your YouTube clone in a few steps</p>
        </div>

        <div class="steps">
            <div class="step-dot <?= $step >= 1 ? ($step > 1 ? 'done' : 'active') : '' ?>"></div>
            <div class="step-dot <?= $step >= 2 ? ($step > 2 ? 'done' : 'active') : '' ?>"></div>
            <div class="step-dot <?= $step >= 3 ? 'done' : '' ?>"></div>
        </div>

        <?php if ($step === 1): ?>
            <h2>Welcome</h2>
            <div class="info-box">
                This installer will set up the database and create an admin account for your YouTube clone.
                Make sure you have MySQL running and the correct credentials ready.
            </div>

            <div style="background:#16213e; padding:16px; border-radius:8px; margin-bottom:20px;">
                <h3 style="font-size:14px; color:#fff; margin-bottom:12px;">Requirements Check</h3>
                <ul style="list-style:none; padding:0;">
                    <li style="padding:4px 0; font-size:14px;">
                        PHP <?= phpversion() ?> &mdash;
                        <?php if (version_compare(PHP_VERSION, '8.0.0', '>=')): ?>
                            <span style="color:#44bb44;">✓ OK</span>
                        <?php else: ?>
                            <span style="color:#ff4444;">✗ Requires PHP 8.0+</span>
                        <?php endif; ?>
                    </li>
                    <li style="padding:4px 0; font-size:14px;">
                        PDO MySQL &mdash;
                        <?php if (extension_loaded('pdo_mysql')): ?>
                            <span style="color:#44bb44;">✓ OK</span>
                        <?php else: ?>
                            <span style="color:#ff4444;">✗ Extension not loaded</span>
                        <?php endif; ?>
                    </li>
                    <li style="padding:4px 0; font-size:14px;">
                        storage/writable &mdash;
                        <?php if (is_writable(dirname(__DIR__) . '/storage')): ?>
                            <span style="color:#44bb44;">✓ OK</span>
                        <?php else: ?>
                            <span style="color:#ff4444;">✗ Not writable</span>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>

            <form method="POST">
                <input type="hidden" name="step" value="2">
                <button type="submit" class="btn">Start Installation</button>
            </form>

        <?php elseif ($step === 2): ?>
            <h2>Configuration</h2>

            <?php if (!empty($errors)): ?>
                <ul class="error-list">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="step" value="2">

                <div class="section-label">Database Configuration</div>

                <div class="row">
                    <div class="form-group">
                        <label>Database Host</label>
                        <input type="text" name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? '127.0.0.1', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Database Port</label>
                        <input type="number" name="db_port" value="<?= htmlspecialchars($_POST['db_port'] ?? '3306', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label>Database Username</label>
                        <input type="text" name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? 'root', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Database Password</label>
                        <input type="password" name="db_pass" value="">
                    </div>
                </div>

                <div class="form-group">
                    <label>Database Name</label>
                    <input type="text" name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? 'youtube_clone', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="section-label">Admin Account</div>

                <div class="row">
                    <div class="form-group">
                        <label>Admin Username</label>
                        <input type="text" name="admin_username" value="<?= htmlspecialchars($_POST['admin_username'] ?? 'admin', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Admin Email</label>
                        <input type="email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? 'admin@youtube.com', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="admin_first_name" value="<?= htmlspecialchars($_POST['admin_first_name'] ?? 'Super', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="admin_last_name" value="<?= htmlspecialchars($_POST['admin_last_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Admin Password</label>
                    <input type="password" name="admin_password" value="Admin@123" required>
                </div>

                <button type="submit" class="btn">Install</button>
            </form>

        <?php elseif ($step === 3): ?>
            <h2>Installation Complete</h2>

            <ul class="success-list">
                <?php foreach ($success as $msg): ?>
                    <li><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>

            <div class="credentials">
                <div class="field">
                    <span class="label">Email</span>
                    <span class="value"><?= htmlspecialchars($adminEmail, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="field">
                    <span class="label">Password</span>
                    <span class="value"><?= htmlspecialchars($adminPassword, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>

            <div class="warning">
                For security, delete this installer file: <code>public/install.php</code>
            </div>

            <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/" class="btn btn-success" style="text-align:center; margin-top:20px; text-decoration:none;">
                Go to Homepage
            </a>
        <?php endif; ?>
    </div>
</body>
</html>
