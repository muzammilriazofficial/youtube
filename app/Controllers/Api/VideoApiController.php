<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Response;
use App\Core\Database;
use App\Models\Video;
use App\Models\Channel;
use App\Models\VideoLike;
use App\Models\Comment;
use App\Services\SecurityService;

class VideoApiController extends ApiController
{
    public function index(): Response
    {
        $videoModel = new Video();
        $page       = $this->getPage();
        $limit      = $this->getPerPage();
        $category   = $this->request->query('category');
        $sort       = $this->getSort('latest');
        $status     = $this->request->query('status', 'published');

        $query = $videoModel->where('visibility', 'public');

        if ($status !== 'all') {
            $query = $query->where('status', $status);
        }

        if ($category !== null) {
            $query = $query->where('category_id', (int) $category);
        }

        $sortMap = [
            'latest'   => ['published_at', 'DESC'],
            'oldest'   => ['published_at', 'ASC'],
            'popular'  => ['view_count', 'DESC'],
            'likes'    => ['like_count', 'DESC'],
        ];
        [$sortCol, $sortDir] = $sortMap[$sort] ?? $sortMap['latest'];

        $videos = $query->orderBy($sortCol, $sortDir)->paginate($limit, $page);

        return $this->paginatedResponse($videos);
    }

    public function show(string $id): Response
    {
        $videoModel = new Video();
        $video      = $videoModel->find((int) $id);

        if ($video === null) {
            return $this->error('Video not found.', 404);
        }

        if ($video['visibility'] !== 'public' && $this->getApiUser() === null) {
            return $this->error('This video is not available.', 403);
        }

        $channelModel = new Channel();
        $channel      = $channelModel->find((int) $video['channel_id']);

        $videoModel->incrementViewCount((int) $video['id']);

        $video['channel'] = $channel;

        $commentModel  = new Comment();
        $video['comments'] = $commentModel->getVideoComments((int) $video['id'], 5);

        $video['related'] = $videoModel->getRelatedVideos((int) $video['id'], 8);

        return $this->success($video);
    }

    public function store(): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $errors = $this->validate([
            'title'        => 'required|max:100',
            'description'  => 'max:5000',
            'category_id'  => 'required|numeric',
            'visibility'   => 'in:public,unlisted,private',
        ]);

        if (!empty($errors)) {
            return $this->error('Validation failed.', 422, $errors);
        }

        $channelModel = new Channel();
        $channel      = $channelModel->findByUserId((int) $this->apiUser['id']);

        if ($channel === null) {
            return $this->error('You must create a channel before uploading videos.', 400);
        }

        $title       = $this->sanitize($this->request->input('title'));
        $description = $this->sanitize($this->request->input('description', ''));

        $videoModel = new Video();
        $video = $videoModel->create([
            'channel_id'     => (int) $channel['id'],
            'title'          => $title,
            'slug'           => slugify($title),
            'description'    => $description,
            'category_id'    => (int) $this->request->input('category_id'),
            'visibility'     => $this->request->input('visibility', 'private'),
            'status'         => 'draft',
            'view_count'     => 0,
            'like_count'     => 0,
            'dislike_count'  => 0,
            'comment_count'  => 0,
        ]);

        SecurityService::getInstance()->logActivity(
            (int) $this->apiUser['id'],
            'video_created',
            'video',
            (int) $video['id']
        );

