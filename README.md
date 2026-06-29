# Campus Found

Campus Found is a responsive lost-and-found management system for campus communities. It helps students and staff report lost or found items, browse campus reports, submit ownership claims, and manage item recovery from one central website instead of scattered chat or social media posts.

Production website:

```text
https://campusfound.me
```

## Overview

Campus Found has two main areas:

- User side: public homepage, searchable board, account dashboard, report form, claims, found reports, email verification, and password reset.
- Admin side: protected dashboard for moderating reports, reviewing claims, managing users, resolving disputes, and viewing audit activity.

## Technology Stack

- Backend: Laravel 13, PHP 8.4
- Database: MySQL
- Frontend: Blade templates, HTML, CSS, Bootstrap 5, Bootstrap Icons, JavaScript
- Build tooling: Vite, npm
- Authentication: Laravel session authentication and Laravel Sanctum API tokens
- Email: Brevo SMTP
- Queue: Laravel database queue
- Storage: Laravel public disk with optimized WebP uploads
- Testing: PHPUnit / Laravel feature tests
- API testing: Postman collection
- Deployment: DigitalOcean, Ubuntu, Nginx, PHP-FPM 8.4, MySQL, Supervisor, Certbot, Namecheap DNS, GitHub

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
- Recently claimed/recovered items
- Admin dashboard for reports, claims, users, moderation, disputes, and audit logs
- Role-based admin access with `user`, `admin`, and `super_admin`
- Sanctum API endpoints for auth, account data, items, claims, email verification, and password reset
- Postman collection for API testing
- Responsive desktop and mobile layouts

## Local Setup

Install PHP and JavaScript dependencies:

```bash
composer install
npm install
copy .env.example .env
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

Open the local site:

```text
http://127.0.0.1:8000
```

For local email testing, use the log mailer:

```env
MAIL_MAILER=log
```

If queued email is enabled locally, keep a worker running in another terminal:

```bash
php artisan queue:work
```

## Admin Access

This project does not store a real administrator account or password in source code.

Create the first super administrator from your own terminal:

```bash
php artisan lostfound:create-super-admin
```

Admin URL:

```text
http://127.0.0.1:8000/admin/login
```

Public registration always creates normal user accounts. Administrator roles must be assigned by a super administrator inside the protected admin dashboard.

Role rules:

- `user`: can report items and submit claims.
- `admin`: can moderate reports, claims, and disputes.
- `super_admin`: can manage users, roles, and account status.
- The final active super administrator cannot be demoted or suspended.

## Email Setup

Campus Found uses Brevo SMTP for production email.

Email is used for:

- Registration verification codes
- Password reset codes
- Claim notifications
- Report and claim status notifications

Example production mail configuration:

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

The project includes API routes for:

- Authentication
- Account data
- Items
- Claims
- Email verification
- Password reset

Import the Postman collection:

```text
postman/LostFound_API.postman_collection.json
```

Before running requests, fill the collection variables locally:

- `base_url`
- `email`
- `password`
- `owner_token`
- `claimant_token`
- `verification_code`
- `reset_email`
- `reset_code`

The collection does not include real login credentials.


## Production Checklist

- Set `APP_ENV=production`.
- Set `APP_DEBUG=false`.
- Set the public HTTPS `APP_URL`.
- Generate a fresh production `APP_KEY`.
- Use a dedicated production database user and password.
- Configure Brevo SMTP credentials through environment variables only.
- Run `php artisan migrate --force`.
- Run `php artisan storage:link`.
- Run `php artisan optimize`.
- Configure Supervisor for the Laravel queue worker.
- Configure persistent storage or object storage for uploaded images.
- Serve the app through Nginx and PHP-FPM 8.4.
- Enable HTTPS with Certbot / Let's Encrypt.
- Keep DNS configured through Namecheap for `campusfound.me`.


## Project Status

Campus Found is deployed as a production Laravel website for `campusfound.me`. The codebase includes automated feature coverage for authentication, email verification, password reset, reports, claims, image uploads, account actions, API endpoints, admin moderation, user management, audit logs, and dispute resolution.
