<?php

declare(strict_types=1);

namespace App\Controllers\Creator;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Video;
use App\Models\Channel;
use App\Models\Category;

class VideoController extends Controller
{
    public function index(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $videoModel = new Video();

        $channel = $channelModel->findByUserId($userId);
        if ($channel === null) {
            $this->session->flash('error', 'Channel not found.');
            return $this->redirect('/');
        }

        $channelId = (int) $channel['id'];
        $filter = $this->request->input('filter', 'all');
        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = 15;

        $query = $videoModel->db->table('videos')
            ->where('channel_id', $channelId)
            ->whereNull('deleted_at');

        if ($filter === 'published') {
            $query = $query->where('status', 'published')->where('visibility', 'public');
        } elseif ($filter === 'processing') {
            $query = $query->where('status', 'processing');
        } elseif ($filter === 'private') {
            $query = $query->where('visibility', 'private');
        } elseif ($filter === 'unlisted') {
            $query = $query->where('visibility', 'unlisted');
        } elseif ($filter === 'error') {
            $query = $query->where('status', 'error');
        }

        $result = $query->orderBy('created_at', 'DESC')->paginate($perPage, $page);

        return $this->view('creator.videos', [
            'title' => 'Videos',
            'activeMenu' => 'videos',
            'videos' => $result['data'],
            'pagination' => $result,
            'filter' => $filter,
            'channel' => $channel,
        ]);
    }

    public function create(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            $this->session->flash('error', 'Channel not found.');
            return $this->redirect('/');
        }

        $categoryModel = new Category();
        $categories = $categoryModel->db->table('categories')
            ->orderBy('name', 'ASC')
            ->get();

        return $this->view('creator.video-create', [
            'title' => 'Upload Video',
            'activeMenu' => 'videos',
            'channel' => $channel,
            'categories' => $categories,
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
            'category_id' => 'required|numeric',
        ]);

        if (!empty($errors)) {
            return $this->respondWithError('Validation failed: ' . implode(', ', array_merge(...array_values($errors))));
        }

        $data = $this->request->only([
            'title', 'description', 'visibility', 'category_id',
        ]);

