# HMI Pemotong Kertas Roll - ESP32 v2.0

Aplikasi Web HMI (Human Machine Interface) untuk mengontrol mesin pemotong kertas roll otomatis berbasis ESP32 dengan dukungan konfigurasi parameter mesin dari web interface.

## 🎯 Fitur Utama

### Core Features
- ✅ Kontrol Panjang Potong (1-10000 mm)
- ✅ Kontrol Jumlah Potong / Loop (1-1000 potong)
- ✅ Tombol START dan STOP mesin
- ✅ Status dan Progress real-time
- ✅ Logging pekerjaan potong detail
- ✅ Mobile-friendly (Bootstrap 5)
- ✅ Single user authentication

### Version 2.0 New Features
- ✨ **Pengaturan Mesin via Web Interface**
  - Diameter roller (default 17mm)
  - Jarak penarik (pull distance)
  - Kecepatan motor (step delay)
  - Timing penarik & pemotong (delays & pauses)
  - Real-time calculation steps berdasarkan roller diameter

- ✨ **WiFi & REST API Integration**
  - ESP32 automatic config loading dari server
  - Progress reporting dari ESP32 ke web
  - Multiple API endpoints untuk control
  - JSON-based communication

- ✨ **Konfigurasi Fleksibel**
  - Ganti parameter tanpa re-upload firmware
  - Automatic step calculation untuk berbagai roller size
  - Customizable timing untuk optimasi performa

- ✨ **Comprehensive Documentation**
  - QUICK_START.md - Setup cepat 5 menit
  - SETUP_GUIDE.md - Panduan lengkap
  - API_REFERENCE.md - Dokumentasi API
  - HARDWARE_CONNECTIONS.md - Koneksi hardware
  - DEPLOYMENT_CHECKLIST.md - Pre-deployment checklist

## 🏗️ Sistem Arsitektur v2.0

```
┌──────────────────────────────────────────────────────────────┐
│                      WEB BROWSER                             │
│                  (Dashboard / Settings)                      │
└─────────────┬──────────────────────────────────────┬─────────┘
              │                                      │
              ↓ HTTP POST                            ↓ HTTP GET
        ┌──────────────┐                      ┌──────────────┐
        │  start.php   │                      │ get_config   │
        │  config.php  │                      │ status.php   │
        └──────┬───────┘                      └──────┬───────┘
               │                                     │
               │ ┌─────────────────────────────────┐ │
               │ │                                 │ │
               ▼ ▼                                 ▼ ▼
        ┌──────────────────────┐
        │    MySQL Database    │
        │                      │
        │ • machine_config     │ ◄─── Configuration
        │ • job_potong         │ ◄─── Job Management
        │ • log_potong         │ ◄─── Logging
        │ • users              │ ◄─── Auth
        └──────────────────────┘
                   ▲
                   │ HTTP Request
                   │ (WiFi)
                   │
        ┌──────────────────────────┐
        │   ESP32 Microcontroller  │
        │                          │
        │  • WiFi Module           │
        │  • Motor Control         │
        │  • Stepper Drivers       │
        │  • Sensors (optional)    │
        └──────────────────────────┘
                   │
        ┌──────────┴──────────┐
        ▼                     ▼
    ┌─────────┐        ┌─────────┐
    │ Motor A │        │ Motor B │
    │ (Pull)  │        │ (Cut)   │
    └─────────┘        └─────────┘
```

## 📁 Struktur Folder v2.0

