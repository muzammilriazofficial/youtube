<?php

declare(strict_types=1);

return [

    'app' => [
        'name'              => $_ENV['APP_NAME'] ?? 'YouTube Clone',
        'url'               => $_ENV['APP_URL'] ?? 'http://localhost/youtube',
        'env'               => $_ENV['APP_ENV'] ?? 'production',
        'debug'             => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'timezone'          => $_ENV['APP_TIMEZONE'] ?? 'UTC',
        'key'               => $_ENV['APP_KEY'] ?? 'change-this-secret-key',
        'charset'           => 'UTF-8',
        'locale'            => 'en',
        'fallback_locale'   => 'en',
    ],

    'database' => [
        'driver'    => $_ENV['DB_DRIVER'] ?? 'mysql',
        'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port'      => $_ENV['DB_PORT'] ?? '3306',
        'database'  => $_ENV['DB_DATABASE'] ?? 'youtube_clone',
        'username'  => $_ENV['DB_USERNAME'] ?? 'root',
        'password'  => $_ENV['DB_PASSWORD'] ?? '',
        'charset'   => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
        'collation' => $_ENV['DB_COLLATION'] ?? 'utf8mb4_unicode_ci',
        'prefix'    => $_ENV['DB_PREFIX'] ?? '',
        'options'   => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ],
    ],

    'mail' => [
        'driver'       => $_ENV['MAIL_DRIVER'] ?? 'smtp',
        'host'         => $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io',
        'port'         => $_ENV['MAIL_PORT'] ?? '587',
        'encryption'   => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
        'username'     => $_ENV['MAIL_USERNAME'] ?? '',
        'password'     => $_ENV['MAIL_PASSWORD'] ?? '',
        'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@youtube-clone.local',
        'from_name'    => $_ENV['MAIL_FROM_NAME'] ?? 'YouTube Clone',
    ],

    'storage' => [
        'driver'             => $_ENV['STORAGE_DRIVER'] ?? 'local',
        'local_path'         => $_ENV['STORAGE_LOCAL_PATH'] ?? __DIR__ . '/../storage',
        's3_key'             => $_ENV['S3_KEY'] ?? '',
        's3_secret'          => $_ENV['S3_SECRET'] ?? '',
        's3_region'          => $_ENV['S3_REGION'] ?? 'us-east-1',
        's3_bucket'          => $_ENV['S3_BUCKET'] ?? '',
        's3_endpoint'        => $_ENV['S3_ENDPOINT'] ?? '',
    ],

    'upload' => [
        'max_size'       => (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? 104857600),
        'allowed_types'  => explode(',', $_ENV['UPLOAD_ALLOWED_TYPES'] ?? 'video/jpeg,video/mp4,image/jpeg,image/png,image/webp,image/gif'),
        'video_max_size' => (int) ($_ENV['VIDEO_MAX_SIZE'] ?? 1048576000),
        'image_max_size' => (int) ($_ENV['IMAGE_MAX_SIZE'] ?? 5242880),
        'thumbnail_width'  => 1280,
        'thumbnail_height' => 720,
    ],

    'session' => [
        'lifetime'    => (int) ($_ENV['SESSION_LIFETIME'] ?? 120),
        'name'        => $_ENV['SESSION_NAME'] ?? 'yt_session',
        'secure'      => filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'httponly'     => true,
        'same_site'   => 'lax',
    ],

    'hashing' => [
        'algorithm' => PASSWORD_BCRYPT,
        'options'   => [
            'cost' => (int) ($_ENV['HASH_COST'] ?? 12),
        ],
    ],

    'pagination' => [
        'per_page'       => (int) ($_ENV['PAGINATION_PER_PAGE'] ?? 20),
        'max_per_page'   => (int) ($_ENV['PAGINATION_MAX_PER_PAGE'] ?? 100),
    ],

    'ratelimit' => [
        'max_attempts' => (int) ($_ENV['RATELIMIT_MAX_ATTEMPTS'] ?? 60),
        'decay_minutes' => (int) ($_ENV['RATELIMIT_DECAY_MINUTES'] ?? 1),
    ],
];
