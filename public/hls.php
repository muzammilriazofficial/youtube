<?php

/**
 * HLS streaming endpoint for serving m3u8 playlists and .ts segments.
 *
 * URL formats:
 *   /hls.php?v={videoId}/master.m3u8
 *   /hls.php?v={videoId}/{quality}/stream.m3u8
 *   /hls.php?v={videoId}/{quality}/{segment}.ts
 *
 * Or via path info:
 *   /hls/{videoId}/master.m3u8
 *   /hls/{videoId}/{quality}/stream.m3u8
 *   /hls/{videoId}/{quality}/{segment}.ts
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

use App\Services\HlsStream;
use App\Models\Video;

$hlsService = new HlsStream();

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$pathInfo   = parse_url($requestUri, PHP_URL_PATH);
$query      = parse_url($requestUri, PHP_URL_QUERY) ?? '';
parse_str($query, $queryParams);

$uri = $pathInfo;

if (str_starts_with($uri, '/hls.php')) {
    $vParam = $queryParams['v'] ?? '';
    if (!empty($vParam)) {
        $uri = '/hls/' . $vParam;
    }
}

$parsed = $hlsService->parseHlsRequest($uri);

if ($parsed === null) {
    http_response_code(400);
    exit('Invalid HLS request');
}

$videoId = $parsed['video_id'];
$token   = $queryParams['token'] ?? '';

if (!empty($token)) {
    if (!$hlsService->validateToken($videoId, $token)) {
        http_response_code(403);
        exit('Invalid or expired token');
    }
} else {
    $videoModel = new Video();
    $video      = $videoModel->find($videoId);

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

        $db = \App\Core\Database::getInstance();
        $channel = $db->table('channels')
            ->where('id', $video['channel_id'])
            ->first();

        if ($channel === null || (int) $channel['user_id'] !== (int) $userId) {
            http_response_code(403);
            exit('Access denied');
        }
    }
}

switch ($parsed['type']) {
    case 'master':
        $content = $hlsService->getMasterPlaylist($videoId);
        if ($content === null) {
            http_response_code(404);
            exit('Master playlist not found');
        }
        header('Content-Type: application/vnd.apple.mpegurl');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Access-Control-Allow-Origin: *');
        echo $content;
        break;

    case 'quality_playlist':
        $content = $hlsService->getQualityPlaylist($videoId, $parsed['quality']);
        if ($content === null) {
            http_response_code(404);
            exit('Quality playlist not found');
        }
        header('Content-Type: application/vnd.apple.mpegurl');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Access-Control-Allow-Origin: *');
        echo $content;
        break;

    case 'segment':
        $segment = $hlsService->getSegment($videoId, $parsed['quality'], $parsed['segment']);
        if ($segment === null) {
            http_response_code(404);
            exit('Segment not found');
        }
        header('Content-Type: ' . $segment['mime_type']);
        header('Content-Length: ' . $segment['size']);
        header('Cache-Control: public, max-age=86400');
        header('Access-Control-Allow-Origin: *');
        header('Accept-Ranges: bytes');
        readfile($segment['path']);
        break;

    default:
        http_response_code(400);
        exit('Unknown HLS request type');
}
