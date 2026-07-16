<?php

declare(strict_types=1);

namespace App\Controllers\Support;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class CreatorController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('support', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = (int) ($this->request->input('page', 1));
        $monetization = $this->request->input('monetization', '');
        $partner = $this->request->input('partner', '');
        $search = $this->request->input('search', '');

        $query = $db->table('channels')
            ->join('users', 'channels.user_id', '=', 'users.id')
            ->leftJoin('channel_monetization', 'channels.id', '=', 'channel_monetization.channel_id');

        if ($monetization !== '') {
            $query = $query->where('channel_monetization.status', $monetization);
        }

        if ($partner === 'yes') {
            $query = $query->where('channels.is_partner', 1);
        } elseif ($partner === 'no') {
            $query = $query->where('channels.is_partner', 0);
        }

        if ($search !== '') {
            $query = $query->where('channels.name', 'LIKE', "%{$search}%");
        }

        $creators = $query->orderBy('channels.created_at', 'DESC')->paginate(20, $page);

        return $this->view('support.creators', [
            'title' => 'Creator Management',
            'activeMenu' => 'creators',
            'creators' => $creators,
            'filters' => [
                'monetization' => $monetization,
                'partner' => $partner,
                'search' => $search,
            ],
        ]);
    }
}
