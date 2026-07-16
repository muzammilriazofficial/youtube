<?php

declare(strict_types=1);

namespace App\Controllers\Moderator;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class ViolationController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('moderator', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = 20;

        $result = $db->table('violations')
            ->join('users', 'violations.taken_by', '=', 'users.id')
            ->leftJoin('videos', 'violations.video_id', '=', 'videos.id')
            ->leftJoin('comments', 'violations.comment_id', '=', 'comments.id')
            ->leftJoin('channels', 'violations.channel_id', '=', 'channels.id')
            ->orderBy('violations.created_at', 'DESC')
            ->paginate($perPage, $page);

        return $this->view('moderator.violations', [
            'title' => 'Violations Log',
            'activeMenu' => 'violations',
            'violations' => $result['data'],
            'pagination' => $result,
        ]);
    }

    public function store(): Response
    {
        if (!$this->hasRole('moderator', 'admin')) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $errors = $this->validate([
            'type' => 'required|max:50',
            'description' => 'required|max:1000',
            'action_taken' => 'required|max:255',
        ]);

        if (!empty($errors)) {
            return $this->json(['error' => 'Invalid input', 'errors' => $errors], 422);
        }

        $userId = (int) $this->session->get('user_id');
        $db = Database::getInstance();

        $data = [
            'type' => $this->request->input('type'),
            'description' => $this->request->input('description'),
            'action_taken' => $this->request->input('action_taken'),
            'taken_by' => $userId,
        ];

        $videoId = $this->request->input('video_id');
        $commentId = $this->request->input('comment_id');
        $channelId = $this->request->input('channel_id');
        $targetUserId = $this->request->input('target_user_id');

        if ($videoId !== null) {
            $data['video_id'] = (int) $videoId;
        }
        if ($commentId !== null) {
            $data['comment_id'] = (int) $commentId;
        }
        if ($channelId !== null) {
            $data['channel_id'] = (int) $channelId;
        }
        if ($targetUserId !== null) {
            $data['user_id'] = (int) $targetUserId;
        }

        $db->table('violations')->insert($data);

        if ($this->isApiRequest()) {
            return $this->json(['success' => true, 'message' => 'Violation logged successfully.']);
        }

        $this->withSuccess('Violation logged successfully.');
        return $this->redirect('/moderator/violations');
    }
}
