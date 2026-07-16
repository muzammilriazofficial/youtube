<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class ContactMessageController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $status = $this->request->input('status', '');

        $query = $db->table('contact_messages');

        if ($status !== '') {
            $query = $query->where('status', $status);
        }

        $messages = $query->orderBy('created_at', 'DESC')->paginate(20, $page);

        $unread = $db->table('contact_messages')->where('status', 'unread')->count();

        return $this->view('admin.contact-messages', [
            'title' => 'Contact Messages',
            'activeMenu' => 'contact-messages',
            'messages' => $messages,
            'status' => $status,
            'unread' => $unread,
        ]);
    }

    public function show(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $message = $db->table('contact_messages')->where('id', (int) $id)->first();

        if (!$message) {
            return $this->withError('Message not found.')->redirect('/admin/contact-messages');
        }

        if (($message['status'] ?? '') === 'unread') {
            $db->table('contact_messages')->where('id', (int) $id)->update([
                'status' => 'read',
                'read_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $replies = $db->table('contact_message_replies')
            ->where('contact_message_id', (int) $id)
            ->orderBy('created_at', 'ASC')
            ->get();

        return $this->view('admin.contact-message-show', [
            'title' => 'View Message',
            'activeMenu' => 'contact-messages',
            'message' => $message,
            'replies' => $replies,
        ]);
    }

    public function reply(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $errors = $this->validate([
            'reply' => 'required',
        ]);

        if (!empty($errors)) {
            return $this->withInput()->withError('Reply cannot be empty.')->redirect("/admin/contact-messages/show/{$id}");
        }

        $db = Database::getInstance();
        $message = $db->table('contact_messages')->where('id', (int) $id)->first();

        if (!$message) {
            return $this->withError('Message not found.')->redirect('/admin/contact-messages');
        }

        $adminId = (int) $this->session->get('user_id');

        $db->table('contact_message_replies')->insert([
            'contact_message_id' => (int) $id,
            'admin_id' => $adminId,
            'reply' => $this->request->input('reply', ''),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $db->table('contact_messages')->where('id', (int) $id)->update([
            'status' => 'replied',
            'replied_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->withSuccess('Reply sent.')->redirect("/admin/contact-messages/show/{$id}");
    }
}
