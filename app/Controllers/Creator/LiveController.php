<?php

declare(strict_types=1);

namespace App\Controllers\Creator;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Video;
use App\Models\Channel;

class LiveController extends Controller
{
    public function index(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            $this->session->flash('error', 'Channel not found.');
            return $this->redirect('/');
        }

        $channelId = (int) $channel['id'];
        $page = max(1, (int) $this->request->input('page', 1));

        $result = (new Video())->db->table('videos')
            ->where('channel_id', $channelId)
            ->where('is_live', 1)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('creator.live', [
            'title' => 'Live Streams',
            'activeMenu' => 'live',
            'streams' => $result['data'],
            'pagination' => $result,
            'channel' => $channel,
        ]);
    }

    public function start(): Response
    {
        if (!$this->validateCsrf()) {
            return $this->respondWithError('Invalid CSRF token.');
        }

        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            return $this->respondWithError('Channel not found.');
        }

        $errors = $this->validate([
            'title' => 'required|max:100',
            'description' => 'max:5000',
        ]);

        if (!empty($errors)) {
            return $this->respondWithError('Validation failed: ' . implode(', ', array_merge(...array_values($errors))));
        }

        $streamKey = 'live_' . bin2hex(random_bytes(16));

        $videoModel = new Video();
        $video = $videoModel->create([
            'channel_id' => (int) $channel['id'],
            'title' => $this->request->input('title'),
            'slug' => strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $this->request->input('title')), '-')) . '-' . bin2hex(random_bytes(4)),
            'description' => $this->request->input('description', ''),
            'visibility' => $this->request->input('visibility', 'public'),
            'status' => 'live',
            'is_live' => 1,
            'stream_key' => $streamKey,
            'view_count' => 0,
            'like_count' => 0,
            'dislike_count' => 0,
            'comment_count' => 0,
            'published_at' => date('Y-m-d H:i:s'),
        ]);

        $this->session->flash('success', 'Live stream started. Stream key: ' . $streamKey);
        return $this->redirect('/creator/live/' . $video['id'] . '/manage');
    }

    public function manage(string $id): Response
    {
        $userId = (int) $this->session->get('user_id');
        $videoModel = new Video();
        $video = $videoModel->find((int) $id);
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($video === null || $channel === null || (int) $video['channel_id'] !== (int) $channel['id']) {
            $this->session->flash('error', 'Stream not found.');
            return $this->redirect('/creator/live');
        }

        $chatMessages = $videoModel->db->table('live_chat_messages')
            ->where('video_id', $id)
            ->orderBy('created_at', 'ASC')
            ->limit(50)
            ->get();

        return $this->view('creator.live', [
            'title' => 'Manage Live Stream',
            'activeMenu' => 'live',
            'stream' => $video,
            'chatMessages' => $chatMessages,
            'managing' => true,
            'channel' => $channel,
        ]);
    }

    public function end(string $id): Response
    {
        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $userId = (int) $this->session->get('user_id');
        $videoModel = new Video();
        $video = $videoModel->find((int) $id);
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($video === null || $channel === null || (int) $video['channel_id'] !== (int) $channel['id']) {
            return $this->json(['error' => 'Stream not found'], 404);
        }

        $videoModel->updateById((int) $id, [
            'status' => 'published',
            'duration' => (int) $this->request->input('duration', 0),
        ]);

        if ($this->request->expectsJson()) {
            return $this->json(['success' => true, 'message' => 'Stream ended.']);
        }

        $this->session->flash('success', 'Live stream ended.');
        return $this->redirect('/creator/live');
    }
}
