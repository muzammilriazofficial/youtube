<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class SystemHealthController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $phpVersion = phpversion();
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
        $mysqlVersion = 'N/A';
        $diskTotal = 'N/A';
        $diskFree = 'N/A';
        $diskUsed = 'N/A';
        $dbSize = 'N/A';
        $memoryLimit = ini_get('memory_limit');
        $maxExecutionTime = ini_get('max_execution_time');
        $uploadMaxSize = ini_get('upload_max_filesize');
        $postMaxSize = ini_get('post_max_size');

        try {
            $db = Database::getInstance();
            $mysqlVersion = $db->getPdo()->query('SELECT VERSION()')->fetchColumn();

            $dbResult = $db->raw(
                "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size FROM information_schema.tables WHERE table_schema = DATABASE()"
            )->fetch();
            $dbSize = ($dbResult['size'] ?? 0) . ' MB';

            $tableCount = $db->getPdo()->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn();
        } catch (\Throwable $e) {
            $tableCount = 0;
        }

        $diskTotalBytes = disk_total_space(ROOT_PATH ?: __DIR__);
        $diskFreeBytes = disk_free_space(ROOT_PATH ?: __DIR__);

        if ($diskTotalBytes !== false) {
            $diskTotal = round($diskTotalBytes / 1024 / 1024 / 1024, 2) . ' GB';
            $diskFree = round($diskFreeBytes / 1024 / 1024 / 1024, 2) . ' GB';
            $diskUsed = round(($diskTotalBytes - $diskFreeBytes) / 1024 / 1024 / 1024, 2) . ' GB';
        }

        $ffmpegPath = $this->getSetting('ffmpeg_path') ?: '/usr/bin/ffmpeg';
        $ffmpegStatus = 'Not Available';
        $ffmpegVersion = '';
        exec("{$ffmpegPath} -version 2>&1", $output, $returnCode);
        if ($returnCode === 0 && !empty($output)) {
            $ffmpegStatus = 'Available';
            $ffmpegVersion = explode("\n", $output[0])[0] ?? '';
        }

        $ffprobePath = $this->getSetting('ffprobe_path') ?: '/usr/bin/ffprobe';
        $ffprobeStatus = 'Not Available';
        exec("{$ffprobePath} -version 2>&1", $output, $returnCode);
        if ($returnCode === 0 && !empty($output)) {
            $ffprobeStatus = 'Available';
        }

        $extensions = [
            'PDO' => extension_loaded('pdo'),
            'PDO MySQL' => extension_loaded('pdo_mysql'),
            'JSON' => extension_loaded('json'),
            'Mbstring' => extension_loaded('mbstring'),
            'GD' => extension_loaded('gd'),
            'Curl' => extension_loaded('curl'),
            'Fileinfo' => extension_loaded('fileinfo'),
            'Zip' => extension_loaded('zip'),
            'XML' => extension_loaded('xml'),
            'Intl' => extension_loaded('intl'),
        ];

        $queueStatus = 'N/A';
        $queueJobs = 0;
        try {
            $db2 = Database::getInstance();
            if ($this->tableExists($db2, 'jobs')) {
                $queueJobs = $db2->table('jobs')->count();
                $queueStatus = $queueJobs > 0 ? "{$queueJobs} jobs pending" : 'Queue empty';
            }
        } catch (\Throwable $e) {
            $queueStatus = 'N/A';
        }

        $cacheDriver = $this->getSetting('cache_driver') ?: 'file';
        $cacheStatus = 'N/A';
        $cacheDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'cache';
        if (is_dir($cacheDir)) {
            $cacheFiles = count(glob($cacheDir . DIRECTORY_SEPARATOR . '*'));
            $cacheStatus = "{$cacheFiles} cached items";
        }

        return $this->view('admin.system-health', [
            'title' => 'System Health',
            'activeMenu' => 'system-health',
            'phpVersion' => $phpVersion,
            'serverSoftware' => $serverSoftware,
            'mysqlVersion' => $mysqlVersion,
            'diskTotal' => $diskTotal,
            'diskFree' => $diskFree,
            'diskUsed' => $diskUsed,
            'dbSize' => $dbSize,
            'tableCount' => $tableCount ?? 0,
            'memoryLimit' => $memoryLimit,
            'maxExecutionTime' => $maxExecutionTime,
            'uploadMaxSize' => $uploadMaxSize,
            'postMaxSize' => $postMaxSize,
            'ffmpegStatus' => $ffmpegStatus,
            'ffmpegVersion' => $ffmpegVersion,
            'ffprobeStatus' => $ffprobeStatus,
            'extensions' => $extensions,
            'queueStatus' => $queueStatus,
            'cacheDriver' => $cacheDriver,
            'cacheStatus' => $cacheStatus,
        ]);
    }

    private function getSetting(string $key): ?string
    {
        try {
            $db = Database::getInstance();
            $result = $db->table('settings')->where('key', $key)->first();
            return $result['value'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function tableExists(Database $db, string $table): bool
    {
        try {
            $db->table($table)->limit(1)->get();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
