<?php

/**
 * Video streaming endpoint with range request support (partial content for seeking).
 *
 * URL format: /video.php?id={videoId}&quality={quality}&token={token}
 * Or rewrite: /video/{videoId}
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

$envFile = ROOT_PATH . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
        }
    }
}

require_once ROOT_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Autoloader.php';
\App\Core\Autoloader::boot(ROOT_PATH);

use App\Core\Database;
use App\Models\Video;

$videoId   = (int) ($_GET['id'] ?? 0);
$quality   = $_GET['quality'] ?? 'original';
$token     = $_GET['token'] ?? '';

if ($videoId <= 0) {
    http_response_code(400);
    exit('Invalid video ID');
}

$db   = Database::getInstance();
$videoModel = new Video();
$video = $videoModel->find($videoId);

if ($video === null) {
    http_response_code(404);
    exit('Video not found');
}

if ($video['visibility'] !== 'public') {
    session_start();
    $userId = $_SESSION['user_id'] ?? null;

    if ($userId === null) {
        http_response_code(403);
        exit('Access denied');
    }

    $channel = $db->table('channels')
        ->where('id', $video['channel_id'])
        ->first();

    if ($channel === null || (int) $channel['user_id'] !== (int) $userId) {
        http_response_code(403);
        exit('Access denied');
    }
}

$storagePath = $_ENV['STORAGE_LOCAL_PATH'] ?? (ROOT_PATH . DIRECTORY_SEPARATOR . 'storage');

if ($quality !== 'original' && $quality !== 'auto') {
    $hlsDir = $storagePath . DIRECTORY_SEPARATOR . 'hls' . DIRECTORY_SEPARATOR . $videoId . DIRECTORY_SEPARATOR . $quality;

    if (is_dir($hlsDir)) {
        $segmentDir = $hlsDir;
        $filePath   = null;
        $mimeType   = 'video/mp2t';

        header('Content-Type: video/mp4');
        header('Accept-Ranges: bytes');

        $playlistFile = $hlsDir . DIRECTORY_SEPARATOR . 'stream.m3u8';
        if (file_exists($playlistFile)) {
            header('Content-Type: application/vnd.apple.mpegurl');
            header('Content-Length: ' . filesize($playlistFile));
            readfile($playlistFile);
            exit;
        }
    }
}

$filePath = $storagePath . DIRECTORY_SEPARATOR . $video['file_path'];

if (!file_exists($filePath)) {
    http_response_code(404);
    exit('Video file not found');
}

$fileSize   = filesize($filePath);
$mimeType   = mime_content_type($filePath) ?: 'video/mp4';
$rangeStart = 0;
$rangeEnd   = $fileSize - 1;

if (isset($_SERVER['HTTP_RANGE'])) {
    $range = $_SERVER['HTTP_RANGE'];
    $range = str_replace('bytes=', '', $range);
    $parts = explode('-', $range);

    $rangeStart = (int) $parts[0];
    $rangeEnd   = isset($parts[1]) ? (int) $parts[1] : $fileSize - 1;

    $rangeEnd = min($rangeEnd, $fileSize - 1);

    if ($rangeStart >= $fileSize || $rangeEnd >= $fileSize || $rangeStart > $rangeEnd) {
        http_response_code(416);
        header('Content-Range: bytes */' . $fileSize);
        exit;
    }

    $contentLength = $rangeEnd - $rangeStart + 1;

    http_response_code(206);
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . $contentLength);
    header('Content-Range: bytes ' . $rangeStart . '-' . $rangeEnd . '/' . $fileSize);
    header('Accept-Ranges: bytes');
    header('Cache-Control: public, max-age=3600');
    header('Connection: close');

    $handle = fopen($filePath, 'rb');
    fseek($handle, $rangeStart);

    $bufferSize = 8192;
    $remaining  = $contentLength;

    while ($remaining > 0 && !feof($handle)) {
        $readSize = min($bufferSize, $remaining);
        echo fread($handle, $readSize);
        $remaining -= $readSize;
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }

    fclose($handle);
    exit;
}

http_response_code(200);
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . $fileSize);
header('Accept-Ranges: bytes');
header('Cache-Control: public, max-age=3600');
header('Connection: close');

$handle = fopen($filePath, 'rb');
$bufferSize = 8192;

while (!feof($handle)) {
    echo fread($handle, $bufferSize);
    if (ob_get_level() > 0) {
        ob_flush();
    }
    flush();
}

fclose($handle);
