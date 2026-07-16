<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class VideoController extends Controller
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

        $query = $db->table('videos')
            ->join('channels', 'videos.channel_id', '=', 'channels.id')
            ->join('users', 'channels.user_id', '=', 'users.id');

        if ($search !== '') {
            $query = $query->where('videos.title', 'LIKE', "%{$search}%")
                ->orWhere('users.username', 'LIKE', "%{$search}%");
        }

        if ($status !== '') {
            $query = $query->where('videos.status', $status);
        }

        $videos = $query->select(
            'videos.*',
            'channels.name as channel_name',
            'users.username'
        )
            ->orderBy('videos.created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('admin.videos', [
            'title' => 'Video Management',
            'activeMenu' => 'videos',
            'videos' => $videos,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function pending(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));

        $videos = $db->table('videos')
            ->join('channels', 'videos.channel_id', '=', 'channels.id')
            ->join('users', 'channels.user_id', '=', 'users.id')
            ->where('videos.status', 'pending')
            ->select('videos.*', 'channels.name as channel_name', 'users.username')
            ->orderBy('videos.created_at', 'ASC')
            ->paginate(20, $page);

        return $this->view('admin.videos-pending', [
            'title' => 'Pending Review Videos',
            'activeMenu' => 'videos',
            'videos' => $videos,
        ]);
    }

    public function reported(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));

        $videos = $db->table('videos')
            ->join('channels', 'videos.channel_id', '=', 'channels.id')
            ->join('users', 'channels.user_id', '=', 'users.id')
            ->where('videos.status', 'reported')
            ->select('videos.*', 'channels.name as channel_name', 'users.username')
            ->orderBy('videos.created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('admin.videos-reported', [
            'title' => 'Reported Videos',
            'activeMenu' => 'videos',
            'videos' => $videos,
        ]);
    }

    public function action(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $action = $this->request->input('action', '');
        $videoId = (int) $id;

        switch ($action) {
            case 'approve':
                $db->table('videos')->where('id', $videoId)->update([
                    'status' => 'published',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                return $this->withSuccess('Video approved.')->redirect('/admin/videos');

            case 'reject':
                $db->table('videos')->where('id', $videoId)->update([
                    'status' => 'rejected',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                return $this->withSuccess('Video rejected.')->redirect('/admin/videos');

            case 'remove':
                $db->table('videos')->where('id', $videoId)->update([
                    'deleted_at' => date('Y-m-d H:i:s'),
                ]);
                return $this->withSuccess('Video removed.')->redirect('/admin/videos');

            case 'feature':
                $db->table('videos')->where('id', $videoId)->update([
                    'is_featured' => 1,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                return $this->withSuccess('Video featured.')->redirect('/admin/videos');

            default:
                return $this->withError('Invalid action.')->redirect('/admin/videos');
        }
    }
}