        return $this->created($video, 'Video created successfully.');
    }

    public function update(string $id): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $videoModel = new Video();
        $video      = $videoModel->find((int) $id);

        if ($video === null) {
            return $this->error('Video not found.', 404);
        }

        $channelModel = new Channel();
        $channel      = $channelModel->find((int) $video['channel_id']);

        $ownershipCheck = $this->authorizeChannelOwnership($channel);
        if ($ownershipCheck instanceof Response) {
            return $ownershipCheck;
        }

        $data = $this->request->only(['title', 'description', 'category_id', 'visibility', 'status']);

        if (isset($data['title'])) {
            $data['title'] = $this->sanitize($data['title']);
            $data['slug']  = slugify($data['title']);
        }

        if (isset($data['description'])) {
            $data['description'] = $this->sanitize($data['description']);
        }

        if (isset($data['visibility']) && !in_array($data['visibility'], ['public', 'unlisted', 'private'], true)) {
            return $this->error('Invalid visibility value.', 422);
        }

        if (isset($data['status']) && !in_array($data['status'], ['draft', 'published', 'archived'], true)) {
            return $this->error('Invalid status value.', 422);
        }

        $videoModel->updateById((int) $id, $data);

        SecurityService::getInstance()->logActivity(
            (int) $this->apiUser['id'],
            'video_updated',
            'video',
            (int) $id
        );

        return $this->success($videoModel->find((int) $id), 'Video updated successfully.');
    }

    public function delete(string $id): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $videoModel = new Video();
        $video      = $videoModel->find((int) $id);

        if ($video === null) {
            return $this->error('Video not found.', 404);
        }

        $channelModel = new Channel();
        $channel      = $channelModel->find((int) $video['channel_id']);

        $ownershipCheck = $this->authorizeChannelOwnership($channel);
        if ($ownershipCheck instanceof Response) {
            return $ownershipCheck;
        }

        $videoModel->deleteById((int) $id);

        SecurityService::getInstance()->logActivity(
            (int) $this->apiUser['id'],
            'video_deleted',
            'video',
            (int) $id
        );

        return $this->deleted('Video deleted successfully.');
    }

    public function like(string $id): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $videoModel = new Video();
        $video      = $videoModel->find((int) $id);

        if ($video === null) {
            return $this->error('Video not found.', 404);
        }

        $likeModel = new VideoLike();
        $result    = $likeModel->toggleLike((int) $this->apiUser['id'], (int) $id);

        return $this->success([
            'action'    => $result,
            'like_count' => $videoModel->find((int) $id)['like_count'] ?? 0,
        ], 'Reaction updated.');
    }

    public function view(string $id): Response
    {
        $videoModel = new Video();
        $video      = $videoModel->find((int) $id);

        if ($video === null) {
            return $this->error('Video not found.', 404);
        }

        $videoModel->incrementViewCount((int) $id);

        if ($this->getApiUser() !== null) {
            $watchHistory = new \App\Models\WatchHistory();
            $watchHistory->recordWatch(
                (int) $this->apiUser['id'],
                (int) $id,
                0,
                0,
                false
            );
        }

        return $this->success([
            'view_count' => $videoModel->find((int) $id)['view_count'] ?? 0,
        ], 'View recorded.');
    }

    public function comments(string $id): Response
    {
        $videoModel = new Video();
        $video      = $videoModel->find((int) $id);

        if ($video === null) {
            return $this->error('Video not found.', 404);
        }

        $page  = $this->getPage();
        $limit = $this->getPerPage(50);

        $commentModel = new Comment();
        $comments     = $commentModel->getVideoComments((int) $id, $limit, $page);

        return $this->paginatedResponse($comments);
    }

    public function trending(): Response
    {
        $videoModel = new Video();
        $limit      = min((int) $this->request->query('limit', 20), 50);

        $oneWeekAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
        $videos = $videoModel->where('visibility', 'public')
            ->where('status', 'published')
            ->where('published_at', '>', $oneWeekAgo)
            ->orderBy('view_count', 'DESC')
            ->limit($limit)
            ->get();

        return $this->success($videos, 'Trending videos.');
    }

    public function related(string $id): Response
    {
        $videoModel = new Video();
        $video      = $videoModel->find((int) $id);

        if ($video === null) {
            return $this->error('Video not found.', 404);
        }

        $limit = min((int) $this->request->query('limit', 12), 30);
        $related = $videoModel->getRelatedVideos((int) $id, $limit);

        return $this->success($related, 'Related videos.');
    }
}
