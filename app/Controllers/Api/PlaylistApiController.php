<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Response;
use App\Models\Playlist;
use App\Models\Video;
use App\Services\SecurityService;

class PlaylistApiController extends ApiController
{
    public function index(): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $playlistModel = new Playlist();
        $playlists     = $playlistModel->getUserPlaylists((int) $this->apiUser['id'], 100);

        return $this->success($playlists, 'Your playlists.');
    }

    public function store(): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $errors = $this->validate([
            'title'       => 'required|max:100',
            'description' => 'max:1000',
            'visibility'  => 'in:public,unlisted,private',
        ]);

        if (!empty($errors)) {
            return $this->error('Validation failed.', 422, $errors);
        }

        $playlistModel = new Playlist();
        $playlist = $playlistModel->create([
            'user_id'      => (int) $this->apiUser['id'],
            'title'        => $this->sanitize($this->request->input('title')),
            'description'  => $this->sanitize($this->request->input('description', '')),
            'visibility'   => $this->request->input('visibility', 'private'),
            'video_count'  => 0,
        ]);

        SecurityService::getInstance()->logActivity(
            (int) $this->apiUser['id'],
            'playlist_created',
            'playlist',
            (int) $playlist['id']
        );

        return $this->created($playlist, 'Playlist created successfully.');
    }

    public function show(string $id): Response
    {
        $playlistModel = new Playlist();
        $playlist      = $playlistModel->find((int) $id);

        if ($playlist === null) {
            return $this->error('Playlist not found.', 404);
        }

        if ($playlist['visibility'] !== 'public') {
            if ($this->getApiUser() === null) {
                return $this->error('This playlist is not available.', 403);
            }
            if ((int) $playlist['user_id'] !== (int) $this->apiUser['id']) {
                return $this->error('You do not have access to this playlist.', 403);
            }
        }

        $playlist['videos'] = $playlistModel->getVideos((int) $id, 100);

        $userModel = new \App\Models\User();
        $playlist['user'] = $userModel->find((int) $playlist['user_id']);
        if (isset($playlist['user']['password'])) {
            unset($playlist['user']['password']);
        }

        return $this->success($playlist);
    }

    public function update(string $id): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $playlistModel = new Playlist();
        $playlist      = $playlistModel->find((int) $id);

        if ($playlist === null) {
            return $this->error('Playlist not found.', 404);
        }

        $ownershipCheck = $this->authorizeOwnership($playlist);
        if ($ownershipCheck instanceof Response) {
            return $ownershipCheck;
        }

        $data = $this->request->only(['title', 'description', 'visibility']);

        if (isset($data['title'])) {
            $data['title'] = $this->sanitize($data['title']);
        }
        if (isset($data['description'])) {
            $data['description'] = $this->sanitize($data['description']);
        }
        if (isset($data['visibility']) && !in_array($data['visibility'], ['public', 'unlisted', 'private'], true)) {
            return $this->error('Invalid visibility value.', 422);
        }

        $playlistModel->updateById((int) $id, $data);

        SecurityService::getInstance()->logActivity(
            (int) $this->apiUser['id'],
            'playlist_updated',
            'playlist',
            (int) $id
        );

        return $this->success($playlistModel->find((int) $id), 'Playlist updated successfully.');
    }

    public function delete(string $id): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $playlistModel = new Playlist();
        $playlist      = $playlistModel->find((int) $id);

        if ($playlist === null) {
            return $this->error('Playlist not found.', 404);
        }

        $ownershipCheck = $this->authorizeOwnership($playlist);
        if ($ownershipCheck instanceof Response) {
            return $ownershipCheck;
        }

        $playlistModel->deleteById((int) $id);

        SecurityService::getInstance()->logActivity(
            (int) $this->apiUser['id'],
            'playlist_deleted',
            'playlist',
            (int) $id
        );

        return $this->deleted('Playlist deleted successfully.');
    }

    public function addVideo(string $id): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $errors = $this->validate([
            'video_id' => 'required|numeric',
            'position' => 'numeric',
        ]);

        if (!empty($errors)) {
            return $this->error('Validation failed.', 422, $errors);
        }

        $playlistModel = new Playlist();
        $playlist      = $playlistModel->find((int) $id);

        if ($playlist === null) {
            return $this->error('Playlist not found.', 404);
        }

        $ownershipCheck = $this->authorizeOwnership($playlist);
        if ($ownershipCheck instanceof Response) {
            return $ownershipCheck;
        }

        $videoId   = (int) $this->request->input('video_id');
        $position  = $this->request->input('position') !== null
            ? (int) $this->request->input('position')
            : 0;

        $videoModel = new Video();
        $video      = $videoModel->find($videoId);
        if ($video === null) {
            return $this->error('Video not found.', 404);
        }

        $added = $playlistModel->addVideo((int) $id, $videoId, $position);

        if (!$added) {
            return $this->error('This video is already in the playlist.', 409);
        }

        SecurityService::getInstance()->logActivity(
            (int) $this->apiUser['id'],
            'playlist_video_added',
            'playlist',
            (int) $id,
            null,
            ['video_id' => $videoId]
        );

        return $this->success($playlistModel->find((int) $id), 'Video added to playlist.');
    }

    public function removeVideo(string $id, string $videoId): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $playlistModel = new Playlist();
        $playlist      = $playlistModel->find((int) $id);

        if ($playlist === null) {
            return $this->error('Playlist not found.', 404);
        }

        $ownershipCheck = $this->authorizeOwnership($playlist);
        if ($ownershipCheck instanceof Response) {
            return $ownershipCheck;
        }

        $removed = $playlistModel->removeVideo((int) $id, (int) $videoId);

        if (!$removed) {
            return $this->error('Video not found in this playlist.', 404);
        }

        SecurityService::getInstance()->logActivity(
            (int) $this->apiUser['id'],
            'playlist_video_removed',
            'playlist',
            (int) $id,
            ['video_id' => (int) $videoId],
            null
        );

        return $this->success($playlistModel->find((int) $id), 'Video removed from playlist.');
    }
}
