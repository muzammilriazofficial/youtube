<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Full-text search service for videos, channels, and combined results.
 */
class SearchService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Search videos with filters, full-text matching, and sorting.
     */
    public function searchVideos(string $query, array $filters = []): array
    {
        $perPage = $filters['per_page'] ?? 20;
        $page    = $filters['page'] ?? 1;
        $sort    = $filters['sort'] ?? 'relevance';

        $q       = '%' . trim($query) . '%';
        $bindings = [':q' => $q];

        $conditions = [
            '(v.title LIKE :q OR v.description LIKE :q)',
            'v.visibility = :vis',
            'v.deleted_at IS NULL',
        ];
        $bindings[':vis'] = 'public';

        if (!empty($filters['category_id'])) {
            $conditions[]        = 'v.category_id = :cat_id';
            $bindings[':cat_id'] = (int) $filters['category_id'];
        }

        if (!empty($filters['duration'])) {
            $durationMap = [
                'short'  => ['min' => 0, 'max' => 60],
                'medium' => ['min' => 60, 'max' => 600],
                'long'   => ['min' => 600, 'max' => 999999],
            ];
            if (isset($durationMap[$filters['duration']])) {
                $range = $durationMap[$filters['duration']];
                $conditions[]   = 'v.duration BETWEEN :dur_min AND :dur_max';
                $bindings[':dur_min'] = $range['min'];
                $bindings[':dur_max'] = $range['max'];
            }
        }

        if (!empty($filters['date'])) {
            $dateMap = [
                'today'     => 1,
                'this_week' => 7,
                'this_month' => 30,
                'this_year'  => 365,
            ];
            if (isset($dateMap[$filters['date']])) {
                $days = $dateMap[$filters['date']];
                $conditions[]      = 'v.published_at >= :date_from';
                $bindings[':date_from'] = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            }
        }

        $where = implode(' AND ', $conditions);

        $orderByMap = [
            'relevance' => 'MATCH(v.title, v.description) AGAINST(:q2 IN BOOLEAN MODE) DESC, v.view_count DESC',
            'date'      => 'v.published_at DESC',
            'views'     => 'v.view_count DESC',
            'rating'    => 'v.like_count DESC',
        ];
        $orderSql = $orderByMap[$sort] ?? $orderByMap['relevance'];
        $bindings[':q2'] = $q;

        $offset  = ($page - 1) * $perPage;
        $countSql = "SELECT COUNT(*) as aggregate FROM videos v WHERE {$where}";

        $countStmt = $this->db->getPdo()->prepare($countSql);
        $countStmt->execute($bindings);
        $total = (int) $countStmt->fetch()['aggregate'];

        $sql = "SELECT v.*, c.name as channel_name, c.slug as channel_slug, c.avatar as channel_avatar,
                       cat.name as category_name
                FROM videos v
                LEFT JOIN channels c ON v.channel_id = c.id
                LEFT JOIN categories cat ON v.category_id = cat.id
                WHERE {$where}
                ORDER BY {$orderSql}
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->db->getPdo()->prepare($sql);
        $stmt->execute($bindings);
        $results = $stmt->fetchAll();

        $lastPage = (int) ceil($total / $perPage);

        return [
            'data'           => $results,
            'total'          => $total,
            'per_page'       => $perPage,
            'current_page'   => $page,
            'last_page'      => $lastPage,
            'has_more_pages' => $page < $lastPage,
        ];
    }

    /**
     * Search channels by name or description.
     */
    public function searchChannels(string $query, int $limit = 20): array
    {
        $q = '%' . trim($query) . '%';

        return $this->db->table('channels')
            ->where('name', 'LIKE', $q)
            ->orWhere('description', 'LIKE', $q)
            ->whereNull('deleted_at')
            ->orderBy('subscriber_count', 'DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Combined search across videos and channels.
     */
    public function search(string $query, string $type = 'all', array $filters = []): array
    {
        $results = ['videos' => [], 'channels' => [], 'total' => 0];

        if ($type === 'all' || $type === 'videos') {
            $videoResults     = $this->searchVideos($query, $filters);
            $results['videos'] = $videoResults['data'];
            $results['total'] += $videoResults['total'];
        }

        if ($type === 'all' || $type === 'channels') {
            $channelResults       = $this->searchChannels($query, $filters['limit'] ?? 20);
            $results['channels']  = $channelResults;
            $results['total']    += count($channelResults);
        }

        return $results;
    }

    /**
     * Get trending/popular videos for a period and optional category.
     */
    public function getTrendingVideos(string $period = 'today', ?int $categoryId = null, int $limit = 20): array
    {
        $periodMap = [
            'today'     => 1,
            'this_week' => 7,
            'this_month' => 30,
        ];

        $days = $periodMap[$period] ?? 7;
        $dateFrom = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $query = $this->db->table('videos')
            ->where('visibility', 'public')
            ->where('published_at', '>=', $dateFrom)
            ->whereNull('deleted_at');

        if ($categoryId !== null) {
            $query = $query->where('category_id', $categoryId);
        }

        return $query
            ->orderBy('view_count', 'DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Get related videos based on category and tags.
     */
    public function getRelatedVideos(int $videoId, int $limit = 10): array
    {
        $video = $this->db->table('videos')->where('id', $videoId)->first();

        if ($video === null) {
            return [];
        }

        $tagIds = $this->db->table('video_tags')
            ->where('video_id', $videoId)
            ->get();
        $tagIdArray = array_column($tagIds, 'tag_id');

        $query = $this->db->table('videos')
            ->where('id', '!=', $videoId)
            ->where('visibility', 'public')
            ->whereNull('deleted_at');

        if (!empty($tagIdArray)) {
            $query->where('category_id', $video['category_id']);
        }

        $results = $query
            ->orderBy('view_count', 'DESC')
            ->limit($limit * 2)
            ->get();

        if (!empty($tagIdArray) && count($results) < $limit) {
            $extra = $this->db->table('videos')
                ->join('video_tags', 'videos.id', '=', 'video_tags.video_id')
                ->where('video_tags.tag_id', 'IN', $tagIdArray)
                ->where('videos.id', '!=', $videoId)
                ->where('videos.visibility', 'public')
                ->whereNull('videos.deleted_at')
                ->orderBy('videos.view_count', 'DESC')
                ->limit($limit)
                ->get();

            $existingIds = array_column($results, 'id');
            foreach ($extra as $item) {
                if (!in_array($item['id'], $existingIds, true)) {
                    $results[] = $item;
                }
            }
        }

        return array_slice($results, 0, $limit);
    }

    /**
     * Get autocomplete suggestions for search (AJAX).
     */
    public function getAutocompleteSuggestions(string $query, int $limit = 8): array
    {
        $q = '%' . trim($query) . '%';

        $videos = $this->db->table('videos')
            ->select('id', 'title', 'thumbnail', 'view_count')
            ->where('title', 'LIKE', $q)
            ->where('visibility', 'public')
            ->whereNull('deleted_at')
            ->orderBy('view_count', 'DESC')
            ->limit((int) ceil($limit / 2))
            ->get();

        $channels = $this->db->table('channels')
            ->select('id', 'name', 'avatar', 'subscriber_count')
            ->where('name', 'LIKE', $q)
            ->whereNull('deleted_at')
            ->orderBy('subscriber_count', 'DESC')
            ->limit((int) ceil($limit / 2))
            ->get();

        $suggestions = [];

        foreach ($videos as $video) {
            $suggestions[] = [
                'type'       => 'video',
                'id'         => $video['id'],
                'title'      => $video['title'],
                'thumbnail'  => $video['thumbnail'],
                'subtitle'   => format_number((int) $video['view_count']) . ' views',
            ];
        }

        foreach ($channels as $channel) {
            $suggestions[] = [
                'type'       => 'channel',
                'id'         => $channel['id'],
                'title'      => $channel['name'],
                'thumbnail'  => $channel['avatar'],
                'subtitle'   => format_number((int) $channel['subscriber_count']) . ' subscribers',
            ];
        }

        return $suggestions;
    }
}
