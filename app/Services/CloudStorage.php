<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Cloud storage abstraction supporting local and S3-compatible backends.
 */
class CloudStorage
{
    private string $driver;
    private string $localPath;
    private string $s3Key;
    private string $s3Secret;
    private string $s3Region;
    private string $s3Bucket;
    private string $s3Endpoint;

    public function __construct()
    {
        $this->driver     = $_ENV['STORAGE_DRIVER'] ?? 'local';
        $this->localPath  = $_ENV['STORAGE_LOCAL_PATH'] ?? (ROOT_PATH . DIRECTORY_SEPARATOR . 'storage');
        $this->s3Key      = $_ENV['S3_KEY'] ?? '';
        $this->s3Secret   = $_ENV['S3_SECRET'] ?? '';
        $this->s3Region   = $_ENV['S3_REGION'] ?? 'us-east-1';
        $this->s3Bucket   = $_ENV['S3_BUCKET'] ?? '';
        $this->s3Endpoint = $_ENV['S3_ENDPOINT'] ?? '';
    }

    /**
     * Upload a file to cloud storage.
     */
    public function upload(string $localPath, string $cloudPath): bool
    {
        if (!file_exists($localPath)) {
            throw new \RuntimeException("Local file not found: {$localPath}");
        }

        if ($this->driver === 's3') {
            return $this->s3Upload($localPath, $cloudPath);
        }

        return $this->localUpload($localPath, $cloudPath);
    }

    /**
     * Download a file from cloud storage.
     */
    public function download(string $cloudPath, string $localPath): bool
    {
        $dir = dirname($localPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if ($this->driver === 's3') {
            return $this->s3Download($cloudPath, $localPath);
        }

        return $this->localDownload($cloudPath, $localPath);
    }

    /**
     * Delete a file from cloud storage.
     */
    public function delete(string $cloudPath): bool
    {
        if ($this->driver === 's3') {
            return $this->s3Delete($cloudPath);
        }

        return $this->localDelete($cloudPath);
    }

    /**
     * Generate a signed/temporary URL for a file.
     */
    public function getSignedUrl(string $cloudPath, int $expiry = 3600): string
    {
        if ($this->driver === 's3') {
            return $this->s3SignedUrl($cloudPath, $expiry);
        }

        return $this->localSignedUrl($cloudPath, $expiry);
    }

    /**
     * List files matching a prefix.
     */
    public function listFiles(string $prefix = ''): array
    {
        if ($this->driver === 's3') {
            return $this->s3ListFiles($prefix);
        }

        return $this->localListFiles($prefix);
    }

    // ─── Local Storage Methods ───────────────────────────────────

    private function localUpload(string $localPath, string $cloudPath): bool
    {
        $dest = $this->localPath . DIRECTORY_SEPARATOR . $cloudPath;
        $dir  = dirname($dest);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return copy($localPath, $dest);
    }

    private function localDownload(string $cloudPath, string $localPath): bool
    {
        $source = $this->localPath . DIRECTORY_SEPARATOR . $cloudPath;

        if (!file_exists($source)) {
            return false;
        }

        return copy($source, $localPath);
    }

    private function localDelete(string $cloudPath): bool
    {
        $fullPath = $this->localPath . DIRECTORY_SEPARATOR . $cloudPath;

        if (!file_exists($fullPath)) {
            return false;
        }

        return unlink($fullPath);
    }

    private function localSignedUrl(string $cloudPath, int $expiry): string
    {
        $token = bin2hex(random_bytes(32));
        $expires = time() + $expiry;
        $path = urlencode($cloudPath);

        $signature = hash_hmac('sha256', "{$path}:{$expires}:{$token}", $_ENV['APP_KEY'] ?? 'change-this-secret-key');

        return url("storage/{$path}?token={$token}&expires={$expires}&sig={$signature}");
    }

    private function localListFiles(string $prefix): array
    {
        $fullDir = $this->localPath . DIRECTORY_SEPARATOR . $prefix;

        if (!is_dir($fullDir)) {
            return [];
        }

        $files  = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($fullDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relative = str_replace($this->localPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $files[]  = [
                    'key'  => $relative,
                    'size' => $file->getSize(),
                    'modified' => date('c', $file->getMTime()),
                ];
            }
        }

        return $files;
    }

    // ─── S3 Storage Methods (curl-based) ─────────────────────────

    private function s3Upload(string $localPath, string $cloudPath): bool
    {
        $date      = gmdate('D, d M Y H:i:s T');
        $mime      = mime_content_type($localPath) ?: 'application/octet-stream';
        $body      = file_get_contents($localPath);
        $contentMd5 = base64_encode(md5($body, true));

        $headers = [
            'Date: ' . $date,
            'Content-Type: ' . $mime,
            'Content-MD5: ' . $contentMd5,
        ];

        $stringToSign = "PUT\n{$contentMd5}\n{$mime}\n{$date}\n/{$this->s3Bucket}/{$cloudPath}";
        $signature    = $this->s3Sign($stringToSign);
        $headers[]    = 'Authorization: AWS {$this->s3Key}:{$signature}';

        $url = $this->getS3BaseUrl() . '/' . $cloudPath;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_HTTPHEADER   => $headers,
            CURLOPT_POSTFIELDS   => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT      => 300,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }

    private function s3Download(string $cloudPath, string $localPath): bool
    {
        $date     = gmdate('D, d M Y H:i:s T');
        $url      = $this->getS3BaseUrl() . '/' . $cloudPath;
        $resource = "/{$this->s3Bucket}/{$cloudPath}";

        $stringToSign = "GET\n\n\n{$date}\n{$resource}";
        $signature    = $this->s3Sign($stringToSign);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Date: ' . $date,
                'Authorization: AWS {$this->s3Key}:{$signature}',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT      => 300,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $body === false) {
            return false;
        }

        return file_put_contents($localPath, $body) !== false;
    }

