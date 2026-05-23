# Aplikasi Pembelajaran Koas

Aplikasi Pembelajaran Koas adalah sistem informasi rekam medis berbasis Laravel untuk membantu pengelolaan data pasien, riwayat medis, dan laporan internal di lingkungan rumah sakit.

## Deskripsi Singkat

Project ini digunakan untuk:

- mendata pasien
- mencatat riwayat medis dan diagnosa
- mengelola lampiran rekam medis secara private
- melihat history aktivitas penting
- menampilkan grafik penyakit untuk kebutuhan monitoring
- mengatur akun petugas melalui admin

## Role Pengguna

Role yang tersedia di aplikasi ini:

- `admin`
- `doctor`
- `koas`
- `management`

## Fitur Utama

- Login dan logout user
- Manajemen data pasien
- Tambah, ubah, hapus riwayat medis
- Upload dan download lampiran rekam medis melalui controller protected
- Penyimpanan lampiran rekam medis secara private
- History aktivitas
- Grafik penyakit untuk admin dan management
- Manajemen akun user oleh admin

## Kebutuhan Sistem

- PHP 8.2+
- Composer
- Node.js dan npm
- Database MySQL atau database lain yang didukung Laravel

## Instalasi

1. Clone repository ini.
2. Install dependency PHP:

   ```bash
   composer install
   ```

3. Install dependency frontend:

   ```bash
   npm install
   ```

4. Salin file environment:

   ```bash
   cp .env.example .env
   ```

5. Generate application key:

   ```bash
   php artisan key:generate
   ```

6. Atur konfigurasi database di file `.env`.
7. Jalankan migrasi:

   ```bash
   php artisan migrate
   ```

8. Jalankan seeder jika dibutuhkan:

   ```bash
   php artisan db:seed
   ```

## Menjalankan Aplikasi

### Mode development

1. Jalankan server Laravel:

   ```bash
   php artisan serve
   ```

2. Jalankan watcher frontend jika diperlukan:

   ```bash
   npm run dev
   ```

### Build produksi

```bash
npm run build
```

## Catatan Penggunaan

- Akses fitur dashboard mengikuti role user.
- Lampiran rekam medis tidak dipublikasikan lewat `/storage`; file hanya bisa diunduh lewat route controller yang diproteksi.
- Jika seeding admin default dipakai, pastikan kredensialnya diganti sesuai kebutuhan lingkungan sebelum dipakai di produksi.

## Testing

Jalankan test suite dengan:

```bash
php artisan test
```

