<?php

declare(strict_types=1);

use App\Services\FileUpload;

/**
 * Helper functions for file uploads used by controllers.
 */

if (!function_exists('upload_video')) {
    /**
     * Upload a video file for a user.
     */
    function upload_video(array $file, int $userId): array
    {
        $uploader = new FileUpload();
        return $uploader->handleVideoUpload($file, $userId);
    }
}

if (!function_exists('upload_short')) {
    /**
     * Upload a short video for a user.
     */
    function upload_short(array $file, int $userId): array
    {
        $uploader = new FileUpload();
        return $uploader->handleShortUpload($file, $userId);
    }
}

if (!function_exists('upload_thumbnail')) {
    /**
     * Upload a thumbnail image.
     */
    function upload_thumbnail(array $file): array
    {
        $uploader = new FileUpload();
        return $uploader->handleThumbnailUpload($file);
    }
}

if (!function_exists('upload_avatar')) {
    /**
     * Upload a user avatar.
     */
    function upload_avatar(array $file, int $userId): array
    {
        $uploader = new FileUpload();
        return $uploader->handleAvatarUpload($file, $userId);
    }
}

if (!function_exists('upload_banner')) {
    /**
     * Upload a channel banner.
     */
    function upload_banner(array $file, int $userId): array
    {
        $uploader = new FileUpload();
        return $uploader->handleBannerUpload($file, $userId);
    }
}

if (!function_exists('upload_ad')) {
    /**
     * Upload an advertisement media file.
     */
    function upload_ad(array $file): array
    {
        $uploader = new FileUpload();
        return $uploader->handleAdUpload($file);
    }
}

if (!function_exists('validate_upload')) {
    /**
     * Validate an uploaded file against allowed MIME types and max size.
     */
    function validate_upload(array $file, array $allowedTypes, int $maxSize): array
    {
        $uploader = new FileUpload();
        return $uploader->validateFile($file, $allowedTypes, $maxSize);
    }
}

if (!function_exists('delete_uploaded_file')) {
    /**
     * Delete an uploaded file.
     */
    function delete_uploaded_file(string $path): bool
    {
        $uploader = new FileUpload();
        return $uploader->deleteFile($path);
    }
}

if (!function_exists('get_upload_path')) {
    /**
     * Get the storage path for a specific upload type.
     */
    function get_upload_path(string $type, int $userId = 0): string
    {
        $uploader = new FileUpload();
        return $uploader->getFilePath($type, $userId);
    }
}

if (!function_exists('is_valid_image')) {
    /**
     * Quick check if a file is a valid image using finfo.
     */
    function is_valid_image(array $file): bool
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        return in_array($mimeType, $allowed, true);
    }
}

if (!function_exists('is_valid_video')) {
    /**
     * Quick check if a file is a valid video using finfo.
     */
    function is_valid_video(array $file): bool
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        $allowed = [
            'video/mp4', 'video/quicktime', 'video/x-msvideo',
            'video/x-matroska', 'video/webm', 'video/3gpp',
        ];
        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        return in_array($mimeType, $allowed, true);
    }
}
