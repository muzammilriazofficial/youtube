<?php

declare(strict_types=1);

namespace App\Controllers\Creator;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Channel;

class CommunityController extends Controller
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

        $result = $channelModel->db->table('community_posts')
            ->join('channels', 'community_posts.channel_id', '=', 'channels.id')
            ->where('community_posts.channel_id', $channelId)
            ->whereNull('community_posts.deleted_at')
            ->orderBy('community_posts.created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('creator.community', [
            'title' => 'Community Posts',
            'activeMenu' => 'community',
            'posts' => $result['data'],
            'pagination' => $result,
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
            'content' => 'required|max:10000',
            'post_type' => 'in:text,poll,image,video',
        ]);

        if (!empty($errors)) {
            return $this->respondWithError('Validation failed: ' . implode(', ', array_merge(...array_values($errors))));
        }

        $data = [
            'channel_id' => (int) $channel['id'],
            'content' => $this->request->input('content'),
            'post_type' => $this->request->input('post_type', 'text'),
            'like_count' => 0,
            'comment_count' => 0,
            'vote_count' => 0,
        ];

        $image = $this->request->file('post_image');
        if ($image !== null && $image['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $image['tmp_name']);
            finfo_close($finfo);

            if (in_array($type, $allowed, true)) {
                $uploadDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'community';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
                $filename = 'post_' . bin2hex(random_bytes(8)) . '.' . $ext;
                move_uploaded_file($image['tmp_name'], $uploadDir . DIRECTORY_SEPARATOR . $filename);
                $data['image_path'] = '/uploads/community/' . $filename;
            }
        }

        $pollOptions = $this->request->input('poll_options', '');
        if (!empty($pollOptions) && $data['post_type'] === 'poll') {
            $options = array_filter(array_map('trim', explode("\n", $pollOptions)));
            $data['poll_options'] = json_encode($options);
        }

        $channelModel->db->table('community_posts')->insert($data);

        $this->session->flash('success', 'Community post published.');
        return $this->redirect('/creator/community');
    }
}
