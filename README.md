# Organisasi

Aplikasi manajemen organisasi berbasis Laravel 12, Inertia, Vue 3, Tailwind CSS, dan Vite.

## Prasyarat

Pastikan perangkat sudah memiliki:

- PHP 8.2 atau lebih baru
- Composer
- Node.js dan npm
- MySQL atau MariaDB
- Git

Pastikan ekstensi PHP untuk Laravel aktif, terutama `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `xml`, dan `ctype`.

## Setup Setelah Clone

Clone repository, lalu masuk ke folder project:

```bash
git clone <url-repository>
cd organisasi
```

Install dependency backend:

```bash
composer install
```

Install dependency frontend:

```bash
npm install
```

Salin file environment:

```bash
cp .env.example .env
```

Untuk Windows PowerShell, gunakan:

```powershell
Copy-Item .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

## Konfigurasi Database

Buat database baru di MySQL/MariaDB, misalnya:

```sql
CREATE DATABASE organisasi;
```

Lalu sesuaikan konfigurasi database di file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=organisasi
DB_USERNAME=root
DB_PASSWORD=
```

Gunakan username dan password sesuai konfigurasi database lokal masing-masing.

## Migrasi dan Seeder

Jalankan migrasi dan isi data awal:

```bash
php artisan migrate --seed
```

Seeder akan membuat data role, user awal, member, inventory, kas, dan pembayaran.

Akun login default:

```text
Super Admin : superadmin@test.com / password123
Admin       : admin@test.com / password123
Treasurer   : treasurer@test.com / password123
Warehouse   : warehouse@test.com / password123
User        : user@test.com / password123
```

## Menjalankan Project Secara Lokal

Cara paling praktis adalah menjalankan backend, queue, dan Vite sekaligus:

```bash
composer run dev
```

Aplikasi dapat dibuka di:

```text
http://127.0.0.1:8000
```

Jika ingin menjalankan service secara terpisah, buka beberapa terminal:

```bash
php artisan serve
```

```bash
php artisan queue:listen --tries=1
```

```bash
npm run dev
```

## Build Frontend

Untuk membuat asset production:

```bash
npm run build
```

Untuk build dengan SSR:

```bash
npm run build:ssr
```

## Menjalankan Test

Jalankan test Laravel:

```bash
composer run test
```

Atau langsung:

```bash
php artisan test
```

## Perintah yang Sering Dipakai

Membersihkan cache konfigurasi dan route:

```bash
php artisan optimize:clear
```

Membuat ulang database dari awal beserta seed data:

```bash
php artisan migrate:fresh --seed
```

Melihat daftar route:

```bash
php artisan route:list
```

## Dokumentasi Tambahan

- `QUICK_START.md` berisi ringkasan penggunaan awal dan contoh testing API.
- `API_DOCUMENTATION.md` berisi dokumentasi endpoint API.
- `DATABASE_SCHEMA.md` berisi dokumentasi struktur database.
- `IMPLEMENTATION_GUIDE.md` berisi panduan implementasi fitur.

## Troubleshooting

Jika halaman tidak memuat asset frontend, pastikan `npm run dev` sedang berjalan atau jalankan `composer run dev`.

Jika muncul error koneksi database, cek kembali nilai `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di `.env`, lalu jalankan:

```bash
php artisan config:clear
```

Jika tabel session, cache, atau queue belum tersedia, jalankan ulang:

```bash
php artisan migrate
```
