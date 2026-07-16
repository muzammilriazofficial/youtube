<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class SettingsController extends Controller
{
    private function getSettingsByGroup(string $group): array
    {
        $db = Database::getInstance();
        $settings = $db->table('settings')->where('setting_group', $group)->get();
        $map = [];
        foreach ($settings as $s) {
            $map[$s['setting_key']] = $s['value'];
        }
        return $map;
    }

    private function saveSettings(string $group, array $fields): void
    {
        $db = Database::getInstance();
        foreach ($fields as $field) {
            $value = $this->request->input($field, '');
            $existing = $db->table('settings')->where('setting_group', $group)->where('setting_key', $field)->first();
            if ($existing) {
                $db->table('settings')->where('id', (int) $existing['id'])->update([
                    'value' => $value,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $db->table('settings')->insert([
                    'setting_group' => $group,
                    'setting_key' => $field,
                    'value' => $value,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function general(): Response
    {
        if (!$this->hasRole('admin')) return $this->redirect('/');
        return $this->view('admin.general-settings', [
            'title' => 'General Settings', 'activeMenu' => 'settings',
            'settings' => $this->getSettingsByGroup('general'),
        ]);
    }

    public function updateGeneral(): Response
    {
        if (!$this->hasRole('admin')) return $this->redirect('/');
        $this->saveSettings('general', [
            'site_name', 'site_description', 'site_url', 'site_logo',
            'site_favicon', 'contact_email', 'default_timezone', 'default_language',
        ]);
        return $this->withSuccess('General settings updated.')->redirect('/admin/general-settings');
    }

    public function security(): Response
    {
        if (!$this->hasRole('admin')) return $this->redirect('/');
        return $this->view('admin.security-settings', [
            'title' => 'Security Settings', 'activeMenu' => 'settings',
            'settings' => $this->getSettingsByGroup('security'),
        ]);
    }

    public function updateSecurity(): Response
    {
        if (!$this->hasRole('admin')) return $this->redirect('/');
        $this->saveSettings('security', [
            'two_factor_enabled', 'max_login_attempts', 'lockout_duration',
            'password_min_length', 'require_email_verification', 'enable_recaptcha',
            'recaptcha_site_key', 'recaptcha_secret_key',
        ]);
        return $this->withSuccess('Security settings updated.')->redirect('/admin/security-settings');
    }

    public function storage(): Response
    {
        if (!$this->hasRole('admin')) return $this->redirect('/');
        return $this->view('admin.storage-settings', [
            'title' => 'Storage Settings', 'activeMenu' => 'settings',
            'settings' => $this->getSettingsByGroup('storage'),
        ]);
    }

    public function updateStorage(): Response
    {
        if (!$this->hasRole('admin')) return $this->redirect('/');
        $this->saveSettings('storage', [
            'storage_driver', 's3_key', 's3_secret', 's3_region',
            's3_bucket', 's3_endpoint', 'max_upload_size', 'allowed_video_formats',
        ]);
        return $this->withSuccess('Storage settings updated.')->redirect('/admin/storage-settings');
    }

    public function ffmpeg(): Response
    {
        if (!$this->hasRole('admin')) return $this->redirect('/');
        return $this->view('admin.ffmpeg-settings', [
            'title' => 'FFmpeg Settings', 'activeMenu' => 'settings',
            'settings' => $this->getSettingsByGroup('ffmpeg'),
        ]);
    }

    public function updateFfmpeg(): Response
    {
        if (!$this->hasRole('admin')) return $this->redirect('/');
        $this->saveSettings('ffmpeg', [
            'ffmpeg_path', 'ffprobe_path', 'default_video_codec',
            'default_audio_codec', 'thumbnail_timestamp', 'transcoding_presets',
        ]);
        return $this->withSuccess('FFmpeg settings updated.')->redirect('/admin/ffmpeg-settings');
    }

    public function api(): Response
    {
        if (!$this->hasRole('admin')) return $this->redirect('/');
        return $this->view('admin.api-settings', [
            'title' => 'API Settings', 'activeMenu' => 'settings',
            'settings' => $this->getSettingsByGroup('api'),
        ]);
    }

    public function updateApi(): Response
    {
        if (!$this->hasRole('admin')) return $this->redirect('/');
        $this->saveSettings('api', [
            'api_rate_limit', 'api_key', 'enable_api_logging',
            'cors_origins', 'enable_webhooks', 'webhook_secret',
        ]);
        return $this->withSuccess('API settings updated.')->redirect('/admin/api-settings');
    }

    public function paymentGateways(): Response
    {
        if (!$this->hasRole('admin')) return $this->redirect('/');
        return $this->view('admin.payment-gateways', [
            'title' => 'Payment Gateways', 'activeMenu' => 'settings',
            'settings' => $this->getSettingsByGroup('payment'),
        ]);
    }

    public function updatePaymentGateways(): Response
    {
        if (!$this->hasRole('admin')) return $this->redirect('/');
        $this->saveSettings('payment', [
            'stripe_enabled', 'stripe_key', 'stripe_secret', 'stripe_webhook',
            'paypal_enabled', 'paypal_client_id', 'paypal_secret',
            'currency', 'minimum_payout',
        ]);
        return $this->withSuccess('Payment gateway settings updated.')->redirect('/admin/payment-gateways');
    }

    public function socialLogin(): Response
    {
        if (!$this->hasRole('admin')) return $this->redirect('/');
        return $this->view('admin.social-login', [
            'title' => 'Social Login', 'activeMenu' => 'settings',
            'settings' => $this->getSettingsByGroup('social'),
        ]);
    }

    public function updateSocialLogin(): Response
    {
        if (!$this->hasRole('admin')) return $this->redirect('/');
        $this->saveSettings('social', [
            'google_enabled', 'google_client_id', 'google_client_secret',
            'facebook_enabled', 'facebook_app_id', 'facebook_app_secret',
            'github_enabled', 'github_client_id', 'github_client_secret',
        ]);
        return $this->withSuccess('Social login settings updated.')->redirect('/admin/social-login');
    }

    public function seo(): Response
    {
        if (!$this->hasRole('admin')) return $this->redirect('/');
        return $this->view('admin.seo-settings', [
            'title' => 'SEO Settings', 'activeMenu' => 'settings',
            'settings' => $this->getSettingsByGroup('seo'),
        ]);
    }

    public function updateSeo(): Response
    {
        if (!$this->hasRole('admin')) return $this->redirect('/');
        $this->saveSettings('seo', [
            'meta_title', 'meta_description', 'meta_keywords',
            'og_image', 'google_analytics_id', 'enable_sitemap',
            'enable_robots_txt',
        ]);
        return $this->withSuccess('SEO settings updated.')->redirect('/admin/seo-settings');
    }
}
