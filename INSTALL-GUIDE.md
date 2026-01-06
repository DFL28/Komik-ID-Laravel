# Cara Install & Setup Komik-ID Laravel

## Masalah: Composer Not Found

Composer adalah package manager untuk PHP yang diperlukan untuk mengelola dependencies Laravel.

---

## ✅ Solusi 1: Install Composer (RECOMMENDED)

### Step 1: Download Composer

1. Buka browser dan download Composer installer for Windows:
   **https://getcomposer.org/Composer-Setup.exe**

2. Jalankan installer
3. Ikuti wizard instalasi (next, next, finish)
4. Restart terminal/PowerShell setelah instalasi

### Step 2: Verify Installation

```powershell
composer --version
```

Jika berhasil, akan muncul versi composer (contoh: Composer version 2.6.5)

### Step 3: Install Dependencies

```powershell
cd "G:\Backup-Komik-ID (4)\Backup-Komik-ID\Komik-ID\Komik-ID-Laravel"
composer install
```

---

## ✅ Solusi 2: Manual Setup (Tanpa Composer)

Jika tidak ingin install composer, ikuti cara manual ini:

### Step 1: Download Laravel Framework Manual

```powershell
# Download Laravel framework files dari proyek lain atau gunakan cara berikut
# Sayangnya Laravel memerlukan composer untuk autoloading
# Opsi ini TIDAK RECOMMENDED untuk Laravel
```

**CATATAN**: Laravel dirancang untuk menggunakan Composer, jadi cara manual sangat tidak praktis.

---

## ✅ Solusi 3: Gunakan XAMPP dengan Composer Built-in

### Step 1: Install XAMPP

1. Download XAMPP for Windows: https://www.apachefriends.org/
2. Install XAMPP (centang PHP minimal 8.1)
3. XAMPP sudah include PHP

### Step 2: Install Composer Setelah XAMPP

Ikuti Solusi 1 di atas

---

## 🚀 Setelah Composer Terinstall

Jalankan perintah berikut:

```powershell
# 1. Install dependencies
composer install

# 2. Copy environment file
copy .env.example .env

# 3. Generate application key
php artisan key:generate

# 4. Create database
type nul > database\database.sqlite

# 5. Run migrations
php artisan migrate

# 6. Create admin user
php artisan db:seed --class=AdminSeeder

# 7. Link storage
php artisan storage:link

# 8. Start server
php artisan serve
```

### Access the application

- Homepage: http://localhost:8000
- Admin: http://localhost:8000/admin
  - Username: admin
  - Password: admin123 (dari .env)

---

## ⚠️ Troubleshooting

### Error: PHP tidak ditemukan

Install PHP terlebih dahulu:
- Download PHP: https://windows.php.net/download/
- Atau install via XAMPP

### Error: SQLite extension not loaded

1. Buka `php.ini`
2. Uncomment (hapus `;`) dari baris:
   ```
   ;extension=sqlite3
   ;extension=pdo_sqlite
   ```
3. Restart terminal

### Error: Permission denied

```powershell
# Jalankan PowerShell as Administrator
```

---

## 📝 Alternative: Gunakan Versi Node.js

Jika setup Laravel terlalu kompleks, gunakan versi Node.js original yang sudah ada:

```powershell
cd "G:\Backup-Komik-ID (4)\Backup-Komik-ID\Komik-ID\backend"
npm install
npm run migrate
npm start
```

Versi Node.js tidak memerlukan Composer dan lebih mudah di-setup.

---

## 🎯 Recommendation

**Untuk kemudahan development**, saya recommend:
1. **Install XAMPP** (include PHP, MySQL, Apache)
2. **Install Composer** (via installer)
3. **Atau gunakan versi Node.js** yang sudah ada

Laravel lebih powerful tapi memerlukan PHP ecosystem.
Node.js lebih mudah setup tapi memerlukan Node.js & npm.

Pilih yang paling sesuai dengan environment Anda!
