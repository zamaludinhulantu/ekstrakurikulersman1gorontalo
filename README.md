# Sistem Informasi Ekstrakurikuler SMAN 1 Gorontalo

Aplikasi web berbasis Laravel untuk mengelola kegiatan ekstrakurikuler sekolah secara terpusat. Sistem ini mencakup publikasi daftar ekskul, autentikasi multi-role, pendaftaran siswa, verifikasi admin, pengelolaan jadwal, presensi, penilaian/prestasi, dan laporan.

## Ringkasan Fitur

- Landing page publik untuk menampilkan ekskul aktif.
- Login dan logout untuk role `admin`, `coach`, `student`, dan `principal`.
- Dashboard berbeda sesuai role pengguna.
- Manajemen profil pengguna.
- CRUD data master:
  - pengguna
  - siswa
  - pembina
  - ekstrakurikuler
- Pendaftaran ekskul oleh siswa dan verifikasi status oleh admin.
- Pengelolaan jadwal kegiatan oleh pembina.
- Presensi peserta kegiatan oleh pembina.
- Penilaian dan pencatatan prestasi siswa oleh pembina.
- Laporan untuk admin, pembina, dan kepala sekolah.
- Filter laporan berdasarkan ekskul, pembina, dan periode tanggal.
- Export laporan ke format CSV.

## Stack

- PHP `^8.2`
- Laravel `^12`
- MySQL atau SQLite
- Blade
- Vite
- Tailwind CSS 4
- Bootstrap 5
- Bootstrap Icons

## Struktur Akses

- `admin`
  - kelola data master
  - verifikasi pendaftaran
  - akses laporan peserta, jadwal, presensi, penilaian, dan ringkasan
- `student`
  - lihat daftar/detail ekskul
  - daftar ekskul
  - lihat status pendaftaran
  - lihat jadwal, presensi, dan penilaian pribadi
- `coach`
  - lihat ekskul yang dibina
  - kelola jadwal
  - kelola presensi peserta
  - kelola penilaian dan prestasi
  - buat dan export laporan
- `principal`
  - lihat dashboard ringkasan
  - lihat dan export laporan

## Model Data Utama

- `users` 1-1 `students`
- `users` 1-1 `coaches`
- `coaches` 1-M `extracurriculars`
- `extracurriculars` 1-M `registrations`
- `students` 1-M `registrations`
- `extracurriculars` 1-M `schedules`
- `schedules` 1-M `attendances`
- `students` 1-M `attendances`
- `students` 1-M `assessments`
- `extracurriculars` 1-M `assessments`
- `users` 1-M `reports`

## Instalasi

1. Masuk ke folder project.
2. Install dependency backend:
   ```bash
   composer install
   ```
3. Install dependency frontend:
   ```bash
   npm install
   ```
4. Buat file environment:
   ```powershell
   Copy-Item .env.example .env
   ```
5. Generate application key:
   ```bash
   php artisan key:generate
   ```
6. Atur koneksi database pada `.env`.

   Contoh MySQL:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sistem_ekstrakurikuler
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Contoh SQLite:
   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   ```
7. Jalankan migrasi dan seeder:
   ```bash
   php artisan migrate --seed
   ```
8. Jalankan asset builder untuk development:
   ```bash
   npm run dev
   ```
9. Jalankan server aplikasi:
   ```bash
   php artisan serve
   ```
10. Buka aplikasi di `http://127.0.0.1:8000`.

## Perintah Cepat

Setup awal cepat:

```bash
composer run setup
```

Catatan: perintah ini menjalankan migrasi tanpa seeder demo. Jika Anda membutuhkan akun demo, jalankan:

```bash
php artisan migrate:fresh --seed
```

Menjalankan mode development penuh:

```bash
composer run dev
```

Menjalankan test:

```bash
composer test
```

## Akun Demo

Semua akun demo menggunakan password:

```text
11111111
```

- Admin: `admin@gmail.com`
- Kepala sekolah: `kepsek@gmail.com`
- Pembina Pramuka: `pembina1@gmail.com`
- Pembina Paskibra: `pembina2@gmail.com`
- Siswa 1: `siswa1@gmail.com`
- Siswa 2: `siswa2@gmail.com`
- Siswa 3: `siswa3@gmail.com`

## Modul Route

Route utama yang tersedia:

- `/` landing page publik
- `/login` autentikasi
- `/dashboard` dashboard sesuai role
- `/admin/*` modul admin
- `/student/*` modul siswa
- `/coach/*` modul pembina
- `/principal/*` modul kepala sekolah

## Catatan Implementasi

- Middleware role digunakan untuk membatasi akses per peran.
- Landing page publik tetap dapat menampilkan data dummy bila data ekskul aktif belum tersedia.
- Export laporan saat ini menggunakan format CSV.

## Verifikasi Dasar

```bash
php artisan route:list
php artisan migrate:fresh --seed
composer test
```
