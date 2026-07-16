<?php

declare(strict_types=1);

namespace App\Controllers\Viewer;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Playlist;
use App\Models\Video;

class PlaylistController extends Controller
{
    public function index(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $playlistModel = new Playlist();
        $playlists = $playlistModel->getUserPlaylists($userId, 50);

        return $this->view('viewer.playlists', [
            'title' => 'My Playlists',
            'playlists' => $playlists,
        ]);
    }

    public function create(): Response
    {
        return $this->view('viewer.playlist-form', [
            'title' => 'Create Playlist',
            'playlist' => null,
        ]);
    }

    public function store(): Response
    {
        $userId = (int) $this->session->get('user_id');

        $errors = $this->validate([
            'title' => 'required|max:150',
            'description' => 'max:500',
            'visibility' => 'in:public,private,unlisted',
        ]);

        if (!empty($errors)) {
            return $this->withInput()->respondWithError('Please fix the validation errors.');
        }

        $playlistModel = new Playlist();
        $playlistModel->create([
            'user_id' => $userId,
            'title' => $this->request->input('title'),
            'description' => $this->request->input('description', ''),
            'visibility' => $this->request->input('visibility', 'private'),
            'video_count' => 0,
        ]);

        $this->session->flash('success', 'Playlist created successfully.');
        return $this->redirect('/viewer/playlists');
    }

    public function show(string $id): Response
    {
        $userId = (int) $this->session->get('user_id');
        $playlistModel = new Playlist();
        $playlist = $playlistModel->find((int) $id);

        if ($playlist === null || (int) $playlist['user_id'] !== $userId) {
            return $this->view('errors.404', ['title' => 'Not Found'], 404);
        }

        $videos = $playlistModel->getVideos((int) $id);

        return $this->view('viewer.playlist-show', [
            'title' => $playlist['title'],
            'playlist' => $playlist,
            'videos' => $videos,
        ]);
    }

    public function edit(string $id): Response
    {
        $userId = (int) $this->session->get('user_id');
        $playlistModel = new Playlist();
        $playlist = $playlistModel->find((int) $id);

        if ($playlist === null || (int) $playlist['user_id'] !== $userId) {
            return $this->view('errors.404', ['title' => 'Not Found'], 404);
        }

        return $this->view('viewer.playlist-form', [
            'title' => 'Edit Playlist',
            'playlist' => $playlist,
        ]);
    }

    public function update(string $id): Response
    {
        $userId = (int) $this->session->get('user_id');
        $playlistModel = new Playlist();
        $playlist = $playlistModel->find((int) $id);

        if ($playlist === null || (int) $playlist['user_id'] !== $userId) {
            return $this->view('errors.404', ['title' => 'Not Found'], 404);
        }

        $errors = $this->validate([
            'title' => 'required|max:150',
            'description' => 'max:500',
            'visibility' => 'in:public,private,unlisted',
        ]);

        if (!empty($errors)) {
            return $this->withInput()->respondWithError('Please fix the validation errors.');
        }

        $playlistModel->updateById((int) $id, [
            'title' => $this->request->input('title'),
            'description' => $this->request->input('description', ''),
            'visibility' => $this->request->input('visibility', 'private'),
        ]);

        $this->session->flash('success', 'Playlist updated successfully.');
        return $this->redirect('/viewer/playlists/' . $id);
    }

    public function delete(string $id): Response
    {
        $userId = (int) $this->session->get('user_id');
        $playlistModel = new Playlist();
        $playlist = $playlistModel->find((int) $id);

        if ($playlist === null || (int) $playlist['user_id'] !== $userId) {
            return $this->view('errors.404', ['title' => 'Not Found'], 404);
        }

        $playlistModel->deleteById((int) $id);

        if ($this->request->expectsJson()) {
            return $this->json(['status' => 'ok']);
        }

        $this->session->flash('success', 'Playlist deleted.');
        return $this->redirect('/viewer/playlists');
    }

    public function addVideo(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $playlistId = (int) $this->request->input('playlist_id');
        $videoId = (int) $this->request->input('video_id');

        $playlistModel = new Playlist();
        $playlist = $playlistModel->find($playlistId);

        if ($playlist === null || (int) $playlist['user_id'] !== $userId) {
            return $this->json(['error' => 'Playlist not found'], 404);
        }

        $added = $playlistModel->addVideo($playlistId, $videoId);

        return $this->json(['status' => $added ? 'added' : 'exists']);
    }

    public function removeVideo(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $playlistId = (int) $this->request->input('playlist_id');
        $videoId = (int) $this->request->input('video_id');

        $playlistModel = new Playlist();
        $playlist = $playlistModel->find($playlistId);

        if ($playlist === null || (int) $playlist['user_id'] !== $userId) {
            return $this->json(['error' => 'Playlist not found'], 404);
        }

        $playlistModel->removeVideo($playlistId, $videoId);

        return $this->json(['status' => 'ok']);
    }
}
