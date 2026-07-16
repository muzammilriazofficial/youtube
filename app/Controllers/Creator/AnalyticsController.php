<?php

declare(strict_types=1);

namespace App\Controllers\Creator;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Video;
use App\Models\Channel;

class AnalyticsController extends Controller
{
    public function index(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            $this->session->flash('error', 'Channel not found.');
            return $this->redirect('/');
        }

        $channelId = (int) $channel['id'];
        $days = (int) $this->request->input('days', 28);
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        $totalViews = $this->getViewCount($channelId, $startDate);
        $totalWatchTime = $this->getWatchTime($channelId, $startDate);
        $newSubscribers = $this->getNewSubscribers($channelId, $startDate);
        $estimatedRevenue = $this->getRevenue($channelId, $startDate);

        $viewsOverTime = $this->getViewsOverTime($channelId, $startDate);
        $topVideos = $this->getTopVideos($channelId, $startDate, 10);

        return $this->view('creator.analytics', [
            'title' => 'Analytics Overview',
            'activeMenu' => 'analytics',
            'channel' => $channel,
            'totalViews' => $totalViews,
            'totalWatchTime' => $totalWatchTime,
            'newSubscribers' => $newSubscribers,
            'estimatedRevenue' => $estimatedRevenue,
            'viewsOverTime' => $viewsOverTime,
            'topVideos' => $topVideos,
            'days' => $days,
        ]);
    }

    public function videos(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            $this->session->flash('error', 'Channel not found.');
            return $this->redirect('/');
        }

        $channelId = (int) $channel['id'];
        $page = max(1, (int) $this->request->input('page', 1));

        $result = (new Video())->db->table('videos')
            ->where('channel_id', $channelId)
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->orderBy('view_count', 'DESC')
            ->paginate(20, $page);

        foreach ($result['data'] as &$video) {
            $video['avg_view_duration'] = $this->getAvgViewDuration((int) $video['id']);
            $video['ctr'] = $this->getVideoCTR((int) $video['id']);
        }

        return $this->view('creator.analytics-videos', [
            'title' => 'Video Analytics',
            'activeMenu' => 'analytics',
            'videos' => $result['data'],
            'pagination' => $result,
            'channel' => $channel,
        ]);
    }

    public function revenue(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            $this->session->flash('error', 'Channel not found.');
            return $this->redirect('/');
        }

        $channelId = (int) $channel['id'];
        $days = (int) $this->request->input('days', 28);
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        $totalRevenue = $this->getRevenue($channelId, $startDate);
        $revenueByVideo = $this->getRevenueByVideo($channelId, $startDate);
        $revenueOverTime = $this->getRevenueOverTime($channelId, $startDate);

        return $this->view('creator.analytics-revenue', [
            'title' => 'Revenue Analytics',
            'activeMenu' => 'analytics',
            'channel' => $channel,
            'totalRevenue' => $totalRevenue,
            'revenueByVideo' => $revenueByVideo,
            'revenueOverTime' => $revenueOverTime,
            'days' => $days,
        ]);
    }

    public function audience(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            $this->session->flash('error', 'Channel not found.');
            return $this->redirect('/');
        }

        $channelId = (int) $channel['id'];

        $demographics = $this->getDemographics($channelId);
        $geography = $this->getGeography($channelId);
        $devices = $this->getDevices($channelId);
        $genderSplit = $this->getGenderSplit($channelId);

        return $this->view('creator.analytics-audience', [
            'title' => 'Audience Analytics',
            'activeMenu' => 'analytics',
            'channel' => $channel,
            'demographics' => $demographics,
            'geography' => $geography,
            'devices' => $devices,
            'genderSplit' => $genderSplit,
        ]);
    }

    public function getData(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            return $this->json(['error' => 'Channel not found'], 404);
        }

        $channelId = (int) $channel['id'];
        $metric = $this->request->input('metric', 'views');
        $days = (int) $this->request->input('days', 28);
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        $data = match ($metric) {
            'views' => $this->getViewsOverTime($channelId, $startDate),
            'subscribers' => $this->getSubscriberGrowth($channelId, $startDate),
            'revenue' => $this->getRevenueOverTime($channelId, $startDate),
            'watch_time' => $this->getWatchTimeOverTime($channelId, $startDate),
            default => [],
        };

        return $this->json([
            'labels' => array_column($data, 'date'),
            'values' => array_column($data, 'value'),
        ]);
    }

    private function getViewCount(int $channelId, string $startDate): int
    {
        return (int) (new Video())->db->table('video_views')
            ->join('videos', 'video_views.video_id', '=', 'videos.id')
            ->where('videos.channel_id', $channelId)
            ->where('video_views.watched_at', '>=', $startDate)
            ->count();
    }

    private function getWatchTime(int $channelId, string $startDate): float
    {
        return (float) (new Video())->db->table('video_views')
            ->join('videos', 'video_views.video_id', '=', 'videos.id')
            ->where('videos.channel_id', $channelId)
            ->where('video_views.watched_at', '>=', $startDate)
            ->sum('watch_duration');
    }

    private function getNewSubscribers(int $channelId, string $startDate): int
    {
        return (int) (new Channel())->db->table('subscriptions')
            ->where('channel_id', $channelId)
            ->where('created_at', '>=', $startDate)
            ->count();
    }

    private function getRevenue(int $channelId, string $startDate): float
    {
        return (float) (new Channel())->db->table('earnings')
            ->where('channel_id', $channelId)
            ->where('earned_at', '>=', $startDate)
            ->sum('amount');
    }

    private function getViewsOverTime(int $channelId, string $startDate): array
    {
        return (new Video())->db->table('video_views')
            ->join('videos', 'video_views.video_id', '=', 'videos.id')
            ->where('videos.channel_id', $channelId)
            ->where('video_views.watched_at', '>=', $startDate)
            ->select('DATE(video_views.watched_at) as date', 'COUNT(*) as value')
            ->groupBy('DATE(video_views.watched_at)')
            ->orderBy('date', 'ASC')
            ->get();
    }

    private function getSubscriberGrowth(int $channelId, string $startDate): array
    {
        return (new Channel())->db->table('subscriptions')
            ->where('channel_id', $channelId)
            ->where('created_at', '>=', $startDate)
            ->select('DATE(created_at) as date', 'COUNT(*) as value')
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->get();
    }

    private function getRevenueOverTime(int $channelId, string $startDate): array
    {
        return (new Channel())->db->table('earnings')
            ->where('channel_id', $channelId)
            ->where('earned_at', '>=', $startDate)
            ->select('DATE(earned_at) as date', 'SUM(amount) as value')
            ->groupBy('DATE(earned_at)')
            ->orderBy('date', 'ASC')
            ->get();
    }

    private function getWatchTimeOverTime(int $channelId, string $startDate): array
    {
        return (new Video())->db->table('video_views')
            ->join('videos', 'video_views.video_id', '=', 'videos.id')
            ->where('videos.channel_id', $channelId)
            ->where('video_views.watched_at', '>=', $startDate)
            ->select('DATE(video_views.watched_at) as date', 'SUM(video_views.watch_duration) as value')
            ->groupBy('DATE(video_views.watched_at)')
            ->orderBy('date', 'ASC')
            ->get();
    }

    private function getTopVideos(int $channelId, string $startDate, int $limit): array
    {
        return (new Video())->db->table('videos')
            ->where('channel_id', $channelId)
            ->where('status', 'published')
            ->where('published_at', '>=', $startDate)
            ->whereNull('deleted_at')
            ->orderBy('view_count', 'DESC')
            ->limit($limit)
            ->get();
    }

    private function getRevenueByVideo(int $channelId, string $startDate): array
    {
        return (new Video())->db->table('earnings')
            ->join('videos', 'earnings.video_id', '=', 'videos.id')
            ->where('videos.channel_id', $channelId)
            ->where('earnings.earned_at', '>=', $startDate)
            ->select('videos.title', 'SUM(earnings.amount) as revenue')
            ->groupBy('videos.id', 'videos.title')
            ->orderBy('revenue', 'DESC')
            ->limit(10)
            ->get();
    }

    private function getAvgViewDuration(int $videoId): float
    {
        return (float) (new Video())->db->table('video_views')
            ->where('video_id', $videoId)
            ->avg('watch_duration');
    }

    private function getVideoCTR(int $videoId): float
    {
        $impressions = (int) (new Video())->db->table('video_impressions')
            ->where('video_id', $videoId)
            ->count();

        if ($impressions === 0) {
            return 0.0;
        }

        $views = (int) (new Video())->db->table('videos')
            ->where('id', $videoId)
            ->value('view_count');

        return round(($views / $impressions) * 100, 2);
    }

    private function getDemographics(int $channelId): array
    {
        return (new Channel())->db->table('audience_demographics')
            ->where('channel_id', $channelId)
            ->select('age_range', 'COUNT(*) as count')
            ->groupBy('age_range')
            ->get();
    }

    private function getGeography(int $channelId): array
    {
        return (new Channel())->db->table('audience_geography')
            ->where('channel_id', $channelId)
            ->select('country', 'COUNT(*) as views')
            ->groupBy('country')
            ->orderBy('views', 'DESC')
            ->limit(10)
            ->get();
    }

    private function getDevices(int $channelId): array
    {
        return (new Channel())->db->table('audience_devices')
            ->where('channel_id', $channelId)
            ->select('device_type', 'COUNT(*) as count')
            ->groupBy('device_type')
            ->get();
    }

    private function getGenderSplit(int $channelId): array
    {
        return (new Channel())->db->table('audience_demographics')
            ->where('channel_id', $channelId)
            ->select('gender', 'COUNT(*) as count')
            ->groupBy('gender')
            ->get();
    }
}
