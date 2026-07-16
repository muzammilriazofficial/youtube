<?php

declare(strict_types=1);

namespace App\Controllers\Guest;

use App\Core\Controller;
use App\Core\Response;

class SubscriptionsPreviewController extends Controller
{
    public function index(): Response
    {
        if ($this->isAuthenticated()) {
            return $this->redirect('/viewer/subscriptions');
        }

        return $this->view('guest.videos', [
            'title' => 'Subscriptions',
            'videos' => ['data' => [], 'total' => 0, 'per_page' => 24, 'current_page' => 1, 'last_page' => 1, 'has_more_pages' => false, 'has_prev_page' => false],
            'currentSort' => 'latest',
            'currentCategory' => '',
            'pageTitle' => 'Subscriptions',
            'isPreview' => true,
        ]);
    }
}
