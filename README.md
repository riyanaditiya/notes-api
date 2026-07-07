# Notes Management System API

REST API untuk Notes Management System Api dengan autentikasi JWT, dibangun menggunakan Laravel.

## Tech Stack

- **Framework**: Laravel 13
- **Database**: MySQL
- **Authentication**: JWT (tymon/jwt-auth)
- **Architecture**: Layered Architecture (Controller → Service → Repository)

## Fitur

### Wajib
- Register, Login, Logout dengan JWT Authentication
- CRUD Notes (Create, Read, Update, Delete)
- Pagination, Search by title, Filter by status
- User hanya dapat mengakses data miliknya sendiri
- Validasi input & password hashing
- Response format konsisten

### Nilai Tambah
- Layered Architecture (Repository Pattern + Service Layer)
- Soft Delete Notes (trash, restore, force delete)
- Favorite Notes (toggle & filter)
- Note Categories (1 note = 1 kategori)
- Note Tags (many-to-many, 1 note bisa banyak tag)
- Feature Testing (PHPUnit)
- Dokumentasi lengkap (README, Postman, OpenAPI/Swagger)

## Cara Menjalankan Aplikasi

### 1. Clone repository

```bash
git clone https://github.com/riyanaditiya/notes-api.git
cd notes-api
```

### 2. Install dependency

```bash
composer install
```

### 3. Setup environment

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

### 4. Generate JWT secret

```bash
php artisan jwt:secret
```

### 5. Migrasi database

```bash
php artisan migrate
```

### 6. Jalankan server

```bash
php artisan serve
```

Aplikasi berjalan di `http://localhost:8000`

### 7. Menjalankan Test

Testing menggunakan SQLite in-memory terpisah, tidak akan mengganggu database MySQL utama (sudah dikonfigurasi di `phpunit.xml`).

```bash
php artisan test
```

## Struktur Project

```
app/
  Http/
    Controllers/Api/
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
  Repositories/         -
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
  migrations/           -> Migration users, notes, categories, tags, note_tag
tests/
  Feature/
    NoteApiTest.php     
schema.sql              
postman_collection.json 
openapi.yaml            
```

## API Documentation

Format response konsisten di semua endpoint:

```json
{
  "status": "Success",
  "message": "Pesan terkait",
  "data": {}
}
```

### Authentication

| Method | Endpoint | Auth | Deskripsi |
|---|---|---|---|
| POST | `/api/register` | ❌ | Register user baru |
| POST | `/api/login` | ❌ | Login, mendapatkan JWT token |
| POST | `/api/logout` | ✅ | Logout, invalidate token |
| GET | `/api/me` | ✅ | Info user yang sedang login |

Semua endpoint yang butuh autentikasi wajib menyertakan header:
```
Authorization: Bearer <token>
```

### Notes

| Method | Endpoint | Auth | Deskripsi |
|---|---|---|---|
| GET | `/api/notes` | ✅ | List notes (pagination, search, filter) |
| POST | `/api/notes` | ✅ | Buat note baru |
| GET | `/api/notes/{id}` | ✅ | Detail note |
| PUT/PATCH | `/api/notes/{id}` | ✅ | Update note |
| DELETE | `/api/notes/{id}` | ✅ | Soft delete note |
| GET | `/api/notes-trashed` | ✅ | List note yang sudah dihapus |
| PATCH | `/api/notes/{id}/restore` | ✅ | Kembalikan note dari trash |
| DELETE | `/api/notes/{id}/force-delete` | ✅ | Hapus permanen |
| PATCH | `/api/notes/{id}/favorite` | ✅ | Toggle status favorite |

**Query Parameters untuk `GET /api/notes`**

| Param | Tipe | Deskripsi |
|---|---|---|
| `search` | string | Cari berdasarkan title |
| `status` | string | Filter: `active` atau `archived` |
| `is_favorite` | boolean | Filter: `true` atau `false` |
| `category_id` | integer | Filter berdasarkan kategori |
| `per_page` | integer | Jumlah data per halaman (default 10) |

**Contoh Request Create Note (dengan category & tags)**
```json
POST /api/notes
{
  "title": "Meeting dengan klien",
  "content": "Bahas kontrak baru",
  "category_id": 1,
  "tags": [1, 2]
}
```

**Contoh Response**
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

| Method | Endpoint | Auth | Deskripsi |
|---|---|---|---|
| GET | `/api/categories` | ✅ | List semua kategori milik user |
| POST | `/api/categories` | ✅ | Buat kategori baru |
| GET | `/api/categories/{id}` | ✅ | Detail kategori |
| PUT/PATCH | `/api/categories/{id}` | ✅ | Update kategori |
| DELETE | `/api/categories/{id}` | ✅ | Hapus kategori |

### Tags

| Method | Endpoint | Auth | Deskripsi |
|---|---|---|---|
| GET | `/api/tags` | ✅ | List semua tag milik user |
| POST | `/api/tags` | ✅ | Buat tag baru |
| DELETE | `/api/tags/{id}` | ✅ | Hapus tag |

Tags di-assign ke note lewat parameter `tags` (array of tag_id) saat `POST /api/notes` atau `PUT /api/notes/{id}` — menggunakan mekanisme sync (mengganti seluruh tag note dengan array yang dikirim).

## Testing

Feature test tersedia di `tests/Feature/NoteApiTest.php`, mencakup Create, Read, Update, Delete Note beserta validasi dan keamanan akses data.

```bash
php artisan test
```

## Postman Collection

Import file `postman_collection.json` ke Postman untuk mencoba semua endpoint secara langsung.

1. Buat Environment baru dengan variable `base_url` = `http://localhost:8000/api`
2. Jalankan request **Login**, token akan otomatis tersimpan ke variable `{{token}}`
3. Semua request lain otomatis menggunakan token tersebut

## Swagger / OpenAPI

Spesifikasi API tersedia di `openapi.yaml`. Bisa dibuka menggunakan [Swagger Editor](https://editor.swagger.io) untuk melihat dokumentasi interaktif.

## Database Schema

Lihat file `schema.sql` untuk struktur lengkap tabel database (`users`, `notes`, `categories`, `tags`, `note_tag`).

## Deployment

**URL Production**: `https://notes-api-production.up.railway.app`

Deploy menggunakan Railway dengan MySQL sebagai database.

## Author

Nama Kamu — [GitHub](https://github.com/username-kamu)