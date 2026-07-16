<?php

declare(strict_types=1);

namespace App\Controllers\Advertiser;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('advertiser', 'admin')) {
            return $this->redirect('/');
        }

        $userId = (int) $this->session->get('user_id');
        $db = Database::getInstance();

        $totalCampaigns = $db->table('ad_campaigns')
            ->where('advertiser_id', $userId)
            ->count();

        $activeCampaigns = $db->table('ad_campaigns')
            ->where('advertiser_id', $userId)
            ->where('status', 'active')
            ->count();

        $totalAds = $db->table('advertisements')
            ->where('advertiser_id', $userId)
            ->count();

        $activeAds = $db->table('advertisements')
            ->where('advertiser_id', $userId)
            ->where('status', 'active')
            ->count();

        $totalImpressions = (int) $db->table('advertisements')
            ->where('advertiser_id', $userId)
            ->sum('impressions');

        $totalClicks = (int) $db->table('advertisements')
            ->where('advertiser_id', $userId)
            ->sum('clicks');

        $totalSpend = (float) $db->table('advertisements')
            ->where('advertiser_id', $userId)
            ->sum('spend');

        $campaignSpend = (float) $db->table('ad_campaigns')
            ->where('advertiser_id', $userId)
            ->sum('spent');

        $ctr = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0;

        $recentCampaigns = $db->table('ad_campaigns')
            ->where('advertiser_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get();

        $recentAds = $db->table('advertisements')
            ->where('advertiser_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get();

        $spendChart = $db->table('advertisements')
            ->where('advertiser_id', $userId)
            ->select('DATE(created_at) as date', 'SUM(spend) as daily_spend')
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-30 days')))
            ->get();

        return $this->view('advertiser.dashboard', [
            'title' => 'Advertiser Dashboard',
            'activeMenu' => 'dashboard',
            'totalCampaigns' => $totalCampaigns,
            'activeCampaigns' => $activeCampaigns,
            'totalAds' => $totalAds,
            'activeAds' => $activeAds,
            'totalImpressions' => $totalImpressions,
            'totalClicks' => $totalClicks,
            'totalSpend' => $totalSpend,
            'campaignSpend' => $campaignSpend,
            'ctr' => $ctr,
            'recentCampaigns' => $recentCampaigns,
            'recentAds' => $recentAds,
            'spendChart' => $spendChart,
        ]);
    }
}
