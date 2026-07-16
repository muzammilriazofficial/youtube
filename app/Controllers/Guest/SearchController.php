<?php

declare(strict_types=1);

namespace App\Controllers\Guest;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Database;
use App\Models\Video;
use App\Models\Channel;

class SearchController extends Controller
{
    public function index(): Response
    {
        $query = $this->request->query('q', '');
        $type = $this->request->query('type', 'all');
        $page = (int) $this->request->query('page', 1);
        $sortBy = $this->request->query('sort', 'relevance');
        $duration = $this->request->query('duration', '');
        $uploadDate = $this->request->query('date', '');

        $results = ['videos' => [], 'channels' => []];
        $totalResults = 0;

        if ($query !== '') {
            $db = Database::getInstance();

            if ($type === 'all' || $type === 'videos') {
                $videoQuery = $db->table('videos')
                    ->join('channels', 'videos.channel_id', '=', 'channels.id')
                    ->select('videos.*', 'channels.name as channel_name', 'channels.slug as channel_slug', 'channels.custom_url as channel_custom_url', 'channels.avatar as channel_avatar')
                    ->where('videos.visibility', 'public')
                    ->where('videos.status', 'published')
                    ->whereNull('videos.deleted_at')
                    ->where('videos.title', 'LIKE', "%{$query}%");

                if ($duration === 'short') {
                    $videoQuery = $videoQuery->where('videos.duration', '<', 240);
                } elseif ($duration === 'medium') {
                    $videoQuery = $videoQuery->where('videos.duration', '>=', 240)->where('videos.duration', '<=', 1200);
                } elseif ($duration === 'long') {
                    $videoQuery = $videoQuery->where('videos.duration', '>', 1200);
                }

                if ($uploadDate === 'today') {
                    $videoQuery = $videoQuery->where('videos.published_at', '>=', date('Y-m-d 00:00:00'));
                } elseif ($uploadDate === 'week') {
                    $videoQuery = $videoQuery->where('videos.published_at', '>=', date('Y-m-d H:i:s', strtotime('-7 days')));
                } elseif ($uploadDate === 'month') {
                    $videoQuery = $videoQuery->where('videos.published_at', '>=', date('Y-m-d H:i:s', strtotime('-30 days')));
                } elseif ($uploadDate === 'year') {
                    $videoQuery = $videoQuery->where('videos.published_at', '>=', date('Y-m-d H:i:s', strtotime('-365 days')));
                }

                $sortMap = [
                    'relevance' => ['videos.view_count', 'DESC'],
                    'upload_date' => ['videos.published_at', 'DESC'],
                    'view_count' => ['videos.view_count', 'DESC'],
                ];
                [$sortCol, $sortDir] = $sortMap[$sortBy] ?? $sortMap['relevance'];
                $results['videos'] = $videoQuery->orderBy($sortCol, $sortDir)->paginate(20, $page);
                $totalResults += $results['videos']['total'] ?? 0;
            }

            if ($type === 'all' || $type === 'channels') {
                $results['channels'] = $db->table('channels')
                    ->where('name', 'LIKE', "%{$query}%")
                    ->whereNull('deleted_at')
                    ->orderBy('subscriber_count', 'DESC')
                    ->limit(10)
                    ->get();
                $totalResults += count($results['channels']);
            }
        }

        return $this->view('guest.search', [
            'title' => $query ? "Search: {$query}" : 'Search',
            'query' => $query,
            'results' => $results,
            'totalResults' => $totalResults,
            'currentType' => $type,
            'currentSort' => $sortBy,
            'currentDuration' => $duration,
            'currentDate' => $uploadDate,
        ]);
    }
}
