<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Response;
use App\Core\Database;
use App\Models\Video;
use App\Models\Channel;

class SearchApiController extends ApiController
{
    public function index(): Response
    {
        $query = $this->request->query('q', '');
        $type  = $this->request->query('type', 'all');
        $page  = $this->getPage();
        $limit = $this->getPerPage();

        if ($query === '') {
            return $this->error('Search query parameter (q) is required.', 422);
        }

        $sanitizedQuery = $this->sanitize($query);
        $db = Database::getInstance();
        $results = ['videos' => [], 'channels' => []];
        $meta    = [];

        if ($type === 'all' || $type === 'videos') {
            $videoQuery = $db->table('videos')
                ->where('visibility', 'public')
                ->where('status', 'published')
                ->whereNull('deleted_at')
                ->where('title', 'LIKE', "%{$sanitizedQuery}%");

            $videoPaginated = $videoQuery
                ->orderBy('view_count', 'DESC')
                ->paginate($limit, $page);

            $results['videos'] = $videoPaginated['data'] ?? [];
            $meta['videos'] = [
                'total'       => $videoPaginated['total'] ?? 0,
                'current_page' => $videoPaginated['current_page'] ?? $page,
                'last_page'   => $videoPaginated['last_page'] ?? 1,
            ];
        }

        if ($type === 'all' || $type === 'channels') {
            $channels = $db->table('channels')
                ->where('name', 'LIKE', "%{$sanitizedQuery}%")
                ->whereNull('deleted_at')
                ->orderBy('subscriber_count', 'DESC')
                ->limit(10)
                ->get();

            $results['channels'] = $channels;
            $meta['channels'] = [
                'total' => count($channels),
            ];
        }

        $meta['query']    = $sanitizedQuery;
        $meta['type']     = $type;
        $meta['per_page'] = $limit;

        return $this->jsonResponse('success', 'Search results.', $results, 200, $meta);
    }

    public function suggestions(): Response
    {
        $query = $this->request->query('q', '');

        if (mb_strlen($query) < 2) {
            return $this->success([], 'Enter at least 2 characters for suggestions.');
        }

        $sanitizedQuery = $this->sanitize($query);
        $db = Database::getInstance();

        $videoTitles = $db->table('videos')
            ->where('visibility', 'public')
            ->whereNull('deleted_at')
            ->where('title', 'LIKE', "%{$sanitizedQuery}%")
            ->select('title')
            ->orderBy('view_count', 'DESC')
            ->limit(5)
            ->get();

        $channelNames = $db->table('channels')
            ->where('name', 'LIKE', "%{$sanitizedQuery}%")
            ->whereNull('deleted_at')
            ->select('name')
            ->orderBy('subscriber_count', 'DESC')
            ->limit(5)
            ->get();

        $suggestions = array_merge(
            array_column($videoTitles, 'title'),
            array_column($channelNames, 'name')
        );

        return $this->success(array_unique($suggestions), 'Suggestions.');
    }
}
