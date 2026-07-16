<?php

declare(strict_types=1);

namespace App\Controllers\Moderator;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class CommentController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('moderator', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $search = $this->request->input('search', '');
        $perPage = 20;

        $query = $db->table('comments')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->join('videos', 'comments.video_id', '=', 'videos.id')
            ->whereNull('comments.deleted_at');

        if ($search !== '') {
            $query = $query->where('comments.body', 'LIKE', '%' . $search . '%');
        }

        $result = $query->orderBy('comments.created_at', 'DESC')
            ->paginate($perPage, $page);

        return $this->view('moderator.comments', [
            'title' => 'Manage Comments',
            'activeMenu' => 'comments',
            'comments' => $result['data'],
            'pagination' => $result,
            'search' => $search,
        ]);
    }

    public function reported(): Response
    {
        if (!$this->hasRole('moderator', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));

        $result = $db->table('reports')
            ->join('users', 'reports.reporter_id', '=', 'users.id')
            ->leftJoin('comments', 'reports.reportable_id', '=', 'comments.id')
            ->leftJoin('users AS comment_user', 'comments.user_id', '=', 'comment_user.id')
            ->leftJoin('videos', 'comments.video_id', '=', 'videos.id')
            ->where('reports.reportable_type', 'comment')
            ->orderBy('reports.created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('moderator.comment-reports', [
            'title' => 'Reported Comments',
            'activeMenu' => 'comment-reports',
            'reports' => $result['data'],
            'pagination' => $result,
        ]);
    }

    public function action(): Response
    {
        if (!$this->hasRole('moderator', 'admin')) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $errors = $this->validate([
            'comment_id' => 'required|numeric',
            'action' => 'required|in:approve,hide,delete,warn',
        ]);

        if (!empty($errors)) {
            return $this->json(['error' => 'Invalid input', 'errors' => $errors], 422);
        }

        $commentId = (int) $this->request->input('comment_id');
        $action = $this->request->input('action');
        $reason = $this->request->input('reason', '');
        $userId = (int) $this->session->get('user_id');

        $db = Database::getInstance();
        $comment = $db->table('comments')->find($commentId);

        if ($comment === null) {
            return $this->json(['error' => 'Comment not found'], 404);
        }

        match ($action) {
            'approve' => $db->table('comments')->where('id', $commentId)->update([
                'status' => 'visible',
            ]),
            'hide' => $db->table('comments')->where('id', $commentId)->update([
                'status' => 'hidden',
            ]),
            'delete' => $db->table('comments')->where('id', $commentId)->update([
                'status' => 'deleted',
                'deleted_at' => date('Y-m-d H:i:s'),
            ]),
            'warn' => $db->table('violations')->insert([
                'comment_id' => $commentId,
                'user_id' => $comment['user_id'],
                'type' => 'warning',
                'description' => $reason !== '' ? $reason : 'Warning issued for comment',
                'action_taken' => 'Warning for comment',
                'taken_by' => $userId,
            ]),
        };

        $db->table('reports')
            ->where('reportable_type', 'comment')
            ->where('reportable_id', $commentId)
            ->where('status', 'pending')
            ->update([
                'status' => 'resolved',
                'reviewed_by' => $userId,
                'reviewed_at' => date('Y-m-d H:i:s'),
            ]);

        $db->table('violations')->insert([
            'comment_id' => $commentId,
            'user_id' => $comment['user_id'],
            'type' => $action,
            'description' => $reason !== '' ? $reason : "Action {$action} taken on comment #{$commentId}",
            'action_taken' => ucfirst($action) . ' comment',
            'taken_by' => $userId,
        ]);

        if ($this->isApiRequest()) {
            return $this->json(['success' => true, 'message' => "Comment action '{$action}' completed."]);
        }

        $this->withSuccess("Action '{$action}' completed on the comment.");
        return $this->redirect('/moderator/comments');
    }
}
