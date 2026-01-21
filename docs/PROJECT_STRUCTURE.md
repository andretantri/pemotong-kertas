# Struktur Project HMI Pemotong Kertas

## 📁 Struktur Folder Lengkap

```
pemotongKertas/
│
├── api/                          # API Endpoints
│   ├── start.php                 # API untuk memulai pekerjaan potong
│   ├── stop.php                  # API untuk menghentikan pekerjaan
│   └── progress.php              # API untuk menerima progress dari ESP32
│
├── config/                       # Konfigurasi & Core Functions
│   ├── database.php              # Konfigurasi database & koneksi
│   ├── auth.php                  # Sistem autentikasi & session
│   └── functions.php             # Helper functions (sanitize, ESP32 request, dll)
│
├── database/                     # Database Scripts
│   └── schema.sql                # Script SQL untuk membuat database & tabel
│
├── index.php                     # Redirect ke login.php
├── login.php                     # Halaman login
├── logout.php                    # Handler logout
├── dashboard.php                 # Halaman utama kontrol mesin
├── log.php                       # Halaman log pekerjaan
│
├── .htaccess                     # Apache configuration & security
│
├── ESP32_EXAMPLE.ino             # Contoh kode ESP32 (Arduino)
│
└── Dokumentasi/
    ├── README.md                 # Dokumentasi utama
    ├── ARCHITECTURE.md           # Arsitektur aplikasi
    ├── ERD.md                    # Entity Relationship Diagram
    ├── DEPLOYMENT.md             # Panduan deployment
    └── PROJECT_STRUCTURE.md      # File ini
```

## 📄 Deskripsi File

### 🔐 Authentication & Core

#### `index.php`
- Redirect otomatis ke `login.php`
- Entry point aplikasi

#### `login.php`
- Halaman login dengan Bootstrap 5
- Form autentikasi
- Validasi username & password
- Session management

#### `logout.php`
- Handler untuk logout
- Clear session & cookies
- Redirect ke login

### 🎛️ Dashboard & Control

#### `dashboard.php`
- Halaman utama kontrol mesin
- Input panjang & jumlah potong
- Tombol START & STOP
- Status mesin real-time
- Progress bar
- Tabel pekerjaan terakhir
- Auto-refresh setiap 2 detik

#### `log.php`
- Halaman log detail potong
- Filter by job_id
- Pagination
- Tabel log dengan status

### 🔌 API Endpoints

#### `api/start.php`
- **Method:** POST
- **Input:** JSON `{panjang: int, jumlah: int}`
- **Output:** JSON response
- **Fungsi:** 
  - Validasi input
  - Buat job record
  - Kirim command ke ESP32
  - Update status job

#### `api/stop.php`
- **Method:** POST
- **Input:** None
- **Output:** JSON response
- **Fungsi:**
  - Cari job yang sedang running
  - Kirim stop command ke ESP32
  - Update status job ke STOPPED

#### `api/progress.php`
- **Method:** POST
- **Input:** JSON `{job_id: int, potong_ke: int, status: string, panjang_mm: int}`
- **Output:** JSON response
- **Fungsi:**
  - Terima progress dari ESP32
  - Insert log record
  - Update progress job
  - Cek completion status

### ⚙️ Configuration

#### `config/database.php`
- Konfigurasi database (host, user, pass, name)
- Konfigurasi ESP32 (IP, timeout)
- Konfigurasi session
- Function `getDBConnection()`
- Function `closeDBConnection()`

#### `config/auth.php`
- Session management
- Function `isLoggedIn()`
- Function `requireLogin()`
- Function `loginUser()`
- Function `logoutUser()`
- Function `verifyPassword()`
- Function `hashPassword()`

#### `config/functions.php`
- Function `sanitize()` - Sanitize input
- Function `sendESP32Request()` - HTTP request ke ESP32
- Function `jsonResponse()` - Format JSON response
- Function `getStatusText()` - Convert status ke bahasa Indonesia
- Function `getStatusBadgeClass()` - Get Bootstrap badge class

### 🗄️ Database

#### `database/schema.sql`
- CREATE DATABASE
- CREATE TABLE users
- CREATE TABLE job_potong
- CREATE TABLE log_potong
- CREATE VIEW v_job_summary
- INSERT default admin user
- Indexes & Foreign Keys

### 🔒 Security

