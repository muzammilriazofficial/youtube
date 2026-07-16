<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Advertisement extends Model
{
    protected string $table = 'advertisements';

    protected bool $timestamps = true;

    protected bool $softDeletes = false;

    protected array $fillable = [
        'campaign_id',
        'title',
        'type',
        'content_url',
        'destination_url',
        'thumbnail',
        'duration',
        'impressions',
        'clicks',
        'spend',
        'status',
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [
        'impressions' => 'integer',
        'clicks'      => 'integer',
        'spend'       => 'float',
        'duration'    => 'integer',
    ];

    public function getCampaign(int $adId): ?array
    {
        $ad = $this->find($adId);
        if ($ad === null) {
            return null;
        }

        return (new AdCampaign())->find((int) $ad['campaign_id']);
    }

    public function getActiveAds(int $limit = 20): array
    {
        $now = date('Y-m-d H:i:s');

        return $this->where('status', 'active')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->orderBy('impressions', 'ASC')
            ->limit($limit)
            ->get();
    }

    public function getAdsByType(string $type, int $limit = 20): array
    {
        return $this->where('type', $type)
            ->where('status', 'active')
            ->orderBy('impressions', 'ASC')
            ->limit($limit)
            ->get();
    }

    public function recordImpression(int $adId): bool
    {
        return $this->db->table('advertisements')
            ->where('id', $adId)
            ->update([
                'impressions' => new \App\Core\RawExpression('impressions + 1'),
            ]) > 0;
    }

    public function recordClick(int $adId): bool
    {
        return $this->db->table('advertisements')
            ->where('id', $adId)
            ->update([
                'clicks' => new \App\Core\RawExpression('clicks + 1'),
            ]) > 0;
    }

    public function updateSpend(int $adId, float $amount): bool
    {
        return $this->db->table('advertisements')
            ->where('id', $adId)
            ->update([
                'spend' => new \App\Core\RawExpression("spend + {$amount}"),
            ]) > 0;
    }

    public function getCampaignAds(int $campaignId, int $limit = 50): array
    {
        return $this->where('campaign_id', $campaignId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }
}
