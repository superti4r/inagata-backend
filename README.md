# RESTful Blog API (Laravel 12 + Sanctum)

RESTful Blog API production-ready dengan pendekatan Clean Architecture, autentikasi Sanctum (tanpa JWT), dan format response JSON yang konsisten.

## Fitur Utama

- Controller tipis, business logic di Service.
- Query database hanya melalui Repository.
- DTO immutable (`readonly`) untuk data antar layer.
- Validasi request menggunakan FormRequest.
- Response standar: `success`, `message`, `data/errors`.
- Authorization berbasis role via Policy (`admin`, `user`).
- Dokumentasi API dengan Swagger (L5 Swagger).

## Stack

- PHP 8.2+
- Laravel 12
- Laravel Sanctum
- L5 Swagger (`darkaonline/l5-swagger`)
- PHPUnit + Laravel Pint

## Prasyarat

- PHP 8.2+
- Composer
- MySQL
- Node.js + npm (opsional, hanya jika pakai `composer run dev`)

## Setup Project

1) Install dependency:

```bash
composer install
```

2) Siapkan environment:

```bash
cp .env.example .env
php artisan key:generate
```

3) Atur koneksi database di `.env` (default project memakai MySQL):

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inagata_restfulapi_technical_test
DB_USERNAME=root
DB_PASSWORD=
```

4) Migrasi + seed data awal:

```bash
php artisan migrate --seed
```

## Akun Seed Default

Setelah `php artisan migrate --seed`, tersedia akun berikut (password: `password`):

- Admin: `admin@example.com`
- User: `test@example.com`

## Menjalankan Aplikasi

### Mode API only (paling sederhana)

```bash
php artisan serve
```

### Mode development lengkap (server + queue + log + vite)

```bash
composer run dev
```

## Dokumentasi API (Swagger)

- URL UI: `/api/documentation`
- Regenerate manual bila perlu:

```bash
php artisan l5-swagger:generate
```

Catatan: file hasil generate Swagger disimpan di `storage/api-docs` dan tidak ditrack ke repository.

## Alur Auth Singkat

1) Login via `POST /api/login`.
2) Ambil token dari response.
3) Kirim token di header:

```http
Authorization: Bearer <token>
```

Untuk API token-based ini, request Swagger/Postman **tidak membutuhkan CSRF token**. Header `X-CSRF-TOKEN` boleh dikosongkan atau tidak dikirim.

Endpoint yang butuh login + role admin:

- `POST /api/categories`
- `POST /api/articles`
- `PUT /api/articles/{id}`
- `DELETE /api/articles/{id}`

## Ringkasan Endpoint

### Auth

- `POST /api/register`
- `POST /api/login`
- `POST /api/logout` (auth:sanctum)

### Categories

- `GET /api/categories`
- `POST /api/categories` (admin only, auth:sanctum)

### Articles

- `GET /api/articles?page=1&limit=10`
- `GET /api/articles/{id}`
- `GET /api/articles/search?category_id=&keyword=&page=1&limit=10`
- `POST /api/articles` (admin only, auth:sanctum)
- `PUT /api/articles/{id}` (admin only, auth:sanctum)
- `DELETE /api/articles/{id}` (admin only, auth:sanctum)

## Format Response

### Success

```json
{
  "success": true,
  "message": "string",
  "data": {}
}
```

### Error

```json
{
  "success": false,
  "message": "string",
  "errors": {}
}
```

## Quality Check

Jalankan test:

```bash
composer test
```

Cek style:

```bash
vendor/bin/pint --test
```

Auto format style:

```bash
vendor/bin/pint
```

## Utility

Laravel Tinker tersedia:

```bash
php artisan tinker
```

## Catatan

- Semua endpoint menggunakan prefix `/api` (tanpa `/v1`).
- Untuk production, set `APP_ENV=production`, `APP_DEBUG=false`, dan sesuaikan `L5_SWAGGER_GENERATE_ALWAYS=false`.
