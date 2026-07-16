<?php

return [
    'up' => "

-- ============================================================
-- Seed Roles
-- ============================================================
INSERT INTO `roles` (`name`, `slug`, `description`, `level`, `is_system`, `created_at`, `updated_at`) VALUES
('Guest', 'guest', 'Unauthenticated visitor with read-only access.', 0, 1, NOW(), NOW()),
('User', 'user', 'Registered user with basic platform privileges.', 1, 1, NOW(), NOW()),
('Creator', 'creator', 'Content creator who can upload and manage videos.', 2, 1, NOW(), NOW()),
('Moderator', 'moderator', 'Community moderator who can review and manage content.', 3, 1, NOW(), NOW()),
('Reviewer', 'reviewer', 'Staff reviewer for copyright, reports, and content quality.', 4, 1, NOW(), NOW()),
('Advertiser', 'advertiser', 'Advertiser who can manage ad campaigns.', 5, 1, NOW(), NOW()),
('Support', 'support', 'Support staff who can handle tickets and FAQs.', 6, 1, NOW(), NOW()),
('Admin', 'admin', 'Full system administrator with all privileges.', 7, 1, NOW(), NOW());

-- ============================================================
-- Seed Permissions
-- ============================================================
-- Video permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `group_name`, `created_at`, `updated_at`) VALUES
('View Videos', 'video.view', 'View published videos.', 'video', NOW(), NOW()),
('Upload Videos', 'video.upload', 'Upload new videos.', 'video', NOW(), NOW()),
('Edit Own Videos', 'video.edit-own', 'Edit own video details.', 'video', NOW(), NOW()),
('Edit Any Video', 'video.edit-any', 'Edit any user video.', 'video', NOW(), NOW()),
('Delete Own Videos', 'video.delete-own', 'Delete own videos.', 'video', NOW(), NOW()),
('Delete Any Video', 'video.delete-any', 'Delete any user video.', 'video', NOW(), NOW()),
('Publish Videos', 'video.publish', 'Publish or unpublish videos.', 'video', NOW(), NOW()),
('Feature Videos', 'video.feature', 'Feature videos on homepage.', 'video', NOW(), NOW()),
('Download Videos', 'video.download', 'Download videos for offline viewing.', 'video', NOW(), NOW()),
('View Video Analytics', 'video.analytics', 'View video analytics and stats.', 'video', NOW(), NOW());

-- Channel permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `group_name`, `created_at`, `updated_at`) VALUES
('Create Channel', 'channel.create', 'Create a channel.', 'channel', NOW(), NOW()),
('Edit Own Channel', 'channel.edit-own', 'Edit own channel details.', 'channel', NOW(), NOW()),
('Edit Any Channel', 'channel.edit-any', 'Edit any channel.', 'channel', NOW(), NOW()),
('Delete Own Channel', 'channel.delete-own', 'Delete own channel.', 'channel', NOW(), NOW()),
('Delete Any Channel', 'channel.delete-any', 'Delete any channel.', 'channel', NOW(), NOW()),
('Verify Channel', 'channel.verify', 'Mark channels as verified.', 'channel', NOW(), NOW()),
('View Channel Analytics', 'channel.analytics', 'View channel analytics.', 'channel', NOW(), NOW());

-- Comment permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `group_name`, `created_at`, `updated_at`) VALUES
('View Comments', 'comment.view', 'View comments on videos.', 'comment', NOW(), NOW()),
('Create Comment', 'comment.create', 'Post comments on videos.', 'comment', NOW(), NOW()),
('Edit Own Comment', 'comment.edit-own', 'Edit own comments.', 'comment', NOW(), NOW()),
('Delete Own Comment', 'comment.delete-own', 'Delete own comments.', 'comment', NOW(), NOW()),
('Delete Any Comment', 'comment.delete-any', 'Delete any comment.', 'comment', NOW(), NOW()),
('Pin Comment', 'comment.pin', 'Pin comments on videos.', 'comment', NOW(), NOW()),
('Heart Comment', 'comment.heart', 'Heart comments on videos.', 'comment', NOW(), NOW());

-- Playlist permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `group_name`, `created_at`, `updated_at`) VALUES
('View Playlists', 'playlist.view', 'View playlists.', 'playlist', NOW(), NOW()),
('Create Playlist', 'playlist.create', 'Create playlists.', 'playlist', NOW(), NOW()),
('Edit Own Playlist', 'playlist.edit-own', 'Edit own playlists.', 'playlist', NOW(), NOW()),
('Delete Own Playlist', 'playlist.delete-own', 'Delete own playlists.', 'playlist', NOW(), NOW()),
('Delete Any Playlist', 'playlist.delete-any', 'Delete any playlist.', 'playlist', NOW(), NOW());