#### `.htaccess`
- Security headers
- Prevent directory listing
- Protect sensitive files (.sql, .log, .ini)
- PHP settings

### 📱 ESP32 Integration

#### `ESP32_EXAMPLE.ino`
- Contoh kode Arduino untuk ESP32
- WiFi Access Point setup
- Web server endpoints (/start, /stop, /status)
- HTTP client untuk POST progress
- Contoh logic proses potong

### 📚 Dokumentasi

#### `README.md`
- Overview aplikasi
- Fitur-fitur
- Instalasi
- Konfigurasi
- Integrasi ESP32
- Testing
- Troubleshooting

#### `ARCHITECTURE.md`
- Arsitektur aplikasi detail
- Flow diagram
- Network architecture
- Security architecture
- Design principles

#### `ERD.md`
- Entity Relationship Diagram
- Detail setiap tabel
- Relasi antar tabel
- Query examples
- Business rules

#### `DEPLOYMENT.md`
- Panduan deployment step-by-step
- Konfigurasi Laragon/XAMPP
- Network setup
- Testing checklist
- Troubleshooting guide

## 🔄 Data Flow

### 1. User Login Flow
```
User → login.php → Verify Password → Create Session → Redirect dashboard.php
```

### 2. Start Job Flow
```
Dashboard → POST /api/start.php → Create Job → Send to ESP32 → Update Status → Response
```

### 3. Progress Update Flow
```
ESP32 → POST /api/progress.php → Insert Log → Update Job → Check Complete → Response
```

### 4. Stop Job Flow
```
Dashboard → POST /api/stop.php → Find Running Job → Send Stop to ESP32 → Update Status → Response
```

## 🎨 Frontend Components

### Bootstrap 5 Components Used
- Navbar (gradient purple)
- Cards (control panel, status)
- Forms (input groups, validation)
- Buttons (START green, STOP red)
- Progress bars (animated)
- Tables (responsive, hover)
- Modals (alerts)
- Badges (status indicators)
- Icons (Bootstrap Icons)

### JavaScript Features
- Fetch API untuk AJAX
- Auto-refresh setiap 2 detik
- Form validation
- Modal alerts
- Event listeners

## 🔐 Security Features

1. **Password Hashing:** bcrypt
2. **SQL Injection Prevention:** Prepared statements
3. **XSS Prevention:** Output escaping
4. **Session Security:** Custom session name, lifetime
5. **Input Validation:** Sanitize semua input
6. **File Protection:** .htaccess rules

## 📊 Database Tables

1. **users** - User management
2. **job_potong** - Job records
3. **log_potong** - Detailed cut logs
4. **v_job_summary** - View untuk summary

## 🌐 Network Communication

### Web → ESP32
- Protocol: HTTP GET
- Endpoints: `/start`, `/stop`
- Timeout: 5 detik

### ESP32 → Web
- Protocol: HTTP POST
- Endpoint: `/api/progress.php`
- Format: JSON
- CORS: Enabled

## 📱 Mobile Support

- Responsive design (Bootstrap 5)
- Touch-friendly buttons
- Mobile-optimized layout
- Viewport meta tag
- Auto-refresh untuk real-time updates

## 🧪 Testing Files (Optional)

Jika diperlukan untuk testing, bisa dibuat:
- `test_db.php` - Test database connection
- `test_esp32.php` - Test ESP32 connection
- `info.php` - PHP info (hapus setelah testing)

## 📝 File yang Perlu Dikonfigurasi

1. **config/database.php**
   - DB_HOST, DB_USER, DB_PASS, DB_NAME
   - ESP32_IP

2. **ESP32_EXAMPLE.ino**
   - WiFi SSID & Password
   - Server URL
   - Pin assignments

## ✅ Checklist Setup

- [ ] Copy semua file ke web server
- [ ] Import database schema.sql
- [ ] Konfigurasi config/database.php
- [ ] Test koneksi database
- [ ] Test login (admin/admin123)
- [ ] Konfigurasi ESP32 IP
- [ ] Test koneksi ESP32
- [ ] Upload ESP32 code
- [ ] Test API endpoints
- [ ] Test mobile browser
- [ ] Ganti password default

---

**Total Files:** ~20 files  
**Lines of Code:** ~2000+ lines  
**Database Tables:** 3 tables + 1 view  
**API Endpoints:** 3 endpoints  

---

**Versi:** 1.0.0  
**Update Terakhir:** 2024

