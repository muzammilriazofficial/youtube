<?php

declare(strict_types=1);

use App\Core\Application;
use App\Core\Response;
use App\Controllers\Auth\AuthController;
use App\Controllers\HomeController;
use App\Controllers\Guest\VideoController as GuestVideoController;
use App\Controllers\Guest\ShortsController;
use App\Controllers\Guest\LiveController;
use App\Controllers\Guest\CategoryController;
use App\Controllers\Guest\ChannelController;
use App\Controllers\Guest\SearchController;
use App\Controllers\Guest\TrendingController;
use App\Controllers\Guest\MusicController;
use App\Controllers\Guest\GamingController;
use App\Controllers\Guest\NewsController;
use App\Controllers\Guest\SportsController;
use App\Controllers\Guest\LearningController;
use App\Controllers\Guest\SubscriptionsPreviewController;
use App\Controllers\StaticController;
use App\Controllers\Viewer\DashboardController;
use App\Controllers\Viewer\ProfileController;
use App\Controllers\Viewer\HistoryController;
use App\Controllers\Viewer\WatchLaterController;
use App\Controllers\Viewer\LikedVideosController;
use App\Controllers\Viewer\DownloadsController;
use App\Controllers\Viewer\PlaylistController;
use App\Controllers\Viewer\SubscriptionController;
use App\Controllers\Viewer\NotificationController;
use App\Controllers\AjaxController;
use App\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Controllers\Admin\UserController as AdminUserController;
use App\Controllers\Admin\VideoController as AdminVideoController;
use App\Controllers\Admin\ChannelController as AdminChannelController;
use App\Controllers\Admin\CommentController as AdminCommentController;
use App\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Controllers\Admin\TagController as AdminTagController;
use App\Controllers\Admin\RoleController as AdminRoleController;
use App\Controllers\Admin\PermissionController as AdminPermissionController;
use App\Controllers\Admin\ReportController as AdminReportController;
use App\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Controllers\Admin\LoginLogController as AdminLoginLogController;
use App\Controllers\Admin\BlogController as AdminBlogController;
use App\Controllers\Admin\PageController as AdminPageController;
use App\Controllers\Admin\FaqController as AdminFaqController;
use App\Controllers\Admin\EmailTemplateController as AdminEmailTemplateController;
use App\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Controllers\Admin\MonetizationController as AdminMonetizationController;
use App\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Controllers\Admin\PayoutController as AdminPayoutController;
use App\Controllers\Admin\RevenueController as AdminRevenueController;
use App\Controllers\Admin\CreatorController as AdminCreatorController;
use App\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Controllers\Admin\BackupController as AdminBackupController;
use App\Controllers\Admin\SystemHealthController as AdminSystemHealthController;
use App\Controllers\Admin\PolicyController as AdminPolicyController;
use App\Controllers\Admin\CopyrightController as AdminCopyrightController;
use App\Controllers\Admin\AdController as AdminAdController;
use App\Controllers\Admin\EmailSettingsController as AdminEmailSettingsController;
use App\Controllers\Admin\SmsSettingsController as AdminSmsSettingsController;
use App\Controllers\Creator\DashboardController as CreatorDashboardController;
use App\Controllers\Creator\VideoController as CreatorVideoController;
use App\Controllers\Creator\ChannelController as CreatorChannelController;
use App\Controllers\Creator\CommentController as CreatorCommentController;
use App\Controllers\Creator\PlaylistController as CreatorPlaylistController;
use App\Controllers\Creator\AnalyticsController as CreatorAnalyticsController;
use App\Controllers\Creator\MonetizationController as CreatorMonetizationController;
use App\Controllers\Creator\CommunityController as CreatorCommunityController;
use App\Controllers\Creator\LiveController as CreatorLiveController;
use App\Controllers\Creator\ShortController as CreatorShortController;
use App\Controllers\Creator\CopyrightController as CreatorCopyrightController;

$app = Application::getInstance();
$router = $app->getRouter();