```
pemotongKertas/
├── ESP32/
│   └── fix.ino ........................... Firmware ESP32 (v2.0 rewritten)
│
├── api/
│   ├── start.php ......................... (existing) Memulai pekerjaan
│   ├── stop.php .......................... (existing) Menghentikan pekerjaan
│   ├── status.php ........................ (existing) Status mesin
│   ├── progress.php ...................... (existing) Progress update
│   ├── get_config.php .................... ✨ NEW: Ambil config
│   ├── config.php ........................ ✨ NEW: Update config
│   └── esp32_start.php ................... ✨ NEW: Start job
│
├── config/
│   ├── database.php ...................... Konfigurasi database
│   ├── auth.php .......................... Sistem autentikasi
│   └── functions.php ..................... Helper functions
│
├── database/
│   └── schema.sql ........................ Database schema (updated)
│
├── assets/
│   ├── css/ .............................. Styling
│   └── js/ ............................... JavaScript
│
├── DOCUMENTATION/
│   ├── QUICK_START.md .................... ✨ NEW: Quick setup (5 min)
│   ├── SETUP_GUIDE.md .................... ✨ NEW: Panduan lengkap
│   ├── API_REFERENCE.md .................. ✨ NEW: Referensi API
│   ├── HARDWARE_CONNECTIONS.md ........... ✨ NEW: Koneksi hardware
│   ├── DEPLOYMENT_CHECKLIST.md ........... ✨ NEW: Pre-deployment
│   ├── IMPLEMENTATION_SUMMARY.md ......... ✨ NEW: Ringkasan changes
│   ├── FILE_CHANGES_SUMMARY.md ........... ✨ NEW: File modifications
│   ├── ARCHITECTURE.md ................... (existing) Arsitektur
│   └── README.md ......................... (this file)
│
├── dashboard.php ......................... Halaman utama (updated)
├── settings.php .......................... ✨ NEW: Pengaturan mesin
├── login.php ............................. Halaman login
├── logout.php ............................ Logout handler
├── log.php ............................... Halaman log pekerjaan
├── index.php ............................. Home page redirect
└── .htaccess ............................. Apache configuration
```

## 🗄️ Database Schema v2.0

### Tabel: `machine_config` (NEW)
Menyimpan konfigurasi mesin yang dapat diubah via web:
- `roller_diameter_mm` - Diameter roller (default 17mm)
- `pull_distance_cm` - Jarak tarik per siklus (default 5cm)
- `pull_delay_ms` - Delay penarik (default 500ms)
- `cut_delay_ms` - Delay pemotong (default 500ms)
- `step_delay_us` - Delay motor (default 1200µs)
- `pull_pause_ms` - Pause setelah penarik (default 1000ms)
- `cut_pause_ms` - Pause setelah pemotong (default 2000ms)

### Tabel: `job_potong`
Menyimpan data pekerjaan potong:
- Status: READY, RUNNING, STOPPED, DONE, ERROR
- Tracking progress dengan `potong_selesai`
- Timestamp: started_at, stopped_at, completed_at

### Tabel: `log_potong`
Menyimpan log detail setiap potong:
- potong_ke - Urutan potong
- status - SUCCESS, FAILED, SKIPPED
- waktu_potong - Timestamp eksekusi

### Tabel: `users`
Menyimpan data user/admin:
- Password menggunakan bcrypt hashing
```
- Status: SUCCESS, FAILED, SKIPPED

## 🚀 Instalasi

### 1. Persyaratan
- PHP 7.4+ (dengan extension: mysqli, curl, json)
- MySQL/MariaDB 5.7+
- Apache/Nginx web server
- Laragon/XAMPP/WAMP (untuk Windows)

### 2. Setup Database

```bash
# Import database schema
mysql -u root -p < database/schema.sql

# Atau melalui phpMyAdmin:
# 1. Buka phpMyAdmin
# 2. Import file database/schema.sql
```

### 3. Konfigurasi

Edit file `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hmi_pemotong_kertas');
```

Edit ESP32 IP di `config/database.php`:
```php
define('ESP32_IP', '192.168.4.1'); // Ganti dengan IP ESP32 Anda
```

### 4. Default Login
- **Username:** `admin`
- **Password:** `admin123`

⚠️ **PENTING:** Ganti password default setelah instalasi pertama!

## 📡 Integrasi ESP32

### Endpoint ESP32 yang Diharapkan

#### 1. START Command
```
GET http://192.168.4.1/start?panjang=XXX&jumlah=YYY
```
- `panjang`: Panjang potong dalam mm
- `jumlah`: Jumlah potong yang diminta

**Response yang diharapkan:**
- HTTP 200 OK (berhasil)
- HTTP 4xx/5xx (gagal)

#### 2. STOP Command
```
GET http://192.168.4.1/stop
```

**Response yang diharapkan:**
- HTTP 200 OK (berhasil)
- HTTP 4xx/5xx (gagal)

### ESP32 Mengirim Progress

ESP32 harus mengirim POST request ke server:

```
POST http://YOUR_SERVER_IP/pemotongKertas/api/progress.php
Content-Type: application/json

{
    "job_id": 1,
    "potong_ke": 5,
    "status": "SUCCESS",
    "panjang_mm": 100
}
```

**Parameter:**
- `job_id`: ID pekerjaan dari database
- `potong_ke`: Urutan potong ke berapa (1, 2, 3, ...)
- `status`: SUCCESS, FAILED, atau SKIPPED
- `panjang_mm`: Panjang potong (opsional, akan menggunakan dari job jika tidak ada)

## 💻 Contoh Request HTTP ke ESP32

### Menggunakan cURL (Testing)

```bash
# Start job
curl "http://192.168.4.1/start?panjang=100&jumlah=20"

