<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Video;

/**
 * Video processing service using FFmpeg for transcoding, thumbnails, and HLS streaming.
 */
class VideoProcessor
{
    private string $ffmpegPath;
    private string $ffprobePath;
    private string $storagePath;
    private string $hlsPath;
    private Database $db;
    private Video $videoModel;

    private array $qualities = [
        '1080p' => ['width' => 1920, 'height' => 1080, 'bitrate' => '5000k', 'maxrate' => '5350k', 'bufsize' => '7500k'],
        '720p'  => ['width' => 1280, 'height' => 720,  'bitrate' => '2800k', 'maxrate' => '2996k', 'bufsize' => '4200k'],
        '480p'  => ['width' => 854,  'height' => 480,  'bitrate' => '1400k', 'maxrate' => '1498k', 'bufsize' => '2100k'],
        '360p'  => ['width' => 640,  'height' => 360,  'bitrate' => '800k',  'maxrate' => '856k',  'bufsize' => '1200k'],
    ];

    public function __construct()
    {
        $this->ffmpegPath  = $_ENV['FFMPEG_PATH'] ?? 'ffmpeg';
        $this->ffprobePath = $_ENV['FFPROBE_PATH'] ?? 'ffprobe';
        $this->storagePath = $_ENV['STORAGE_LOCAL_PATH'] ?? (ROOT_PATH . DIRECTORY_SEPARATOR . 'storage');
        $this->hlsPath     = $this->storagePath . DIRECTORY_SEPARATOR . 'hls';
        $this->db          = Database::getInstance();
        $this->videoModel  = new Video();
    }

    /**
     * Get video metadata using ffprobe.
     */
    public function getVideoInfo(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }

        $cmd = sprintf(
            '%s -v quiet -print_format json -show_format -show_streams "%s" 2>&1',
            escapeshellcmd($this->ffprobePath),
            escapeshellarg($filePath)
        );

        $output = $this->executeCommand($cmd);
        $data   = json_decode($output, true);

        if ($data === null) {
            throw new \RuntimeException('Failed to parse ffprobe output');
        }

        $videoStream = null;
        $audioStream = null;

        foreach ($data['streams'] ?? [] as $stream) {
            if ($stream['codec_type'] === 'video' && $videoStream === null) {
                $videoStream = $stream;
            }
            if ($stream['codec_type'] === 'audio' && $audioStream === null) {
                $audioStream = $stream;
            }
        }

        $duration = (float) ($data['format']['duration'] ?? 0);
        $width    = (int) ($videoStream['width'] ?? 0);
        $height   = (int) ($videoStream['height'] ?? 0);

