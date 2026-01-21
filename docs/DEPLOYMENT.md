# Panduan Deployment Lokal

## 📋 Persiapan

### 1. Software yang Diperlukan

#### Windows (Laragon/XAMPP)
- **Laragon** (Recommended) - https://laragon.org/
- Atau **XAMPP** - https://www.apachefriends.org/
- **phpMyAdmin** (sudah included)
- **Browser** (Chrome, Firefox, Edge)

#### Linux
- Apache/Nginx
- PHP 7.4+
- MySQL/MariaDB
- phpMyAdmin (optional)

#### Mac
- **MAMP** - https://www.mamp.info/
- Atau **XAMPP** untuk Mac

### 2. Hardware yang Diperlukan
- ESP32 Development Board
- Koneksi WiFi (untuk komunikasi)
- Hardware mesin pemotong (motor, sensor, dll)

---

## 🚀 Instalasi Step-by-Step (Laragon)

### Step 1: Install Laragon
1. Download Laragon dari https://laragon.org/
2. Install dengan default settings
3. Start Laragon
4. Klik "Start All" untuk menjalankan Apache dan MySQL

### Step 2: Copy Project
1. Copy folder `pemotongKertas` ke:
   ```
   C:\laragon\www\pemotongKertas
   ```

### Step 3: Buat Database
1. Buka browser, akses: `http://localhost/phpmyadmin`
2. Klik tab "Import"
3. Pilih file: `database/schema.sql`
4. Klik "Go" untuk import
5. Pastikan database `hmi_pemotong_kertas` sudah dibuat

**Atau via Command Line:**
```bash
cd C:\laragon\bin\mysql\mysql-8.x.x\bin
mysql -u root -p < C:\laragon\www\pemotongKertas\database\schema.sql
```

### Step 4: Konfigurasi Database
1. Buka file: `config/database.php`
2. Sesuaikan konfigurasi:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Default Laragon kosong
   define('DB_NAME', 'hmi_pemotong_kertas');
   ```

### Step 5: Konfigurasi ESP32 IP
1. Buka file: `config/database.php`
2. Sesuaikan IP ESP32:
   ```php
   define('ESP32_IP', '192.168.4.1'); // Ganti dengan IP ESP32 Anda
   ```

### Step 6: Test Aplikasi
1. Buka browser: `http://localhost/pemotongKertas`
2. Login dengan:
   - Username: `admin`
   - Password: `admin123`
3. Pastikan dashboard muncul

---

## 🚀 Instalasi Step-by-Step (XAMPP)

### Step 1: Install XAMPP
1. Download XAMPP dari https://www.apachefriends.org/
2. Install dengan default settings
3. Buka XAMPP Control Panel
4. Start Apache dan MySQL

### Step 2: Copy Project
1. Copy folder `pemotongKertas` ke:
   ```
   C:\xampp\htdocs\pemotongKertas
   ```

### Step 3: Buat Database
1. Buka browser: `http://localhost/phpmyadmin`
2. Klik tab "Import"
3. Pilih file: `database/schema.sql`
4. Klik "Go"

### Step 4-6: Sama seperti Laragon

---

## 🔧 Konfigurasi Tambahan

### 1. PHP Extensions
Pastikan extension berikut aktif di `php.ini`:
```ini
extension=mysqli
extension=curl
extension=json
extension=session
```

**Cek via browser:**
- Buat file `info.php`:
  ```php
  <?php phpinfo(); ?>
  ```
- Akses: `http://localhost/pemotongKertas/info.php`
- Cari "mysqli" dan "curl"

### 2. Folder Permissions
Pastikan folder berikut writable:
- Folder session (biasanya `C:\Windows\Temp` atau `C:\laragon\tmp`)
- Folder upload (jika ada)

### 3. Firewall
- Pastikan firewall tidak memblokir Apache (port 80)
- Jika ESP32 dan server berbeda network, pastikan firewall mengizinkan komunikasi

---

## 🌐 Konfigurasi Network

### Skenario 1: ESP32 sebagai Access Point
```
ESP32 (AP Mode)
  IP: 192.168.4.1
  SSID: ESP32_PemotongKertas
  
Server (Laragon)
  IP: 192.168.1.100 (dari router)
  
Client (Browser)
  Connect ke router WiFi
  Akses: http://192.168.1.100/pemotongKertas
```

**Catatan:**
- Server dan Client harus dalam network yang sama
- ESP32 harus bisa POST ke server (pastikan server accessible dari ESP32)

### Skenario 2: ESP32 sebagai WiFi Client
```
Router WiFi
  SSID: YourWiFi
  IP Range: 192.168.1.x
  
ESP32 (Client Mode)
  IP: 192.168.1.50 (dari router)
  
Server (Laragon)
  IP: 192.168.1.100 (dari router)
  
Client (Browser)
  Connect ke router WiFi
  Akses: http://192.168.1.100/pemotongKertas
```

**Konfigurasi:**
- Update ESP32 code untuk WiFi Client mode
- Update `ESP32_IP` di `config/database.php` dengan IP ESP32

---

## 🧪 Testing

