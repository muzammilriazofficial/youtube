<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\VideoLike;
use App\Models\Comment;
use App\Models\Video;
use App\Models\Report;

class AjaxController extends Controller
{
    public function toggleLike(): Response
    {
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Please login to like videos.'], 401);
        }

        $userId = (int) $this->session->get('user_id');
        $videoId = (int) $this->request->input('video_id');

        if ($videoId <= 0) {
            return $this->json(['error' => 'Invalid video ID.'], 400);
        }

        $channelModel = new \App\Models\Channel();
        $userChannel = $channelModel->findByUserId($userId);
        if ($userChannel === null) {
            return $this->json(['error' => 'You must create a channel before liking videos.'], 403);
        }

        $likeModel = new VideoLike();
        $result = $likeModel->toggleLike($userId, $videoId);

        $videoModel = new Video();
        $video = $videoModel->find($videoId);

        return $this->json([
            'status' => $result,
            'like_count' => $video['like_count'] ?? 0,
            'dislike_count' => $video['dislike_count'] ?? 0,
        ]);
    }

    public function addComment(): Response
    {
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Please login to comment.'], 401);
        }

        $errors = $this->validate([
            'video_id' => 'required|numeric',
            'body' => 'required|max:5000',
        ]);

        if (!empty($errors)) {
            return $this->json(['error' => 'Validation failed.', 'errors' => $errors], 422);
        }

        $userId = (int) $this->session->get('user_id');
        $videoId = (int) $this->request->input('video_id');
        $body = $this->request->input('body');
        $parentId = $this->request->input('parent_id') ? (int) $this->request->input('parent_id') : null;

        $channelModel = new \App\Models\Channel();
        $userChannel = $channelModel->findByUserId($userId);
        if ($userChannel === null) {
            return $this->json(['error' => 'You must create a channel before commenting.'], 403);
        }

        $commentModel = new Comment();
        $comment = $commentModel->create([
            'user_id' => $userId,
            'video_id' => $videoId,
            'parent_id' => $parentId,
            'body' => $body,
            'like_count' => 0,
            'replies_count' => 0,
        ]);

        if ($parentId !== null) {
            $commentModel->incrementReplyCount($parentId);
        }

        $videoModel = new Video();
        $videoModel->db->table('videos')
            ->where('id', $videoId)
            ->update(['comments_count' => new \App\Core\RawExpression('comments_count + 1')]);

        $user = $this->user();

        $avatar = $user['avatar'] ?? ($userChannel['avatar'] ?? null);

        return $this->json([
            'status' => 'ok',
            'comment' => $comment,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'avatar' => $avatar,
            ],
        ], 201);
    }

    public function deleteComment(): Response
    {
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Unauthorized.'], 401);
        }

        $userId = (int) $this->session->get('user_id');
        $commentId = (int) $this->request->input('comment_id');

        $commentModel = new Comment();
        $comment = $commentModel->find($commentId);

        if ($comment === null) {
            return $this->json(['error' => 'Comment not found.'], 404);
        }

        if ((int) $comment['user_id'] !== $userId && !$this->hasRole('admin', 'moderator')) {
            return $this->json(['error' => 'You can only delete your own comments.'], 403);
        }

        $commentModel->deleteById($commentId);

        return $this->json(['status' => 'ok']);
    }

    public function likeComment(): Response
    {
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Please login.'], 401);
        }

        $commentId = (int) $this->request->input('comment_id');

        $commentModel = new Comment();
        $comment = $commentModel->find($commentId);

        if ($comment === null) {
            return $this->json(['error' => 'Comment not found.'], 404);
        }

        $commentModel->incrementLikeCount($commentId);

        return $this->json([
            'status' => 'ok',
            'like_count' => (int) $comment['like_count'] + 1,
        ]);
    }

    public function submitReport(): Response
    {
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Please login to report.'], 401);
        }

        $errors = $this->validate([
            'reportable_type' => 'required|in:video,comment,channel',
            'reportable_id' => 'required|numeric',
            'reason' => 'required|max:200',
            'description' => 'max:2000',
        ]);

        if (!empty($errors)) {
            return $this->json(['error' => 'Validation failed.', 'errors' => $errors], 422);
        }

        $userId = (int) $this->session->get('user_id');
        $reportModel = new Report();

        if ($reportModel->hasUserReported($userId, $this->request->input('reportable_type'), (int) $this->request->input('reportable_id'))) {
            return $this->json(['error' => 'You have already reported this item.'], 409);
        }

        $reportModel->create([
            'reporter_id' => $userId,
            'reportable_type' => $this->request->input('reportable_type'),
            'reportable_id' => (int) $this->request->input('reportable_id'),
            'reason' => $this->request->input('reason'),
            'description' => $this->request->input('description', ''),
            'status' => 'pending',
        ]);

        return $this->json(['status' => 'ok', 'message' => 'Report submitted.']);
    }
}