        return [
            'duration'     => (int) round($duration),
            'duration_float' => $duration,
            'width'        => $width,
            'height'       => $height,
            'resolution'   => "{$width}x{$height}",
            'codec'        => $videoStream['codec_name'] ?? 'unknown',
            'codec_long'   => $videoStream['codec_long_name'] ?? 'unknown',
            'bitrate'      => (int) ($data['format']['bit_rate'] ?? 0),
            'format'       => $data['format']['format_name'] ?? 'unknown',
            'format_long'  => $data['format']['format_long_name'] ?? 'unknown',
            'size'         => (int) ($data['format']['size'] ?? 0),
            'audio_codec'  => $audioStream['codec_name'] ?? 'none',
            'audio_bitrate' => (int) ($audioStream['bit_rate'] ?? 0),
            'fps'          => $this->calculateFps($videoStream['r_frame_rate'] ?? '0/1'),
            'raw'          => $data,
        ];
    }

    /**
     * Generate a single thumbnail at a specific timestamp.
     */
    public function generateThumbnail(string $filePath, string $outputPath, float $time = 1.0): bool
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }

        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $cmd = sprintf(
            '%s -y -ss %s -i "%s" -vframes 1 -vf "scale=1280:-2" -q:v 2 "%s" 2>&1',
            escapeshellcmd($this->ffmpegPath),
            $time,
            escapeshellarg($filePath),
            escapeshellarg($outputPath)
        );

        $this->executeCommand($cmd);

        return file_exists($outputPath);
    }

    /**
     * Generate multiple thumbnails at 25%, 50%, 75% of video duration.
     */
    public function generateThumbnails(int $videoId, string $filePath): array
    {
        $info     = $this->getVideoInfo($filePath);
        $duration = $info['duration_float'];

        if ($duration <= 0) {
            throw new \RuntimeException('Cannot generate thumbnails for video with zero duration');
        }

        $outputDir = $this->storagePath . DIRECTORY_SEPARATOR . 'thumbnails' . DIRECTORY_SEPARATOR . $videoId;
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $percentages = [0.25, 0.50, 0.75];
        $thumbnails  = [];

        foreach ($percentages as $index => $pct) {
            $time     = $duration * $pct;
            $filename = 'thumb_' . ($index + 1) . '.jpg';
            $output   = $outputDir . DIRECTORY_SEPARATOR . $filename;

            if ($this->generateThumbnail($filePath, $output, $time)) {
                $thumbnails[] = "thumbnails/{$videoId}/{$filename}";
            }
        }

        if (!empty($thumbnails) && empty($this->getFirstThumbnail($videoId))) {
            $this->videoModel->updateById($videoId, [
                'thumbnail' => $thumbnails[0],
            ]);
        }

        return $thumbnails;
    }

    private function getFirstThumbnail(int $videoId): ?string
    {
        $video = $this->videoModel->find($videoId);
        return $video['thumbnail'] ?? null;
    }

    /**
     * Transcode video to HLS format (m3u8 playlist + .ts segments).
     */
    public function transcodeToHLS(string $inputPath, string $outputDir): string
    {
        if (!file_exists($inputPath)) {
            throw new \RuntimeException("File not found: {$inputPath}");
        }

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $playlistFile = $outputDir . DIRECTORY_SEPARATOR . 'stream.m3u8';
        $segmentPattern = $outputDir . DIRECTORY_SEPARATOR . 'segment_%03d.ts';

        $cmd = sprintf(
            '%s -y -i "%s" -codec: copy -start_number 0 -hls_time 10 -hls_list_size 0 -f hls "%s" 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($inputPath),
            escapeshellarg($playlistFile)
        );

        $this->executeCommand($cmd);

        if (!file_exists($playlistFile)) {
            throw new \RuntimeException('HLS transcoding failed: playlist not generated');
        }

        return $playlistFile;
    }

    /**
     * Transcode video to multiple quality variants.
     */
    public function transcodeToMultipleQualities(string $inputPath, string $outputDir): array
    {
        $info     = $this->getVideoInfo($inputPath);
        $srcHeight = $info['height'];
        $results  = [];

        foreach ($this->qualities as $label => $params) {
            if ($srcHeight < $params['height']) {
                continue;
            }

            $qualityDir = $outputDir . DIRECTORY_SEPARATOR . $label;
            if (!is_dir($qualityDir)) {
                mkdir($qualityDir, 0755, true);
            }

            $playlistFile = $qualityDir . DIRECTORY_SEPARATOR . 'stream.m3u8';

            $cmd = sprintf(
                '%s -y -i "%s" -vf "scale=%d:%d" -c:v libx264 -b:v %s -maxrate %s -bufsize %s -c:a aac -b:a 128k -hls_time 10 -hls_list_size 0 -f hls "%s" 2>&1',
                escapeshellcmd($this->ffmpegPath),
                escapeshellarg($inputPath),
                $params['width'],
                $params['height'],
                $params['bitrate'],
                $params['maxrate'],
                $params['bufsize'],
                escapeshellarg($playlistFile)
            );

            $this->executeCommand($cmd);

            if (file_exists($playlistFile)) {
                $results[$label] = [
                    'playlist' => $playlistFile,
                    'label'    => $label,
                    'width'    => $params['width'],
                    'height'   => $params['height'],
                    'bitrate'  => $params['bitrate'],
                ];
            }
        }

        return $results;
    }

    /**
     * Generate master HLS playlist with all quality variants.
     */
    public function generateHLSPlaylist(int $videoId): string
    {
        $hlsDir = $this->hlsPath . DIRECTORY_SEPARATOR . $videoId;
        $masterPlaylist = $hlsDir . DIRECTORY_SEPARATOR . 'master.m3u8';

        if (!is_dir($hlsDir)) {
            mkdir($hlsDir, 0755, true);
        }

        $lines = ['#EXTM3U', '#EXT-X-VERSION:3'];

        foreach ($this->qualities as $label => $params) {
            $qualityDir = $hlsDir . DIRECTORY_SEPARATOR . $label;
            $playlist   = $qualityDir . DIRECTORY_SEPARATOR . 'stream.m3u8';

            if (!file_exists($playlist)) {
                continue;
            }

            $bandwidth = (int) str_replace('k', '', $params['bitrate']) * 1000;
            $lines[]   = sprintf(
                '#EXT-X-STREAM-INF:BANDWIDTH=%d,RESOLUTION=%dx%d',
                $bandwidth,
                $params['width'],
                $params['height']
            );
            $lines[] = "{$label}/stream.m3u8";
        }

        file_put_contents($masterPlaylist, implode("\n", $lines));

        return $masterPlaylist;
    }

    /**
     * Main orchestration - process uploaded video completely.
     */
    public function processVideo(int $videoId): bool
    {
        $video = $this->videoModel->find($videoId);
        if ($video === null) {
            throw new \RuntimeException("Video not found: {$videoId}");
        }

        $this->videoModel->updateById($videoId, ['status' => 'processing']);

        try {
            $filePath = $this->storagePath . DIRECTORY_SEPARATOR . $video['file_path'];

            if (!file_exists($filePath)) {
                throw new \RuntimeException("Video file not found: {$filePath}");
            }

            $info = $this->getVideoInfo($filePath);

            $this->videoModel->updateById($videoId, [
                'duration'   => $info['duration'],
                'resolution' => $info['resolution'],
            ]);

            $this->generateThumbnails($videoId, $filePath);

            $hlsDir = $this->hlsPath . DIRECTORY_SEPARATOR . $videoId;
            $this->transcodeToMultipleQualities($filePath, $hlsDir);
            $this->generateHLSPlaylist($videoId);

            $this->videoModel->updateById($videoId, [
                'status'       => 'published',
                'published_at' => date('Y-m-d H:i:s'),
            ]);

            return true;
        } catch (\Throwable $e) {
            error_log("[VideoProcessor] Error processing video {$videoId}: {$e->getMessage()}");

            $this->videoModel->updateById($videoId, ['status' => 'failed']);

            return false;
        }
    }

    /**
     * Detect video format and codec information.
     */
    public function detectFormat(string $filePath): array
    {
        $info = $this->getVideoInfo($filePath);

        return [
            'format'  => $info['format'],
            'codec'   => $info['codec'],
            'is_video' => true,
            'extension' => $this->getExtensionForFormat($info['format']),
        ];
    }

    /**
     * Validate that a file is a valid video.
     */
    public function validateVideoFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return ['valid' => false, 'error' => 'File does not exist'];
        }

        $allowedFormats = ['mov', 'mp4', 'avi', 'mkv', 'webm', 'flv', 'wmv', 'm4v', '3gp'];
        $ext            = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedFormats, true)) {
            return ['valid' => false, 'error' => "Unsupported file extension: {$ext}"];
        }

        try {
            $info = $this->getVideoInfo($filePath);

            if ($info['duration'] <= 0) {
                return ['valid' => false, 'error' => 'Video has no duration'];
            }

            if ($info['width'] <= 0 || $info['height'] <= 0) {
                return ['valid' => false, 'error' => 'Video has no valid video stream'];
            }

            return ['valid' => true, 'info' => $info];
        } catch (\Throwable $e) {
            return ['valid' => false, 'error' => 'Failed to probe video: ' . $e->getMessage()];
        }
    }

    /**
     * Generate streaming URL for a video with quality parameter.
     */
    public function getStreamingUrl(array $video, string $quality = 'auto'): string
    {
        $videoId = $video['id'];

        if ($quality === 'auto') {
            return url("hls/{$videoId}/master.m3u8");
        }

        return url("hls/{$videoId}/{$quality}/stream.m3u8");
    }

    /**
     * Execute a shell command and return output.
     *
     * @throws \RuntimeException on failure
     */
    private function executeCommand(string $cmd): string
    {
        $output    = [];
        $exitCode  = 0;

        exec($cmd . ' 2>&1', $output, $exitCode);

        $combined = implode("\n", $output);

        if ($exitCode !== 0) {
            error_log("[VideoProcessor] Command failed (exit {$exitCode}): {$cmd}");
            error_log("[VideoProcessor] Output: {$combined}");
            throw new \RuntimeException("Command failed with exit code {$exitCode}: " . substr($combined, -500));
        }

        return $combined;
    }

    /**
     * Calculate FPS from a frame rate fraction string.
     */
    private function calculateFps(string $frameRate): float
    {
        if (str_contains($frameRate, '/')) {
            [$num, $den] = explode('/', $frameRate, 2);
            $den = (float) $den;
            return $den > 0 ? round((float) $num / $den, 2) : 0.0;
        }

        return (float) $frameRate;
    }

    /**
     * Get file extension for a given format name.
     */
    private function getExtensionForFormat(string $format): string
    {
        $map = [
            'mov,mp4,m4a,3gp,3g2,mj2' => 'mp4',
            'avi'                       => 'avi',
            'matroska,webm'             => 'mkv',
            'flv'                       => 'flv',
            'mpegts'                    => 'ts',
        ];

        return $map[$format] ?? 'mp4';
    }
}
