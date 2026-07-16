<?php

declare(strict_types=1);

namespace App\Controllers\Moderator;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('moderator', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();

        $pendingVideos = $db->table('videos')
            ->where('status', 'pending')
            ->count();

        $reportedVideos = $db->table('reports')
            ->where('reportable_type', 'video')
            ->where('status', 'pending')
            ->count();

        $reportedComments = $db->table('reports')
            ->where('reportable_type', 'comment')
            ->where('status', 'pending')
            ->count();

        $totalReports = $db->table('reports')
            ->where('status', 'pending')
            ->count();

        $totalViolations = $db->table('violations')->count();

        $recentViolations = $db->table('violations')
            ->join('users', 'violations.taken_by', '=', 'users.id')
            ->leftJoin('videos', 'violations.video_id', '=', 'videos.id')
            ->orderBy('violations.created_at', 'DESC')
            ->limit(10)
            ->get();

        $recentReports = $db->table('reports')
            ->join('users', 'reports.reporter_id', '=', 'users.id')
            ->orderBy('reports.created_at', 'DESC')
            ->limit(10)
            ->get();

        $reportReasons = $db->table('reports')
            ->where('status', 'pending')
            ->select('reason', 'COUNT(*) as count')
            ->groupBy('reason')
            ->orderBy('count', 'DESC')
            ->get();

        return $this->view('moderator.dashboard', [
            'title' => 'Moderator Dashboard',
            'activeMenu' => 'dashboard',
            'pendingVideos' => $pendingVideos,
            'reportedVideos' => $reportedVideos,
            'reportedComments' => $reportedComments,
            'totalReports' => $totalReports,
            'totalViolations' => $totalViolations,
            'recentViolations' => $recentViolations,
            'recentReports' => $recentReports,
            'reportReasons' => $reportReasons,
        ]);
    }
}
