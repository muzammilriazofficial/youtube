<?php

declare(strict_types=1);

namespace App\Controllers\Guest;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Video;
use App\Models\Category;

class SportsController extends Controller
{
    public function index(): Response
    {
        $videoModel = new Video();
        $categoryModel = new Category();
        $page = (int) $this->request->query('page', 1);

        $sportsCategory = $categoryModel->where('slug', 'sports')->first();

        $videos = [];
        if ($sportsCategory) {
            $videos = $videoModel->where('category_id', (int) $sportsCategory['id'])
                ->where('visibility', 'public')
                ->where('status', 'published')
                ->orderBy('published_at', 'DESC')
                ->paginate(24, $page);
        }

        return $this->view('guest.videos', [
            'title' => 'Sports',
            'videos' => $videos,
            'currentSort' => 'latest',
            'currentCategory' => 'sports',
            'pageTitle' => 'Sports',
        ]);
    }
}