-- User permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `group_name`, `created_at`, `updated_at`) VALUES
('View Users', 'user.view', 'View user profiles.', 'user', NOW(), NOW()),
('Edit Own Profile', 'user.edit-own', 'Edit own profile.', 'user', NOW(), NOW()),
('Edit Any User', 'user.edit-any', 'Edit any user profile.', 'user', NOW(), NOW()),
('Delete Own Account', 'user.delete-own', 'Delete own account.', 'user', NOW(), NOW()),
('Delete Any User', 'user.delete-any', 'Delete any user account.', 'user', NOW(), NOW()),
('Ban Users', 'user.ban', 'Ban users from the platform.', 'user', NOW(), NOW()),
('Unban Users', 'user.unban', 'Unban users.', 'user', NOW(), NOW()),
('View User Activity', 'user.activity', 'View user activity logs.', 'user', NOW(), NOW());

-- Role permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `group_name`, `created_at`, `updated_at`) VALUES
('View Roles', 'role.view', 'View roles.', 'role', NOW(), NOW()),
('Create Roles', 'role.create', 'Create new roles.', 'role', NOW(), NOW()),
('Edit Roles', 'role.edit', 'Edit roles.', 'role', NOW(), NOW()),
('Delete Roles', 'role.delete', 'Delete roles.', 'role', NOW(), NOW()),
('Assign Roles', 'role.assign', 'Assign roles to users.', 'role', NOW(), NOW());

-- Category permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `group_name`, `created_at`, `updated_at`) VALUES
('View Categories', 'category.view', 'View categories.', 'category', NOW(), NOW()),
('Create Categories', 'category.create', 'Create categories.', 'category', NOW(), NOW()),
('Edit Categories', 'category.edit', 'Edit categories.', 'category', NOW(), NOW()),
('Delete Categories', 'category.delete', 'Delete categories.', 'category', NOW(), NOW());

-- Report permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `group_name`, `created_at`, `updated_at`) VALUES
('Create Report', 'report.create', 'Submit reports.', 'report', NOW(), NOW()),
('View Reports', 'report.view', 'View all reports.', 'report', NOW(), NOW()),
('Review Reports', 'report.review', 'Review and triage reports.', 'report', NOW(), NOW()),
('Resolve Reports', 'report.resolve', 'Resolve or dismiss reports.', 'report', NOW(), NOW());

-- Admin permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `group_name`, `created_at`, `updated_at`) VALUES
('Access Admin Panel', 'admin.access', 'Access the admin dashboard.', 'admin', NOW(), NOW()),
('Manage Settings', 'admin.settings', 'Manage platform settings.', 'admin', NOW(), NOW()),
('View Dashboard', 'admin.dashboard', 'View admin dashboard stats.', 'admin', NOW(), NOW()),
('Manage Blog', 'admin.blog', 'Manage blog posts.', 'admin', NOW(), NOW()),
('Manage Pages', 'admin.pages', 'Manage static pages.', 'admin', NOW(), NOW()),
('Manage FAQs', 'admin.faqs', 'Manage FAQ entries.', 'admin', NOW(), NOW()),
('Manage Email Templates', 'admin.email-templates', 'Manage email templates.', 'admin', NOW(), NOW()),
('View Audit Logs', 'admin.audit-logs', 'View audit logs.', 'admin', NOW(), NOW());

-- Creator permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `group_name`, `created_at`, `updated_at`) VALUES
('View Earnings', 'creator.earnings', 'View earnings and revenue.', 'creator', NOW(), NOW()),
('Manage Monetization', 'creator.monetization', 'Manage monetization settings.', 'creator', NOW(), NOW()),
('View Creator Analytics', 'creator.analytics', 'View detailed creator analytics.', 'creator', NOW(), NOW()),
('Manage Subtitles', 'creator.subtitles', 'Upload and manage subtitles.', 'creator', NOW(), NOW()),
('Go Live', 'creator.live', 'Start live streams.', 'creator', NOW(), NOW()),
('Create Shorts', 'creator.shorts', 'Create YouTube Shorts.', 'creator', NOW(), NOW());

