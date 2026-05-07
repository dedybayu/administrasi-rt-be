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

<details>
<summary><b>POST /api/login</b> - Masuk ke sistem</summary>

**Request Body (JSON):**
- `username` (string, required)
- `password` (string, required)

**Response (200 OK):**
```json
{
  "message": "Login successful",
  "access_token": "eyJhbGciOi...",
  "token_type": "bearer",
  "expires_in": 3600,
  "user": {
    "user_id": 1,
    "username": "ketuart",
    "role": "rt",
    "occupant_id": null
  }
}
```
</details>

<details>
<summary><b>POST /api/update-profile</b> - Update Profil</summary>

**Request Body (JSON):**
- `username` (string, optional)
- `password` (string, optional)

**Response (200 OK):**
```json
{
  "message": "Profile updated successfully",
  "data": { ... }
}
```
</details>

### 2. Dashboard & Laporan (Khusus RT)

<details>
<summary><b>GET /api/dashboard/report-cashflow</b> - Laporan arus kas tahunan</summary>

**Response (200 OK):**
```json
{
  "message": "Success retrieve cashflow report",
  "total_balance": 5000000,
  "years": [
    {
      "year": 2026,
      "monthly_data": [
        {
          "month": 1,
          "month_name": "January",
          "income": 1000000,
          "expense": 200000,
          "balance": 800000,
          "running_balance": 800000
        }
      ]
    }
  ]
}
```
</details>

<details>
<summary><b>GET /api/dashboard/report-cashflow-detailed</b> - Rincian Transaksi Bulanan</summary>

**Query Parameters:**
- `year` (integer, required)
- `month` (integer, required)

**Response (200 OK):**
```json
{
  "message": "Success retrieve detailed cashflow report",
  "data": {
    "incomes": [...],
    "expenses": [...]
  }
}
```
</details>

### 3. Manajemen Data (Khusus RT)

<details>
<summary><b>POST /api/occupants</b> - Tambah data Warga</summary>

**Request Body (Multipart/form-data):**
- `occupant_name` (string, required)
- `occupant_status` (string: 'tetap'|'kontrak', required)
- `occupant_phone_number` (string, required)
- `is_married` (boolean, required)
- `occupant_gender` (string: 'L'|'P', optional)
- `username` (string, required, unique)
- `password` (string, required)
- `occupant_ktp_photo` (file image, optional)

**Response (201 Created):**
```json
{
  "message": "Occupant and User created successfully",
  "data": { 
    "occupant_id": 1, 
    "occupant_name": "Budi",
    "users": {
        "user_id": 2,
        "username": "budi123",
        "occupant_id": 1
    }
  }
}
```
</details>

<details>
<summary><b>POST /api/houses</b> - Tambah data Rumah</summary>

**Request Body (JSON):**
- `house_name` (string, required)
- `house_number` (string, required)

**Response (201 Created):**
```json
{
  "message": "House created successfully",
  "data": { "house_id": 1, "house_name": "Blok A", ... }
}
```
</details>

<details>
<summary><b>POST /api/house-occupants</b> - Tambah penghuni rumah</summary>

**Request Body (JSON):**
- `house_id` (integer, required)
- `occupant_id` (integer, required)
- `start_in_date` (date, required)
- `end_in_date` (date, optional/required jika tidak aktif)
- `is_current` (boolean, required)
- `is_head_family` (boolean, required)

**Response (201 Created):**
```json
{
  "message": "House occupant relation created successfully",
  "data": { "house_occupant_id": 1, ... }
}
```
</details>

<details>
<summary><b>POST /api/payments</b> - Catat pembayaran iuran</summary>

**Request Body (Multipart/form-data):**
- `dues_type_id` (integer, required)
- `payer_occupant_id` (integer, required)
- `house_occupant_id` (integer, required)
- `payment_amount` (numeric, required)
- `payment_date` (date, optional)
- `payment_period_month` (integer 1-12, required)
- `payment_period_year` (integer, required)
- `payment_status` (string: pending,success,rejected, nullable)
- `payment_proof` (file image, required jika status success)

**Response (201 Created):**
```json
{
  "message": "Payment created successfully",
  "data": { "payment_id": 1, "payment_status": "success", ... }
}
```
</details>

<details>
<summary><b>POST /api/expenses</b> - Catat pengeluaran kas</summary>

**Request Body (JSON):**
- `expense_amount` (numeric, required)
- `expense_date` (date, required)
- `expense_description` (string, required)

**Response (201 Created):**
```json
{
  "message": "Expense created successfully",
  "data": { "expense_id": 1, "expense_amount": "50000", ... }
}
```
</details>

<details>
<summary><b>POST /api/dues-types</b> - Tambah jenis iuran</summary>

**Request Body (JSON):**
- `dues_type_name` (string, required)
- `dues_type_amount` (numeric, required)

**Response (201 Created):**
```json
{
  "message": "Dues type created successfully",
  "data": { "dues_type_id": 1, "dues_type_name": "Kebersihan", "dues_type_amount": "50000" }
}
```
</details>


### 4. Fitur Warga

<details>
<summary><b>GET /api/warga/my-dues</b> - Tagihan Iuran Saya</summary>

**Response (200 OK):**
```json
{
  "message": "Success retrieve my dues",
  "data": [
    {
      "payment_period_month": 5,
      "payment_period_year": 2026,
      "amount": "100000",
      "status": "unpaid"
    }
  ]
}
```
</details>

<details>
<summary><b>POST /api/warga/pay</b> - Konfirmasi Pembayaran Iuran</summary>

**Request Body (Multipart/form-data):**
- `dues_type_id` (integer, required)
- `payment_period_month` (integer, required)
- `payment_period_year` (integer, required)
- `payment_amount` (numeric, required)
- `payment_proof` (file image, required)

**Response (200 OK):**
```json
{
  "message": "Payment confirmation submitted successfully. Waiting for verification.",
  "data": { "payment_id": 2, "payment_status": "pending", ... }
}
```
</details>

---