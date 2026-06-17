# Campus Found

Campus Found is a responsive lost-and-found management system for campus communities. Students and staff can browse reports, submit lost or found items, send ownership claims, and review claim activity. Administrators can moderate reports and claims from a protected dashboard.

## Technology Stack

- Frontend: Blade templates, HTML, CSS, Bootstrap assets, Vite
- Backend: Laravel, PHP
- Database: MySQL
- Authentication: Laravel session authentication and Sanctum API tokens
- Storage: Laravel public disk with optimized WebP uploads
- API testing: Postman collection

## Main Features

- Public home page and community board
- Search, status filters, category filters, date filter, and sorting
- User registration, login, logout, and account dashboard
- User dashboard API for reports, claims, and account statistics
- Email verification for new user accounts
- Password reset by email code
- User-owned report creation, editing, and deletion
- Lost/found report image upload and optimization
- Claim submission with ownership verification answers
- Owner review for pending claims
- Recently claimed section
- Admin dashboard for reports, claims, users, moderation, and audit activity
- Sanctum API endpoints with Postman examples
- Responsive desktop and mobile layouts

## Local Setup

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
```

Update `.env` with your own local database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=campus_found
DB_USERNAME=your_local_user
DB_PASSWORD=your_local_password
```

Then run:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Open the website at:

```text
http://127.0.0.1:8000
```

For local email testing, use the log mailer:

```env
MAIL_MAILER=log
```

If you use queued email locally, keep a second terminal running:

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

Do not commit `.env`, real admin passwords, database passwords, API keys, exported database files, or local SQLite databases.

Public registration always creates normal user accounts. Administrator roles must be assigned by a super administrator inside the protected admin dashboard.

Role rules:

- `user`: can report items and submit claims.
- `admin`: can moderate reports, claims, and disputes.
- `super_admin`: can manage users, roles, and account status.
- The final active super administrator cannot be demoted or suspended.

## Testing

```bash
composer test
npm run build
```

Optional quality checks:

```bash
composer audit --locked
vendor/bin/pint --test
```

Current verified result:

```text
31 tests, 216 assertions
```

## Email Setup

Campus Found supports email verification, password reset codes, and claim notifications. This project now uses standard SMTP so you can connect it to MailerSend and keep Resend for another project.

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailersend.net
MAIL_PORT=587
MAIL_USERNAME=your_mailersend_smtp_username
MAIL_PASSWORD=your_mailersend_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@campusfound.me"
MAIL_FROM_NAME="${APP_NAME}"
QUEUE_CONNECTION=database
```

In MailerSend, verify the sending domain `campusfound.me`, then generate an SMTP user for that domain and place the username and password in your `.env`.

Because emails are queued, production needs a running worker:

```bash
php artisan queue:work --tries=3
```

## Postman

Import:

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

## Security Notes

- `.env` is ignored and must stay local.
- Generated files, local databases, SQL dumps, storage uploads, and keys are ignored.
- The seeded data is sample-only and does not create an administrator account.
- Use unique passwords for local, staging, and production databases.
- Rotate any credentials that were previously committed or shared.
- If credentials were pushed in Git history, rewrite or recreate the repository history before public sharing.

## Production Checklist

- Set `APP_ENV=production`.
- Set `APP_DEBUG=false`.
- Set the public HTTPS `APP_URL`.
- Generate a new production `APP_KEY`.
- Use a dedicated production database user and password.
- Configure mail credentials through environment variables only.
- Run `php artisan migrate --force`.
- Run `php artisan storage:link`.
- Run `php artisan optimize`.
- Configure a queue worker for verification, password reset, and claim notification emails.
- Configure persistent storage or object storage for uploaded images.
- Enable HTTPS and rotate any exposed credentials before deployment.
