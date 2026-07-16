<?php

declare(strict_types=1);

namespace App\Controllers\Guest;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Video;

class TrendingController extends Controller
{
    public function index(): Response
    {
        $db = \App\Core\Database::getInstance();
        $page = (int) $this->request->query('page', 1);
        $category = $this->request->query('category', '');

        $query = $db->table('videos')
            ->join('channels', 'videos.channel_id', '=', 'channels.id')
            ->select('videos.*', 'channels.name as channel_name', 'channels.slug as channel_slug', 'channels.custom_url as channel_custom_url', 'channels.avatar as channel_avatar')
            ->where('videos.visibility', 'public')
            ->where('videos.status', 'published');

        if ($category !== '') {
            $categoryModel = new \App\Models\Category();
            $cat = $categoryModel->findBySlug($category);
            if ($cat) {
                $query = $query->where('videos.category_id', (int) $cat['id']);
            }
        }

        $trending = $query->orderBy('videos.view_count', 'DESC')
            ->paginate(24, $page);

        $categories = (new \App\Models\Category())->getActive();

        return $this->view('guest.trending', [
            'title' => 'Trending',
            'trending' => $trending,
            'categories' => $categories,
            'currentCategory' => $category,
        ]);
    }
}
