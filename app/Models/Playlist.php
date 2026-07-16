<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Playlist extends Model
{
    protected string $table = 'playlists';

    protected bool $timestamps = true;

    protected bool $softDeletes = true;

    protected array $fillable = [
        'user_id',
        'title',
        'description',
        'visibility',
        'video_count',
        'thumbnail',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [
        'video_count' => 'integer',
    ];

    public function getUser(int $playlistId): ?array
    {
        $playlist = $this->find($playlistId);
        if ($playlist === null) {
            return null;
        }

        return (new User())->find((int) $playlist['user_id']);
    }

    public function getVideos(int $playlistId, int $limit = 50): array
    {
        return $this->db->table('playlist_videos')
            ->join('videos', 'playlist_videos.video_id', '=', 'videos.id')
            ->where('playlist_videos.playlist_id', $playlistId)
            ->whereNull('videos.deleted_at')
            ->orderBy('playlist_videos.position', 'ASC')
            ->limit($limit)
            ->get();
    }

    public function getUserPlaylists(int $userId, int $limit = 50): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('updated_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function addVideo(int $playlistId, int $videoId, int $position = 0): bool
    {
        $existing = $this->db->table('playlist_videos')
            ->where('playlist_id', $playlistId)
            ->where('video_id', $videoId)
            ->first();

        if ($existing !== null) {
            return false;
        }

        if ($position === 0) {
            $maxPos = $this->db->table('playlist_videos')
                ->where('playlist_id', $playlistId)
                ->first();

            $position = ($maxPos['position'] ?? -1) + 1;
        }

        $result = $this->db->table('playlist_videos')->insert([
            'playlist_id' => $playlistId,
            'video_id'    => $videoId,
            'position'    => $position,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        if ($result) {
            $this->db->table('playlists')
                ->where('id', $playlistId)
                ->update([
                    'video_count' => new \App\Core\RawExpression('video_count + 1'),
                ]);
        }

        return (bool) $result;
    }

    public function removeVideo(int $playlistId, int $videoId): bool
    {
        $result = $this->db->table('playlist_videos')
            ->where('playlist_id', $playlistId)
            ->where('video_id', $videoId)
            ->delete();

        if ($result > 0) {
            $this->db->table('playlists')
                ->where('id', $playlistId)
                ->update([
                    'video_count' => new \App\Core\RawExpression('GREATEST(video_count - 1, 0)'),
                ]);
        }

        return $result > 0;
    }
}
