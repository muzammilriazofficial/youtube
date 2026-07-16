<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class CommentController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $search = $this->request->input('search', '');
        $status = $this->request->input('status', '');

        $query = $db->table('comments')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->join('videos', 'comments.video_id', '=', 'videos.id');

        if ($search !== '') {
            $query = $query->where('comments.content', 'LIKE', "%{$search}%")
                ->orWhere('users.username', 'LIKE', "%{$search}%");
        }

        if ($status !== '') {
            $query = $query->where('comments.status', $status);
        }

        $comments = $query->select(
            'comments.*',
            'users.username',
            'videos.title as video_title'
        )
            ->orderBy('comments.created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('admin.comments', [
            'title' => 'Comment Management',
            'activeMenu' => 'comments',
            'comments' => $comments,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function action(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $action = $this->request->input('action', '');
        $commentId = (int) $id;

        switch ($action) {
            case 'approve':
                $db->table('comments')->where('id', $commentId)->update([
                    'status' => 'approved',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                return $this->withSuccess('Comment approved.')->redirect('/admin/comments');

            case 'hide':
                $db->table('comments')->where('id', $commentId)->update([
                    'status' => 'hidden',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                return $this->withSuccess('Comment hidden.')->redirect('/admin/comments');

            case 'delete':
                $db->table('comments')->where('id', $commentId)->update([
                    'deleted_at' => date('Y-m-d H:i:s'),
                ]);
                return $this->withSuccess('Comment deleted.')->redirect('/admin/comments');

            default:
                return $this->withError('Invalid action.')->redirect('/admin/comments');
        }
    }
}
