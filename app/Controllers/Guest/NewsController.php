<?php

declare(strict_types=1);

namespace App\Controllers\Guest;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Video;
use App\Models\Category;

class NewsController extends Controller
{
    public function index(): Response
    {
        $videoModel = new Video();
        $categoryModel = new Category();
        $page = (int) $this->request->query('page', 1);

        $newsCategory = $categoryModel->where('slug', 'news')->first();

        $videos = [];
        if ($newsCategory) {
            $videos = $videoModel->where('category_id', (int) $newsCategory['id'])
                ->where('visibility', 'public')
                ->where('status', 'published')
                ->orderBy('published_at', 'DESC')
                ->paginate(24, $page);
        }

        return $this->view('guest.videos', [
            'title' => 'News',
            'videos' => $videos,
            'currentSort' => 'latest',
            'currentCategory' => 'news',
            'pageTitle' => 'News',
        ]);
    }
}