### 1. Test Database Connection
Buat file `test_db.php`:
```php
<?php
require_once 'config/database.php';
$conn = getDBConnection();
echo "Database connected successfully!";
?>
```
Akses: `http://localhost/pemotongKertas/test_db.php`

### 2. Test ESP32 Connection
```bash
# Test ping
ping 192.168.4.1

# Test HTTP endpoint
curl http://192.168.4.1/start?panjang=100&jumlah=5
```

### 3. Test API Endpoints

**Start API:**
```bash
curl -X POST http://localhost/pemotongKertas/api/start.php ^
  -H "Content-Type: application/json" ^
  -d "{\"panjang\":100,\"jumlah\":10}"
```

**Stop API:**
```bash
curl -X POST http://localhost/pemotongKertas/api/stop.php
```

**Progress API (Simulasi ESP32):**
```bash
curl -X POST http://localhost/pemotongKertas/api/progress.php ^
  -H "Content-Type: application/json" ^
  -d "{\"job_id\":1,\"potong_ke\":1,\"status\":\"SUCCESS\",\"panjang_mm\":100}"
```

### 4. Test dari Browser
1. Login: `http://localhost/pemotongKertas/login.php`
2. Dashboard: `http://localhost/pemotongKertas/dashboard.php`
3. Log: `http://localhost/pemotongKertas/log.php`

---

## 🔒 Security Checklist

### Setelah Instalasi
- [ ] Ganti password default admin
- [ ] Update `ESP32_IP` sesuai network Anda
- [ ] Pastikan `.htaccess` aktif
- [ ] Hapus file `test_db.php` atau `info.php` jika ada
- [ ] Backup database secara berkala

### Untuk Production
- [ ] Setup HTTPS (SSL certificate)
- [ ] Batasi akses IP jika memungkinkan
- [ ] Enable PHP error logging
- [ ] Setup firewall rules
- [ ] Regular backup database
- [ ] Monitor log files

---

## 🐛 Troubleshooting

### Problem: Database Connection Error
**Solusi:**
1. Pastikan MySQL berjalan di XAMPP/Laragon
2. Cek konfigurasi di `config/database.php`
3. Pastikan database sudah dibuat
4. Test koneksi manual via phpMyAdmin

### Problem: ESP32 Tidak Merespons
**Solusi:**
1. Cek koneksi WiFi ESP32
2. Test ping: `ping 192.168.4.1`
3. Test langsung via browser: `http://192.168.4.1/status`
4. Cek firewall/antivirus
5. Pastikan ESP32 code sudah di-upload

### Problem: Session Tidak Berfungsi
**Solusi:**
1. Cek `session.save_path` di php.ini
2. Pastikan folder session writable
3. Cek browser cookies enabled
4. Clear browser cache

### Problem: API Progress Tidak Menerima Data
**Solusi:**
1. Cek CORS headers di `api/progress.php`
2. Pastikan ESP32 mengirim Content-Type: application/json
3. Cek error log PHP
4. Test dengan Postman/curl

### Problem: Halaman Blank
**Solusi:**
1. Enable error display di php.ini:
   ```ini
   display_errors = On
   error_reporting = E_ALL
   ```
2. Cek error log Apache
3. Pastikan semua file ada dan tidak corrupt
4. Cek PHP version (minimal 7.4)

---

## 📱 Mobile Testing

### Android Browser
1. Pastikan Android dan server dalam network yang sama
2. Akses: `http://IP_SERVER/pemotongKertas`
   Contoh: `http://192.168.1.100/pemotongKertas`
3. Login dan test semua fitur

### iOS Browser
1. Sama seperti Android
2. Pastikan Safari mengizinkan cookies

---

## 📊 Monitoring

### Log Files Location

**Laragon:**
- Apache Error: `C:\laragon\bin\apache\apache-x.x.x\logs\error.log`
- PHP Error: `C:\laragon\bin\php\php-x.x.x\php_errors.log`

**XAMPP:**
- Apache Error: `C:\xampp\apache\logs\error.log`
- PHP Error: `C:\xampp\php\logs\php_error_log`

### Database Backup
```bash
# Backup via command line
mysqldump -u root -p hmi_pemotong_kertas > backup.sql

# Restore
mysql -u root -p hmi_pemotong_kertas < backup.sql
```

---

## ✅ Checklist Deployment

- [ ] Laragon/XAMPP terinstall dan berjalan
- [ ] Database diimport dengan sukses
- [ ] Konfigurasi database.php sudah benar
- [ ] Konfigurasi ESP32_IP sudah benar
- [ ] Login berhasil dengan default credentials
- [ ] Dashboard muncul dan berfungsi
- [ ] ESP32 bisa diakses (ping/HTTP test)
- [ ] API endpoints berfungsi
- [ ] Mobile browser bisa akses
- [ ] Password default sudah diganti
- [ ] Backup database sudah dibuat

---

## 📞 Support

Jika mengalami masalah:
1. Cek error log PHP dan Apache
2. Cek console browser (F12)
3. Test setiap komponen secara terpisah
4. Pastikan semua requirements terpenuhi

---

**Selamat menggunakan aplikasi HMI Pemotong Kertas!** 🎉

