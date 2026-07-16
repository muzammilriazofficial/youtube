<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Advertisement;

/**
 * Ad serving service for managing video and display advertisements.
 */
class AdService
{
    private Database $db;
    private Advertisement $adModel;

    public function __construct()
    {
        $this->db     = Database::getInstance();
        $this->adModel = new Advertisement();
    }

    /**
     * Get an appropriate ad for a video placement position.
     */
    public function getAdForVideo(int $videoId, string $position = 'pre_roll'): ?array
    {
        $now = date('Y-m-d H:i:s');

        $typeMap = [
            'pre_roll'  => 'video',
            'mid_roll'  => 'video',
            'post_roll' => 'video',
            'overlay'   => 'overlay',
            'banner'    => 'banner',
        ];

        $adType = $typeMap[$position] ?? 'video';

        $ads = $this->db->table('advertisements')
            ->where('status', 'active')
            ->where('type', $adType)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->orderBy('impressions', 'ASC')
            ->limit(10)
            ->get();

        if (empty($ads)) {
            return null;
        }

        usort($ads, fn($a, $b) => (int) $a['impressions'] <=> (int) $b['impressions']);

        return $ads[0];
    }

    /**
     * Get a banner/display ad for a page.
     */
    public function getBannerAd(string $page): ?array
    {
        $now = date('Y-m-d H:i:s');

        $ads = $this->db->table('advertisements')
            ->where('status', 'active')
            ->where('type', 'banner')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->orderBy('impressions', 'ASC')
            ->limit(5)
            ->get();

        if (empty($ads)) {
            return null;
        }

        $adIndex = crc32($page . date('Ymd')) % count($ads);

        return $ads[$adIndex];
    }

    /**
     * Record an ad impression.
     */
    public function recordImpression(int $adId, int $videoId, string $position = 'pre_roll'): bool
    {
        $result = $this->adModel->recordImpression($adId);

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
     * Record an ad click.
     */
    public function recordClick(int $adId): bool
    {
        $result = $this->adModel->recordClick($adId);

        if ($result) {
            $this->db->table('ad_clicks')->insert([
                'ad_id'      => $adId,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $result;
    }

    /**
     * Determine if an ad should be shown for a video at a given position.
     */
    public function shouldShowAd(int $videoId, string $position): bool
    {
        $video = $this->db->table('videos')
            ->where('id', $videoId)
            ->first();

        if ($video === null) {
            return false;
        }

        $channel = $this->db->table('channels')
            ->where('id', $video['channel_id'])
            ->first();

        if ($channel !== null && !empty($channel['is_partner'])) {
            $viewCount = (int) $video['view_count'];
            if ($viewCount < 100) {
                return false;
            }
        }

        if ($position === 'mid_roll') {
            $duration = (int) ($video['duration'] ?? 0);
            if ($duration < 300) {
                return false;
            }
        }

        $recentImpression = $this->db->table('ad_impressions')
            ->where('video_id', $videoId)
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($recentImpression !== null) {
            $diff = time() - strtotime($recentImpression['created_at']);
            if ($diff < 30) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get ad performance statistics.
     */
    public function getAdStats(int $adId): array
    {
        $ad = $this->adModel->find($adId);
        if ($ad === null) {
            return [];
        }

        $impressions = (int) $ad['impressions'];
        $clicks      = (int) $ad['clicks'];
        $ctr         = $impressions > 0 ? round($clicks / $impressions * 100, 2) : 0;

        $dailyStats = $this->db->raw(
            "SELECT DATE(created_at) as date, COUNT(*) as impressions
             FROM ad_impressions WHERE ad_id = :ad_id
             GROUP BY DATE(created_at) ORDER BY date ASC",
            [':ad_id' => $adId]
        );

        return [
            'ad_id'        => $adId,
            'impressions'  => $impressions,
            'clicks'       => $clicks,
            'ctr'          => $ctr,
            'spend'        => (float) $ad['spend'],
            'daily_stats'  => $dailyStats->fetchAll(),
        ];
    }
}
