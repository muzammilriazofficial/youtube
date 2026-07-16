<?php

declare(strict_types=1);

namespace App\Controllers\Viewer;

use App\Core\Controller;
use App\Core\Response;
use App\Models\WatchLater;
use App\Models\Video;

class WatchLaterController extends Controller
{
    public function index(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $watchLaterModel = new WatchLater();
        $page = (int) $this->request->query('page', 1);

        $list = $watchLaterModel->getUserList($userId, 30, $page);

        $videoModel = new Video();
        foreach ($list['data'] as &$item) {
            $item['video'] = $videoModel->find((int) $item['video_id']);
        }

        return $this->view('viewer.watch-later', [
            'title' => 'Watch Later',
            'watchLater' => $list,
        ]);
    }

    public function add(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $videoId = (int) $this->request->input('video_id');

        $watchLaterModel = new WatchLater();
        $result = $watchLaterModel->toggle($userId, $videoId);

        if ($this->request->expectsJson()) {
            return $this->json(['status' => $result]);
        }

        $this->session->flash('success', $result === 'added' ? 'Added to Watch Later.' : 'Removed from Watch Later.');
        return $this->back();
    }

    public function remove(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $videoId = (int) $this->request->input('video_id');

        $watchLaterModel = new WatchLater();
        $watchLaterModel->removeFromWatchLater($userId, $videoId);

        if ($this->request->expectsJson()) {
            return $this->json(['status' => 'ok']);
        }

        $this->session->flash('success', 'Removed from Watch Later.');
        return $this->redirect('/viewer/watch-later');
    }
}
