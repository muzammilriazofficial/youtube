<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Thumbnail generation service using FFmpeg.
 */
class ThumbnailGenerator
{
    private string $ffmpegPath;

    public function __construct()
    {
        $this->ffmpegPath = $_ENV['FFMPEG_PATH'] ?? 'ffmpeg';
    }

    /**
     * Generate a single thumbnail at a given timestamp.
     */
    public function generate(string $videoPath, string $outputPath, float $timestamp = 1.0): bool
    {
        if (!file_exists($videoPath)) {
            throw new \RuntimeException("Video file not found: {$videoPath}");
        }

        $this->ensureDirectoryExists($outputPath);

        $cmd = sprintf(
            '%s -y -ss %s -i "%s" -vframes 1 -vf "scale=1280:-2" -q:v 2 "%s" 2>&1',
            escapeshellcmd($this->ffmpegPath),
            $timestamp,
            escapeshellarg($videoPath),
            escapeshellarg($outputPath)
        );

        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        return $exitCode === 0 && file_exists($outputPath);
    }

    /**
     * Generate multiple evenly-spaced thumbnails.
     */
    public function generateMultiple(string $videoPath, string $outputDir, int $count = 4): array
    {
        if (!file_exists($videoPath)) {
            throw new \RuntimeException("Video file not found: {$videoPath}");
        }

        $this->ensureDirectoryExists($outputDir . DIRECTORY_SEPARATOR . 'placeholder');

        $duration = $this->getDuration($videoPath);

        if ($duration <= 0) {
            throw new \RuntimeException('Cannot generate thumbnails for zero-duration video');
        }

        $thumbnails = [];
        $interval   = $duration / ($count + 1);

        for ($i = 1; $i <= $count; $i++) {
            $time     = $interval * $i;
            $filename = 'thumb_' . $i . '_' . (int) $time . 's.jpg';
            $output   = $outputDir . DIRECTORY_SEPARATOR . $filename;

            if ($this->generate($videoPath, $output, $time)) {
                $thumbnails[] = [
                    'index'     => $i,
                    'timestamp' => (int) $time,
                    'path'      => $output,
                    'filename'  => $filename,
                ];
            }
        }

        return $thumbnails;
    }

    /**
     * Generate a custom-sized thumbnail.
     */
    public function createCustom(
        string $videoPath,
        string $outputPath,
        int $width,
        int $height,
        float $timestamp = 1.0
    ): bool {
        if (!file_exists($videoPath)) {
            throw new \RuntimeException("Video file not found: {$videoPath}");
        }

        $this->ensureDirectoryExists($outputPath);

        $cmd = sprintf(
            '%s -y -ss %s -i "%s" -vframes 1 -vf "scale=%d:%d:force_original_aspect_ratio=decrease,pad=%d:%d:(ow-iw)/2:(oh-ih)/2" -q:v 2 "%s" 2>&1',
            escapeshellcmd($this->ffmpegPath),
            $timestamp,
            escapeshellarg($videoPath),
            $width,
            $height,
            $width,
            $height,
            escapeshellarg($outputPath)
        );

        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        return $exitCode === 0 && file_exists($outputPath);
    }

    /**
     * Get video duration using ffprobe.
     */
    private function getDuration(string $videoPath): float
    {
        $ffprobe = $_ENV['FFPROBE_PATH'] ?? 'ffprobe';

        $cmd = sprintf(
            '%s -v quiet -show_entries format=duration -of csv=p=0 "%s" 2>&1',
            escapeshellcmd($ffprobe),
            escapeshellarg($videoPath)
        );

        $output = [];
        exec($cmd, $output, $exitCode);

        return $exitCode === 0 ? (float) trim(implode('', $output)) : 0.0;
    }

    /**
     * Ensure the directory for a file path exists.
     */
    private function ensureDirectoryExists(string $filePath): void
    {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
