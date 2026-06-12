# Lost & Found (RUPP)

Laravel + Blade + MySQL + Sanctum API. React version archived in `legacy-react/`.

## MySQL

**Automatic setup (recommended):**

```bash
php artisan lostfound:setup-mysql
php artisan lostfound:check-db
```

Uses database `contactappdb` and lab user `laravel` / `Rupp2357.!` (same as Labs 7–12).

**Optional — use Workbench database `LostFoundDB` with password `2103#Davit`:**

1. Run `database/grant-laravel.sql` in Workbench as **root**.
2. Update `.env`: `DB_DATABASE=LostFoundDB` and `DB_PASSWORD="2103#Davit"` (quotes required).
3. Run `php artisan migrate:fresh --seed`.

## Web app

```bash
php artisan serve
```

- Home: `http://127.0.0.1:8000`
- Admin UI: `/admin/login` — password `RUPPSTAFF`

## Postman API

Import: `postman/LostFound_API.postman_collection.json`

| Variable | Default |
|----------|---------|
| `base_url` | `http://127.0.0.1:8000/api` |
| `email` | `admin@rupp.edu.kh` |
| `password` | `password` |

**Flow:** Run **Login** first (saves `token`) → **List Items** / **Report Item** → **Delete Item** needs Bearer token.

### API routes

| Method | URL | Auth |
|--------|-----|------|
| POST | `/api/login` | — |
| POST | `/api/register` | — |
| GET | `/api/items` | — |
| POST | `/api/items` | — |
| GET | `/api/items/{id}` | — |
| DELETE | `/api/items/{id}` | Sanctum Bearer |
