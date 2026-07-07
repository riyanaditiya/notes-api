# Notes Management System API

REST API untuk Notes Management System dengan autentikasi JWT, dibangun menggunakan Laravel.

## Tech Stack

- Framework: Laravel 13
- Database: MySQL
- Authentication: JWT (tymon/jwt-auth)
- Architecture: Layered Architecture (Controller, Service, Repository)

## Fitur

Fitur wajib:

- Register, Login, Logout dengan JWT Authentication
- CRUD Notes (Create, Read, Update, Delete)
- Pagination, Search by title, Filter by status
- User hanya dapat mengakses data miliknya sendiri
- Validasi input dan password hashing
- Response format konsisten

Nilai tambah:

- Layered Architecture (Repository Pattern dan Service Layer)
- Soft Delete Notes (trash, restore, force delete)
- Favorite Notes (toggle dan filter)
- Note Categories (satu note memiliki satu kategori)
- Note Tags (relasi many-to-many, satu note dapat memiliki banyak tag)
- Feature Testing (PHPUnit)
- Dokumentasi lengkap (README, Postman Collection, OpenAPI/Swagger)

## Cara Menjalankan Aplikasi

1. Clone repository

```bash
git clone https://github.com/username-kamu/notes-api.git
cd notes-api
```

2. Install dependency

```bash
composer install
```

3. Setup environment

```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi database di file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=notes_api
DB_USERNAME=root
DB_PASSWORD=
```

4. Generate JWT secret

```bash
php artisan jwt:secret
```

5. Migrasi database

```bash
php artisan migrate
```

6. Jalankan server

```bash
php artisan serve
```

Aplikasi berjalan di `http://localhost:8000`.

7. Menjalankan test

Testing menggunakan SQLite in-memory yang terpisah dari database utama, sudah dikonfigurasi di `phpunit.xml`.

```bash
php artisan test
```

## Struktur Project

```
app/
  Http/
    Controllers/Api/
      SwaggerInfo.php
      AuthController.php
      NoteController.php
      CategoryController.php
      TagController.php
    Requests/
      LoginRequest.php
      RegisterRequest.php
      StoreCategoryRequest.php
      StoreNoteRequest.php
      StoreTagRequest.php
      UpdateNoteRequest.php
  Models/
    User.php
    Note.php
    Category.php
    Tag.php
  Repositories/
    CategoryRepository.php
    NoteRepository.php
    TagRepository.php
  Services/
    CategoryService.php
    NoteService.php
    TagService.php
  Traits/
    ApiResponse.php
routes/
  api.php
database/
  migrations/
tests/
  Feature/
    NoteApiTest.php
NOTES API.postman_collection.json
schema.sql
```

## API Documentation

Base URL (local): `http://localhost:8000/api`
Base URL (production): `https://notes-api-production-4a37.up.railway.app/`

Semua response menggunakan format berikut:

```json
{
  "status": "Success",
  "message": "Pesan terkait",
  "data": {}
}
```

### Authentication

| Method | Endpoint | Autentikasi | Deskripsi |
|---|---|---|---|
| POST | /register | Tidak | Register user baru |
| POST | /login | Tidak | Login, mendapatkan JWT token |
| POST | /logout | Ya | Logout, invalidate token |
| GET | /me | Ya | Info user yang sedang login |

Endpoint yang membutuhkan autentikasi wajib menyertakan header berikut:

```
Authorization: Bearer <token>
```

### Notes

| Method | Endpoint | Autentikasi | Deskripsi |
|---|---|---|---|
| GET | /notes | Ya | List notes dengan pagination, search, dan filter |
| POST | /notes | Ya | Membuat note baru |
| GET | /notes/{id} | Ya | Melihat detail note |
| PUT/PATCH | /notes/{id} | Ya | Memperbarui note |
| DELETE | /notes/{id} | Ya | Menghapus note (soft delete) |
| GET | /notes-trashed | Ya | List note yang sudah dihapus |
| PATCH | /notes/{id}/restore | Ya | Mengembalikan note dari trash |
| DELETE | /notes/{id}/force-delete | Ya | Menghapus note secara permanen |
| PATCH | /notes/{id}/favorite | Ya | Mengubah status favorite pada note |