$router->group('/login', function ($router) {
    $router->get('/', [AuthController::class, 'showLogin']);
    $router->post('/', [AuthController::class, 'login']);
}, ['guest']);

$router->group('/register', function ($router) {
    $router->get('/', [AuthController::class, 'showRegister']);
    $router->post('/', [AuthController::class, 'register']);
}, ['guest']);

$router->post('/logout', [AuthController::class, 'logout']);

$router->group('/forgot-password', function ($router) {
    $router->get('/', [AuthController::class, 'showForgotPassword']);
    $router->post('/', [AuthController::class, 'forgotPassword']);
}, ['guest']);

$router->group('/reset-password', function ($router) {
    $router->get('/{token}', [AuthController::class, 'showResetPassword']);
    $router->post('/{token}', [AuthController::class, 'resetPassword']);
}, ['guest']);

$router->get('/', [HomeController::class, 'index']);
$router->get('/health', function () {
    return \App\Core\Response::json(['status' => 'ok', 'time' => date('Y-m-d H:i:s'), 'version' => '1.0.0']);
});

$router->get('/dashboard', function () {
    $session = \App\Core\Session::getInstance();
    if (!$session->isAuthenticated()) {
        return \App\Core\Response::redirect('/login');
    }

    $userModel = new \App\Models\User();
    $user = $userModel->find($session->getAuthUserId());

    if ($user === null) {
        $session->logout();
        return \App\Core\Response::redirect('/login');
    }

    if (!empty($user['is_admin'])) {
        return \App\Core\Response::redirect('/admin/dashboard');
    }

    return \App\Core\Response::redirect('/');
});

$router->get('/videos', [GuestVideoController::class, 'index']);
$router->get('/video/{slug}', [GuestVideoController::class, 'show']);
$router->get('/shorts', [ShortsController::class, 'index']);
$router->get('/live', [LiveController::class, 'index']);
$router->get('/categories', [CategoryController::class, 'index']);
$router->get('/category/{slug}', [CategoryController::class, 'show']);
$router->get('/channels', [ChannelController::class, 'index']);
$router->get('/channel/{username}', [ChannelController::class, 'show']);
$router->get('/search', [SearchController::class, 'index']);
$router->get('/trending', [TrendingController::class, 'index']);
$router->get('/music', [MusicController::class, 'index']);
$router->get('/gaming', [GamingController::class, 'index']);
$router->get('/news', [NewsController::class, 'index']);
$router->get('/sports', [SportsController::class, 'index']);
$router->get('/learning', [LearningController::class, 'index']);
$router->get('/subscriptions-preview', [SubscriptionsPreviewController::class, 'index']);
$router->get('/about', [StaticController::class, 'about']);
$router->get('/contact', [StaticController::class, 'contact']);
$router->post('/contact', [StaticController::class, 'submitContact']);
$router->get('/privacy', [StaticController::class, 'privacy']);
$router->get('/terms', [StaticController::class, 'terms']);

