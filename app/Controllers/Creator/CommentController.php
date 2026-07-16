<?php

declare(strict_types=1);

namespace App\Controllers\Creator;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Comment;
use App\Models\Channel;
use App\Models\Video;

class CommentController extends Controller
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

        $result = (new Comment())->db->table('comments')
            ->join('videos', 'comments.video_id', '=', 'videos.id')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->where('videos.channel_id', $channelId)
            ->whereNull('comments.deleted_at')
            ->orderBy('comments.created_at', 'DESC')
            ->paginate(25, $page);

        return $this->view('creator.comments', [
            'title' => 'Comments',
            'activeMenu' => 'comments',
            'comments' => $result['data'],
            'pagination' => $result,
            'channel' => $channel,
        ]);
    }

    public function reported(): Response
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

        $result = (new Comment())->db->table('comments')
            ->join('videos', 'comments.video_id', '=', 'videos.id')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->join('comment_reports', 'comments.id', '=', 'comment_reports.comment_id')
            ->where('videos.channel_id', $channelId)
            ->whereNull('comments.deleted_at')
            ->orderBy('comment_reports.created_at', 'DESC')
            ->paginate(25, $page);

        return $this->view('creator.comments', [
            'title' => 'Reported Comments',
            'activeMenu' => 'comments',
            'comments' => $result['data'],
            'pagination' => $result,
            'showReported' => true,
            'channel' => $channel,
        ]);
    }

    public function moderate(string $id): Response
    {
        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $action = $this->request->input('action', '');
        $userId = (int) $this->session->get('user_id');
        $channelId = $this->getChannelId($userId);
        $commentModel = new Comment();
        $comment = $commentModel->find((int) $id);

        if ($comment === null) {
            return $this->json(['error' => 'Comment not found'], 404);
        }

        $videoModel = new Video();
        $video = $videoModel->find((int) $comment['video_id']);
        if ($video === null || (int) $video['channel_id'] !== $channelId) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        match ($action) {
            'approve' => $commentModel->updateById((int) $id, ['is_approved' => 1]),
            'hide' => $commentModel->updateById((int) $id, ['is_approved' => 0]),
            'delete' => $commentModel->deleteById((int) $id),
            default => null,
        };

        if ($action === 'delete' && !empty($video['channel_id'])) {
            (new Channel())->db->table('videos')
                ->where('id', $comment['video_id'])
                ->update(['comments_count' => new \App\Core\RawExpression('GREATEST(comments_count - 1, 0)')]);
        }

        if ($this->request->expectsJson()) {
            return $this->json(['success' => true, 'action' => $action]);
        }

        $this->session->flash('success', 'Comment ' . $action . 'd successfully.');
        return $this->redirect('/creator/comments');
    }

    private function getChannelId(int $userId): int
    {
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);
        return $channel ? (int) $channel['id'] : 0;
    }
}