# Stop job
curl "http://192.168.4.1/stop"
```

### Menggunakan JavaScript (Frontend)

```javascript
// Start
fetch('http://192.168.4.1/start?panjang=100&jumlah=20')
    .then(response => response.text())
    .then(data => console.log(data));

// Stop
fetch('http://192.168.4.1/stop')
    .then(response => response.text())
    .then(data => console.log(data));
```

### Menggunakan PHP (Backend)

```php
// Sudah diimplementasikan di config/functions.php
$response = sendESP32Request('/start', [
    'panjang' => 100,
    'jumlah' => 20
]);
```

## 🔒 Keamanan

### Implementasi Keamanan

1. **Password Hashing**
   - Menggunakan `password_hash()` dengan bcrypt
   - Password tidak disimpan dalam plain text

2. **Session Management**
   - Session PHP dengan lifetime 1 jam
   - Session name custom untuk menghindari collision

3. **SQL Injection Prevention**
   - Menggunakan prepared statements
   - Input sanitization dengan `sanitize()`

4. **XSS Prevention**
   - Output escaping dengan `htmlspecialchars()`
   - Content-Type header untuk JSON API

5. **CORS**
   - API progress.php mengizinkan CORS untuk ESP32
   - Header security di .htaccess

### Rekomendasi Tambahan

- ✅ Ganti password default setelah instalasi
- ✅ Gunakan HTTPS di production
- ✅ Batasi akses IP jika memungkinkan
- ✅ Backup database secara berkala
- ✅ Monitor log error PHP

## 🧪 Testing

### 1. Test Database Connection
```php
<?php
require_once 'config/database.php';
$conn = getDBConnection();
echo "Connected successfully!";
?>
```

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
curl -X POST http://localhost/pemotongKertas/api/start.php \
  -H "Content-Type: application/json" \
  -d '{"panjang":100,"jumlah":10}'
```

**Stop API:**
```bash
curl -X POST http://localhost/pemotongKertas/api/stop.php
```

**Progress API (simulasi ESP32):**
```bash
curl -X POST http://localhost/pemotongKertas/api/progress.php \
  -H "Content-Type: application/json" \
  -d '{"job_id":1,"potong_ke":1,"status":"SUCCESS","panjang_mm":100}'
```

## 📱 Mobile Support

Aplikasi sudah dioptimasi untuk mobile dengan:
- Bootstrap 5 responsive design
- Touch-friendly buttons
- Auto-refresh setiap 2 detik
- Viewport meta tag untuk mobile browser

## 🐛 Troubleshooting

### Database Connection Error
- Pastikan MySQL/MariaDB berjalan
- Cek konfigurasi di `config/database.php`
- Pastikan database sudah dibuat

### ESP32 Tidak Merespons
- Cek koneksi WiFi ESP32
- Pastikan IP ESP32 benar di config
- Cek firewall/antivirus yang memblokir koneksi
- Test dengan browser/Postman langsung ke ESP32

### Session Tidak Berfungsi
- Pastikan folder session writable
- Cek `session.save_path` di php.ini
- Pastikan cookies enabled di browser

### API Progress Tidak Menerima Data
- Cek CORS headers
- Pastikan ESP32 mengirim Content-Type: application/json
- Cek error log PHP

## 📝 Catatan Deployment Lokal

### Laragon (Windows)
1. Copy folder ke `C:\laragon\www\pemotongKertas`
2. Buka Laragon, start Apache & MySQL
3. Import database via phpMyAdmin
4. Akses: `http://localhost/pemotongKertas`

### XAMPP (Windows/Linux/Mac)
1. Copy folder ke `htdocs/pemotongKertas`
2. Start Apache & MySQL di XAMPP Control Panel
3. Import database via phpMyAdmin
4. Akses: `http://localhost/pemotongKertas`

### Manual Setup
1. Pastikan PHP, MySQL, Apache terinstall
2. Copy folder ke document root
3. Import database
4. Konfigurasi database.php
5. Set permissions folder session

## 📄 License

Project ini dibuat untuk keperluan akademik/skripsi.

## 👨‍💻 Author

Dibuat untuk sistem kontrol mesin pemotong kertas roll berbasis ESP32.

---

**Versi:** 1.0.0  
**Update Terakhir:** 2024

