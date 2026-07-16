<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Report extends Model
{
    protected string $table = 'reports';

    protected bool $timestamps = true;

    protected bool $softDeletes = false;

    protected array $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'reason',
        'description',
        'status',
        'reviewed_by',
        'reviewed_at',
        'resolution',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [];

    public function getPendingReports(int $limit = 50, int $page = 1): array
    {
        return $this->where('status', 'pending')
            ->orderBy('created_at', 'ASC')
            ->paginate($limit, $page);
    }

    public function getReportsByType(string $type, int $limit = 50, int $page = 1): array
    {
        return $this->where('reportable_type', $type)
            ->orderBy('created_at', 'DESC')
            ->paginate($limit, $page);
    }

    public function resolve(int $reportId, int $reviewedBy, string $resolution): bool
    {
        return $this->updateById($reportId, [
            'status'     => 'resolved',
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'resolution'  => $resolution,
        ]);
    }

    public function dismiss(int $reportId, int $reviewedBy, string $resolution = ''): bool
    {
        return $this->updateById($reportId, [
            'status'     => 'dismissed',
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'resolution'  => $resolution,
        ]);
    }

    public function hasUserReported(int $userId, string $type, int $itemId): bool
    {
        return $this->where('reporter_id', $userId)
            ->where('reportable_type', $type)
            ->where('reportable_id', $itemId)
            ->exists();
    }

    public function getReportsForItem(string $type, int $itemId): array
    {
        return $this->where('reportable_type', $type)
            ->where('reportable_id', $itemId)
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}
