<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Response;
use App\Core\Database;
use App\Models\Channel;
use App\Models\Video;
use App\Models\Subscription;
use App\Models\Playlist;
use App\Services\SecurityService;

class ChannelApiController extends ApiController
{
    public function index(): Response
    {
        $channelModel = new Channel();
        $page  = $this->getPage();
        $limit = $this->getPerPage();
        $sort  = $this->getSort('subscribers');

        $sortMap = [
            'subscribers' => ['subscriber_count', 'DESC'],
            'latest'      => ['created_at', 'DESC'],
            'name'        => ['name', 'ASC'],
        ];
        [$sortCol, $sortDir] = $sortMap[$sort] ?? $sortMap['subscribers'];

        $channels = $channelModel->orderBy($sortCol, $sortDir)->paginate($limit, $page);

        return $this->paginatedResponse($channels);
    }

    public function show(string $id): Response
    {
        $channelModel = new Channel();
        $channel      = $channelModel->find((int) $id);

        if ($channel === null) {
            return $this->error('Channel not found.', 404);
        }

        $videoModel = new Video();
        $channel['videos'] = $videoModel->where('channel_id', (int) $channel['id'])
            ->where('visibility', 'public')
            ->where('status', 'published')
            ->orderBy('published_at', 'DESC')
            ->limit(12)
            ->get();

        $channel['is_subscribed'] = false;
        if ($this->getApiUser() !== null) {
            $subModel = new Subscription();
            $channel['is_subscribed'] = $subModel->isSubscribed(
                (int) $this->apiUser['id'],
                (int) $channel['id']
            );
        }

        return $this->success($channel);
    }

    public function update(string $id): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $channelModel = new Channel();
        $channel      = $channelModel->find((int) $id);

        if ($channel === null) {
            return $this->error('Channel not found.', 404);
        }

        $ownershipCheck = $this->authorizeChannelOwnership($channel);
        if ($ownershipCheck instanceof Response) {
            return $ownershipCheck;
        }

        $data = $this->request->only(['name', 'description', 'country', 'website', 'custom_url', 'avatar', 'banner']);

        if (isset($data['name'])) {
            $data['name'] = $this->sanitize($data['name']);
        }
        if (isset($data['description'])) {
            $data['description'] = $this->sanitize($data['description']);
        }

        $channelModel->updateById((int) $id, $data);

        SecurityService::getInstance()->logActivity(
            (int) $this->apiUser['id'],
            'channel_updated',
            'channel',
            (int) $id
        );

        return $this->success($channelModel->find((int) $id), 'Channel updated successfully.');
    }

    public function subscribe(string $id): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $channelModel = new Channel();
        $channel      = $channelModel->find((int) $id);

        if ($channel === null) {
            return $this->error('Channel not found.', 404);
        }

        if ((int) $channel['user_id'] === (int) $this->apiUser['id']) {
            return $this->error('You cannot subscribe to your own channel.', 400);
        }

        $subModel     = new Subscription();
        $isSubscribed = $subModel->isSubscribed((int) $this->apiUser['id'], (int) $id);

        if ($isSubscribed) {
            $db = Database::getInstance();
            $db->table('subscriptions')
                ->where('subscriber_id', (int) $this->apiUser['id'])
                ->where('channel_id', (int) $id)
                ->delete();

            $channelModel->decrementSubscribers((int) $id);
            $action = 'unsubscribed';
        } else {
            $subModel->create([
                'subscriber_id' => (int) $this->apiUser['id'],
                'channel_id'    => (int) $id,
            ]);
            $channelModel->incrementSubscribers((int) $id);
            $action = 'subscribed';

            $notiModel = new \App\Models\Notification();
            $notiModel->createNotification(
                (int) $channel['user_id'],
                (int) $this->apiUser['id'],
                'subscription',
                'New Subscriber',
                $this->apiUser['display_name'] . ' subscribed to your channel.',
                ['channel_id' => (int) $id]
            );
        }

        $updatedChannel = $channelModel->find((int) $id);

        return $this->success([
            'action'           => $action,
            'subscriber_count' => $updatedChannel['subscriber_count'] ?? 0,
        ], 'Subscription updated.');
    }

    public function videos(string $id): Response
    {
        $channelModel = new Channel();
        $channel      = $channelModel->find((int) $id);

        if ($channel === null) {
            return $this->error('Channel not found.', 404);
        }

        $page       = $this->getPage();
        $limit      = $this->getPerPage();
        $sort       = $this->getSort('latest');
        $visibility = $this->request->query('visibility', 'public');

        $sortMap = [
            'latest'  => ['published_at', 'DESC'],
            'oldest'  => ['published_at', 'ASC'],
            'popular' => ['view_count', 'DESC'],
        ];
        [$sortCol, $sortDir] = $sortMap[$sort] ?? $sortMap['latest'];

        $videoModel = new Video();
        $query = $videoModel->where('channel_id', (int) $id);

        if ($visibility !== 'all') {
            if ($this->getApiUser() === null || (int) $channel['user_id'] !== (int) $this->apiUser['id']) {
                $query = $query->where('visibility', 'public');
            } elseif ($visibility !== 'public') {
                $query = $query->where('visibility', $visibility);
            }
        }

        $videos = $query->orderBy($sortCol, $sortDir)->paginate($limit, $page);

        return $this->paginatedResponse($videos);
    }

    public function playlists(string $id): Response
    {
        $channelModel = new Channel();
        $channel      = $channelModel->find((int) $id);

        if ($channel === null) {
            return $this->error('Channel not found.', 404);
        }

        $playlistModel = new Playlist();
        $playlists = $playlistModel->where('user_id', (int) $channel['user_id'])
            ->where('visibility', 'public')
            ->orderBy('updated_at', 'DESC')
            ->get();

        return $this->success($playlists, 'Channel playlists.');
    }
}
