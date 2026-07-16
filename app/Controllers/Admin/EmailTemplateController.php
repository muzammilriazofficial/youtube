<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class EmailTemplateController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $templates = $db->table('email_templates')->orderBy('name', 'ASC')->get();

        return $this->view('admin.email-templates', [
            'title' => 'Email Templates',
            'activeMenu' => 'email-templates',
            'templates' => $templates,
        ]);
    }

    public function edit(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $template = $db->table('email_templates')->where('id', (int) $id)->first();

        if (!$template) {
            return $this->withError('Template not found.')->redirect('/admin/email-templates');
        }

        return $this->view('admin.email-template-edit', [
            'title' => 'Edit Email Template',
            'activeMenu' => 'email-templates',
            'template' => $template,
        ]);
    }

    public function update(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $db->table('email_templates')->where('id', (int) $id)->update([
            'subject' => $this->request->input('subject', ''),
            'body' => $this->request->input('body', ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->withSuccess('Template updated.')->redirect('/admin/email-templates');
    }
}
