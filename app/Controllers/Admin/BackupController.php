<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class BackupController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $backupPath = ROOT_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $backups = [];
        $files = glob($backupPath . DIRECTORY_SEPARATOR . '*.sql');
        if ($files !== false) {
            foreach ($files as $file) {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => round(filesize($file) / 1024 / 1024, 2) . ' MB',
                    'size_bytes' => filesize($file),
                    'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                ];
            }
        }

        usort($backups, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $this->view('admin.backup', [
            'title' => 'Backup Management',
            'activeMenu' => 'backup',
            'backups' => $backups,
        ]);
    }

    public function create(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $backupPath = ROOT_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $filename = 'backup_' . date('Y-m-d_His') . '.sql';
        $filepath = $backupPath . DIRECTORY_SEPARATOR . $filename;

        try {
            $db = Database::getInstance();
            $pdo = $db->getPdo();
            $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();

            $tables = $pdo->query("SHOW TABLES FROM `{$dbName}`")->fetchAll(\PDO::FETCH_COLUMN);

            $output = "-- YouTube Clone Database Backup\n";
            $output .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
            $output .= "-- Database: {$dbName}\n\n";
            $output .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

            foreach ($tables as $table) {
                $output .= "-- Table: {$table}\n";
                $output .= "DROP TABLE IF EXISTS `{$table}`;\n";

                $createTable = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
                $output .= $createTable['Create Table'] . ";\n\n";

                $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_NUM);
                foreach ($rows as $row) {
                    $values = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote($v), $row);
                    $output .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                }
                $output .= "\n";
            }

            $output .= "SET FOREIGN_KEY_CHECKS = 1;\n";

            file_put_contents($filepath, $output);

            return $this->withSuccess("Backup created: {$filename}")->redirect('/admin/backup');
        } catch (\Throwable $e) {
            return $this->withError('Backup failed: ' . $e->getMessage())->redirect('/admin/backup');
        }
    }

    public function restore(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $filename = $this->request->input('filename', '');
        if ($filename === '' || str_contains($filename, '..') || str_contains($filename, '/')) {
            return $this->withError('Invalid filename.')->redirect('/admin/backup');
        }

        $backupPath = ROOT_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';
        $filepath = $backupPath . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($filepath)) {
            return $this->withError('Backup file not found.')->redirect('/admin/backup');
        }

        try {
            $db = Database::getInstance();
            $pdo = $db->getPdo();
            $sql = file_get_contents($filepath);
            $pdo->exec($sql);

            return $this->withSuccess('Database restored successfully.')->redirect('/admin/backup');
        } catch (\Throwable $e) {
            return $this->withError('Restore failed: ' . $e->getMessage())->redirect('/admin/backup');
        }
    }

    public function download(string $file): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $backupPath = ROOT_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';
        $filepath = $backupPath . DIRECTORY_SEPARATOR . $file;

        if (!file_exists($filepath) || str_contains($file, '..')) {
            return $this->withError('File not found.')->redirect('/admin/backup');
        }

        $content = file_get_contents($filepath);
        return new Response($content, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $file . '"',
        ]);
    }
}
