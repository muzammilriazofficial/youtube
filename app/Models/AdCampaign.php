<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AdCampaign extends Model
{
    protected string $table = 'ad_campaigns';

    protected bool $timestamps = true;

    protected bool $softDeletes = false;

    protected array $fillable = [
        'user_id',
        'name',
        'budget',
        'daily_budget',
        'spent',
        'status',
        'target_audience',
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [
        'budget'       => 'float',
        'daily_budget' => 'float',
        'spent'        => 'float',
    ];

    public function getUserCampaigns(int $userId, int $limit = 50): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function getActiveCampaigns(int $limit = 50): array
    {
        $now = date('Y-m-d H:i:s');

        return $this->where('status', 'active')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function pauseCampaign(int $campaignId): bool
    {
        return $this->updateById($campaignId, ['status' => 'paused']);
    }

    public function resumeCampaign(int $campaignId): bool
    {
        return $this->updateById($campaignId, ['status' => 'active']);
    }

    public function completeCampaign(int $campaignId): bool
    {
        return $this->updateById($campaignId, ['status' => 'completed']);
    }

    public function addSpend(int $campaignId, float $amount): bool
    {
        return $this->db->table('ad_campaigns')
            ->where('id', $campaignId)
            ->update([
                'spent' => new \App\Core\RawExpression("spent + {$amount}"),
            ]) > 0;
    }

    public function hasBudgetRemaining(int $campaignId): bool
    {
        $campaign = $this->find($campaignId);

        if ($campaign === null) {
            return false;
        }

        return $campaign['spent'] < $campaign['budget'];
    }

    public function getStats(int $campaignId): array
    {
        $ads = (new Advertisement())->getCampaignAds($campaignId);
        $campaign = $this->find($campaignId);

        $totalImpressions = array_sum(array_column($ads, 'impressions'));
        $totalClicks = array_sum(array_column($ads, 'clicks'));

        return [
            'campaign'        => $campaign,
            'total_impressions' => $totalImpressions,
            'total_clicks'      => $totalClicks,
            'ctr'               => $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0,
            'ad_count'          => count($ads),
            'budget_remaining'  => $campaign['budget'] - $campaign['spent'],
        ];
    }
}
