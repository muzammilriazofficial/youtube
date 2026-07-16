<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();

        $totalUsers = $db->table('users')->count();
        $totalVideos = $db->table('videos')->count();
        $totalChannels = $db->table('channels')->count();

        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t 23:59:59');
        $revenueThisMonth = (float) $db->table('payouts')
            ->where('status', 'completed')
            ->whereBetween('created_at', $startOfMonth, $endOfMonth)
            ->sum('amount');

        $recentUsers = $db->table('users')
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        $recentVideos = $db->table('videos')
            ->join('channels', 'videos.channel_id', '=', 'channels.id')
            ->join('users', 'channels.user_id', '=', 'users.id')
            ->orderBy('videos.created_at', 'DESC')
            ->limit(10)
            ->get();

        $pendingVideos = $db->table('videos')->where('status', 'pending')->count();
        $pendingReports = $db->table('reports')->where('status', 'pending')->count();
        $pendingPayouts = $db->table('payouts')->where('status', 'pending')->count();

        $dbSize = $this->getDatabaseSize();
        $diskFree = $this->getDiskFreeSpace();

        return $this->view('admin.dashboard', [
            'title' => 'Admin Dashboard',
            'activeMenu' => 'dashboard',
            'totalUsers' => $totalUsers,
            'totalVideos' => $totalVideos,
            'totalChannels' => $totalChannels,
            'revenueThisMonth' => $revenueThisMonth,
            'recentUsers' => $recentUsers,
            'recentVideos' => $recentVideos,
            'pendingVideos' => $pendingVideos,
            'pendingReports' => $pendingReports,
            'pendingPayouts' => $pendingPayouts,
            'dbSize' => $dbSize,
            'diskFree' => $diskFree,
        ]);
    }

    public function getChartData(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $type = $this->request->input('type', 'user_growth');
        $db = Database::getInstance();

        switch ($type) {
            case 'user_growth':
                $labels = [];
                $data = [];
                for ($i = 29; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-{$i} days"));
                    $labels[] = date('M d', strtotime($date));
                    $count = $db->table('users')
                        ->where('created_at', '>=', $date . ' 00:00:00')
                        ->where('created_at', '<=', $date . ' 23:59:59')
                        ->count();
                    $data[] = $count;
                }
                return $this->json(['labels' => $labels, 'data' => $data]);

            case 'video_uploads':
                $labels = [];
                $data = [];
                for ($i = 29; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-{$i} days"));
                    $labels[] = date('M d', strtotime($date));
                    $count = $db->table('videos')
                        ->where('created_at', '>=', $date . ' 00:00:00')
                        ->where('created_at', '<=', $date . ' 23:59:59')
                        ->count();
                    $data[] = $count;
                }
                return $this->json(['labels' => $labels, 'data' => $data]);

            case 'revenue':
                $labels = [];
                $data = [];
                for ($i = 11; $i >= 0; $i--) {
                    $monthStart = date('Y-m-01', strtotime("-{$i} months"));
                    $monthEnd = date('Y-m-t 23:59:59', strtotime("-{$i} months"));
                    $labels[] = date('M Y', strtotime($monthStart));
                    $sum = (float) $db->table('payouts')
                        ->where('status', 'completed')
                        ->whereBetween('created_at', $monthStart, $monthEnd)
                        ->sum('amount');
                    $data[] = $sum;
                }
                return $this->json(['labels' => $labels, 'data' => $data]);

            default:
                return $this->json(['error' => 'Invalid chart type'], 400);
        }
    }

    private function getDatabaseSize(): string
    {
        try {
            $db = Database::getInstance();
            $result = $db->raw("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size FROM information_schema.tables WHERE table_schema = DATABASE()")->fetch();
            return ($result['size'] ?? 0) . ' MB';
        } catch (\Throwable $e) {
            return 'N/A';
        }
    }

    private function getDiskFreeSpace(): string
    {
        $free = @disk_free_space(ROOT_PATH ?: __DIR__);
        if ($free === false) {
            return 'N/A';
        }
        return round($free / 1024 / 1024 / 1024, 2) . ' GB';
    }
}
