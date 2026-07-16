<?php
declare(strict_types=1);
$rootPath = dirname(__DIR__);
require $rootPath . '/.env';

$host = $_ENV['DB_HOST'] ?? '';
$db   = $_ENV['DB_DATABASE'] ?? '';
$user = $_ENV['DB_USERNAME'] ?? '';
$pass = $_ENV['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$db};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $fixes = [
        "ALTER TABLE `videos` CHANGE COLUMN `views_count` `view_count` BIGINT UNSIGNED NOT NULL DEFAULT 0",
        "ALTER TABLE `videos` CHANGE COLUMN `likes_count` `like_count` BIGINT UNSIGNED NOT NULL DEFAULT 0",
        "ALTER TABLE `videos` CHANGE COLUMN `dislikes_count` `dislike_count` BIGINT UNSIGNED NOT NULL DEFAULT 0",
        "ALTER TABLE `videos` CHANGE COLUMN `shares_count` `share_count` BIGINT UNSIGNED NOT NULL DEFAULT 0",
        "ALTER TABLE `comments` CHANGE COLUMN `likes_count` `like_count` BIGINT UNSIGNED NOT NULL DEFAULT 0",
        "ALTER TABLE `playlists` CHANGE COLUMN `views_count` `view_count` BIGINT UNSIGNED NOT NULL DEFAULT 0",
        "ALTER TABLE `blog_posts` CHANGE COLUMN `views_count` `view_count` BIGINT UNSIGNED NOT NULL DEFAULT 0",
        "ALTER TABLE `users` ADD COLUMN `is_verified` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_banned`",
    ];

    $ok = 0;
    $skip = 0;
    foreach ($fixes as $sql) {
        try {
            $pdo->exec($sql);
            $ok++;
        } catch (\Throwable $e) {
            $skip++;
        }
    }

    echo "Done! {$ok} columns fixed, {$skip} skipped (already correct).\n";
    echo "<br><a href='/'>Go to Homepage</a>";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}
