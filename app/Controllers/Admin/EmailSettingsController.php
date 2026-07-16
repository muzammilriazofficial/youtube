<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class EmailSettingsController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $settings = $db->table('settings')
            ->where('setting_group', 'email')
            ->get();

        $settingsMap = [];
        foreach ($settings as $s) {
            $settingsMap[$s['setting_key']] = $s['value'];
        }

        return $this->view('admin.email-settings', [
            'title' => 'Email Settings',
            'activeMenu' => 'email-settings',
            'settings' => $settingsMap,
        ]);
    }

    public function update(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $fields = [
            'mail_driver', 'mail_host', 'mail_port', 'mail_username',
            'mail_password', 'mail_from_address', 'mail_from_name',
            'mail_encryption',
        ];

        foreach ($fields as $field) {
            $value = $this->request->input($field, '');
            $existing = $db->table('settings')->where('setting_group', 'email')->where('setting_key', $field)->first();
            if ($existing) {
                $db->table('settings')->where('id', (int) $existing['id'])->update([
                    'value' => $value,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $db->table('settings')->insert([
                    'setting_group' => 'email',
                    'setting_key' => $field,
                    'value' => $value,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return $this->withSuccess('Email settings updated.')->redirect('/admin/email-settings');
    }
}
