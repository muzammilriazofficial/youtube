<?php

declare(strict_types=1);

namespace App\Controllers\Guest;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Video;
use App\Models\Category;

class MusicController extends Controller
{
    public function index(): Response
    {
        $videoModel = new Video();
        $categoryModel = new Category();
        $page = (int) $this->request->query('page', 1);

        $musicCategory = $categoryModel->where('slug', 'music')->first();

        $videos = [];
        if ($musicCategory) {
            $videos = $videoModel->where('category_id', (int) $musicCategory['id'])
                ->where('visibility', 'public')
                ->where('status', 'published')
                ->orderBy('view_count', 'DESC')
                ->paginate(24, $page);
        }

        return $this->view('guest.videos', [
            'title' => 'Music',
            'videos' => $videos,
            'currentSort' => 'popular',
            'currentCategory' => 'music',
            'pageTitle' => 'Music',
        ]);
    }
}