Query parameter untuk `GET /notes`:

| Parameter | Tipe | Deskripsi |
|---|---|---|
| search | string | Mencari note berdasarkan title |
| status | string | Filter berdasarkan status: active atau archived |
| is_favorite | boolean | Filter berdasarkan status favorite |
| category_id | integer | Filter berdasarkan kategori |
| per_page | integer | Jumlah data per halaman, default 10 |

Contoh request membuat note dengan kategori dan tag:

```json
POST /notes
{
  "title": "Meeting dengan klien",
  "content": "Bahas kontrak baru",
  "category_id": 1,
  "tags": [1, 2]
}
```

Contoh response:

```json
{
  "status": "Success",
  "message": "Note created successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "category_id": 1,
    "title": "Meeting dengan klien",
    "content": "Bahas kontrak baru",
    "status": "active",
    "is_favorite": false,
    "category": { "id": 1, "name": "Kerja" },
    "tags": [
      { "id": 1, "name": "urgent" },
      { "id": 2, "name": "meeting" }
    ],
    "created_at": "2026-07-06T10:00:00.000000Z",
    "updated_at": "2026-07-06T10:00:00.000000Z"
  }
}
```

### Categories

| Method | Endpoint | Autentikasi | Deskripsi |
|---|---|---|---|
| GET | /categories | Ya | List semua kategori milik user |
| POST | /categories | Ya | Membuat kategori baru |
| GET | /categories/{id} | Ya | Melihat detail kategori |
| PUT/PATCH | /categories/{id} | Ya | Memperbarui kategori |
| DELETE | /categories/{id} | Ya | Menghapus kategori |

### Tags

| Method | Endpoint | Autentikasi | Deskripsi |
|---|---|---|---|
| GET | /tags | Ya | List semua tag milik user |
| POST | /tags | Ya | Membuat tag baru |
| DELETE | /tags/{id} | Ya | Menghapus tag |

Tag di-assign ke note melalui parameter `tags` (array berisi tag_id) saat membuat atau memperbarui note. Mekanisme ini menggunakan sync, artinya seluruh tag pada note akan digantikan dengan array yang dikirim.

## Testing

Feature test tersedia di `tests/Feature/NoteApiTest.php`, mencakup pengujian Create, Read, Update, Delete Note, validasi input, dan keamanan akses antar user.

```bash
php artisan test
```

## Postman Collection

File `NOTES API.postman_collection.json` dapat langsung diimport ke Postman.

1. Buat Environment baru dengan variable `base_url` bernilai `http://localhost:8000/api` untuk local, dan `https://notes-api-production-4a37.up.railway.app/` untuk production, kemudian buat variable `token` dan kosongkan nilainya 
2. Jalankan request Login, token akan otomatis tersimpan ke variable `token`
3. Seluruh request lain akan otomatis menggunakan token tersebut

## Swagger / OpenAPI

Dokumentasi API dibuat menggunakan PHP Attributes (package darkaonline/l5-swagger) yang ditulis langsung di setiap Controller. Dokumentasi di-generate otomatis dari kode tersebut.

Generate dokumentasi:

```bash
php artisan l5-swagger:generate
```

Setelah server berjalan, dokumentasi interaktif dapat diakses di:

```
http://localhost:8000/api/documentation
```

## Database Schema

File `schema.sql` berisi struktur lengkap seluruh tabel: users, notes, categories, tags, dan note_tag.

## Deployment

URL Production: https://notes-api-production-4a37.up.railway.app/

Aplikasi di-deploy menggunakan Railway dengan MySQL sebagai database.
