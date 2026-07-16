<?php

declare(strict_types=1);

namespace App\Controllers\Creator;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Channel;

class ChannelController extends Controller
{
    public function create(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $existing = $channelModel->findByUserId($userId);

        if ($existing !== null) {
            return $this->redirect('/creator/channel');
        }

        $userModel = new \App\Models\User();
        $user = $userModel->find($userId);

        return $this->view('creator.channel-create', [
            'title' => 'Create Channel',
            'user' => $user,
        ]);
    }

    public function store(): Response
    {
        if (!$this->validateCsrf()) {
            return $this->respondWithError('Invalid CSRF token.');
        }

        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $existing = $channelModel->findByUserId($userId);

        if ($existing !== null) {
            return $this->redirect('/creator/channel');
        }

        $errors = $this->validate([
            'name' => 'required|max:100',
            'description' => 'max:5000',
        ]);

        if (!empty($errors)) {
            $this->session->flash('error', 'Validation failed: ' . implode(', ', array_merge(...array_values($errors))));
            return $this->redirect('/creator/channel/create');
        }

        $name = trim($this->request->input('name', ''));
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        $slug = trim($slug, '-');

        $existingSlug = $channelModel->where('slug', $slug)->first();
        if ($existingSlug !== null) {
            $slug = $slug . '-' . substr(uniqid(), -4);
        }

        $data = [
            'user_id' => $userId,
            'name' => $name,
            'slug' => $slug,
            'description' => trim($this->request->input('description', '')),
            'language' => $this->request->input('language', 'en'),
        ];

        $handle = trim($this->request->input('handle', ''));
        if (!empty($handle)) {
            $handle = ltrim($handle, '@');
            $existingHandle = $channelModel->where('custom_url', $handle)->first();
            if ($existingHandle === null) {
                $data['custom_url'] = $handle;
            }
        }

        $channel = $channelModel->create($data);

        $avatar = $this->request->file('avatar');
        if ($avatar !== null && $avatar['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $avatar['tmp_name']);
            finfo_close($finfo);

            if (in_array($type, $allowed, true) && $avatar['size'] <= 4 * 1024 * 1024) {
                $uploadDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'channels';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = pathinfo($avatar['name'], PATHINFO_EXTENSION);
                $filename = 'avatar_' . $channel['id'] . '_' . time() . '.' . $ext;
                move_uploaded_file($avatar['tmp_name'], $uploadDir . DIRECTORY_SEPARATOR . $filename);
                $channelModel->updateById((int) $channel['id'], ['avatar' => '/uploads/channels/' . $filename]);
            }
        }

        $this->session->flash('success', 'Channel created successfully!');
        return $this->redirect('/channel/' . ($data['custom_url'] ?? $slug));
    }

    public function index(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            return $this->redirect('/creator/channel/create');
        }

        $channelId = (int) $channel['id'];

        $videoCount = $channelModel->db->table('videos')
            ->where('channel_id', $channelId)
            ->where('visibility', 'public')
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->count();

        $totalViews = $channelModel->db->table('videos')
            ->where('channel_id', $channelId)
            ->where('visibility', 'public')
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->sum('view_count');

        $playlistCount = $channelModel->db->table('playlists')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->count();

        return $this->view('creator.channel', [
            'title' => 'Channel Overview',
            'activeMenu' => 'channel',
            'channel' => $channel,
            'videoCount' => $videoCount,
            'totalViews' => $totalViews,
            'playlistCount' => $playlistCount,
        ]);
    }

    public function customize(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            $this->session->flash('error', 'Channel not found.');
            return $this->redirect('/');
        }

        return $this->view('creator.channel-customize', [
            'title' => 'Customize Channel',
            'activeMenu' => 'channel',
            'channel' => $channel,
        ]);
    }

    public function update(): Response
    {
        if (!$this->validateCsrf()) {
            return $this->respondWithError('Invalid CSRF token.');
        }

        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            return $this->respondWithError('Channel not found.');
        }

        $errors = $this->validate([
            'name' => 'required|max:100',
            'description' => 'max:5000',
            'country' => 'max:2',
            'website' => 'url',
        ]);

        if (!empty($errors)) {
            $this->session->flash('error', 'Validation failed: ' . implode(', ', array_merge(...array_values($errors))));
            return $this->redirect('/creator/channel/customize');
        }

        $data = $this->request->only(['name', 'description', 'country', 'website']);

        if (!empty($data['name'])) {
            $data['slug'] = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['name']), '-'));
        }

        $channelModel->updateById((int) $channel['id'], $data);

        $this->session->flash('success', 'Channel updated successfully.');
        return $this->redirect('/creator/channel/customize');
    }

    public function branding(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            $this->session->flash('error', 'Channel not found.');
            return $this->redirect('/');
        }

        return $this->view('creator.channel-branding', [
            'title' => 'Channel Branding',
            'activeMenu' => 'branding',
            'channel' => $channel,
        ]);
    }

    public function updateBranding(): Response
    {
        if (!$this->validateCsrf()) {
            return $this->respondWithError('Invalid CSRF token.');
        }

        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            return $this->respondWithError('Channel not found.');
        }

        $data = [];
        $uploadDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'channels';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $avatar = $this->request->file('avatar');
        if ($avatar !== null && $avatar['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $avatar['tmp_name']);
            finfo_close($finfo);

            if (!in_array($type, $allowed, true)) {
                return $this->respondWithError('Avatar must be an image (JPEG, PNG, GIF, WebP).');
            }

            if ($avatar['size'] > 4 * 1024 * 1024) {
                return $this->respondWithError('Avatar must be less than 4MB.');
            }

            $ext = pathinfo($avatar['name'], PATHINFO_EXTENSION);
            $filename = 'avatar_' . $channel['id'] . '_' . time() . '.' . $ext;
            move_uploaded_file($avatar['tmp_name'], $uploadDir . DIRECTORY_SEPARATOR . $filename);
            $data['avatar'] = '/uploads/channels/' . $filename;
        }

        $banner = $this->request->file('banner');
        if ($banner !== null && $banner['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $banner['tmp_name']);
            finfo_close($finfo);

            if (!in_array($type, $allowed, true)) {
                return $this->respondWithError('Banner must be an image (JPEG, PNG, GIF, WebP).');
            }

            if ($banner['size'] > 6 * 1024 * 1024) {
                return $this->respondWithError('Banner must be less than 6MB.');
            }

            $ext = pathinfo($banner['name'], PATHINFO_EXTENSION);
            $filename = 'banner_' . $channel['id'] . '_' . time() . '.' . $ext;
            move_uploaded_file($banner['tmp_name'], $uploadDir . DIRECTORY_SEPARATOR . $filename);
            $data['banner'] = '/uploads/channels/' . $filename;
        }

        if (!empty($data)) {
            $channelModel->updateById((int) $channel['id'], $data);
        }

        $this->session->flash('success', 'Branding updated successfully.');
        return $this->redirect('/creator/channel/branding');
    }
}
