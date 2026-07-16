<?php

/**
 * YouTube Clone - Database Setup Script
 *
 * Creates the database, runs the migration, and seeds initial data.
 * Usage: php database/setup.php
 */

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'youtube_clone');

function log_msg(string $msg): void
{
    echo date('[H:i:s]') . " {$msg}" . PHP_EOL;
}

try {
    // Connect without selecting a database
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    log_msg("Connected to MySQL server.");

    // --------------------------------------------------------
    // 1. Create database
    // --------------------------------------------------------
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");
    log_msg("Database '" . DB_NAME . "' is ready.");

    // --------------------------------------------------------
    // 2. Run migration
    // --------------------------------------------------------
    $migration = require __DIR__ . '/migrations/001_create_all_tables.php';

    log_msg("Running migration (up)...");
    $migrationSql = preg_replace('/--.*$/m', '', $migration['up']);
    $statements = array_filter(array_map('trim', explode(';', $migrationSql)));
    foreach ($statements as $sql) {
        if ($sql !== '') {
            $pdo->exec($sql);
        }
    }
    log_msg("Migration completed. All 52 tables created.");

    // --------------------------------------------------------
    // 3. Run seeder
    // --------------------------------------------------------
    $seeder = require __DIR__ . '/seeders/001_seed_roles_permissions.php';

    log_msg("Running seeder...");
    $seederSql = preg_replace('/--.*$/m', '', $seeder['up']);
    $statements = array_filter(array_map('trim', explode(';', $seederSql)));
    foreach ($statements as $sql) {
        if ($sql !== '') {
            $pdo->exec($sql);
        }
    }
    log_msg("Seeder completed. Roles, permissions, and admin user seeded.");

    // --------------------------------------------------------
    // 4. Verify
    // --------------------------------------------------------
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    log_msg("Total tables in database: " . count($tables));

    $roleCount = $pdo->query("SELECT COUNT(*) FROM `roles`")->fetchColumn();
    $permCount = $pdo->query("SELECT COUNT(*) FROM `permissions`")->fetchColumn();
    $userCount = $pdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
    log_msg("Roles: {$roleCount} | Permissions: {$permCount} | Users: {$userCount}");

    log_msg("Setup complete!");

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
