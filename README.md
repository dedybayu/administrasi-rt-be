# Administrasi RT - Backend API

Sistem manajemen administrasi RT berbasis web yang dibangun menggunakan Laravel 11. Repositori ini berisi logika bisnis, manajemen database, dan API endpoint yang digunakan oleh frontend.

### 🚀 Live Demo
Aplikasi ini sudah dideploy dan dapat diakses melalui:
**[https://adm-rt.dbsnetwork.my.id/](https://adm-rt.dbsnetwork.my.id/)**

Backend API: **[https://api-rt.dbsnetwork.my.id/api](https://api-rt.dbsnetwork.my.id/api)**

## Teknologi Utama
- **Framework**: Laravel 11
- **Autentikasi**: JWT (JSON Web Token) via `php-open-source-saver/jwt-auth`
- **Database**: MySQL
- **Bahasa**: PHP 8.2+

---

## Panduan Instalasi (Development)

Ikuti langkah-langkah berikut untuk menjalankan project di lingkungan lokal:

### 1. Prasyarat
Pastikan Anda sudah menginstal:
- PHP >= 8.2
- Composer
- MySQL/MariaDB

### 2. Kloning Repositori
```bash
git clone https://github.com/dedybayu/administrasi-rt-be.git
cd administrasi-rt-be
```

### 3. Instal Dependensi
```bash
composer install
```

### 4. Konfigurasi Environment
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```

Buka file `.env` dan sesuaikan konfigurasi database Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=administrasi_rt
DB_USERNAME=root
DB_PASSWORD=
DB_COLLATION=utf8mb4_0900_ai_ci // Defaultnya tidak ada, isi sesuaikan dengan database Anda ketika ada error saat migrasi.
```

**Penting**
isi DB_COLLATION sesuaikan dengan Collation pada database MySQL Anda defaultnya adalah `utf8mb4_0900_ai_ci`, jika ada error saat migrasi cek collation database lalu sesuaikan collation database Anda di .env. 

### 5. Generate Application Key & JWT Secret
```bash
php artisan key:generate
php artisan jwt:secret
```

### 6. Migrasi & Seed Data
Jalankan migrasi untuk membuat tabel dan seeder untuk data awal (termasuk akun default RT):
```bash
php artisan migrate --seed
```

### 7. Link Storage
Agar file (seperti foto KTP) dapat diakses secara publik:
```bash
php artisan storage:link
```

### 8. Jalankan Server
```bash
php artisan serve
```
API sekarang dapat diakses di `http://127.0.0.1:8000`.

---

## Panduan Instalasi (Production)

Untuk deployment di server production, hampir sama dengan development tapi sedikit berbeda `pastikan mengikuti langkah optimasi berikut`: 

### 1. Instalasi Dependensi Tanpa Dev-tools
```bash
composer install --optimize-autoloader --no-dev
```

### 2. Konfigurasi Environment Keamanan
Pastikan file `.env` diatur untuk production:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com
```

### 3. Caching untuk Performa
Jalankan perintah berikut setiap kali ada perubahan konfigurasi atau rute:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Folder Permissions
Pastikan folder `storage` dan `bootstrap/cache` dapat ditulis oleh web server:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 5. Web Server Configuration
Pastikan root directory web server diarahkan ke folder `/public` dari project ini.

#### Contoh Konfigurasi Apache2 (.conf)
```apache
<VirtualHost *:80>
    ServerName domain-anda.com
    DocumentRoot /var/www/administrasi-rt-be/public

    <Directory /var/www/administrasi-rt-be/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```
**Note:**
* Pastikan modul `mod_rewrite` sudah diaktifkan (`a2enmod rewrite`).*
* Ubah DocumentRoot sesuai dengan lokasi project Anda.*
* Ubah ServerName sesuai dengan domain Anda.*

---

## Akun Default (Setelah Seeding)
- **Role RT**: `ketuart` / `password123`
- **Role Warga**: (Username dapat dilihat di tabel `m_users` setelah seeding, password defaultnya adalah `warga123`)

---

## Dokumentasi API

Seluruh request API (kecuali login & refresh token) wajib menyertakan header:
`Authorization: Bearer <your_jwt_token>`

### 1. Autentikasi
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `POST` | `/api/login` | Masuk ke sistem dan mendapatkan token JWT |
| `POST` | `/api/refresh-token` | Memperbarui token JWT yang sudah expired |
| `POST` | `/api/update-profile` | Memperbarui username atau password pengguna |

### 2. Dashboard & Laporan (Khusus RT)
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET` | `/api/dashboard/report-cashflow` | Laporan arus kas tahunan (Bulanan) |
| `GET` | `/api/dashboard/report-cashflow-detailed` | Rincian transaksi bulanan (Pemasukan & Pengeluaran) |
| `GET` | `/api/dashboard/report-cashflow-daily` | Grafik transaksi harian dalam satu bulan |

### 3. Manajemen Data (Khusus RT)
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET/POST` | `/api/occupants` | List & Tambah data Warga (dengan upload KTP) |
| `GET/PUT/DELETE` | `/api/occupants/{id}` | Detail, Update, & Hapus data Warga |
| `GET/POST` | `/api/houses` | List & Tambah data Rumah |
| `GET/POST` | `/api/house-occupants` | Manajemen penghuni rumah (Tetap/Kontrak) |
| `GET/POST` | `/api/payments` | List & Catat pembayaran iuran |
| `GET/POST` | `/api/expenses` | List & Catat pengeluaran kas |
| `GET` | `/api/dues-types` | List jenis iuran (Satpam, Kebersihan) |

### 4. Fitur Warga
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET` | `/api/warga/dashboard` | Ringkasan tagihan & status iuran warga |
| `GET` | `/api/warga/my-dues` | List tagihan iuran yang harus dibayar |
| `POST` | `/api/warga/pay` | Konfirmasi pembayaran iuran (Upload bukti transfer) |

### 5. Media (Files)
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET` | `/api/ktp-photo/{filename}` | Mengambil file foto KTP |
| `GET` | `/api/payment-proof/{filename}` | Mengambil file bukti pembayaran |

---