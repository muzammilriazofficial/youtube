<?php

declare(strict_types=1);

use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\CreatorController;
use App\Controllers\Admin\VideoController;
use App\Controllers\Admin\ChannelController;
use App\Controllers\Admin\CategoryController;
use App\Controllers\Admin\TagController;
use App\Controllers\Admin\CommentController;
use App\Controllers\Admin\ReportController;
use App\Controllers\Admin\CopyrightController;
use App\Controllers\Admin\AdController;
use App\Controllers\Admin\MonetizationController;
use App\Controllers\Admin\PayoutController;
use App\Controllers\Admin\PaymentController;
use App\Controllers\Admin\NotificationController;
use App\Controllers\Admin\PageController;
use App\Controllers\Admin\FaqController;
use App\Controllers\Admin\BlogController;
use App\Controllers\Admin\ContactMessageController;
use App\Controllers\Admin\PolicyController;
use App\Controllers\Admin\EmailTemplateController;
use App\Controllers\Admin\EmailSettingsController;
use App\Controllers\Admin\SmsSettingsController;
use App\Controllers\Admin\RoleController;
use App\Controllers\Admin\PermissionController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\Admin\ActivityLogController;
use App\Controllers\Admin\LoginLogController;
use App\Controllers\Admin\AuditLogController;
use App\Controllers\Admin\BackupController;
use App\Controllers\Admin\AnalyticsController;
use App\Controllers\Admin\RevenueController;
use App\Controllers\Admin\SystemHealthController;

$app = \App\Core\Application::getInstance();
$router = $app->getRouter();

