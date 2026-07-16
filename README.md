# YouTube Clone

A full-featured YouTube clone built with PHP 8+, MySQL 8, and a custom MVC framework. Features video uploading, streaming (HLS), channels, subscriptions, comments, playlists, live streaming, shorts, ads, and a complete admin panel.

## Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache with mod_rewrite enabled (or equivalent)
- FFmpeg and FFprobe (for video processing/thumbnails)
- PDO MySQL extension

## Installation

### 1. Clone / Download

Place the project in your web server's document root:

```
C:\xampp\htdocs\youtube\   (Windows/XAMPP)
/var/www/html/youtube/     (Linux)
```

### 2. Environment Configuration

Copy and edit the `.env` file if needed:

```bash
cp .env.example .env
```

Key settings:
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `APP_URL` - Your application URL
- `APP_KEY` - Change to a random secret string
- `FFMPEG_PATH` - Path to FFmpeg binary

### 3. Database Setup

**Option A: Web Installer (Recommended)**

Point your browser to:
```
http://localhost/youtube/public/install.php
```

Follow the on-screen instructions to set up the database and admin account.

**Option B: Command Line**

```bash
php database/setup.php
```

### 4. Directory Permissions

Ensure the following directories are writable by the web server:

```
storage/
storage/avatars/
storage/banners/
storage/thumbnails/
storage/videos/
storage/hls/
storage/shorts/
storage/cache/
storage/sessions/
storage/logs/
storage/backups/
storage/temp/
storage/framework/
storage/framework/views/
storage/framework/views/compiled/
```

### 5. Web Server Configuration

**XAMPP:** Point DocumentRoot to the project root, or use the `.htaccess` file (already included).

**Apache:** Enable `mod_rewrite` and set up a VirtualHost:

