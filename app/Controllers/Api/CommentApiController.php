<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Response;
use App\Core\Database;
use App\Models\Comment;
use App\Models\Video;
use App\Services\SecurityService;

class CommentApiController extends ApiController
{
    public function index(): Response
    {
        $videoId = $this->request->query('video_id');

        if ($videoId === null) {
            return $this->error('video_id query parameter is required.', 422);
        }

        $videoModel = new Video();
        $video      = $videoModel->find((int) $videoId);

        if ($video === null) {
            return $this->error('Video not found.', 404);
        }

        $page  = $this->getPage();
        $limit = $this->getPerPage(50);

        $commentModel = new Comment();
        $comments     = $commentModel->getVideoComments((int) $videoId, $limit, $page);

        $userModel = new \App\Models\User();
        foreach ($comments['data'] as &$comment) {
            $comment['user'] = $userModel->find((int) $comment['user_id']);
            if (isset($comment['user']['password'])) {
                unset($comment['user']['password']);
            }
        }

        return $this->paginatedResponse($comments, 'Comments retrieved.');
    }

    public function store(): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $errors = $this->validate([
            'video_id'   => 'required|numeric',
            'body'       => 'required|max:1000',
            'parent_id'  => 'numeric',
        ]);

        if (!empty($errors)) {
            return $this->error('Validation failed.', 422, $errors);
        }

        $videoId = (int) $this->request->input('video_id');
        $body    = $this->sanitize($this->request->input('body'));
        $parentId = $this->request->input('parent_id') !== null
            ? (int) $this->request->input('parent_id')
            : null;

        $videoModel = new Video();
        $video      = $videoModel->find($videoId);

        if ($video === null) {
            return $this->error('Video not found.', 404);
        }

        $commentModel = new Comment();

        if ($parentId !== null) {
            $parentComment = $commentModel->find($parentId);
            if ($parentComment === null || (int) $parentComment['video_id'] !== $videoId) {
                return $this->error('Parent comment not found for this video.', 404);
            }
        }

        $comment = $commentModel->create([
            'user_id'   => (int) $this->apiUser['id'],
            'video_id'  => $videoId,
            'parent_id' => $parentId,
            'body'      => $body,
            'like_count'  => 0,
            'reply_count' => 0,
            'is_pinned'   => false,
            'is_hearted'  => false,
        ]);

        $videoModel->db->table('videos')
            ->where('id', $videoId)
            ->update([
                'comments_count' => new \App\Core\RawExpression('comments_count + 1'),
            ]);

        if ($parentId !== null) {
            $commentModel->incrementReplyCount($parentId);
        }

        $userModel = new \App\Models\User();
        $comment['user'] = $userModel->find((int) $this->apiUser['id']);

        SecurityService::getInstance()->logActivity(
            (int) $this->apiUser['id'],
            'comment_created',
            'comment',
            (int) $comment['id']
        );

        return $this->created($comment, 'Comment posted successfully.');
    }

    public function delete(string $id): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $commentModel = new Comment();
        $comment      = $commentModel->find((int) $id);

        if ($comment === null) {
            return $this->error('Comment not found.', 404);
        }

        $ownershipCheck = $this->authorizeOwnership($comment);
        if ($ownershipCheck instanceof Response) {
            return $ownershipCheck;
        }

        $commentModel->deleteById((int) $id);

        $videoModel = new Video();
        $videoModel->db->table('videos')
            ->where('id', (int) $comment['video_id'])
            ->update([
                'comments_count' => new \App\Core\RawExpression('GREATEST(comments_count - 1, 0)'),
            ]);

        SecurityService::getInstance()->logActivity(
            (int) $this->apiUser['id'],
            'comment_deleted',
            'comment',
            (int) $id
        );

        return $this->deleted('Comment deleted successfully.');
    }

    public function like(string $id): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $commentModel = new Comment();
        $comment      = $commentModel->find((int) $id);

        if ($comment === null) {
            return $this->error('Comment not found.', 404);
        }

        $db         = Database::getInstance();
        $userId     = (int) $this->apiUser['id'];
        $commentId  = (int) $id;

        $existing = $db->table('comment_likes')
            ->where('user_id', $userId)
            ->where('comment_id', $commentId)
            ->first();

        if ($existing !== null) {
            $db->table('comment_likes')
                ->where('user_id', $userId)
                ->where('comment_id', $commentId)
                ->delete();

            $commentModel->decrementLikeCount($commentId);
            $action = 'unliked';
        } else {
            $db->table('comment_likes')->insert([
                'user_id'    => $userId,
                'comment_id' => $commentId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $commentModel->incrementLikeCount($commentId);
            $action = 'liked';
        }

        $updated = $commentModel->find($commentId);

        return $this->success([
            'action'     => $action,
            'like_count' => $updated['like_count'] ?? 0,
        ], 'Comment reaction updated.');
    }

    public function replies(string $id): Response
    {
        $commentModel = new Comment();
        $comment      = $commentModel->find((int) $id);

        if ($comment === null) {
            return $this->error('Comment not found.', 404);
        }

        $page  = $this->getPage();
        $limit = $this->getPerPage(50);

        $replies = $commentModel->getReplies((int) $id, $limit, $page);

        $userModel = new \App\Models\User();
        foreach ($replies['data'] as &$reply) {
            $reply['user'] = $userModel->find((int) $reply['user_id']);
            if (isset($reply['user']['password'])) {
                unset($reply['user']['password']);
            }
        }

        return $this->paginatedResponse($replies, 'Replies retrieved.');
    }
}
