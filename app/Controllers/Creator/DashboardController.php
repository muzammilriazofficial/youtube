<?php

declare(strict_types=1);

namespace App\Controllers\Creator;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Video;
use App\Models\Channel;
use App\Models\Comment;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $videoModel = new Video();
        $commentModel = new Comment();

        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            $this->session->flash('error', 'You need to create a channel first.');
            return $this->redirect('/creator/channel/create');
        }

        $channelId = (int) $channel['id'];

        $totalVideos = $videoModel->db->table('videos')
            ->where('channel_id', $channelId)
            ->whereNull('deleted_at')
            ->count();

        $totalViews = (int) $videoModel->db->table('videos')
            ->where('channel_id', $channelId)
            ->where('visibility', 'public')
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->sum('view_count');
        $totalSubscribers = (int) $channel['subscriber_count'];

        $totalComments = $commentModel->db->table('comments')
            ->join('videos', 'comments.video_id', '=', 'videos.id')
            ->where('videos.channel_id', $channelId)
            ->whereNull('comments.deleted_at')
            ->count();

        $recentVideos = $videoModel->db->table('videos')
            ->where('channel_id', $channelId)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get();

        $recentComments = $commentModel->db->table('comments')
            ->join('videos', 'comments.video_id', '=', 'videos.id')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->where('videos.channel_id', $channelId)
            ->whereNull('comments.deleted_at')
            ->orderBy('comments.created_at', 'DESC')
            ->limit(10)
            ->get();

        $last30Days = date('Y-m-d H:i:s', strtotime('-30 days'));
        $viewsChart = $videoModel->db->table('video_views')
            ->join('videos', 'video_views.video_id', '=', 'videos.id')
            ->where('videos.channel_id', $channelId)
            ->where('video_views.watched_at', '>=', $last30Days)
            ->select('DATE(video_views.watched_at) as date', 'COUNT(*) as views')
            ->groupBy('DATE(video_views.watched_at)')
            ->orderBy('date', 'ASC')
            ->get();

        $revenue = $videoModel->db->table('earnings')
            ->where('channel_id', $channelId)
            ->sum('amount');

        return $this->view('creator.dashboard', [
            'title' => 'Creator Dashboard',
            'activeMenu' => 'dashboard',
            'channel' => $channel,
            'totalVideos' => $totalVideos,
            'totalViews' => $totalViews,
            'totalSubscribers' => $totalSubscribers,
            'totalComments' => $totalComments,
            'recentVideos' => $recentVideos,
            'recentComments' => $recentComments,
            'viewsChart' => $viewsChart,
            'revenue' => $revenue,
        ]);
    }
}
