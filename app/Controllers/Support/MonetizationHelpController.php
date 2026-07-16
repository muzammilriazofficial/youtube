<?php

declare(strict_types=1);

namespace App\Controllers\Support;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class MonetizationHelpController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('support', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();

        $monetizationTickets = $db->table('support_tickets')
            ->join('users', 'support_tickets.user_id', '=', 'users.id')
            ->where('support_tickets.category', 'monetization')
            ->orderBy('support_tickets.created_at', 'DESC')
            ->limit(20)
            ->get();

        $commonIssues = $db->table('support_tickets')
            ->where('category', 'monetization')
            ->select('subject', 'COUNT(*) as count')
            ->groupBy('subject')
            ->orderBy('count', 'DESC')
            ->limit(10)
            ->get();

        $totalMonetizationTickets = $db->table('support_tickets')
            ->where('category', 'monetization')
            ->count();

        $pendingMonetization = $db->table('support_tickets')
            ->where('category', 'monetization')
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        $pendingApplications = $db->table('channel_monetization')
            ->where('status', 'pending')
            ->count();

        return $this->view('support.monetization-help', [
            'title' => 'Monetization Help',
            'activeMenu' => 'monetization-help',
            'monetizationTickets' => $monetizationTickets,
            'commonIssues' => $commonIssues,
            'totalMonetizationTickets' => $totalMonetizationTickets,
            'pendingMonetization' => $pendingMonetization,
            'pendingApplications' => $pendingApplications,
        ]);
    }
}
