# Campus Found

Campus Found is a responsive lost-and-found management system for campus communities. It helps students and staff report lost or found items, browse campus reports, submit ownership claims, and manage item recovery from one central website instead of scattered chat or social media posts.

**Live demo:** [https://campusfound.me](https://campusfound.me)

## Overview

Campus Found has two main areas:

- **User side:** public homepage, searchable board, account dashboard, report form, claims, found reports, email verification, and password reset.
- **Admin side:** protected dashboard for moderating reports, reviewing claims, managing users, resolving disputes, and viewing audit activity.

### Who uses it

| Role | Access |
|------|--------|
| **Guest** | Browse homepage, community board, and public claims |
| **User** | Register, verify email, report items, submit claims, manage account |
| **Admin** | Moderate reports and claims, resolve disputes, view audit log |
| **Super admin** | Everything admins can do, plus user/role management |

## Screenshots

<p align="center">
  <img src="screenshots/homepage.png" alt="Campus Found homepage" width="780">
  <br><em>Landing page with hero search and recent campus reports</em>
</p>

<p align="center">
  <img src="screenshots/public-feature.png" alt="Community board with filters" width="780">
  <br><em>Community board — the core browse-and-search experience</em>
</p>

<p align="center">
  <img src="screenshots/dashboard.png" alt="User account dashboard" width="780">
  <br><em>Account dashboard with owned reports, claims, and activity</em>
</p>

<p align="center">
  <img src="screenshots/create-form.png" alt="Report an item form" width="780">
  <br><em>Report form for submitting a lost or found item</em>
</p>

<p align="center">
  <img src="screenshots/admin-dashboard.png" alt="Admin moderation dashboard" width="780">
  <br><em>Admin dashboard for reports, claims, users, and audit activity</em>
</p>

## Technology Stack

| Layer | Tools |
|-------|-------|
| Backend | Laravel 13, PHP 8.3+ |
| Database | MySQL |
| Frontend | Blade, HTML, CSS, Bootstrap 5, Bootstrap Icons, JavaScript |
| Build | Vite, Tailwind CSS 4 |
| Auth | Laravel sessions (web) + Laravel Sanctum (API) |
| Email | SMTP (Brevo in production) |
| Queue | Laravel database queue |
| Storage | Public disk with WebP image optimization |
| Testing | PHPUnit feature tests |
| Deployment | DigitalOcean, Nginx, PHP-FPM, Supervisor, Certbot |

## Architecture

```
Browser / API client
        │
        ▼
  Laravel routes (web + api)
        │
        ├── Controllers (web + Api/)
        ├── Middleware (auth, active, verified, admin)
        └── Services (ItemDataService, ClaimDataService, EmailCodeService, …)
                │
                ▼
           Eloquent models → MySQL
                │
                ▼
        Queue (notifications) · Public storage (item images)
```

Business logic lives in service classes; controllers stay thin. Authorization is enforced in middleware and controller checks. Email verification and password reset use hashed, expiring one-time codes.

## Main Features

- Public homepage with recent lost and found reports
- Community board with search, status filter, category filter, date filter, and sorting
- User registration, login, logout, and account dashboard
- Email verification by code
- Password reset by email code
- User-owned report creation, editing, and deletion
- Lost/found item image upload and WebP optimization
- Claim submission with private ownership proof
- Found-report flow for lost items
- Owner review for pending claims and found reports
- Admin dashboard for reports, claims, users, moderation, disputes, and audit logs
- Role-based admin access with `user`, `admin`, and `super_admin`
- Sanctum API endpoints for auth, account data, items, claims, email verification, and password reset
- Responsive desktop and mobile layouts

## Local Setup

### Prerequisites

- PHP 8.3+ with `ext-gd` and `ext-pdo_mysql`
- Composer
- Node.js 18+ and npm
- MySQL 8+

### Install

**Windows (PowerShell):**

```powershell
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
```

**macOS / Linux:**

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Update `.env` with your local database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=campus_found
DB_USERNAME=your_local_user
DB_PASSWORD=your_local_password
```

Run migrations, link storage, build assets, and start the app:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

For local email testing, use the log mailer:

```env
MAIL_MAILER=log
```

If queued email is enabled locally, keep a worker running in another terminal:

```bash
php artisan queue:work
```

## Quality Checks

Run before opening a pull request or publishing the repo:

```bash
composer test          # 36 PHPUnit feature tests
vendor/bin/pint --test # Laravel code style
composer audit         # PHP dependency security audit
npm audit              # JavaScript dependency security audit
npm run build          # Production asset build
```

## Regenerating Screenshots

Portfolio screenshots are captured at **1280×800** (viewport, not full-page scroll).

1. Copy the example config and adjust if needed:

   **Windows:** `Copy-Item scripts/screenshot-config.example.json scripts/screenshot-config.json`

   **macOS / Linux:** `cp scripts/screenshot-config.example.json scripts/screenshot-config.json`

2. Start the app locally (`php artisan serve`).
3. Install Playwright browsers once: `npx playwright install chromium`
4. Seed demo data and capture:

   ```bash
   npm run screenshots
   ```

Output is written to `screenshots/`. Local credentials in `scripts/screenshot-config.json` are gitignored — only the example file is tracked.

## Admin Access

This project does not store a real administrator account or password in source code.

Create the first super administrator from your own terminal:

```bash
php artisan lostfound:create-super-admin
```

Admin URL: [http://127.0.0.1:8000/admin/login](http://127.0.0.1:8000/admin/login)

Public registration always creates normal user accounts. Administrator roles must be assigned by a super administrator inside the protected admin dashboard.

**Role rules:**

- `user` — can report items and submit claims
- `admin` — can moderate reports, claims, and disputes
- `super_admin` — can manage users, roles, and account status
- The final active super administrator cannot be demoted or suspended

## Email Setup

Campus Found uses Brevo SMTP for production email.

Email is used for registration verification codes, password reset codes, claim notifications, and report/claim status notifications.

Example production mail configuration (set values in `.env` only — never commit them):

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_smtp_username
MAIL_PASSWORD=your_brevo_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@campusfound.me"
MAIL_FROM_NAME="${APP_NAME}"
QUEUE_CONNECTION=database
```

Because emails are queued, production should run a queue worker:

```bash
php artisan queue:work --tries=3
```

## API Support

The project includes API routes for authentication, account data, items, claims, email verification, and password reset.

Import the Postman collection:

```text
postman/LostFound_API.postman_collection.json
```

Before running requests, fill the collection variables locally:

- `base_url`, `email`, `password`, `owner_token`, `claimant_token`
- `verification_code`, `reset_email`, `reset_code`

The collection does not include real login credentials.

## Repository Safety

These files are **gitignored** and must never be committed:

| File | Why |
|------|-----|
| `.env` | Database, mail, and app secrets |
| `scripts/screenshot-config.json` | Local demo login passwords |
| `auth.json` | Composer credentials |
| `storage/`, `vendor/`, `node_modules/` | Generated or installed dependencies |

Use `.env.example` and `scripts/screenshot-config.example.json` as templates only.

## Production Checklist

- Set `APP_ENV=production` and `APP_DEBUG=false`
- Set the public HTTPS `APP_URL`
- Generate a fresh production `APP_KEY`
- Use a dedicated production database user and password
- Configure Brevo SMTP credentials through environment variables only
- Run `php artisan migrate --force`, `storage:link`, and `optimize`
- Configure Supervisor for the Laravel queue worker
- Configure persistent storage or object storage for uploaded images
- Serve through Nginx and PHP-FPM 8.4 with HTTPS (Certbot)

## Project Status

Campus Found is deployed as a production Laravel website for [campusfound.me](https://campusfound.me). The codebase includes automated feature coverage for authentication, email verification, password reset, reports, claims, image uploads, account actions, API endpoints, admin moderation, user management, audit logs, and dispute resolution.

**Test suite:** 36 tests, 253 assertions (PHPUnit).

## License

This project is licensed under the [MIT License](LICENSE).
