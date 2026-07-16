<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class FaqController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));

        $faqs = $db->table('faqs')
            ->orderBy('sort_order', 'ASC')
            ->paginate(20, $page);

        return $this->view('admin.faqs', [
            'title' => 'FAQ Management',
            'activeMenu' => 'faqs',
            'faqs' => $faqs,
        ]);
    }

    public function store(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $errors = $this->validate([
            'question' => 'required|max:255',
            'answer' => 'required',
        ]);

        if (!empty($errors)) {
            return $this->withInput()->withError('Validation failed.')->redirect('/admin/faqs');
        }

        $db = Database::getInstance();
        $maxOrder = (int) $db->table('faqs')->select('MAX(sort_order) as m')->first()['m'] ?? 0;

        $db->table('faqs')->insert([
            'question' => $this->request->input('question', ''),
            'answer' => $this->request->input('answer', ''),
            'sort_order' => $maxOrder + 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->withSuccess('FAQ created.')->redirect('/admin/faqs');
    }

    public function delete(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $db->table('faqs')->where('id', (int) $id)->delete();

        return $this->withSuccess('FAQ deleted.')->redirect('/admin/faqs');
    }
}
