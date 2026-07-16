<?php

declare(strict_types=1);

namespace App\Controllers\Guest;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Video;

class LiveController extends Controller
{
    public function index(): Response
    {
        $videoModel = new Video();
        $page = (int) $this->request->query('page', 1);

        $liveStreams = $videoModel->where('visibility', 'public')
            ->where('status', 'live')
            ->orderBy('view_count', 'DESC')
            ->paginate(20, $page);

        return $this->view('guest.live', [
            'title' => 'Live Streams',
            'liveStreams' => $liveStreams,
        ]);
    }
}