```apache
<VirtualHost *:80>
    ServerName youtube.local
    DocumentRoot /var/www/html/youtube
    <Directory /var/www/html/youtube>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx:** Add a location block to pass requests to `public/index.php`:

```nginx
location / {
    try_files $uri $uri/ /public/index.php?$query_string;
}
```

## Default Admin Credentials

| Field    | Value             |
|----------|-------------------|
| Email    | admin@youtube.com |
| Password | Admin@123         |

**Change the admin password immediately after first login.**

## Configuration

All configuration is in `config/config.php` and can be overridden via the `.env` file.

| Setting         | Description                              |
|-----------------|------------------------------------------|
| `APP_URL`       | Base URL of the application              |
| `APP_ENV`       | `development` or `production`            |
| `APP_DEBUG`     | `true` enables debug error pages         |
| `DB_*`          | Database connection settings             |
| `FFMPEG_PATH`   | Path to FFmpeg binary for video processing|
| `MAIL_*`        | SMTP settings for email delivery         |
| `SESSION_*`     | Session configuration                    |
| `UPLOAD_*`      | File upload limits                       |
| `RATELIMIT_*`   | API rate limiting settings               |

## Project Structure

```
youtube/
├── app/
│   ├── Controllers/
│   │   ├── Admin/          # Admin panel controllers (33)
│   │   ├── Api/            # REST API controllers (10)
│   │   ├── Auth/           # Authentication controllers
│   │   ├── Creator/        # Content creator controllers (11)
│   │   ├── Guest/          # Public page controllers (13)
│   │   ├── Moderator/      # Moderator controllers (6)
│   │   ├── Reviewer/       # Review staff controllers (5)
│   │   ├── Support/        # Support staff controllers (6)
│   │   ├── Advertiser/     # Advertiser controllers (6)
│   │   └── Viewer/         # Authenticated user controllers (9)
│   ├── Core/               # Framework core (Autoloader, Router, Controller, Model, Database, View, Session, Middleware, Request, Response)
│   ├── Helpers/            # Global helper functions
│   ├── Middleware/          # HTTP middleware (11)
│   ├── Models/             # Eloquent-style models (21)
│   └── Services/           # Business logic services (16)
├── config/                 # Configuration files
├── database/
│   ├── migrations/         # Database migrations (52 tables)
│   └── seeders/            # Roles, permissions, and admin user
├── public/                 # Web root
│   ├── index.php           # Front controller
│   └── install.php         # Web installer
├── resources/
│   └── views/              # Blade-style templates
│       ├── admin/          # Admin panel views
│       ├── auth/           # Login, register, etc.
│       ├── creator/        # Creator dashboard views
│       ├── guest/          # Public pages
│       ├── layouts/        # Layout templates (admin, app, auth, creator, etc.)
│       ├── partials/       # Reusable view partials
│       └── viewer/         # User dashboard views
├── routes/
│   ├── web.php             # Web routes
│   └── api.php             # API routes
└── storage/                # Uploads, cache, logs, sessions
```

## Roles & Permissions

The system ships with 8 roles:

| Role       | Description                              | Level |
|------------|------------------------------------------|-------|
| Guest      | Unauthenticated visitor                  | 0     |
| User       | Registered user with basic privileges    | 1     |
| Creator    | Content creator (upload, manage videos)  | 2     |
| Moderator  | Community moderator                      | 3     |
| Reviewer   | Staff reviewer for copyright/reports     | 4     |
| Advertiser | Manages ad campaigns                     | 5     |
| Support    | Handles tickets and FAQs                 | 6     |
| Admin      | Full system administrator                | 7     |

Over 80 granular permissions cover video, channel, comment, playlist, user, role, category, report, admin, creator, moderator, reviewer, advertiser, support, settings, and backup operations.

## API Documentation

All API endpoints are prefixed with `/api/`.

### Public Endpoints

| Method | Endpoint                    | Description                |
|--------|-----------------------------|----------------------------|
| GET    | /api/status                 | API health check           |
| POST   | /api/auth/login             | Login, returns token       |
| POST   | /api/auth/register          | Register new account       |
| GET    | /api/videos                 | List videos                |
| GET    | /api/videos/{id}            | Get video details          |
| GET    | /api/videos/trending        | Trending videos            |
| GET    | /api/channels               | List channels              |
| GET    | /api/channels/{id}          | Get channel details        |
| GET    | /api/categories             | List categories            |
| GET    | /api/search?q=              | Search videos              |

### Protected Endpoints (Bearer Token)

| Method | Endpoint                    | Description                |
|--------|-----------------------------|----------------------------|
| GET    | /api/auth/me                | Get current user           |
| POST   | /api/auth/logout            | Logout                     |
| POST   | /api/videos                 | Upload video               |
| PUT    | /api/videos/{id}            | Update video               |
| DELETE | /api/videos/{id}            | Delete video               |
| POST   | /api/videos/{id}/like       | Like/unlike video          |
| GET    | /api/user/profile           | Get profile                |
| PUT    | /api/user/profile           | Update profile             |

Include the token in the Authorization header:
```
Authorization: Bearer YOUR_API_TOKEN
```

## Database

The application uses **52 tables** covering:

- **Users & Auth:** users, roles, permissions, role_permissions, user_roles, sessions, password_resets, email_verifications, otp_verifications, social_accounts, two_factor_codes, api_tokens
- **Content:** videos, channels, categories, tags, video_tags, shorts, comments, comment_likes, playlists, playlist_videos
- **Engagement:** video_views, video_likes, watch_history, watch_later, user_downloads, subscriptions, notifications
- **Moderation:** reports, violations, copyright_claims
- **Monetization:** monetization_settings, advertisements, ad_campaigns, ad_placements, payouts, creator_earnings
- **Support:** support_tickets, ticket_replies, faqs, contact_messages
- **Admin:** settings, blog_posts, pages, email_templates, activity_logs, login_logs, audit_logs, backup_logs, cache_table, rate_limits

## License

MIT License
