# Deploy Izin Kerja Khusus

Ada dua pilihan hosting. **VPS** paling sederhana dan semua fitur berjalan apa adanya.
**Vercel** tidak punya disk permanen, jadi database dan dokumen unggahan harus di **Supabase**.

| | VPS | Vercel |
| --- | --- | --- |
| PHP | 8.4-FPM di server | runtime `vercel-php` |
| Database | SQLite, PostgreSQL, atau Supabase | Supabase (PostgreSQL) |
| Dokumen unggahan | disk server (`DOKUMEN_DISK=local`) | Supabase Storage (`DOKUMEN_DISK=s3`) |
| Sesi & cache | file / database | database |

---

## A. VPS (Ubuntu 24.04)

### 1. Pasang paket

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y nginx git unzip php8.4-fpm php8.4-cli php8.4-sqlite3 php8.4-pgsql \
  php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip php8.4-gd php8.4-intl
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo bash - && sudo apt install -y nodejs
```

### 2. Ambil kode dan pasang

```bash
sudo mkdir -p /var/www && cd /var/www
sudo git clone https://github.com/muhammadseptiadi079/izin-kerja-khusus.git
sudo chown -R $USER:www-data izin-kerja-khusus && cd izin-kerja-khusus

composer install --no-dev --optimize-autoloader
cp .env.example .env && php artisan key:generate
```

Ubah `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://ikk.contoh.id`, dan pilih database:

- **SQLite** (paling mudah): biarkan `DB_CONNECTION=sqlite`, lalu `touch database/database.sqlite`.
- **PostgreSQL / Supabase**: isi `DB_CONNECTION=pgsql` dan `DB_URL=...`.

```bash
php artisan migrate --force
php artisan db:seed --force      # akun contoh; ganti kata sandinya setelah masuk
npm ci && npm run build
php artisan optimize
sudo chown -R www-data:www-data storage bootstrap/cache database
```

### 3. Nginx dan HTTPS

```bash
sudo cp deploy/nginx.conf /etc/nginx/sites-available/izin-kerja-khusus   # ubah server_name
sudo ln -s /etc/nginx/sites-available/izin-kerja-khusus /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo apt install -y certbot python3-certbot-nginx && sudo certbot --nginx -d ikk.contoh.id
```

Naikkan juga batas unggah PHP di `/etc/php/8.4/fpm/php.ini`: `upload_max_filesize = 10M`, `post_max_size = 32M`, lalu `sudo systemctl restart php8.4-fpm`.

### 4. Memperbarui aplikasi

```bash
bash deploy/deploy.sh
```

Log bisa dipantau langsung dengan `php artisan pail`.

---

## B. Vercel + Supabase

### 1. Supabase

1. Buat proyek di https://supabase.com (region Singapore paling dekat).
2. **Database**: Project Settings → Database → Connection string → **Transaction pooler** (port 6543). Salin URI-nya.
3. **Storage**: buat bucket **privat** bernama `dokumen-izin`. Lalu Project Settings → Storage → **S3 Connection**: aktifkan dan buat access key.
4. Jalankan migrasi dari komputer sendiri, sekali saja:

```bash
DB_CONNECTION=pgsql DB_URL="postgresql://...:6543/postgres?sslmode=require" php artisan migrate --force
DB_CONNECTION=pgsql DB_URL="postgresql://...:6543/postgres?sslmode=require" php artisan db:seed --force
```

### 2. Vercel

1. Import repo `izin-kerja-khusus` di https://vercel.com/new. Framework: **Other**. Pengaturan build sudah ada di `vercel.json`.
2. Isi **Environment Variables**:

| Nama | Nilai |
| --- | --- |
| `APP_KEY` | hasil `php artisan key:generate --show` |
| `APP_URL` | `https://nama-proyek.vercel.app` |
| `DB_URL` | URI Supabase dari langkah 1.2 |
| `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY` | dari S3 Connection Supabase |
| `AWS_DEFAULT_REGION` | region proyek Supabase, mis. `ap-southeast-1` |
| `AWS_BUCKET` | `dokumen-izin` |
| `AWS_ENDPOINT` | `https://<ref>.supabase.co/storage/v1/s3` |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `true` |
| `GEMINI_API_KEY` | opsional, untuk tombol Saran AI |

Variabel lain (`DB_CONNECTION=pgsql`, `DOKUMEN_DISK=s3`, sesi/cache di database, storage di `/tmp`) sudah diatur di `vercel.json`.

3. Deploy. Setiap push ke `main` otomatis di-deploy ulang.

**Catatan runtime:** `vercel.json` memakai `vercel-php@0.8.0` untuk PHP 8.4. Bila Vercel menolak versi itu, ganti ke `vercel-php@0.9.0` (PHP 8.5; Laravel 13 mendukungnya).

---

## Asisten AI (Google Gemini)

Buat kunci di https://aistudio.google.com/apikey lalu isi `GEMINI_API_KEY`. Tanpa kunci, tombol "Saran AI" disembunyikan dan aplikasi tetap berjalan normal.
AI hanya menyarankan bahaya, pengendalian tambahan, dan APD; pengendalian wajib tetap dicentang pemohon setelah dipastikan di lapangan.
