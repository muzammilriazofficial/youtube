<?php

declare(strict_types=1);

namespace App\Controllers\Support;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('support', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();

        $openTickets = $db->table('support_tickets')
            ->where('status', 'open')
            ->count();

        $inProgressTickets = $db->table('support_tickets')
            ->where('status', 'in_progress')
            ->count();

        $resolvedToday = $db->table('support_tickets')
            ->where('status', 'resolved')
            ->whereBetween('updated_at', date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59'))
            ->count();

        $avgResponse = $db->table('support_tickets')
            ->select('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')
            ->where('status', 'resolved')
            ->first();

        $recentTickets = $db->table('support_tickets')
            ->join('users', 'support_tickets.user_id', '=', 'users.id')
            ->orderBy('support_tickets.created_at', 'DESC')
            ->limit(10)
            ->get();

        $priorityBreakdown = $db->table('support_tickets')
            ->whereNotIn('status', ['closed', 'resolved'])
            ->select('priority', 'COUNT(*) as count')
            ->groupBy('priority')
            ->orderBy('count', 'DESC')
            ->get();

        $categoryBreakdown = $db->table('support_tickets')
            ->select('category', 'COUNT(*) as count')
            ->groupBy('category')
            ->orderBy('count', 'DESC')
            ->get();

        $totalTickets = $db->table('support_tickets')->count();

        return $this->view('support.dashboard', [
            'title' => 'Support Dashboard',
            'activeMenu' => 'dashboard',
            'openTickets' => $openTickets,
            'inProgressTickets' => $inProgressTickets,
            'resolvedToday' => $resolvedToday,
            'avgResponseHours' => round((float) ($avgResponse['avg_hours'] ?? 0), 1),
            'recentTickets' => $recentTickets,
            'priorityBreakdown' => $priorityBreakdown,
            'categoryBreakdown' => $categoryBreakdown,
            'totalTickets' => $totalTickets,
        ]);
    }
}