$router->group('/viewer', function ($router) {
    $router->get('/dashboard', function () {
        return \App\Core\Response::redirect('/');
    });
    $router->get('/profile', [ProfileController::class, 'show']);
    $router->get('/profile/edit', [ProfileController::class, 'edit']);
    $router->post('/profile/update', [ProfileController::class, 'update']);
    $router->get('/change-password', [ProfileController::class, 'changePassword']);
    $router->post('/change-password', [ProfileController::class, 'updatePassword']);
    $router->post('/delete-account', [ProfileController::class, 'deleteAccount']);
    $router->get('/history', [HistoryController::class, 'index']);
    $router->post('/history/add', [HistoryController::class, 'add']);
    $router->post('/history/remove', [HistoryController::class, 'remove']);
    $router->post('/history/clear', [HistoryController::class, 'clear']);
    $router->get('/watch-later', [WatchLaterController::class, 'index']);
    $router->post('/watch-later/add', [WatchLaterController::class, 'add']);
    $router->post('/watch-later/remove', [WatchLaterController::class, 'remove']);
    $router->get('/liked-videos', [LikedVideosController::class, 'index']);
    $router->get('/downloads', [DownloadsController::class, 'index']);
    $router->get('/playlists', [PlaylistController::class, 'index']);
    $router->get('/playlists/create', [PlaylistController::class, 'create']);
    $router->post('/playlists/store', [PlaylistController::class, 'store']);
    $router->get('/playlists/{id}', [PlaylistController::class, 'show']);
    $router->get('/playlists/edit/{id}', [PlaylistController::class, 'edit']);
    $router->post('/playlists/update/{id}', [PlaylistController::class, 'update']);
    $router->post('/playlists/delete/{id}', [PlaylistController::class, 'delete']);
    $router->post('/playlists/add-video', [PlaylistController::class, 'addVideo']);
    $router->post('/playlists/remove-video', [PlaylistController::class, 'removeVideo']);
    $router->get('/subscriptions', [SubscriptionController::class, 'index']);
    $router->post('/subscriptions/toggle', [SubscriptionController::class, 'toggle']);
    $router->get('/notifications', [NotificationController::class, 'index']);
    $router->post('/notifications/read', [NotificationController::class, 'markRead']);
    $router->post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    $router->post('/notifications/delete', [NotificationController::class, 'delete']);
    $router->get('/notifications/check', [NotificationController::class, 'check']);
    $router->post('/like-video', [AjaxController::class, 'toggleLike']);
    $router->post('/comment', [AjaxController::class, 'addComment']);
    $router->post('/comment/delete', [AjaxController::class, 'deleteComment']);
    $router->post('/comment/like', [AjaxController::class, 'likeComment']);
    $router->post('/report', [AjaxController::class, 'submitReport']);
}, ['auth']);

