<?php

declare(strict_types=1);

namespace App\Controllers\Viewer;

use App\Core\Controller;
use App\Core\Response;
use App\Models\WatchHistory;
use App\Models\Video;

class HistoryController extends Controller
{
    public function index(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $historyModel = new WatchHistory();
        $page = (int) $this->request->query('page', 1);

        $history = $historyModel->getUserHistory($userId, 30, $page);

        $videoModel = new Video();
        foreach ($history['data'] as &$item) {
            $item['video'] = $videoModel->find((int) $item['video_id']);
        }

        return $this->view('viewer.history', [
            'title' => 'Watch History',
            'history' => $history,
        ]);
    }

    public function add(): Response
    {
        if (!$this->request->isAjax() && !$this->request->expectsJson()) {
            return $this->back();
        }

        $userId = (int) $this->session->get('user_id');
        $videoId = (int) $this->request->input('video_id');
        $duration = (int) $this->request->input('duration', 0);
        $progress = (int) $this->request->input('progress', 0);
        $completed = $this->request->input('completed') === 'true';

        $historyModel = new WatchHistory();
        $historyModel->recordWatch($userId, $videoId, $duration, $progress, $completed);

        return $this->json(['status' => 'ok']);
    }

    public function remove(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $videoId = (int) $this->request->input('video_id');

        $historyModel = new WatchHistory();
        $historyModel->removeFromHistory($userId, $videoId);

        if ($this->request->expectsJson()) {
            return $this->json(['status' => 'ok']);
        }

        $this->session->flash('success', 'Video removed from history.');
        return $this->redirect('/viewer/history');
    }

    public function clear(): Response
    {
        $userId = (int) $this->session->get('user_id');

        $historyModel = new WatchHistory();
        $historyModel->clearHistory($userId);

        if ($this->request->expectsJson()) {
            return $this->json(['status' => 'ok']);
        }

        $this->session->flash('success', 'Watch history cleared.');
        return $this->redirect('/viewer/history');
    }
}
