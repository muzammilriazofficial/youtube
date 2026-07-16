<?php

declare(strict_types=1);

use App\Core\Application;
use App\Controllers\Api\AuthApiController;
use App\Controllers\Api\VideoApiController;
use App\Controllers\Api\ChannelApiController;
use App\Controllers\Api\CommentApiController;
use App\Controllers\Api\PlaylistApiController;
use App\Controllers\Api\SearchApiController;
use App\Controllers\Api\CategoryApiController;
use App\Controllers\Api\UserApiController;
use App\Controllers\Api\NotificationApiController;

$app = Application::getInstance();
$router = $app->getRouter();

$router->group('/api', function ($router) {

    $router->get('/status', function () {
        return \App\Core\Response::json([
            'status'    => 'success',
            'message'   => 'API is running.',
            'data'      => [
                'version'   => '1.0.0',
                'timestamp' => date('Y-m-d H:i:s'),
            ],
        ]);
    });

    // ── Auth (Public) ─────────────────────────────────────────────────────
    $router->post('/auth/login', [AuthApiController::class, 'login']);
    $router->post('/auth/register', [AuthApiController::class, 'register']);
    $router->post('/auth/forgot-password', [AuthApiController::class, 'forgotPassword']);
    $router->post('/auth/reset-password', [AuthApiController::class, 'resetPassword']);

    // ── Auth (Protected) ──────────────────────────────────────────────────
    $router->group('/auth', function ($router) {
        $router->post('/logout', [AuthApiController::class, 'logout']);
        $router->get('/me', [AuthApiController::class, 'me']);
    }, ['api.auth']);

    // ── Videos (Public) ───────────────────────────────────────────────────
    $router->get('/videos/trending', [VideoApiController::class, 'trending']);
    $router->get('/videos/related/{id}', [VideoApiController::class, 'related']);
    $router->get('/videos', [VideoApiController::class, 'index']);
    $router->get('/videos/{id}', [VideoApiController::class, 'show']);

    // ── Videos (Protected) ────────────────────────────────────────────────
    $router->group('/videos', function ($router) {
        $router->post('/', [VideoApiController::class, 'store']);
        $router->put('/{id}', [VideoApiController::class, 'update']);
        $router->delete('/{id}', [VideoApiController::class, 'delete']);
        $router->post('/{id}/like', [VideoApiController::class, 'like']);
        $router->post('/{id}/view', [VideoApiController::class, 'view']);
        $router->get('/{id}/comments', [VideoApiController::class, 'comments']);
    }, ['api.auth']);

    // ── Channels (Public) ─────────────────────────────────────────────────
    $router->get('/channels', [ChannelApiController::class, 'index']);
    $router->get('/channels/{id}', [ChannelApiController::class, 'show']);
    $router->get('/channels/{id}/videos', [ChannelApiController::class, 'videos']);
    $router->get('/channels/{id}/playlists', [ChannelApiController::class, 'playlists']);

    // ── Channels (Protected) ──────────────────────────────────────────────
    $router->group('/channels', function ($router) {
        $router->put('/{id}', [ChannelApiController::class, 'update']);
        $router->post('/{id}/subscribe', [ChannelApiController::class, 'subscribe']);
    }, ['api.auth']);

    // ── Comments (Public) ─────────────────────────────────────────────────
    $router->get('/comments', [CommentApiController::class, 'index']);
    $router->get('/comments/{id}/replies', [CommentApiController::class, 'replies']);

    // ── Comments (Protected) ──────────────────────────────────────────────
    $router->group('/comments', function ($router) {
        $router->post('/', [CommentApiController::class, 'store']);
        $router->delete('/{id}', [CommentApiController::class, 'delete']);
        $router->post('/{id}/like', [CommentApiController::class, 'like']);
    }, ['api.auth']);

    // ── Playlists (Public) ────────────────────────────────────────────────
    $router->get('/playlists/{id}', [PlaylistApiController::class, 'show']);

    // ── Playlists (Protected) ─────────────────────────────────────────────
    $router->group('/playlists', function ($router) {
        $router->get('/', [PlaylistApiController::class, 'index']);
        $router->post('/', [PlaylistApiController::class, 'store']);
        $router->put('/{id}', [PlaylistApiController::class, 'update']);
        $router->delete('/{id}', [PlaylistApiController::class, 'delete']);
        $router->post('/{id}/videos', [PlaylistApiController::class, 'addVideo']);
        $router->delete('/{id}/videos/{videoId}', [PlaylistApiController::class, 'removeVideo']);
    }, ['api.auth']);

    // ── Categories (Public) ───────────────────────────────────────────────
    $router->get('/categories', [CategoryApiController::class, 'index']);
    $router->get('/categories/{id}', [CategoryApiController::class, 'show']);

    // ── Search (Public) ───────────────────────────────────────────────────
    $router->get('/search', [SearchApiController::class, 'index']);
    $router->get('/search/suggestions', [SearchApiController::class, 'suggestions']);

    // ── User (Protected) ──────────────────────────────────────────────────
    $router->group('/user', function ($router) {
        $router->get('/profile', [UserApiController::class, 'profile']);
        $router->put('/profile', [UserApiController::class, 'profile']);
        $router->get('/subscriptions', [UserApiController::class, 'subscriptions']);
        $router->get('/history', [UserApiController::class, 'history']);
        $router->get('/notifications', [UserApiController::class, 'notifications']);
        $router->post('/notifications/read', [UserApiController::class, 'markNotificationsRead']);
    }, ['api.auth']);

    // ── Notifications (Protected) ─────────────────────────────────────────
    $router->group('/notifications', function ($router) {
        $router->get('/', [NotificationApiController::class, 'index']);
        $router->post('/{id}/read', [NotificationApiController::class, 'read']);
        $router->post('/read-all', [NotificationApiController::class, 'readAll']);
    }, ['api.auth']);

});