$router->group('/admin', function ($router) {
    $router->get('/dashboard', [AdminDashboardController::class, 'index']);
    $router->get('/chart-data', [AdminDashboardController::class, 'getChartData']);

    $router->get('/users', [AdminUserController::class, 'index']);
    $router->get('/users/{id}', [AdminUserController::class, 'show']);
    $router->get('/users/{id}/edit', [AdminUserController::class, 'edit']);
    $router->post('/users/{id}/update', [AdminUserController::class, 'update']);
    $router->post('/users/{id}/delete', [AdminUserController::class, 'delete']);
    $router->post('/users/{id}/ban', [AdminUserController::class, 'ban']);
    $router->post('/users/{id}/unban', [AdminUserController::class, 'unban']);
    $router->post('/users/{id}/role', [AdminUserController::class, 'assignRole']);

    $router->get('/videos', [AdminVideoController::class, 'index']);
    $router->get('/videos/{id}', [AdminVideoController::class, 'show']);
    $router->post('/videos/{id}/approve', [AdminVideoController::class, 'approve']);
    $router->post('/videos/{id}/reject', [AdminVideoController::class, 'reject']);
    $router->post('/videos/{id}/delete', [AdminVideoController::class, 'delete']);
    $router->post('/videos/{id}/feature', [AdminVideoController::class, 'feature']);

    $router->get('/channels', [AdminChannelController::class, 'index']);
    $router->get('/channels/{id}', [AdminChannelController::class, 'show']);
    $router->post('/channels/{id}/verify', [AdminChannelController::class, 'verify']);
    $router->post('/channels/{id}/delete', [AdminChannelController::class, 'delete']);

    $router->get('/comments', [AdminCommentController::class, 'index']);
    $router->post('/comments/{id}/approve', [AdminCommentController::class, 'approve']);
    $router->post('/comments/{id}/delete', [AdminCommentController::class, 'delete']);
    $router->post('/comments/{id}/pin', [AdminCommentController::class, 'pin']);

    $router->get('/categories', [AdminCategoryController::class, 'index']);
    $router->post('/categories/store', [AdminCategoryController::class, 'store']);
    $router->post('/categories/{id}/update', [AdminCategoryController::class, 'update']);
    $router->post('/categories/{id}/delete', [AdminCategoryController::class, 'delete']);

    $router->get('/tags', [AdminTagController::class, 'index']);
    $router->post('/tags/store', [AdminTagController::class, 'store']);
    $router->post('/tags/{id}/update', [AdminTagController::class, 'update']);
    $router->post('/tags/{id}/delete', [AdminTagController::class, 'delete']);

    $router->get('/roles', [AdminRoleController::class, 'index']);
    $router->get('/roles/{id}', [AdminRoleController::class, 'show']);
    $router->post('/roles/store', [AdminRoleController::class, 'store']);
    $router->post('/roles/{id}/update', [AdminRoleController::class, 'update']);
    $router->post('/roles/{id}/delete', [AdminRoleController::class, 'delete']);

    $router->get('/permissions', [AdminPermissionController::class, 'index']);
    $router->post('/permissions/{id}/update', [AdminPermissionController::class, 'update']);

    $router->get('/reports', [AdminReportController::class, 'index']);
    $router->get('/reports/{id}', [AdminReportController::class, 'show']);
    $router->post('/reports/{id}/resolve', [AdminReportController::class, 'resolve']);
    $router->post('/reports/{id}/dismiss', [AdminReportController::class, 'dismiss']);

    $router->get('/settings', [AdminSettingsController::class, 'index']);
    $router->post('/settings/update', [AdminSettingsController::class, 'update']);

    $router->get('/analytics', [AdminAnalyticsController::class, 'index']);

    $router->get('/activity-logs', [AdminActivityLogController::class, 'index']);
    $router->get('/audit-logs', [AdminAuditLogController::class, 'index']);
    $router->get('/login-logs', [AdminLoginLogController::class, 'index']);

    $router->get('/blog', [AdminBlogController::class, 'index']);
    $router->get('/blog/create', [AdminBlogController::class, 'create']);
    $router->post('/blog/store', [AdminBlogController::class, 'store']);
    $router->get('/blog/{id}/edit', [AdminBlogController::class, 'edit']);
    $router->post('/blog/{id}/update', [AdminBlogController::class, 'update']);
    $router->post('/blog/{id}/delete', [AdminBlogController::class, 'delete']);

    $router->get('/pages', [AdminPageController::class, 'index']);
    $router->get('/pages/create', [AdminPageController::class, 'create']);
    $router->post('/pages/store', [AdminPageController::class, 'store']);
    $router->get('/pages/{id}/edit', [AdminPageController::class, 'edit']);
    $router->post('/pages/{id}/update', [AdminPageController::class, 'update']);
    $router->post('/pages/{id}/delete', [AdminPageController::class, 'delete']);

    $router->get('/faqs', [AdminFaqController::class, 'index']);
    $router->post('/faqs/store', [AdminFaqController::class, 'store']);
    $router->post('/faqs/{id}/update', [AdminFaqController::class, 'update']);
    $router->post('/faqs/{id}/delete', [AdminFaqController::class, 'delete']);

    $router->get('/email-templates', [AdminEmailTemplateController::class, 'index']);
    $router->get('/email-templates/{id}/edit', [AdminEmailTemplateController::class, 'edit']);
    $router->post('/email-templates/{id}/update', [AdminEmailTemplateController::class, 'update']);

    $router->get('/contact-messages', [AdminContactMessageController::class, 'index']);
    $router->get('/contact-messages/{id}', [AdminContactMessageController::class, 'show']);
    $router->post('/contact-messages/{id}/read', [AdminContactMessageController::class, 'markRead']);
    $router->post('/contact-messages/{id}/delete', [AdminContactMessageController::class, 'delete']);

    $router->get('/monetization', [AdminMonetizationController::class, 'index']);
    $router->post('/monetization/{id}/approve', [AdminMonetizationController::class, 'approve']);
    $router->post('/monetization/{id}/reject', [AdminMonetizationController::class, 'reject']);

    $router->get('/payments', [AdminPaymentController::class, 'index']);
    $router->get('/payouts', [AdminPayoutController::class, 'index']);
    $router->post('/payouts/{id}/process', [AdminPayoutController::class, 'process']);

    $router->get('/revenue', [AdminRevenueController::class, 'index']);

    $router->get('/creators', [AdminCreatorController::class, 'index']);
    $router->get('/creators/{id}', [AdminCreatorController::class, 'show']);

    $router->get('/notifications', [AdminNotificationController::class, 'index']);
    $router->post('/notifications/store', [AdminNotificationController::class, 'store']);

    $router->get('/copyright', [AdminCopyrightController::class, 'index']);
    $router->get('/copyright/{id}', [AdminCopyrightController::class, 'show']);
    $router->post('/copyright/{id}/resolve', [AdminCopyrightController::class, 'resolve']);

    $router->get('/ads', [AdminAdController::class, 'index']);
    $router->get('/ads/{id}', [AdminAdController::class, 'show']);
    $router->post('/ads/{id}/approve', [AdminAdController::class, 'approve']);
    $router->post('/ads/{id}/reject', [AdminAdController::class, 'reject']);

    $router->get('/backups', [AdminBackupController::class, 'index']);
    $router->post('/backups/create', [AdminBackupController::class, 'create']);
    $router->post('/backups/{id}/restore', [AdminBackupController::class, 'restore']);
    $router->post('/backups/{id}/delete', [AdminBackupController::class, 'delete']);

    $router->get('/system-health', [AdminSystemHealthController::class, 'index']);

    $router->get('/email-settings', [AdminEmailSettingsController::class, 'index']);
    $router->post('/email-settings/update', [AdminEmailSettingsController::class, 'update']);

    $router->get('/sms-settings', [AdminSmsSettingsController::class, 'index']);
    $router->post('/sms-settings/update', [AdminSmsSettingsController::class, 'update']);

    $router->get('/policies', [AdminPolicyController::class, 'index']);
    $router->post('/policies/store', [AdminPolicyController::class, 'store']);
    $router->post('/policies/{id}/update', [AdminPolicyController::class, 'update']);
}, ['auth', 'role:admin']);