$router->group('/admin', function ($router) {

    $router->get('/dashboard', [DashboardController::class, 'index']);
    $router->get('/chart-data', [DashboardController::class, 'getChartData']);

    $router->get('/users', [UserController::class, 'index']);
    $router->get('/users/create', [UserController::class, 'create']);
    $router->post('/users/store', [UserController::class, 'store']);
    $router->get('/users/edit/{id}', [UserController::class, 'edit']);
    $router->post('/users/update/{id}', [UserController::class, 'update']);
    $router->post('/users/delete/{id}', [UserController::class, 'delete']);
    $router->post('/users/toggle-status/{id}', [UserController::class, 'toggleStatus']);

    $router->get('/creators', [CreatorController::class, 'index']);

    $router->get('/videos', [VideoController::class, 'index']);
    $router->get('/videos/pending', [VideoController::class, 'pending']);
    $router->get('/videos/reported', [VideoController::class, 'reported']);
    $router->post('/videos/action/{id}', [VideoController::class, 'action']);

    $router->get('/channels', [ChannelController::class, 'index']);
    $router->post('/channels/action/{id}', [ChannelController::class, 'action']);

    $router->get('/categories', [CategoryController::class, 'index']);
    $router->get('/categories/create', [CategoryController::class, 'create']);
    $router->post('/categories/store', [CategoryController::class, 'store']);
    $router->get('/categories/edit/{id}', [CategoryController::class, 'edit']);
    $router->post('/categories/update/{id}', [CategoryController::class, 'update']);
    $router->post('/categories/delete/{id}', [CategoryController::class, 'delete']);

    $router->get('/tags', [TagController::class, 'index']);
    $router->post('/tags/store', [TagController::class, 'store']);
    $router->post('/tags/delete/{id}', [TagController::class, 'delete']);

    $router->get('/comments', [CommentController::class, 'index']);
    $router->post('/comments/action/{id}', [CommentController::class, 'action']);

    $router->get('/reports', [ReportController::class, 'index']);
    $router->get('/reports/show/{id}', [ReportController::class, 'show']);
    $router->post('/reports/resolve/{id}', [ReportController::class, 'resolve']);

    $router->get('/copyright', [CopyrightController::class, 'index']);
    $router->post('/copyright/action/{id}', [CopyrightController::class, 'action']);

    $router->get('/advertisements', [AdController::class, 'index']);
    $router->post('/advertisements/action/{id}', [AdController::class, 'action']);

    $router->get('/monetization', [MonetizationController::class, 'index']);

    $router->get('/payouts', [PayoutController::class, 'index']);
    $router->post('/payouts/process/{id}', [PayoutController::class, 'process']);

    $router->get('/payments', [PaymentController::class, 'index']);

    $router->get('/notifications', [NotificationController::class, 'index']);
    $router->get('/notifications/send', [NotificationController::class, 'send']);
    $router->post('/notifications/send', [NotificationController::class, 'sendStore']);

    $router->get('/pages', [PageController::class, 'index']);
    $router->get('/pages/create', [PageController::class, 'create']);
    $router->post('/pages/store', [PageController::class, 'store']);
    $router->get('/pages/edit/{id}', [PageController::class, 'edit']);
    $router->post('/pages/update/{id}', [PageController::class, 'update']);
    $router->post('/pages/delete/{id}', [PageController::class, 'delete']);

    $router->get('/faqs', [FaqController::class, 'index']);
    $router->post('/faqs/store', [FaqController::class, 'store']);
    $router->post('/faqs/delete/{id}', [FaqController::class, 'delete']);

    $router->get('/blog', [BlogController::class, 'index']);
    $router->get('/blog/create', [BlogController::class, 'create']);
    $router->post('/blog/store', [BlogController::class, 'store']);
    $router->get('/blog/edit/{id}', [BlogController::class, 'edit']);
    $router->post('/blog/update/{id}', [BlogController::class, 'update']);
    $router->post('/blog/delete/{id}', [BlogController::class, 'delete']);

    $router->get('/contact-messages', [ContactMessageController::class, 'index']);
    $router->get('/contact-messages/show/{id}', [ContactMessageController::class, 'show']);
    $router->post('/contact-messages/reply/{id}', [ContactMessageController::class, 'reply']);

    $router->get('/privacy-policy', [PolicyController::class, 'privacy']);
    $router->post('/privacy-policy/update', [PolicyController::class, 'updatePrivacy']);
    $router->get('/terms', [PolicyController::class, 'terms']);
    $router->post('/terms/update', [PolicyController::class, 'updateTerms']);

    $router->get('/email-templates', [EmailTemplateController::class, 'index']);
    $router->get('/email-templates/edit/{id}', [EmailTemplateController::class, 'edit']);
    $router->post('/email-templates/update/{id}', [EmailTemplateController::class, 'update']);

    $router->get('/email-settings', [EmailSettingsController::class, 'index']);
    $router->post('/email-settings/update', [EmailSettingsController::class, 'update']);

    $router->get('/sms-settings', [SmsSettingsController::class, 'index']);
    $router->post('/sms-settings/update', [SmsSettingsController::class, 'update']);

    $router->get('/roles', [RoleController::class, 'index']);
    $router->get('/roles/create', [RoleController::class, 'create']);
    $router->post('/roles/store', [RoleController::class, 'store']);
    $router->get('/roles/edit/{id}', [RoleController::class, 'edit']);
    $router->post('/roles/update/{id}', [RoleController::class, 'update']);
    $router->post('/roles/delete/{id}', [RoleController::class, 'delete']);
    $router->post('/roles/permissions/{id}', [RoleController::class, 'assignPermissions']);

    $router->get('/permissions', [PermissionController::class, 'index']);
    $router->post('/permissions/store', [PermissionController::class, 'store']);
    $router->post('/permissions/delete/{id}', [PermissionController::class, 'delete']);

    $router->get('/general-settings', [SettingsController::class, 'general']);
    $router->post('/general-settings/update', [SettingsController::class, 'updateGeneral']);
    $router->get('/security-settings', [SettingsController::class, 'security']);
    $router->post('/security-settings/update', [SettingsController::class, 'updateSecurity']);
    $router->get('/storage-settings', [SettingsController::class, 'storage']);
    $router->post('/storage-settings/update', [SettingsController::class, 'updateStorage']);
    $router->get('/ffmpeg-settings', [SettingsController::class, 'ffmpeg']);
    $router->post('/ffmpeg-settings/update', [SettingsController::class, 'updateFfmpeg']);
    $router->get('/api-settings', [SettingsController::class, 'api']);
    $router->post('/api-settings/update', [SettingsController::class, 'updateApi']);
    $router->get('/payment-gateways', [SettingsController::class, 'paymentGateways']);
    $router->post('/payment-gateways/update', [SettingsController::class, 'updatePaymentGateways']);
    $router->get('/social-login', [SettingsController::class, 'socialLogin']);
    $router->post('/social-login/update', [SettingsController::class, 'updateSocialLogin']);
    $router->get('/seo-settings', [SettingsController::class, 'seo']);
    $router->post('/seo-settings/update', [SettingsController::class, 'updateSeo']);

    $router->get('/activity-logs', [ActivityLogController::class, 'index']);
    $router->get('/login-logs', [LoginLogController::class, 'index']);
    $router->get('/audit-logs', [AuditLogController::class, 'index']);

    $router->get('/backup', [BackupController::class, 'index']);
    $router->post('/backup/create', [BackupController::class, 'create']);
    $router->post('/backup/restore', [BackupController::class, 'restore']);
    $router->get('/backup/download/{file}', [BackupController::class, 'download']);

    $router->get('/analytics', [AnalyticsController::class, 'index']);
    $router->get('/analytics/data', [AnalyticsController::class, 'getData']);

    $router->get('/revenue', [RevenueController::class, 'index']);

    $router->get('/system-health', [SystemHealthController::class, 'index']);

}, ['auth']);
