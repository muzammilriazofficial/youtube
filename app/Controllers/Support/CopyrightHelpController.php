<?php

declare(strict_types=1);

namespace App\Controllers\Support;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class CopyrightHelpController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('support', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();

        $copyrightTickets = $db->table('support_tickets')
            ->join('users', 'support_tickets.user_id', '=', 'users.id')
            ->where('support_tickets.category', 'copyright')
            ->orderBy('support_tickets.created_at', 'DESC')
            ->limit(20)
            ->get();

        $totalCopyrightTickets = $db->table('support_tickets')
            ->where('category', 'copyright')
            ->count();

        $pendingCopyright = $db->table('support_tickets')
            ->where('category', 'copyright')
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        $dmcaClaims = $db->table('copyright_claims')
            ->count();

        $pendingDmca = $db->table('copyright_claims')
            ->where('status', 'pending')
            ->count();

        $recentClaims = $db->table('copyright_claims')
            ->join('users', 'copyright_claims.claimant_id', '=', 'users.id')
            ->orderBy('copyright_claims.created_at', 'DESC')
            ->limit(10)
            ->get();

        return $this->view('support.copyright-help', [
            'title' => 'Copyright Help',
            'activeMenu' => 'copyright-help',
            'copyrightTickets' => $copyrightTickets,
            'totalCopyrightTickets' => $totalCopyrightTickets,
            'pendingCopyright' => $pendingCopyright,
            'dmcaClaims' => $dmcaClaims,
            'pendingDmca' => $pendingDmca,
            'recentClaims' => $recentClaims,
        ]);
    }
}