$router->group('/creator', function ($router) {
    $router->get('/', function () { return Response::redirect('/creator/dashboard'); });
    $router->get('/dashboard', [CreatorDashboardController::class, 'index']);
    $router->get('/videos', [CreatorVideoController::class, 'index']);
    $router->get('/videos/create', [CreatorVideoController::class, 'create']);
    $router->post('/videos/store', [CreatorVideoController::class, 'store']);
    $router->get('/videos/{id}/edit', [CreatorVideoController::class, 'edit']);
    $router->post('/videos/{id}/update', [CreatorVideoController::class, 'update']);
    $router->post('/videos/{id}/delete', [CreatorVideoController::class, 'delete']);
    $router->get('/channel/create', [CreatorChannelController::class, 'create']);
    $router->post('/channel/store', [CreatorChannelController::class, 'store']);
    $router->get('/channel', [CreatorChannelController::class, 'index']);
    $router->post('/channel/update', [CreatorChannelController::class, 'update']);
    $router->get('/comments', [CreatorCommentController::class, 'index']);
    $router->post('/comments/{id}/pin', [CreatorCommentController::class, 'pin']);
    $router->post('/comments/{id}/heart', [CreatorCommentController::class, 'heart']);
    $router->post('/comments/{id}/delete', [CreatorCommentController::class, 'delete']);
    $router->get('/playlists', [CreatorPlaylistController::class, 'index']);
    $router->get('/analytics', [CreatorAnalyticsController::class, 'index']);
    $router->get('/monetization', [CreatorMonetizationController::class, 'index']);
    $router->post('/monetization/apply', [CreatorMonetizationController::class, 'apply']);
    $router->get('/community', [CreatorCommunityController::class, 'index']);
    $router->get('/live', [CreatorLiveController::class, 'index']);
    $router->post('/live/start', [CreatorLiveController::class, 'start']);
    $router->get('/shorts', [CreatorShortController::class, 'index']);
    $router->get('/copyright', [CreatorCopyrightController::class, 'index']);
}, ['auth']);