-- Moderator permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `group_name`, `created_at`, `updated_at`) VALUES
('Moderate Comments', 'moderator.comments', 'Moderate and filter comments.', 'moderator', NOW(), NOW()),
('Moderate Videos', 'moderator.videos', 'Review and moderate videos.', 'moderator', NOW(), NOW()),
('Manage Violations', 'moderator.violations', 'Manage content violations.', 'moderator', NOW(), NOW()),
('Issue Warnings', 'moderator.warnings', 'Issue warnings to users.', 'moderator', NOW(), NOW()),
('View Moderation Queue', 'moderator.queue', 'View moderation queue.', 'moderator', NOW(), NOW());

-- Reviewer permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `group_name`, `created_at`, `updated_at`) VALUES
('Review Copyright Claims', 'reviewer.copyright', 'Review and resolve copyright claims.', 'reviewer', NOW(), NOW()),
('Review Reports', 'reviewer.reports', 'Review user reports.', 'reviewer', NOW(), NOW()),
('Review Content', 'reviewer.content', 'Review flagged content.', 'reviewer', NOW(), NOW()),
('Manage Violations', 'reviewer.violations', 'Handle violation actions.', 'reviewer', NOW(), NOW());

-- Advertiser permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `group_name`, `created_at`, `updated_at`) VALUES
('Create Ads', 'advertiser.create', 'Create advertisements.', 'advertiser', NOW(), NOW()),
('Manage Ads', 'advertiser.manage', 'Manage own advertisements.', 'advertiser', NOW(), NOW()),
('View Ad Analytics', 'advertiser.analytics', 'View ad performance analytics.', 'advertiser', NOW(), NOW()),
('Manage Campaigns', 'advertiser.campaigns', 'Manage ad campaigns.', 'advertiser', NOW(), NOW()),
('Manage Budget', 'advertiser.budget', 'Manage ad budgets and payments.', 'advertiser', NOW(), NOW());

-- Support permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `group_name`, `created_at`, `updated_at`) VALUES
('View Tickets', 'support.view-tickets', 'View support tickets.', 'support', NOW(), NOW()),
('Reply to Tickets', 'support.reply', 'Reply to support tickets.', 'support', NOW(), NOW()),
('Assign Tickets', 'support.assign', 'Assign tickets to staff.', 'support', NOW(), NOW()),
('Close Tickets', 'support.close', 'Close support tickets.', 'support', NOW(), NOW()),
('Manage FAQs', 'support.faqs', 'Manage FAQ entries.', 'support', NOW(), NOW()),
('Manage Contact Messages', 'support.contact', 'Manage contact form messages.', 'support', NOW(), NOW());

-- Settings permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `group_name`, `created_at`, `updated_at`) VALUES
('View Settings', 'settings.view', 'View platform settings.', 'settings', NOW(), NOW()),
('Edit Settings', 'settings.edit', 'Edit platform settings.', 'settings', NOW(), NOW());

-- Backup permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `group_name`, `created_at`, `updated_at`) VALUES
('Create Backups', 'backup.create', 'Create database backups.', 'backup', NOW(), NOW()),
('Restore Backups', 'backup.restore', 'Restore from backups.', 'backup', NOW(), NOW()),
('View Backup Logs', 'backup.view', 'View backup history.', 'backup', NOW(), NOW()),
('Delete Backups', 'backup.delete', 'Delete old backups.', 'backup', NOW(), NOW());

-- ============================================================
-- Assign Permissions to Roles
-- ============================================================

-- Admin gets ALL permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`, `updated_at`)
SELECT r.id, p.id, NOW(), NOW()
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.slug = 'admin';

-- Support: all except admin.*, backup.delete, role.*, settings.edit, video.delete-any, channel.delete-any, user.delete-any
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`, `updated_at`)
SELECT r.id, p.id, NOW(), NOW()
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.slug = 'support'
AND p.slug NOT IN (
    'admin.access', 'admin.settings', 'admin.dashboard', 'admin.blog', 'admin.pages',
    'admin.faqs', 'admin.email-templates', 'admin.audit-logs',
    'backup.restore', 'backup.delete',
    'role.create', 'role.edit', 'role.delete', 'role.assign',
    'settings.edit',
    'video.delete-any', 'channel.delete-any', 'user.delete-any',
    'user.ban', 'user.unban', 'user.edit-any'
);

