<?php

declare(strict_types=1);

namespace App\Services;

/**
 * HLS streaming handler for serving m3u8 playlists and .ts segments.
 */
class HlsStream
{
    private string $hlsPath;
    private string $storagePath;

    public function __construct()
    {
        $this->storagePath = $_ENV['STORAGE_LOCAL_PATH'] ?? (ROOT_PATH . DIRECTORY_SEPARATOR . 'storage');
        $this->hlsPath     = $this->storagePath . DIRECTORY_SEPARATOR . 'hls';
    }

    /**
     * Serve the master.m3u8 playlist for a video.
     */
    public function getMasterPlaylist(int $videoId): ?string
    {
        $path = $this->hlsPath . DIRECTORY_SEPARATOR . $videoId . DIRECTORY_SEPARATOR . 'master.m3u8';

        if (!file_exists($path)) {
            return null;
        }

        return file_get_contents($path);
    }

    /**
     * Serve a quality-specific m3u8 playlist.
     */
    public function getQualityPlaylist(int $videoId, string $quality): ?string
    {
        $path = $this->hlsPath . DIRECTORY_SEPARATOR . $videoId . DIRECTORY_SEPARATOR . $quality . DIRECTORY_SEPARATOR . 'stream.m3u8';

        if (!file_exists($path)) {
            return null;
        }

        return file_get_contents($path);
    }

    /**
     * Serve a .ts segment file.
     */
    public function getSegment(int $videoId, string $quality, string $segment): ?array
    {
        $safeSegment = preg_replace('/[^a-zA-Z0-9_\.]/', '', $segment);
        $path = $this->hlsPath . DIRECTORY_SEPARATOR . $videoId . DIRECTORY_SEPARATOR . $quality . DIRECTORY_SEPARATOR . $safeSegment;

        if (!file_exists($path) || !is_file($path)) {
            return null;
        }

        return [
            'path'        => $path,
            'mime_type'   => 'video/mp2t',
            'size'        => filesize($path),
            'segment'     => $safeSegment,
        ];
    }

    /**
     * Check if a URI is an HLS segment or playlist request.
     */
    public function isSegmentRequest(string $uri): bool
    {
        return str_ends_with($uri, '.m3u8') || str_ends_with($uri, '.ts');
    }

    /**
     * Parse an HLS request URI into components.
     *
     * URI formats:
     *   hls/{videoId}/master.m3u8
     *   hls/{videoId}/{quality}/stream.m3u8
     *   hls/{videoId}/{quality}/{segment}.ts
     */
    public function parseHlsRequest(string $uri): ?array
    {
        $uri = '/' . ltrim($uri, '/');
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');

        $segments = explode('/', $uri);
        $segments = array_values(array_filter($segments));

        $hlsIndex = array_search('hls', $segments);
        if ($hlsIndex === false) {
            return null;
        }

        $parts = array_slice($segments, $hlsIndex + 1);

        if (count($parts) < 2) {
            return null;
        }

        $videoId = (int) $parts[0];
        $file    = $parts[1];

        if ($file === 'master.m3u8') {
            return [
                'type'     => 'master',
                'video_id' => $videoId,
            ];
        }

        if (count($parts) >= 3) {
            $quality = $parts[1];
            $file    = $parts[2];

            if (str_ends_with($file, '.m3u8')) {
                return [
                    'type'     => 'quality_playlist',
                    'video_id' => $videoId,
                    'quality'  => $quality,
                ];
            }

            if (str_ends_with($file, '.ts')) {
                return [
                    'type'     => 'segment',
                    'video_id' => $videoId,
                    'quality'  => $quality,
                    'segment'  => $file,
                ];
            }
        }

        return null;
    }

    /**
     * Generate a token-based HLS URL for authenticated access.
     */
    public function generateTokenUrl(int $videoId, string $type = 'master', string $quality = ''): string
    {
        $token = $this->generateToken($videoId);

        if ($type === 'master') {
            return url("hls/{$videoId}/master.m3u8?token={$token}");
        }

        return url("hls/{$videoId}/{$quality}/stream.m3u8?token={$token}");
    }

    /**
     * Validate an HLS access token.
     */
    public function validateToken(int $videoId, string $token): bool
    {
        $expected = $this->generateToken($videoId);
        return hash_equals($expected, $token);
    }

    /**
     * Delete all HLS files for a video.
     */
    public function deleteHlsFiles(int $videoId): bool
    {
        $dir = $this->hlsPath . DIRECTORY_SEPARATOR . $videoId;

        if (!is_dir($dir)) {
            return true;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($dir);

        return true;
    }

    /**
     * Check if HLS output exists for a video.
     */
    public function hasHlsOutput(int $videoId): bool
    {
        $master = $this->hlsPath . DIRECTORY_SEPARATOR . $videoId . DIRECTORY_SEPARATOR . 'master.m3u8';
        return file_exists($master);
    }

    /**
     * Get the list of available qualities for a video.
     */
    public function getAvailableQualities(int $videoId): array
    {
        $dir = $this->hlsPath . DIRECTORY_SEPARATOR . $videoId;

        if (!is_dir($dir)) {
            return [];
        }

        $qualities = [];
        $entries = scandir($dir);

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || $entry === 'master.m3u8') {
                continue;
            }

            $playlist = $dir . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'stream.m3u8';
            if (is_file($playlist)) {
                $qualities[] = $entry;
            }
        }

        return $qualities;
    }

    /**
     * Generate an HMAC token for a video ID.
     */
    private function generateToken(int $videoId): string
    {
        $key = $_ENV['APP_KEY'] ?? 'change-this-secret-key';
        return hash_hmac('sha256', (string) $videoId, $key);
    }
}
