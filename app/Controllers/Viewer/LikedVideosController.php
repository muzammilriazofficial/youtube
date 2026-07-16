<?php

declare(strict_types=1);

namespace App\Controllers\Viewer;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Database;
use App\Models\Video;

class LikedVideosController extends Controller
{
    public function index(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $page = (int) $this->request->query('page', 1);

        $db = Database::getInstance();
        $liked = $db->table('video_likes')
            ->join('videos', 'video_likes.video_id', '=', 'videos.id')
            ->where('video_likes.user_id', $userId)
            ->where('video_likes.type', 'like')
            ->where('videos.visibility', 'public')
            ->whereNull('videos.deleted_at')
            ->orderBy('video_likes.created_at', 'DESC')
            ->paginate(30, $page);

        return $this->view('viewer.liked-videos', [
            'title' => 'Liked Videos',
            'likedVideos' => $liked,
        ]);
    }
}
