<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class AnalyticsController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();

        $totalViews = (int) $db->table('video_views')->count();
        $totalLikes = (int) $db->table('video_likes')->count();
        $totalComments = (int) $db->table('comments')->count();
        $totalSubscriptions = (int) $db->table('subscriptions')->count();

        $todayViews = $db->table('video_views')
            ->where('created_at', '>=', date('Y-m-d 00:00:00'))
            ->count();

        $todaySignups = $db->table('users')
            ->where('created_at', '>=', date('Y-m-d 00:00:00'))
            ->count();

        $topVideos = $db->table('videos')
            ->where('visibility', 'public')
            ->orderBy('view_count', 'DESC')
            ->limit(10)
            ->get();

        $topChannels = $db->table('channels')
            ->orderBy('subscriber_count', 'DESC')
            ->limit(10)
            ->get();

        return $this->view('admin.analytics', [
            'title' => 'Analytics',
            'activeMenu' => 'analytics',
            'totalViews' => $totalViews,
            'totalLikes' => $totalLikes,
            'totalComments' => $totalComments,
            'totalSubscriptions' => $totalSubscriptions,
            'todayViews' => $todayViews,
            'todaySignups' => $todaySignups,
            'topVideos' => $topVideos,
            'topChannels' => $topChannels,
        ]);
    }

    public function getData(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $type = $this->request->input('type', 'views');
        $db = Database::getInstance();

        switch ($type) {
            case 'views':
                $labels = [];
                $data = [];
                for ($i = 29; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-{$i} days"));
                    $labels[] = date('M d', strtotime($date));
                    $count = $db->table('video_views')
                        ->where('created_at', '>=', $date . ' 00:00:00')
                        ->where('created_at', '<=', $date . ' 23:59:59')
                        ->count();
                    $data[] = $count;
                }
                return $this->json(['labels' => $labels, 'data' => $data]);

            case 'signups':
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

            case 'engagement':
                $labels = [];
                $likes = [];
                $comments = [];
                for ($i = 6; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-{$i} days"));
                    $labels[] = date('D', strtotime($date));
                    $likes[] = $db->table('video_likes')
                        ->where('created_at', '>=', $date . ' 00:00:00')
                        ->where('created_at', '<=', $date . ' 23:59:59')
                        ->count();
                    $comments[] = $db->table('comments')
                        ->where('created_at', '>=', $date . ' 00:00:00')
                        ->where('created_at', '<=', $date . ' 23:59:59')
                        ->count();
                }
                return $this->json(['labels' => $labels, 'likes' => $likes, 'comments' => $comments]);

            default:
                return $this->json(['error' => 'Invalid type'], 400);
        }
    }
}
