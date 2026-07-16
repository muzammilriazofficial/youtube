<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Secure file upload handler with MIME validation and organized storage.
 */
class FileUpload
{
    private string $storagePath;

    private array $mimeMap = [
        'video/mp4'                    => 'mp4',
        'video/quicktime'              => 'mov',
        'video/x-msvideo'              => 'avi',
        'video/x-matroska'             => 'mkv',
        'video/webm'                   => 'webm',
        'video/x-flv'                  => 'flv',
        'video/3gpp'                   => '3gp',
        'video/mpeg'                   => 'mpeg',
        'image/jpeg'                   => 'jpg',
        'image/png'                    => 'png',
        'image/gif'                    => 'gif',
        'image/webp'                   => 'webp',
        'application/octet-stream'     => 'bin',
    ];

    public function __construct()
    {
        $this->storagePath = $_ENV['STORAGE_LOCAL_PATH'] ?? (ROOT_PATH . DIRECTORY_SEPARATOR . 'storage');
    }

    /**
     * Handle a video upload from a user.
     */
    public function handleVideoUpload(array $file, int $userId): array
    {
        $allowed = [
            'video/mp4', 'video/quicktime', 'video/x-msvideo',
            'video/x-matroska', 'video/webm', 'video/3gpp',
        ];
        $maxSize = (int) ($_ENV['VIDEO_MAX_SIZE'] ?? 1073741824);

        $validation = $this->validateFile($file, $allowed, $maxSize);
        if (!$validation['valid']) {
            return $validation;
        }

        $filename = $this->generateUniqueFilename($file['name']);
        $relPath  = 'videos' . DIRECTORY_SEPARATOR . $userId . DIRECTORY_SEPARATOR . $filename;
        $fullPath = $this->storagePath . DIRECTORY_SEPARATOR . $relPath;

        $this->ensureDirectoryExists($fullPath);

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return ['valid' => false, 'error' => 'Failed to move uploaded file'];
        }

