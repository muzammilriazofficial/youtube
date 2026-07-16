<?php

declare(strict_types=1);

namespace App\Controllers\Reviewer;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class UploadController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('reviewer', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));

        $result = $db->table('videos')
            ->join('channels', 'videos.channel_id', '=', 'channels.id')
            ->join('users', 'channels.user_id', '=', 'users.id')
            ->leftJoin('categories', 'videos.category_id', '=', 'categories.id')
            ->where('videos.status', 'pending')
            ->whereNull('videos.deleted_at')
            ->orderBy('videos.created_at', 'ASC')
            ->paginate(20, $page);

        return $this->view('reviewer.uploads', [
            'title' => 'Pending Uploads',
            'activeMenu' => 'uploads',
            'uploads' => $result['data'],
            'pagination' => $result,
        ]);
    }

    public function show(string $id): Response
    {
        if (!$this->hasRole('reviewer', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $video = $db->table('videos')
            ->join('channels', 'videos.channel_id', '=', 'channels.id')
            ->join('users', 'channels.user_id', '=', 'users.id')
            ->leftJoin('categories', 'videos.category_id', '=', 'categories.id')
            ->where('videos.id', (int) $id)
            ->whereNull('videos.deleted_at')
            ->first();

        if ($video === null) {
            $this->withError('Video not found.');
            return $this->redirect('/reviewer/uploads');
        }

        $channelStats = $db->table('channels')
            ->where('id', $video['channel_id'])
            ->first();

        return $this->view('reviewer.upload-show', [
            'title' => 'Review Upload',
            'activeMenu' => 'uploads',
            'video' => $video,
            'channelStats' => $channelStats,
        ]);
    }

    public function approve(string $id): Response
    {
        if (!$this->hasRole('reviewer', 'admin')) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $db = Database::getInstance();
        $video = $db->table('videos')->where('id', (int) $id)->first();

        if ($video === null) {
            return $this->respondWithError('Video not found.', 404);
        }

        $db->table('videos')->where('id', (int) $id)->update([
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s'),
        ]);

        $this->withSuccess('Video approved and published.');
        return $this->redirect('/reviewer/uploads');
    }

    public function reject(string $id): Response
    {
        if (!$this->hasRole('reviewer', 'admin')) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $errors = $this->validate([
            'reason' => 'required|max:1000',
        ]);

        if (!empty($errors)) {
            return $this->respondWithError('Please provide a rejection reason.');
        }

        $db = Database::getInstance();
        $video = $db->table('videos')->where('id', (int) $id)->first();

        if ($video === null) {
            return $this->respondWithError('Video not found.', 404);
        }

        $db->table('videos')->where('id', (int) $id)->update([
            'status' => 'rejected',
        ]);

        $this->withSuccess('Video rejected.');
        return $this->redirect('/reviewer/uploads');
    }
}