    private function s3Delete(string $cloudPath): bool
    {
        $date     = gmdate('D, d M Y H:i:s T');
        $url      = $this->getS3BaseUrl() . '/' . $cloudPath;
        $resource = "/{$this->s3Bucket}/{$cloudPath}";

        $stringToSign = "DELETE\n\n\n{$date}\n{$resource}";
        $signature    = $this->s3Sign($stringToSign);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => [
                'Date: ' . $date,
                'Authorization: AWS {$this->s3Key}:{$signature}',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }

    private function s3SignedUrl(string $cloudPath, int $expiry): string
    {
        $expires  = time() + $expiry;
        $resource = "/{$this->s3Bucket}/{$cloudPath}";
        $stringToSign = "GET\n\n\n{$expires}\n{$resource}";
        $signature = $this->s3Sign($stringToSign);

        $encodedPath = rawurlencode($cloudPath);

        return "{$this->getS3BaseUrl()}/{$encodedPath}?AWSAccessKeyId={$this->s3Key}&Expires={$expires}&Signature=" . rawurlencode($signature);
    }

    private function s3ListFiles(string $prefix): array
    {
        $date     = gmdate('D, d M Y H:i:s T');
        $url      = $this->getS3BaseUrl() . "/?prefix=" . rawurlencode($prefix) . "&delimiter=/";
        $resource = "/{$this->s3Bucket}/";

        $stringToSign = "GET\n\n\n{$date}\n{$resource}";
        $signature    = $this->s3Sign($stringToSign);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Date: ' . $date,
                'Authorization: AWS {$this->s3Key}:{$signature}',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $body = curl_exec($ch);
        curl_close($ch);

        if ($body === false) {
            return [];
        }

        $xml = @simplexml_load_string($body);
        if ($xml === false) {
            return [];
        }

        $files = [];
        if (isset($xml->Contents)) {
            foreach ($xml->Contents as $item) {
                $files[] = [
                    'key'      => (string) $item->Key,
                    'size'     => (int) $item->Size,
                    'modified' => (string) $item->LastModified,
                ];
            }
        }

        return $files;
    }

    private function getS3BaseUrl(): string
    {
        if (!empty($this->s3Endpoint)) {
            return rtrim($this->s3Endpoint, '/') . "/{$this->s3Bucket}";
        }

        return "https://{$this->s3Bucket}.s3.{$this->s3Region}.amazonaws.com";
    }

    private function s3Sign(string $stringToSign): string
    {
        return base64_encode(hash_hmac('sha1', $stringToSign, $this->s3Secret, true));
    }
}
