<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class PolicyController extends Controller
{
    public function privacy(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $policy = $db->table('pages')->where('slug', 'privacy-policy')->first();

        return $this->view('admin.privacy-policy', [
            'title' => 'Privacy Policy',
            'activeMenu' => 'privacy-policy',
            'policy' => $policy,
        ]);
    }

    public function updatePrivacy(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $content = $this->request->input('content', '');

        $existing = $db->table('pages')->where('slug', 'privacy-policy')->first();

        if ($existing) {
            $db->table('pages')->where('slug', 'privacy-policy')->update([
                'content' => $content,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $db->table('pages')->insert([
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => $content,
                'is_published' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->withSuccess('Privacy policy updated.')->redirect('/admin/privacy-policy');
    }

    public function terms(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $policy = $db->table('pages')->where('slug', 'terms-of-service')->first();

        return $this->view('admin.terms', [
            'title' => 'Terms of Service',
            'activeMenu' => 'terms',
            'policy' => $policy,
        ]);
    }

    public function updateTerms(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $content = $this->request->input('content', '');

        $existing = $db->table('pages')->where('slug', 'terms-of-service')->first();

        if ($existing) {
            $db->table('pages')->where('slug', 'terms-of-service')->update([
                'content' => $content,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $db->table('pages')->insert([
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => $content,
                'is_published' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->withSuccess('Terms of service updated.')->redirect('/admin/terms');
    }
}