        return [
            'valid'     => true,
            'filename'  => $filename,
            'path'      => $relPath,
            'full_path' => $fullPath,
            'size'      => $file['size'],
            'mime_type' => $validation['mime_type'],
        ];
    }

    /**
     * Handle a short video upload.
     */
    public function handleShortUpload(array $file, int $userId): array
    {
        $result = $this->handleVideoUpload($file, $userId);

        if (!$result['valid']) {
            return $result;
        }

        $result['path']      = 'shorts' . DIRECTORY_SEPARATOR . $userId . DIRECTORY_SEPARATOR . $result['filename'];
        $result['full_path'] = $this->storagePath . DIRECTORY_SEPARATOR . $result['path'];

        $oldPath = $this->storagePath . DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR . $userId . DIRECTORY_SEPARATOR . $result['filename'];

        if (file_exists($oldPath)) {
            rename($oldPath, $result['full_path']);
        }

        return $result;
    }

    /**
     * Handle a thumbnail upload (manual override).
     */
    public function handleThumbnailUpload(array $file): array
    {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = (int) ($_ENV['IMAGE_MAX_SIZE'] ?? 5242880);

        $validation = $this->validateFile($file, $allowed, $maxSize);
        if (!$validation['valid']) {
            return $validation;
        }

        $filename = $this->generateUniqueFilename($file['name']);
        $relPath  = 'thumbnails' . DIRECTORY_SEPARATOR . $filename;
        $fullPath = $this->storagePath . DIRECTORY_SEPARATOR . $relPath;

        $this->ensureDirectoryExists($fullPath);

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return ['valid' => false, 'error' => 'Failed to move uploaded thumbnail'];
        }

        return [
            'valid'     => true,
            'filename'  => $filename,
            'path'      => $relPath,
            'full_path' => $fullPath,
            'size'      => $file['size'],
            'mime_type' => $validation['mime_type'],
        ];
    }

    /**
     * Handle a user avatar upload.
     */
    public function handleAvatarUpload(array $file, int $userId): array
    {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = (int) ($_ENV['IMAGE_MAX_SIZE'] ?? 5242880);

        $validation = $this->validateFile($file, $allowed, $maxSize);
        if (!$validation['valid']) {
            return $validation;
        }

        $filename = $this->generateUniqueFilename($file['name']);
        $relPath  = 'avatars' . DIRECTORY_SEPARATOR . $userId . DIRECTORY_SEPARATOR . $filename;
        $fullPath = $this->storagePath . DIRECTORY_SEPARATOR . $relPath;

        $this->ensureDirectoryExists($fullPath);

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return ['valid' => false, 'error' => 'Failed to move uploaded avatar'];
        }

        return [
            'valid'     => true,
            'filename'  => $filename,
            'path'      => $relPath,
            'full_path' => $fullPath,
            'size'      => $file['size'],
            'mime_type' => $validation['mime_type'],
        ];
    }

    /**
     * Handle a channel banner upload.
     */
    public function handleBannerUpload(array $file, int $userId): array
    {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = (int) ($_ENV['IMAGE_MAX_SIZE'] ?? 5242880);

        $validation = $this->validateFile($file, $allowed, $maxSize);
        if (!$validation['valid']) {
            return $validation;
        }

        $filename = $this->generateUniqueFilename($file['name']);
        $relPath  = 'banners' . DIRECTORY_SEPARATOR . $userId . DIRECTORY_SEPARATOR . $filename;
        $fullPath = $this->storagePath . DIRECTORY_SEPARATOR . $relPath;

        $this->ensureDirectoryExists($fullPath);

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return ['valid' => false, 'error' => 'Failed to move uploaded banner'];
        }

        return [
            'valid'     => true,
            'filename'  => $filename,
            'path'      => $relPath,
            'full_path' => $fullPath,
            'size'      => $file['size'],
            'mime_type' => $validation['mime_type'],
        ];
    }

    /**
     * Handle an advertisement media upload.
     */
    public function handleAdUpload(array $file): array
    {
        $allowed = [
            'video/mp4', 'video/webm', 'video/quicktime',
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        ];
        $maxSize = (int) ($_ENV['VIDEO_MAX_SIZE'] ?? 1073741824);

        $validation = $this->validateFile($file, $allowed, $maxSize);
        if (!$validation['valid']) {
            return $validation;
        }

        $filename = $this->generateUniqueFilename($file['name']);
        $relPath  = 'ads' . DIRECTORY_SEPARATOR . $filename;
        $fullPath = $this->storagePath . DIRECTORY_SEPARATOR . $relPath;

        $this->ensureDirectoryExists($fullPath);

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return ['valid' => false, 'error' => 'Failed to move uploaded ad file'];
        }

        return [
            'valid'     => true,
            'filename'  => $filename,
            'path'      => $relPath,
            'full_path' => $fullPath,
            'size'      => $file['size'],
            'mime_type' => $validation['mime_type'],
        ];
    }

    /**
     * Validate an uploaded file against allowed MIME types and max size.
     */
    public function validateFile(array $file, array $allowedTypes, int $maxSize): array
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'Invalid upload'];
        }

        if ($file['size'] > $maxSize) {
            return [
                'valid' => false,
                'error' => 'File too large. Maximum size: ' . human_file_size($maxSize),
            ];
        }

        if ($file['size'] === 0) {
            return ['valid' => false, 'error' => 'File is empty'];
        }

        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mimeType === false) {
            return ['valid' => false, 'error' => 'Unable to determine file type'];
        }

        if (!in_array($mimeType, $allowedTypes, true)) {
            return [
                'valid' => false,
                'error' => "File type '{$mimeType}' is not allowed. Allowed: " . implode(', ', $allowedTypes),
            ];
        }

        $ext = $this->mimeMap[$mimeType] ?? pathinfo($file['name'], PATHINFO_EXTENSION);
        if (empty($ext)) {
            return ['valid' => false, 'error' => 'Unable to determine file extension'];
        }

        return [
            'valid'    => true,
            'mime_type' => $mimeType,
            'extension' => $ext,
        ];
    }

    /**
     * Generate a unique filename preserving the original extension.
     */
    public function generateUniqueFilename(string $originalName): string
    {
        $ext      = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $name     = bin2hex(random_bytes(16));
        return "{$name}.{$ext}";
    }

    /**
     * Get the storage file path for a specific upload type.
     */
    public function getFilePath(string $type, int $userId = 0): string
    {
        $paths = [
            'video'     => 'videos',
            'short'     => 'shorts',
            'thumbnail' => 'thumbnails',
            'avatar'    => 'avatars',
            'banner'    => 'banners',
            'ad'        => 'ads',
        ];

        $base = $paths[$type] ?? $type;

        if ($userId > 0 && in_array($type, ['video', 'short', 'avatar', 'banner'], true)) {
            return $this->storagePath . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $userId;
        }

        return $this->storagePath . DIRECTORY_SEPARATOR . $base;
    }

    /**
     * Safely delete a file.
     */
    public function deleteFile(string $path): bool
    {
        $fullPath = str_starts_with($path, $this->storagePath)
            ? $path
            : $this->storagePath . DIRECTORY_SEPARATOR . $path;

        if (!file_exists($fullPath) || !is_file($fullPath)) {
            return false;
        }

        return unlink($fullPath);
    }

    /**
     * Get the size of a file in bytes.
     */
    public function getFileSize(string $path): int
    {
        $fullPath = str_starts_with($path, $this->storagePath)
            ? $path
            : $this->storagePath . DIRECTORY_SEPARATOR . $path;

        return file_exists($fullPath) ? filesize($fullPath) : 0;
    }

    /**
     * Ensure the parent directory exists for a given file path.
     */
    private function ensureDirectoryExists(string $filePath): void
    {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
