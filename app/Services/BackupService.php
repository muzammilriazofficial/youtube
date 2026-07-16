<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Database backup service using mysqldump.
 */
class BackupService
{
    private string $backupPath;
    private string $mysqlPath;
    private string $dbHost;
    private string $dbName;
    private string $dbUser;
    private string $dbPass;

    public function __construct()
    {
        $this->backupPath = $_ENV['STORAGE_LOCAL_PATH'] ?? (ROOT_PATH . DIRECTORY_SEPARATOR . 'storage');
        $this->backupPath .= DIRECTORY_SEPARATOR . 'backups';
        $this->mysqlPath  = $_ENV['MYSQL_PATH'] ?? 'mysqldump';
        $this->dbHost     = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $this->dbName     = $_ENV['DB_DATABASE'] ?? 'youtube_clone';
        $this->dbUser     = $_ENV['DB_USERNAME'] ?? 'root';
        $this->dbPass     = $_ENV['DB_PASSWORD'] ?? '';

        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    /**
     * Create a database backup.
     */
    public function createBackup(string $type = 'full'): array
    {
        $timestamp = date('Y-m-d_H-i-s');
        $filename  = "{$type}_backup_{$timestamp}.sql";
        $filePath  = $this->backupPath . DIRECTORY_SEPARATOR . $filename;

        $tables = [];

        if ($type === 'schema') {
            $tables = $this->getTables();
            $cmd    = sprintf(
                '%s --host=%s --user=%s %s --no-data %s > "%s" 2>&1',
                escapeshellcmd($this->mysqlPath),
                escapeshellarg($this->dbHost),
                escapeshellarg($this->dbUser),
                !empty($this->dbPass) ? '--password=' . escapeshellarg($this->dbPass) : '',
                escapeshellarg($this->dbName),
                escapeshellarg($filePath)
            );
        } else {
            $cmd = sprintf(
                '%s --host=%s --user=%s %s --routines --triggers --single-transaction %s > "%s" 2>&1',
                escapeshellcmd($this->mysqlPath),
                escapeshellarg($this->dbHost),
                escapeshellarg($this->dbUser),
                !empty($this->dbPass) ? '--password=' . escapeshellarg($this->dbPass) : '',
                escapeshellarg($this->dbName),
                escapeshellarg($filePath)
            );
        }

        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException("Backup failed: " . implode("\n", $output));
        }

        $fileSize = file_exists($filePath) ? filesize($filePath) : 0;

        $metaPath = $filePath . '.meta.json';
        $meta     = [
            'filename'   => $filename,
            'type'       => $type,
            'created_at' => date('Y-m-d H:i:s'),
            'size'       => $fileSize,
            'database'   => $this->dbName,
            'tables'     => $tables,
        ];
        file_put_contents($metaPath, json_encode($meta, JSON_PRETTY_PRINT));

        return [
            'filename'  => $filename,
            'path'      => $filePath,
            'size'      => $fileSize,
            'type'      => $type,
            'created_at' => $meta['created_at'],
        ];
    }

    /**
     * List all backup files.
     */
    public function listBackups(): array
    {
        $files = glob($this->backupPath . DIRECTORY_SEPARATOR . '*.sql');
        $backups = [];

        foreach ($files as $file) {
            $basename = basename($file);
            $metaPath = $file . '.meta.json';
            $meta     = file_exists($metaPath)
                ? json_decode(file_get_contents($metaPath), true)
                : [
                    'filename'   => $basename,
                    'type'       => 'unknown',
                    'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                    'size'       => filesize($file),
                ];

            $backups[] = $meta;
        }

        usort($backups, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $backups;
    }

    /**
     * Restore the database from a backup file.
     */
    public function restoreBackup(string $filename): bool
    {
        $filePath = $this->backupPath . DIRECTORY_SEPARATOR . basename($filename);

        if (!file_exists($filePath)) {
            throw new \RuntimeException("Backup file not found: {$filename}");
        }

        $cmd = sprintf(
            '%s --host=%s --user=%s %s %s < "%s" 2>&1',
            $_ENV['MYSQL_PATH'] ?? 'mysql',
            escapeshellarg($this->dbHost),
            escapeshellarg($this->dbUser),
            !empty($this->dbPass) ? '--password=' . escapeshellarg($this->dbPass) : '',
            escapeshellarg($this->dbName),
            escapeshellarg($filePath)
        );

        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException("Restore failed: " . implode("\n", $output));
        }

        return true;
    }

    /**
     * Delete a backup file.
     */
    public function deleteBackup(string $filename): bool
    {
        $filePath  = $this->backupPath . DIRECTORY_SEPARATOR . basename($filename);
        $metaPath  = $filePath . '.meta.json';
        $deleted   = false;

        if (file_exists($filePath)) {
            $deleted = unlink($filePath);
        }

        if (file_exists($metaPath)) {
            unlink($metaPath);
        }

        return $deleted;
    }

    /**
     * Get metadata about a backup file.
     */
    public function getBackupInfo(string $filename): ?array
    {
        $filePath = $this->backupPath . DIRECTORY_SEPARATOR . basename($filename);
        $metaPath = $filePath . '.meta.json';

        if (file_exists($metaPath)) {
            return json_decode(file_get_contents($metaPath), true);
        }

        if (file_exists($filePath)) {
            return [
                'filename'   => basename($filename),
                'type'       => 'unknown',
                'created_at' => date('Y-m-d H:i:s', filemtime($filePath)),
                'size'       => filesize($filePath),
            ];
        }

        return null;
    }

    /**
     * Get list of database tables.
     */
    private function getTables(): array
    {
        $cmd = sprintf(
            '%s --host=%s --user=%s %s --skip-column-names -e "SHOW TABLES" %s 2>&1',
            $_ENV['MYSQL_PATH'] ?? 'mysql',
            escapeshellarg($this->dbHost),
            escapeshellarg($this->dbUser),
            !empty($this->dbPass) ? '--password=' . escapeshellarg($this->dbPass) : '',
            escapeshellarg($this->dbName)
        );

        $output = [];
        exec($cmd, $output, $exitCode);

        return $exitCode === 0 ? array_map('trim', $output) : [];
    }
}
