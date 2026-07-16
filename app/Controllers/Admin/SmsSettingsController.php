<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class SmsSettingsController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $settings = $db->table('settings')
            ->where('setting_group', 'sms')
            ->get();

        $settingsMap = [];
        foreach ($settings as $s) {
            $settingsMap[$s['setting_key']] = $s['value'];
        }

        return $this->view('admin.sms-settings', [
            'title' => 'SMS Settings',
            'activeMenu' => 'sms-settings',
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
            'sms_provider', 'sms_api_key', 'sms_api_secret',
            'sms_from_number', 'sms_enabled',
        ];

        foreach ($fields as $field) {
            $value = $this->request->input($field, '');
            $existing = $db->table('settings')->where('setting_group', 'sms')->where('setting_key', $field)->first();
            if ($existing) {
                $db->table('settings')->where('id', (int) $existing['id'])->update([
                    'value' => $value,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $db->table('settings')->insert([
                    'setting_group' => 'sms',
                    'setting_key' => $field,
                    'value' => $value,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return $this->withSuccess('SMS settings updated.')->redirect('/admin/sms-settings');
    }
}
