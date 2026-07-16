<?php

declare(strict_types=1);

namespace App\Controllers\Viewer;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Database;
use App\Models\User;
use App\Models\Channel;
use App\Models\Subscription;

class ProfileController extends Controller
{
    public function show(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $userModel = new User();
        $user = $userModel->find($userId);

        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        $subscriptionModel = new Subscription();
        $subscriptions = $subscriptionModel->getSubscriberChannels($userId, 100);

        $videoCount = 0;
        $totalViews = 0;
        if ($channel) {
            $videoModel = new \App\Models\Video();
            $videoCount = (int) $videoModel->where('channel_id', (int) $channel['id'])
                ->where('visibility', 'public')
                ->where('status', 'published')
                ->whereNull('deleted_at')
                ->count();
            $totalViews = (int) $videoModel->db->table('videos')
                ->where('channel_id', (int) $channel['id'])
                ->where('visibility', 'public')
                ->where('status', 'published')
                ->whereNull('deleted_at')
                ->sum('view_count');
        }

        return $this->view('viewer.profile', [
            'title' => 'My Profile',
            'user' => $user,
            'channel' => $channel,
            'subscriptions' => $subscriptions,
            'videoCount' => $videoCount,
            'totalViews' => $totalViews,
            'subscriptionCount' => count($subscriptions),
        ]);
    }

    public function edit(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $userModel = new User();
        $user = $userModel->find($userId);

        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        return $this->view('viewer.profile-edit', [
            'title' => 'Edit Profile',
            'user' => $user,
            'channel' => $channel,
        ]);
    }

    public function update(): Response
    {
        $userId = (int) $this->session->get('user_id');

        $errors = $this->validate([
            'display_name' => 'required|max:100',
            'description' => 'max:500',
        ]);

        if (!empty($errors)) {
            return $this->withInput()->respondWithError('Please fix the validation errors.');
        }

        $userModel = new User();
        $data = $this->request->only(['display_name', 'description']);

        if ($this->request->hasFile('avatar')) {
            $avatar = $this->request->file('avatar');
            if ($avatar && $avatar['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($avatar['name'], PATHINFO_EXTENSION);
                $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
                $uploadDir = __DIR__ . '/../../../public/storage/uploads/avatars';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                move_uploaded_file($avatar['tmp_name'], $uploadDir . DIRECTORY_SEPARATOR . $filename);
                $data['avatar'] = '/storage/uploads/avatars/' . $filename;
            }
        }

        $userModel->updateById($userId, $data);
        $this->session->forget('current_user');

        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);
        if ($channel) {
            $channelData = $this->request->only(['display_name', 'description']);
            if (isset($data['avatar'])) {
                $channelData['avatar'] = $data['avatar'];
            }
            $channelData['name'] = $data['display_name'] ?? $channel['name'];
            $channelModel->updateById((int) $channel['id'], $channelData);
        }

        $this->session->flash('success', 'Profile updated successfully.');
        return $this->redirect('/viewer/profile');
    }

    public function changePassword(): Response
    {
        return $this->view('viewer.profile-edit', [
            'title' => 'Change Password',
            'user' => $this->user(),
            'channel' => null,
            'activeTab' => 'password',
        ]);
    }

    public function updatePassword(): Response
    {
        $userId = (int) $this->session->get('user_id');

        $errors = $this->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!empty($errors)) {
            return $this->withInput()->respondWithError('Please fix the validation errors.');
        }

        $userModel = new User();
        $user = $userModel->find($userId);

        if (!$userModel->verifyPassword($user, $this->request->input('current_password'))) {
            return $this->withInput()->respondWithError('Current password is incorrect.');
        }

        $userModel->updatePassword($userId, $this->request->input('password'));

        $this->session->flash('success', 'Password changed successfully.');
        return $this->redirect('/viewer/profile');
    }

    public function deleteAccount(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $userModel = new User();
        $userModel->deleteById($userId);

        $this->session->flush();
        $this->session->invalidate();

        $this->session->flash('success', 'Your account has been deleted.');
        return $this->redirect('/');
    }
}
