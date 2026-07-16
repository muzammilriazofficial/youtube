<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class RevenueController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();

        $totalRevenue = (float) $db->table('payments')->where('status', 'completed')->sum('amount');
        $thisMonth = (float) $db->table('payments')
            ->where('status', 'completed')
            ->whereBetween('created_at', date('Y-m-01'), date('Y-m-t 23:59:59'))
            ->sum('amount');
        $lastMonth = (float) $db->table('payments')
            ->where('status', 'completed')
            ->whereBetween('created_at', date('Y-m-01', strtotime('-1 month')), date('Y-m-t 23:59:59', strtotime('-1 month')))
            ->sum('amount');
        $totalPayouts = (float) $db->table('payouts')->where('status', 'completed')->sum('amount');
        $pendingPayouts = (float) $db->table('payouts')->where('status', 'pending')->sum('amount');

        $recentPayments = $db->table('payments')
            ->join('users', 'payments.user_id', '=', 'users.id')
            ->select('payments.*', 'users.username')
            ->orderBy('payments.created_at', 'DESC')
            ->limit(20)
            ->get();

        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("-{$i} months"));
            $monthEnd = date('Y-m-t 23:59:59', strtotime("-{$i} months"));
            $monthlyRevenue[] = [
                'month' => date('M Y', strtotime($monthStart)),
                'revenue' => (float) $db->table('payments')
                    ->where('status', 'completed')
                    ->whereBetween('created_at', $monthStart, $monthEnd)
                    ->sum('amount'),
            ];
        }

        return $this->view('admin.revenue', [
            'title' => 'Revenue Dashboard',
            'activeMenu' => 'revenue',
            'totalRevenue' => $totalRevenue,
            'thisMonth' => $thisMonth,
            'lastMonth' => $lastMonth,
            'totalPayouts' => $totalPayouts,
            'pendingPayouts' => $pendingPayouts,
            'recentPayments' => $recentPayments,
            'monthlyRevenue' => $monthlyRevenue,
        ]);
    }
}
