<?php

declare(strict_types=1);

namespace App\Controllers\Creator;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Video;
use App\Models\Channel;

class ShortController extends Controller
{
    public function index(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            $this->session->flash('error', 'Channel not found.');
            return $this->redirect('/');
        }

        $channelId = (int) $channel['id'];
        $page = max(1, (int) $this->request->input('page', 1));

        $result = (new Video())->db->table('videos')
            ->where('channel_id', $channelId)
            ->where('is_short', 1)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('creator.shorts', [
            'title' => 'Shorts',
            'activeMenu' => 'shorts',
            'shorts' => $result['data'],
            'pagination' => $result,
            'channel' => $channel,
        ]);
    }

    public function upload(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            $this->session->flash('error', 'Channel not found.');
            return $this->redirect('/');
        }

        return $this->view('creator.short-upload', [
            'title' => 'Upload Short',
            'activeMenu' => 'shorts',
            'channel' => $channel,
        ]);
    }

    public function store(): Response
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
            'title' => 'required|max:100',
            'description' => 'max:5000',
            'visibility' => 'in:public,private,unlisted',
        ]);

        if (!empty($errors)) {
            return $this->respondWithError('Validation failed: ' . implode(', ', array_merge(...array_values($errors))));
        }

        $file = $this->request->file('short_video');
        if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
            return $this->respondWithError('Please upload a short video file.');
        }

        $allowed = ['video/mp4', 'video/webm', 'video/quicktime'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($type, $allowed, true)) {
            return $this->respondWithError('Invalid video format. Allowed: MP4, WebM, MOV.');
        }

        if ($file['size'] > 100 * 1024 * 1024) {
            return $this->respondWithError('Short video must be less than 100MB.');
        }

        $uploadDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'shorts';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'mp4';
        $filename = 'short_' . bin2hex(random_bytes(8)) . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $uploadDir . DIRECTORY_SEPARATOR . $filename);

        $videoModel = new Video();
        $video = $videoModel->create([
            'channel_id' => (int) $channel['id'],
            'title' => $this->request->input('title'),
            'slug' => strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $this->request->input('title')), '-')) . '-' . bin2hex(random_bytes(4)),
            'description' => $this->request->input('description', ''),
            'file_path' => '/uploads/shorts/' . $filename,
            'file_size' => $file['size'],
            'visibility' => $this->request->input('visibility', 'public'),
            'status' => 'published',
            'is_short' => 1,
            'view_count' => 0,
            'like_count' => 0,
            'dislike_count' => 0,
            'comment_count' => 0,
            'published_at' => date('Y-m-d H:i:s'),
        ]);

        $channelModel->incrementVideoCount((int) $channel['id']);

        $thumbnail = $this->request->file('thumbnail');
        if ($thumbnail !== null && $thumbnail['error'] === UPLOAD_ERR_OK) {
            $thumbAllowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $thumbType = finfo_file($finfo, $thumbnail['tmp_name']);
            finfo_close($finfo);

            if (in_array($thumbType, $thumbAllowed, true)) {
                $thumbDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'thumbnails';
                if (!is_dir($thumbDir)) {
                    mkdir($thumbDir, 0755, true);
                }
                $thumbExt = pathinfo($thumbnail['name'], PATHINFO_EXTENSION);
                $thumbFilename = 'thumb_short_' . bin2hex(random_bytes(8)) . '.' . $thumbExt;
                move_uploaded_file($thumbnail['tmp_name'], $thumbDir . DIRECTORY_SEPARATOR . $thumbFilename);
                $videoModel->updateById((int) $video['id'], ['thumbnail' => '/uploads/thumbnails/' . $thumbFilename]);
            }
        }

        $this->session->flash('success', 'Short uploaded successfully.');
        return $this->redirect('/creator/shorts');
    }
}
