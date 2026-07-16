<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ActivityLog extends Model
{
    protected string $table = 'activity_logs';

    protected bool $timestamps = true;

    protected bool $softDeletes = false;

    protected array $fillable = [
        'user_id',
        'action',
        'description',
        'properties',
        'ip_address',
        'user_agent',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [
        'properties' => 'array',
    ];

    public function getUserActivity(int $userId, int $limit = 50, int $page = 1): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->paginate($limit, $page);
    }

    public function log(int $userId, string $action, string $description = '', array $properties = []): array
    {
        $request = \App\Core\Request::getInstance();

        return $this->create([
            'user_id'     => $userId,
            'action'      => $action,
            'description' => $description,
            'properties'  => !empty($properties) ? json_encode($properties) : null,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);
    }

    public function getActionCount(string $action, int $days = 30): int
    {
        $startDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        return $this->where('action', $action)
            ->where('created_at', '>=', $startDate)
            ->count();
    }

    public function getRecentActions(int $limit = 20): array
    {
        $startDate = date('Y-m-d H:i:s', strtotime('-24 hours'));

        return $this->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function getDailyActivity(int $days = 30): array
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        return $this->db->table('activity_logs')
            ->where('created_at', '>=', $startDate)
            ->select('DATE(created_at) as date', 'COUNT(*) as count')
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->get();
    }

    public function getMostActiveUsers(int $limit = 20, int $days = 30): array
    {
        $startDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        return $this->db->table('activity_logs')
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->where('activity_logs.created_at', '>=', $startDate)
            ->select('users.id', 'users.username', 'COUNT(*) as activity_count')
            ->groupBy('users.id', 'users.username')
            ->orderBy('activity_count', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function cleanup(int $daysToKeep = 90): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));

        return $this->db->table('activity_logs')
            ->where('created_at', '<', $cutoff)
            ->delete();
    }
}
