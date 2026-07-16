<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Video;
use App\Models\VideoView;
use App\Models\Channel;
use App\Models\Advertisement;

/**
 * Analytics computation service for video, channel, audience, and revenue data.
 */
class AnalyticsService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get statistics for a single video.
     */
    public function getVideoStats(int $videoId): array
    {
        $videoModel = new Video();
        $video      = $videoModel->find($videoId);

        if ($video === null) {
            throw new \RuntimeException("Video not found: {$videoId}");
        }

        $viewModel = new VideoView();
        $totalViews      = $viewModel->getViewCount($videoId);
        $uniqueViews     = $viewModel->getUniqueViewCount($videoId);

        $avgResult = $this->db->table('video_views')
            ->where('video_id', $videoId)
            ->select('AVG(watch_duration) as avg_duration', 'SUM(watch_duration) as total_watch')
            ->first();

        $avgDuration    = (float) ($avgResult['avg_duration'] ?? 0);
        $totalWatchTime = (float) ($avgResult['total_watch'] ?? 0);

        $likes    = (int) $video['like_count'];
        $dislikes = (int) $video['dislike_count'];
        $likeRatio = ($likes + $dislikes) > 0
            ? round($likes / ($likes + $dislikes) * 100, 1)
            : 0;

        $duration = (int) $video['duration'];
        $avgViewDuration = $duration > 0
            ? round($avgDuration / $duration * 100, 1)
            : 0;

        return [
            'video_id'           => $videoId,
            'title'              => $video['title'],
            'total_views'        => $totalViews,
            'unique_views'       => $uniqueViews,
            'likes'              => $likes,
            'dislikes'           => $dislikes,
            'like_ratio'         => $likeRatio,
            'comments'           => (int) $video['comment_count'],
            'avg_view_duration'  => round($avgDuration),
            'avg_view_percentage' => $avgViewDuration,
            'total_watch_time'   => (int) $totalWatchTime,
            'engagement_rate'    => $totalViews > 0
                ? round(($likes + (int) $video['comment_count']) / $totalViews * 100, 2)
                : 0,
        ];
    }

    /**
     * Get channel-level statistics.
     */
    public function getChannelStats(int $channelId): array
    {
        $channelModel = new Channel();
        $channel      = $channelModel->find($channelId);

        if ($channel === null) {
            throw new \RuntimeException("Channel not found: {$channelId}");
        }

        $videoCount = $this->db->table('videos')
            ->where('channel_id', $channelId)
            ->where('visibility', 'public')
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->count();

        $totalViews = (int) $this->db->table('videos')
            ->where('channel_id', $channelId)
            ->where('visibility', 'public')
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->sum('view_count');
        $subscribers = (int) $channel['subscriber_count'];

        $recentVideos = $this->db->table('videos')
            ->where('channel_id', $channelId)
            ->whereNull('deleted_at')
            ->orderBy('published_at', 'DESC')
            ->limit(10)
            ->get();

        $avgViewsPerVideo = $videoCount > 0 ? round($totalViews / $videoCount) : 0;

        return [
            'channel_id'         => $channelId,
            'name'               => $channel['name'],
            'subscribers'        => $subscribers,
            'total_views'        => $totalViews,
            'video_count'        => $videoCount,
            'avg_views_per_video' => $avgViewsPerVideo,
            'is_verified'        => (bool) $channel['is_verified'],
            'is_partner'         => (bool) $channel['is_partner'],
            'recent_video_count' => count($recentVideos),
        ];
    }

    /**
     * Get dashboard overview stats for a channel.
     */
    public function getDashboardStats(int $channelId): array
    {
        $channelStats = $this->getChannelStats($channelId);

        $last30Days = date('Y-m-d H:i:s', strtotime('-30 days'));
        $last7Days  = date('Y-m-d H:i:s', strtotime('-7 days'));

        $viewsLast30 = $this->db->table('video_views')
            ->join('videos', 'video_views.video_id', '=', 'videos.id')
            ->where('videos.channel_id', $channelId)
            ->where('video_views.created_at', '>=', $last30Days)
            ->count();

        $viewsLast7 = $this->db->table('video_views')
            ->join('videos', 'video_views.video_id', '=', 'videos.id')
            ->where('videos.channel_id', $channelId)
            ->where('video_views.created_at', '>=', $last7Days)
            ->count();

        $subscribersLast30 = $this->db->table('subscriptions')
            ->where('channel_id', $channelId)
            ->where('created_at', '>=', $last30Days)
            ->count();

        $topVideo = $this->db->table('videos')
            ->where('channel_id', $channelId)
            ->whereNull('deleted_at')
            ->orderBy('view_count', 'DESC')
            ->limit(1)
            ->first();

        return array_merge($channelStats, [
            'views_last_30_days'    => $viewsLast30,
            'views_last_7_days'     => $viewsLast7,
            'new_subscribers_30_days' => $subscribersLast30,
            'top_video'             => $topVideo,
        ]);
    }

    /**
     * Get time-series video analytics data.
     */
    public function getVideoAnalytics(int $channelId, string $startDate, string $endDate): array
    {
        $daily = $this->db->raw(
            "SELECT DATE(video_views.created_at) as date,
                    COUNT(*) as views,
                    AVG(video_views.watch_duration) as avg_watch_duration,
                    COUNT(DISTINCT video_views.user_id) as unique_viewers
             FROM video_views
             INNER JOIN videos ON video_views.video_id = videos.id
             WHERE videos.channel_id = :ch_id
               AND video_views.created_at BETWEEN :start AND :end
             GROUP BY DATE(video_views.created_at)
             ORDER BY date ASC",
            [':ch_id' => $channelId, ':start' => $startDate, ':end' => $endDate]
        );

        $data = $daily->fetchAll();

        $videoPerformance = $this->db->raw(
            "SELECT videos.id, videos.title, videos.view_count, videos.like_count,
                    videos.published_at
             FROM videos
             WHERE videos.channel_id = :ch_id
               AND videos.published_at BETWEEN :start AND :end
               AND videos.deleted_at IS NULL
             ORDER BY videos.view_count DESC
             LIMIT 10",
            [':ch_id' => $channelId, ':start' => $startDate, ':end' => $endDate]
        );

        return [
            'daily'              => $data,
            'video_performance'  => $videoPerformance->fetchAll(),
        ];
    }

    /**
     * Get audience demographics, devices, and geography stats.
     */
    public function getAudienceStats(int $channelId): array
    {
        $devices = $this->db->raw(
            "SELECT
                CASE
                    WHEN video_views.user_agent LIKE '%Mobile%' OR video_views.user_agent LIKE '%Android%' OR video_views.user_agent LIKE '%iPhone%' THEN 'Mobile'
                    WHEN video_views.user_agent LIKE '%Windows%' THEN 'Desktop'
                    WHEN video_views.user_agent LIKE '%Macintosh%' OR video_views.user_agent LIKE '%Mac OS%' THEN 'Mac'
                    WHEN video_views.user_agent LIKE '%Linux%' THEN 'Linux'
                    ELSE 'Other'
                END as device_type,
                COUNT(*) as count
             FROM video_views
             INNER JOIN videos ON video_views.video_id = videos.id
             WHERE videos.channel_id = :ch_id
             GROUP BY device_type
             ORDER BY count DESC",
            [':ch_id' => $channelId]
        );

        $returningViewers = $this->db->raw(
            "SELECT
                CASE
                    WHEN view_count > 1 THEN 'returning'
                    ELSE 'new'
                END as viewer_type,
                COUNT(*) as count
             FROM (
                SELECT user_id, COUNT(*) as view_count
                FROM video_views
                INNER JOIN videos ON video_views.video_id = videos.id
                WHERE videos.channel_id = :ch_id AND video_views.user_id IS NOT NULL
                GROUP BY user_id
             ) as viewer_counts",
            [':ch_id' => $channelId]
        );

        return [
            'devices'           => $devices->fetchAll(),
            'viewer_types'      => $returningViewers->fetchAll(),
        ];
    }

    /**
     * Get revenue breakdown stats for a channel.
     */
    public function getRevenueStats(int $channelId, string $startDate, string $endDate): array
    {
        $adRevenue = $this->db->raw(
            "SELECT
                DATE(video_views.created_at) as date,
                COUNT(*) as impressions,
                SUM(CASE WHEN advertisements.type = 'video' THEN 1 ELSE 0 END) as video_ads,
                SUM(CASE WHEN advertisements.type = 'banner' THEN 1 ELSE 0 END) as banner_ads
             FROM video_views
             INNER JOIN videos ON video_views.video_id = videos.id
             LEFT JOIN advertisements ON 1=0
             WHERE videos.channel_id = :ch_id
               AND video_views.created_at BETWEEN :start AND :end
             GROUP BY DATE(video_views.created_at)
             ORDER BY date ASC",
            [':ch_id' => $channelId, ':start' => $startDate, ':end' => $endDate]
        );

        return [
            'daily_revenue'  => $adRevenue->fetchAll(),
            'currency'       => 'USD',
        ];
    }

    /**
     * Record a video view with deduplication.
     */
    public function recordView(
        int $videoId,
        ?int $userId,
        string $ip,
        string $userAgent,
        int $watchDuration = 0
    ): bool {
        $viewModel = new VideoView();
        return $viewModel->recordView($videoId, $userId, $ip, $userAgent);
    }

    /**
     * Track an ad impression.
     */
    public function trackImpression(int $adId, int $videoId, string $position = 'pre_roll'): bool
    {
        $adModel = new Advertisement();
        $result  = $adModel->recordImpression($adId);

        if ($result) {
            $this->db->table('ad_impressions')->insert([
                'ad_id'       => $adId,
                'video_id'    => $videoId,
                'position'    => $position,
                'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        return $result;
    }

    /**
     * Track an ad click.
     */
    public function trackClick(int $adId, int $videoId): bool
    {
        $adModel = new Advertisement();
        $result  = $adModel->recordClick($adId);

        if ($result) {
            $this->db->table('ad_clicks')->insert([
                'ad_id'      => $adId,
                'video_id'   => $videoId,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $result;
    }
}
