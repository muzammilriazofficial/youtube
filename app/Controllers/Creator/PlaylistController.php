<?php

declare(strict_types=1);

namespace App\Controllers\Creator;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Playlist;
use App\Models\Channel;

class PlaylistController extends Controller
{
    public function index(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $page = max(1, (int) $this->request->input('page', 1));

        $playlistModel = new Playlist();
        $result = $playlistModel->where('user_id', $userId)
            ->orderBy('updated_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('creator.playlists', [
            'title' => 'Playlists',
            'activeMenu' => 'playlists',
            'playlists' => $result['data'],
            'pagination' => $result,
        ]);
    }

    public function create(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        return $this->view('creator.playlists', [
            'title' => 'Create Playlist',
            'activeMenu' => 'playlists',
            'showCreateForm' => true,
            'channel' => $channel,
        ]);
    }

    public function store(): Response
    {
        if (!$this->validateCsrf()) {
            return $this->respondWithError('Invalid CSRF token.');
        }

        $userId = (int) $this->session->get('user_id');

        $errors = $this->validate([
            'title' => 'required|max:150',
            'description' => 'max:5000',
            'visibility' => 'in:public,private,unlisted',
        ]);

        if (!empty($errors)) {
            return $this->respondWithError('Validation failed: ' . implode(', ', array_merge(...array_values($errors))));
        }

        $playlistModel = new Playlist();
        $playlist = $playlistModel->create([
            'user_id' => $userId,
            'title' => $this->request->input('title'),
            'description' => $this->request->input('description', ''),
            'visibility' => $this->request->input('visibility', 'private'),
            'video_count' => 0,
        ]);

        $this->session->flash('success', 'Playlist created successfully.');
        return $this->redirect('/creator/playlists');
    }
}