-- Advertiser: advertiser.*, video.view, video.analytics, channel.view (via edit-own), channel.analytics, video.download, comment.view, playlist.view
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`, `updated_at`)
SELECT r.id, p.id, NOW(), NOW()
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.slug = 'advertiser'
AND p.slug IN (
    'video.view', 'video.download', 'video.analytics',
    'channel.edit-own', 'channel.analytics',
    'comment.view',
    'playlist.view',
    'user.edit-own', 'user.view',
    'advertiser.create', 'advertiser.manage', 'advertiser.analytics', 'advertiser.campaigns', 'advertiser.budget'
);

-- Reviewer: reviewer.*, report.*, video.view, video.edit-any, video.delete-any, comment.view, comment.delete-any, moderator.*
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`, `updated_at`)
SELECT r.id, p.id, NOW(), NOW()
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.slug = 'reviewer'
AND p.slug IN (
    'video.view', 'video.edit-any', 'video.delete-any', 'video.publish', 'video.analytics',
    'channel.edit-any', 'channel.delete-any', 'channel.verify', 'channel.analytics',
    'comment.view', 'comment.delete-any', 'comment.pin', 'comment.heart',
    'playlist.view', 'playlist.delete-any',
    'user.view', 'user.edit-any', 'user.ban', 'user.unban', 'user.activity',
    'category.view',
    'report.view', 'report.review', 'report.resolve',
    'reviewer.copyright', 'reviewer.reports', 'reviewer.content', 'reviewer.violations',
    'moderator.comments', 'moderator.videos', 'moderator.violations', 'moderator.warnings', 'moderator.queue'
);

-- Moderator: moderator.*, report.view, report.review, video.view, video.edit-any, video.delete-any, comment.*, channel.edit-any
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`, `updated_at`)
SELECT r.id, p.id, NOW(), NOW()
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.slug = 'moderator'
AND p.slug IN (
    'video.view', 'video.edit-any', 'video.delete-any', 'video.publish', 'video.analytics',
    'channel.view', 'channel.edit-any', 'channel.analytics',
    'comment.view', 'comment.create', 'comment.edit-own', 'comment.delete-own', 'comment.delete-any', 'comment.pin', 'comment.heart',
    'playlist.view',
    'user.view', 'user.edit-any', 'user.ban', 'user.unban', 'user.activity',
    'category.view',
    'report.view', 'report.review',
    'moderator.comments', 'moderator.videos', 'moderator.violations', 'moderator.warnings', 'moderator.queue'
);

-- Creator: creator.*, video.*, channel.*, comment.*, playlist.*, category.view, user.view, user.edit-own
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`, `updated_at`)
SELECT r.id, p.id, NOW(), NOW()
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.slug = 'creator'
AND p.slug IN (
    'video.view', 'video.upload', 'video.edit-own', 'video.delete-own', 'video.publish', 'video.download', 'video.analytics',
    'channel.create', 'channel.edit-own', 'channel.delete-own', 'channel.analytics',
    'comment.view', 'comment.create', 'comment.edit-own', 'comment.delete-own', 'comment.heart',
    'playlist.view', 'playlist.create', 'playlist.edit-own', 'playlist.delete-own',
    'user.view', 'user.edit-own',
    'category.view',
    'report.create',
    'creator.earnings', 'creator.monetization', 'creator.analytics', 'creator.subtitles', 'creator.live', 'creator.shorts'
);

-- User: video.view, video.download, channel.view, channel.edit-own (own channel), comment.*, playlist.*, user.edit-own, report.create, category.view
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`, `updated_at`)
SELECT r.id, p.id, NOW(), NOW()
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.slug = 'user'
AND p.slug IN (
    'video.view', 'video.download',
    'comment.view', 'comment.create', 'comment.edit-own', 'comment.delete-own',
    'playlist.view', 'playlist.create', 'playlist.edit-own', 'playlist.delete-own',
    'user.view', 'user.edit-own',
    'report.create',
    'category.view'
);

-- Guest: video.view, comment.view, playlist.view, category.view, user.view
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`, `updated_at`)
SELECT r.id, p.id, NOW(), NOW()
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.slug = 'guest'
AND p.slug IN (
    'video.view',
    'comment.view',
    'playlist.view',
    'category.view'
);

-- ============================================================
-- Seed Super Admin User
-- ============================================================
INSERT INTO `users` (`username`, `email`, `password`, `first_name`, `last_name`, `role_id`, `email_verified_at`, `is_active`, `created_at`, `updated_at`) VALUES
('admin', 'admin@youtube.com', '$2y$12$plcZqjq8q9cwZEbwe0vut.soxs5RgLjMWJ0RKBwGBnE1s1ovXi4P2', 'Super', 'Admin', 1, NOW(), 1, NOW(), NOW());

-- Assign admin role via user_roles
INSERT INTO `user_roles` (`user_id`, `role_id`, `created_at`, `updated_at`)
SELECT u.id, r.id, NOW(), NOW()
FROM `users` u
CROSS JOIN `roles` r
WHERE u.email = 'admin@youtube.com'
AND r.slug = 'admin';
",
];
