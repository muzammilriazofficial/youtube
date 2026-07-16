<?php

declare(strict_types=1);

namespace App\Controllers\Reviewer;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('reviewer', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();

        $pendingUploads = $db->table('videos')
            ->where('status', 'pending')
            ->count();

        $pendingCopyright = $db->table('copyright_claims')
            ->where('status', 'pending')
            ->count();

        $monetizationApps = $db->table('monetization_settings')
            ->where('is_enabled', 0)
            ->whereNotNull('application_date')
            ->where('is_eligible', 0)
            ->count();

        $totalAppeals = $db->table('reports')
            ->where('status', 'pending')
            ->count();

        $recentUploads = $db->table('videos')
            ->join('channels', 'videos.channel_id', '=', 'channels.id')
            ->join('users', 'channels.user_id', '=', 'users.id')
            ->where('videos.status', 'pending')
            ->whereNull('videos.deleted_at')
            ->orderBy('videos.created_at', 'DESC')
            ->limit(10)
            ->get();

        $recentCopyright = $db->table('copyright_claims')
            ->join('videos', 'copyright_claims.video_id', '=', 'videos.id')
            ->where('copyright_claims.status', 'pending')
            ->orderBy('copyright_claims.created_at', 'DESC')
            ->limit(5)
            ->get();

        $recentMonetization = $db->table('monetization_settings')
            ->join('channels', 'monetization_settings.channel_id', '=', 'channels.id')
            ->join('users', 'channels.user_id', '=', 'users.id')
            ->where('monetization_settings.is_enabled', 0)
            ->whereNotNull('monetization_settings.application_date')
            ->orderBy('monetization_settings.created_at', 'DESC')
            ->limit(5)
            ->get();

        return $this->view('reviewer.dashboard', [
            'title' => 'Reviewer Dashboard',
            'activeMenu' => 'dashboard',
            'pendingUploads' => $pendingUploads,
            'pendingCopyright' => $pendingCopyright,
            'monetizationApps' => $monetizationApps,
            'totalAppeals' => $totalAppeals,
            'recentUploads' => $recentUploads,
            'recentCopyright' => $recentCopyright,
            'recentMonetization' => $recentMonetization,
        ]);
    }
}
