<?php

declare(strict_types=1);

namespace App\Controllers\Advertiser;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class AnalyticsController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('advertiser', 'admin')) {
            return $this->redirect('/');
        }

        $userId = (int) $this->session->get('user_id');
        $db = Database::getInstance();

        $totalImpressions = (int) $db->table('advertisements')
            ->where('advertiser_id', $userId)
            ->sum('impressions');

        $totalClicks = (int) $db->table('advertisements')
            ->where('advertiser_id', $userId)
            ->sum('clicks');

        $totalSpend = (float) $db->table('advertisements')
            ->where('advertiser_id', $userId)
            ->sum('spend');

        $ctr = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0;
        $cpc = $totalClicks > 0 ? round($totalSpend / $totalClicks, 2) : 0;
        $cpm = $totalImpressions > 0 ? round(($totalSpend / $totalImpressions) * 1000, 2) : 0;

        $byType = $db->table('advertisements')
            ->where('advertiser_id', $userId)
            ->select('type', 'SUM(impressions) as impressions', 'SUM(clicks) as clicks', 'SUM(spend) as spend')
            ->groupBy('type')
            ->get();

        $topAds = $db->table('advertisements')
            ->where('advertiser_id', $userId)
            ->orderBy('clicks', 'DESC')
            ->limit(10)
            ->get();

        return $this->view('advertiser.analytics', [
            'title' => 'Ad Analytics',
            'activeMenu' => 'analytics',
            'totalImpressions' => $totalImpressions,
            'totalClicks' => $totalClicks,
            'totalSpend' => $totalSpend,
            'ctr' => $ctr,
            'cpc' => $cpc,
            'cpm' => $cpm,
            'byType' => $byType,
            'topAds' => $topAds,
        ]);
    }

    public function getData(): Response
    {
        if (!$this->hasRole('advertiser', 'admin')) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $userId = (int) $this->session->get('user_id');
        $days = max(1, (int) $this->request->input('days', 30));
        $db = Database::getInstance();

        $data = $db->table('advertisements')
            ->where('advertiser_id', $userId)
            ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime("-{$days} days")))
            ->select(
                'DATE(created_at) as date',
                'SUM(impressions) as impressions',
                'SUM(clicks) as clicks',
                'SUM(spend) as spend'
            )
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->get();

        return $this->json([
            'labels' => array_column($data, 'date'),
            'impressions' => array_map('intval', array_column($data, 'impressions')),
            'clicks' => array_map('intval', array_column($data, 'clicks')),
            'spend' => array_map('floatval', array_column($data, 'spend')),
        ]);
    }
}