        $data['channel_id'] = (int) $channel['id'];
        $data['slug'] = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['title']), '-')) . '-' . bin2hex(random_bytes(4));
        $data['status'] = 'pending';
        $data['processing_status'] = 'pending';
        $data['view_count'] = 0;
        $data['like_count'] = 0;

        $thumbnail = $this->request->file('thumbnail');
        if ($thumbnail !== null && $thumbnail['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $thumbnail['tmp_name']);
            finfo_close($finfo);

            if (in_array($type, $allowed, true)) {
                $uploadDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'thumbnails';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = pathinfo($thumbnail['name'], PATHINFO_EXTENSION);
                $filename = 'thumb_' . bin2hex(random_bytes(8)) . '.' . $ext;
                move_uploaded_file($thumbnail['tmp_name'], $uploadDir . DIRECTORY_SEPARATOR . $filename);
                $data['thumbnail_path'] = '/uploads/thumbnails/' . $filename;
            }
        }

        $tags = $this->request->input('tags', '');
        if (!empty($tags)) {
            $data['seo_keywords'] = $tags;
        }

        $videoModel = new Video();
        $video = $videoModel->create($data);

        $this->session->flash('success', 'Video info saved. Now upload the video file.');
        return $this->redirect('/creator/videos/' . $video['id'] . '/edit');
    }

    public function upload(): Response
    {
        if (!$this->request->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $userId = (int) $this->session->get('user_id');
        $videoId = (int) $this->request->input('video_id', 0);

        if ($videoId === 0) {
            return $this->json(['error' => 'Video ID required'], 400);
        }

        $videoModel = new Video();
        $video = $videoModel->find($videoId);

        if ($video === null) {
            return $this->json(['error' => 'Video not found'], 404);
        }

        $file = $this->request->file('video_file');
        if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit.',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            ];
            $errCode = $file['error'] ?? UPLOAD_ERR_NO_FILE;
            return $this->json(['error' => $errorMessages[$errCode] ?? 'Upload error occurred.'], 400);
        }

        $allowed = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo', 'video/x-flv', 'video/mpeg'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($type, $allowed, true)) {
            return $this->json(['error' => 'Invalid video format. Allowed: MP4, WebM, MOV, AVI, FLV, MPEG.'], 400);
        }

        $maxSize = 10 * 1024 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return $this->json(['error' => 'File size must be less than 10GB.'], 400);
        }

        $uploadDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'videos';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'mp4';
        $filename = 'video_' . $videoId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return $this->json(['error' => 'Failed to save uploaded file.'], 500);
        }

        $videoModel->updateById($videoId, [
            'file_path' => '/uploads/videos/' . $filename,
            'file_size' => $file['size'],
            'status' => 'processing',
        ]);

        $this->simulateProcessing($videoId);

        return $this->json([
            'success' => true,
            'video_id' => $videoId,
            'message' => 'Video uploaded successfully. Processing...',
        ]);
    }

    public function uploadChunk(): Response
    {
        if (!$this->request->isPost() || !$this->validateCsrf()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $videoId = (int) $this->request->input('video_id', 0);
        $chunkIndex = (int) $this->request->input('chunk_index', 0);
        $totalChunks = (int) $this->request->input('total_chunks', 1);

        if ($videoId === 0) {
            return $this->json(['error' => 'Video ID required'], 400);
        }

        $file = $this->request->file('chunk');
        if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
            return $this->json(['error' => 'Chunk upload failed'], 400);
        }

        $chunkDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'chunks' . DIRECTORY_SEPARATOR . $videoId;
        if (!is_dir($chunkDir)) {
            mkdir($chunkDir, 0755, true);
        }

        $chunkPath = $chunkDir . DIRECTORY_SEPARATOR . $chunkIndex . '.chunk';
        move_uploaded_file($file['tmp_name'], $chunkPath);

        $uploadedChunks = glob($chunkDir . '/*.chunk');
        $uploadedCount = count($uploadedChunks);

        if ($uploadedCount >= $totalChunks) {
            $uploadDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'videos';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = 'video_' . $videoId . '_' . bin2hex(random_bytes(8)) . '.mp4';
            $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;
            $out = fopen($destination, 'wb');

            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkFile = $chunkDir . DIRECTORY_SEPARATOR . $i . '.chunk';
                if (file_exists($chunkFile)) {
                    $in = fopen($chunkFile, 'rb');
                    stream_copy_to_stream($in, $out);
                    fclose($in);
                    unlink($chunkFile);
                }
            }

            fclose($out);
            rmdir($chunkDir);

            $totalSize = filesize($destination);
            $videoModel = new Video();
            $videoModel->updateById($videoId, [
                'file_path' => '/uploads/videos/' . $filename,
                'file_size' => $totalSize,
                'status' => 'processing',
            ]);

            $this->simulateProcessing($videoId);

            return $this->json([
                'success' => true,
                'complete' => true,
                'video_id' => $videoId,
                'message' => 'Upload complete. Processing...',
            ]);
        }

        return $this->json([
            'success' => true,
            'complete' => false,
            'uploaded' => $uploadedCount,
            'total' => $totalChunks,
            'progress' => round(($uploadedCount / $totalChunks) * 100),
        ]);
    }

    public function processStatus(): Response
    {
        $videoId = (int) $this->request->input('video_id', 0);
        if ($videoId === 0) {
            return $this->json(['error' => 'Video ID required'], 400);
        }

        $videoModel = new Video();
        $video = $videoModel->find($videoId);

        if ($video === null) {
            return $this->json(['error' => 'Video not found'], 404);
        }

        return $this->json([
            'video_id' => $videoId,
            'status' => $video['status'],
            'file_path' => $video['file_path'] ?? null,
            'duration' => $video['duration'] ?? null,
        ]);
    }

    public function edit(string $id): Response
    {
        $userId = (int) $this->session->get('user_id');
        $videoModel = new Video();
        $video = $videoModel->find((int) $id);

        if ($video === null || (int) $video['channel_id'] !== $this->getChannelId($userId)) {
            $this->session->flash('error', 'Video not found.');
            return $this->redirect('/creator/videos');
        }

        $categoryModel = new Category();
        $categories = $categoryModel->db->table('categories')
            ->orderBy('name', 'ASC')
            ->get();

        return $this->view('creator.video-edit', [
            'title' => 'Edit Video',
            'activeMenu' => 'videos',
            'video' => $video,
            'categories' => $categories,
        ]);
    }

    public function update(string $id): Response
    {
        if (!$this->validateCsrf()) {
            return $this->respondWithError('Invalid CSRF token.');
        }

        $userId = (int) $this->session->get('user_id');
        $videoModel = new Video();
        $video = $videoModel->find((int) $id);

        if ($video === null || (int) $video['channel_id'] !== $this->getChannelId($userId)) {
            return $this->respondWithError('Video not found.');
        }

        $errors = $this->validate([
            'title' => 'required|max:100',
            'description' => 'max:5000',
            'visibility' => 'in:public,private,unlisted',
            'category_id' => 'required|numeric',
        ]);

        if (!empty($errors)) {
            return $this->respondWithError('Validation failed: ' . implode(', ', array_merge(...array_values($errors))));
        }

        $data = $this->request->only([
            'title', 'description', 'visibility', 'category_id',
        ]);

        $tags = $this->request->input('tags', '');
        $data['tags'] = !empty($tags) ? json_encode(array_map('trim', explode(',', $tags))) : '[]';

        $thumbnail = $this->request->file('thumbnail');
        if ($thumbnail !== null && $thumbnail['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $thumbnail['tmp_name']);
            finfo_close($finfo);

            if (in_array($type, $allowed, true)) {
                $uploadDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'thumbnails';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = pathinfo($thumbnail['name'], PATHINFO_EXTENSION);
                $filename = 'thumb_' . bin2hex(random_bytes(8)) . '.' . $ext;
                move_uploaded_file($thumbnail['tmp_name'], $uploadDir . DIRECTORY_SEPARATOR . $filename);
                $data['thumbnail_path'] = '/uploads/thumbnails/' . $filename;
            }
        }

        if (!empty($data['title']) && $data['title'] !== $video['title']) {
            $data['slug'] = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['title']), '-')) . '-' . bin2hex(random_bytes(4));
        }

        if (($video['status'] === 'draft') && ($data['visibility'] ?? '') === 'public') {
            $data['status'] = 'published';
            $data['published_at'] = date('Y-m-d H:i:s');
            $channelModel = new Channel();
            $channelModel->incrementVideoCount((int) $video['channel_id']);
        }

        $videoModel->updateById((int) $id, $data);

        $this->session->flash('success', 'Video updated successfully.');
        return $this->redirect('/creator/videos/' . $id . '/edit');
    }

    public function delete(string $id): Response
    {
        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $userId = (int) $this->session->get('user_id');
        $videoModel = new Video();
        $video = $videoModel->find((int) $id);

        if ($video === null || (int) $video['channel_id'] !== $this->getChannelId($userId)) {
            return $this->json(['error' => 'Video not found'], 404);
        }

        $videoModel->deleteById((int) $id);

        $channelModel = new Channel();
        $channelModel->db->table('channels')
            ->where('id', $video['channel_id'])
            ->update([
                'video_count' => new \App\Core\RawExpression('GREATEST(video_count - 1, 0)'),
            ]);

        if ($this->request->expectsJson()) {
            return $this->json(['success' => true, 'message' => 'Video deleted.']);
        }

        $this->session->flash('success', 'Video moved to trash.');
        return $this->redirect('/creator/videos');
    }

    public function drafts(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelId = $this->getChannelId($userId);
        $videoModel = new Video();
        $page = max(1, (int) $this->request->input('page', 1));

        $result = $videoModel->db->table('videos')
            ->where('channel_id', $channelId)
            ->whereNull('deleted_at')
            ->where(function ($query) {
                $query->where('status', 'draft')
                    ->orWhere('visibility', 'unlisted');
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(15, $page);

        return $this->view('creator.videos', [
            'title' => 'Draft Videos',
            'activeMenu' => 'videos',
            'videos' => $result['data'],
            'pagination' => $result,
            'filter' => 'drafts',
        ]);
    }

    private function getChannelId(int $userId): int
    {
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);
        return $channel ? (int) $channel['id'] : 0;
    }

    private function simulateProcessing(int $videoId): void
    {
        $videoModel = new Video();
        $video = $videoModel->find($videoId);

        $duration = 0;
        if ($video && !empty($video['file_path'])) {
            $fullPath = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . $video['file_path'];
            if (file_exists($fullPath)) {
                $duration = (int) round($this->getMp4Duration($fullPath) ?? 0);
            }
        }

        $videoModel->updateById($videoId, [
            'status' => 'published',
            'duration' => $duration,
            'published_at' => date('Y-m-d H:i:s'),
        ]);

        if ($video) {
            $channelModel = new Channel();
            $channelModel->incrementVideoCount((int) $video['channel_id']);
        }
    }

    private function getMp4Duration(string $path): ?float
    {
        $fp = @fopen($path, 'rb');
        if (!$fp) return null;

        $size = filesize($path);
        $offset = 0;

        while ($offset < $size - 8) {
            fseek($fp, $offset);
            $header = fread($fp, 8);
            if (strlen($header) < 8) break;

            $boxSize = unpack('N', substr($header, 0, 4))[1];
            $boxType = substr($header, 4, 4);

            if ($boxSize < 8) break;

            if ($boxType === 'moov') {
                $moovData = fread($fp, $boxSize - 8);
                fclose($fp);
                if ($moovData === false) return null;
                return $this->parseMoovAtom($moovData);
            }

            $offset += $boxSize;
        }

        fclose($fp);
        return null;
    }

    private function parseMoovAtom(string $data): ?float
    {
        $pos = 0;
        $len = strlen($data);

        while ($pos < $len - 8) {
            $boxSize = unpack('N', substr($data, $pos, 4))[1];
            $boxType = substr($data, $pos + 4, 4);

            if ($boxSize < 8) break;

            if ($boxType === 'mvhd') {
                $ver = ord($data[$pos + 8]);
                if ($ver === 0) {
                    $timescale = unpack('N', substr($data, $pos + 20, 4))[1];
                    $duration = unpack('N', substr($data, $pos + 24, 4))[1];
                } else {
                    $timescale = unpack('N', substr($data, $pos + 28, 4))[1];
                    $duration = unpack('J', substr($data, $pos + 32, 8))[1];
                }
                return $timescale > 0 ? $duration / $timescale : null;
            }

            $pos += $boxSize;
        }
        return null;
    }
}
