<?php

declare(strict_types=1);

namespace App\Controllers\Guest;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Category;
use App\Models\Video;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categoryModel = new Category();
        $categories = $categoryModel->getActive();

        return $this->view('guest.categories', [
            'title' => 'Categories',
            'categories' => $categories,
        ]);
    }

    public function show(string $slug): Response
    {
        $categoryModel = new Category();
        $category = $categoryModel->findBySlug($slug);

        if ($category === null) {
            return $this->view('errors.404', ['title' => 'Not Found'], 404);
        }

        $videoModel = new Video();
        $page = (int) $this->request->query('page', 1);
        $sort = $this->request->query('sort', 'latest');

        $sortMap = [
            'latest' => ['videos.published_at', 'DESC'],
            'popular' => ['videos.view_count', 'DESC'],
        ];
        [$sortCol, $sortDir] = $sortMap[$sort] ?? $sortMap['latest'];

        $db = \App\Core\Database::getInstance();
        $videos = $db->table('videos')
            ->join('channels', 'videos.channel_id', '=', 'channels.id')
            ->select('videos.*', 'channels.name as channel_name', 'channels.slug as channel_slug', 'channels.custom_url as channel_custom_url', 'channels.avatar as channel_avatar')
            ->where('videos.category_id', (int) $category['id'])
            ->where('videos.visibility', 'public')
            ->where('videos.status', 'published')
            ->orderBy($sortCol, $sortDir)
            ->paginate(24, $page);

        return $this->view('guest.category-show', [
            'title' => $category['name'],
            'category' => $category,
            'videos' => $videos,
            'currentSort' => $sort,
        ]);
    }
}
