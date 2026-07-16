<?php

declare(strict_types=1);

namespace App\Controllers\Guest;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Video;

class ShortsController extends Controller
{
    public function index(): Response
    {
        $videoModel = new Video();
        $page = (int) $this->request->query('page', 1);

        $shorts = $videoModel->where('visibility', 'public')
            ->where('status', 'published')
            ->where('is_short', 1)
            ->orderBy('published_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('guest.shorts', [
            'title' => 'Shorts',
            'shorts' => $shorts,
        ]);
    }
}
