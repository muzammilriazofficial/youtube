<?php

declare(strict_types=1);

namespace App\Controllers\Guest;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Video;
use App\Models\Channel;
use App\Models\Comment;
use App\Models\VideoLike;
use App\Models\WatchLater;

class VideoController extends Controller
{
    public function index(): Response
    {
        $page = (int) $this->request->query('page', 1);
        $category = $this->request->query('category');
        $sort = $this->request->query('sort', 'latest');

        $db = \App\Core\Database::getInstance();
        $query = $db->table('videos')
            ->join('channels', 'videos.channel_id', '=', 'channels.id')
            ->select('videos.*', 'channels.name as channel_name', 'channels.slug as channel_slug', 'channels.custom_url as channel_custom_url', 'channels.avatar as channel_avatar')
            ->where('videos.visibility', 'public')
            ->where('videos.status', 'published');

        if ($category) {
            $categoryModel = new \App\Models\Category();
            $cat = $categoryModel->findBySlug($category);
            if ($cat) {
                $query = $query->where('videos.category_id', (int) $cat['id']);
            }
        }

        $sortMap = [
            'latest' => ['videos.published_at', 'DESC'],
            'popular' => ['videos.view_count', 'DESC'],
            'oldest' => ['videos.published_at', 'ASC'],
        ];
        [$sortCol, $sortDir] = $sortMap[$sort] ?? $sortMap['latest'];

        $videos = $query->orderBy($sortCol, $sortDir)->paginate(24, $page);

        return $this->view('guest.videos', [
            'title' => 'Videos',
            'videos' => $videos,
            'currentSort' => $sort,
            'currentCategory' => $category,
        ]);
    }

    public function show(string $slug): Response
    {
        $videoModel = new Video();
        $video = $videoModel->findBySlug($slug);

        if ($video === null) {
            return $this->view('errors.404', ['title' => 'Not Found'], 404);
        }

        $videoModel->incrementViewCount((int) $video['id']);

        $channelModel = new Channel();
        $channel = $channelModel->find((int) $video['channel_id']);

        $commentModel = new Comment();
        $comments = $commentModel->getNestedComments((int) $video['id'], 20);

        $relatedVideos = $videoModel->getRelatedVideos((int) $video['id'], 12);

        $userReaction = null;
        $isWatchLater = false;
        $userChannel = null;
        if ($this->isAuthenticated()) {
            $userId = (int) $this->session->get('user_id');
            $likeModel = new VideoLike();
            $userReaction = $likeModel->getUserReaction($userId, (int) $video['id']);
            $wlModel = new WatchLater();
            $isWatchLater = $wlModel->isWatchLater($userId, (int) $video['id']);
            $userChannel = $channelModel->findByUserId($userId);
        }

        return $this->view('guest.video-show', [
            'title' => $video['title'],
            'video' => $video,
            'channel' => $channel,
            'comments' => $comments,
            'relatedVideos' => $relatedVideos,
            'userReaction' => $userReaction,
            'isWatchLater' => $isWatchLater,
            'userChannel' => $userChannel,
        ]);
    }
}
