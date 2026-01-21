# 🔪 Mesin Pemotong Kertas Roll - ESP8266

![ESP8266](https://img.shields.io/badge/ESP8266-NodeMCU-blue)
![PHP](https://img.shields.io/badge/PHP-8.x-purple)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-blueviolet)
![React](https://img.shields.io/badge/React-18-61DAFB)
![License](https://img.shields.io/badge/License-MIT-green)

Sistem kontrol mesin pemotong kertas roll otomatis berbasis **ESP8266** dengan antarmuka web dashboard menggunakan **PHP** dan **React**.

![Dashboard Preview](docs/images/dashboard-preview.png)

---

## 📋 Daftar Isi

- [Fitur](#-fitur)
- [Arsitektur Sistem](#-arsitektur-sistem)
- [Requirement](#-requirement)
- [Instalasi](#-instalasi)
- [Konfigurasi](#-konfigurasi)
- [Penggunaan](#-penggunaan)
- [API Endpoints](#-api-endpoints)
- [Struktur Folder](#-struktur-folder)
- [Troubleshooting](#-troubleshooting)
- [Kontribusi](#-kontribusi)

---

## ✨ Fitur

### Dashboard Web
- ✅ **Real-time monitoring** - Status mesin dan progress pemotongan
- ✅ **Kontrol jarak jauh** - Start/Stop pekerjaan via web
- ✅ **Konfigurasi dinamis** - Atur parameter mesin tanpa upload ulang kode
- ✅ **Responsive design** - Bisa diakses dari PC, tablet, atau smartphone
- ✅ **Offline capable** - Semua aset tersimpan lokal, tidak butuh internet

### ESP8266
- ✅ **Access Point Mode** - ESP8266 memancarkan WiFi sendiri
- ✅ **Web Server** - Menerima perintah via HTTP
- ✅ **Motor Stepper Control** - Mengontrol motor penarik dan pemotong
- ✅ **Konfigurasi via Web** - Menerima parameter dari dashboard

### Mesin
- ✅ **Motor Penarik** - Menarik kertas sesuai panjang yang ditentukan
- ✅ **Motor Pemotong** - Memotong kertas dengan presisi
- ✅ **Multi-job** - Bisa mengerjakan banyak pemotongan secara berurutan

---

## 🏗 Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────────┐
│                         LAPTOP                               │
│  ┌─────────────────┐    ┌─────────────────────────────────┐ │
│  │   Browser       │◄──►│   Laragon (Apache + MySQL)      │ │
│  │   Dashboard     │    │   - PHP API                     │ │
│  │   (React)       │    │   - Database                    │ │
│  └─────────────────┘    └─────────────────────────────────┘ │
│           │                          │                       │
│           │                          │ HTTP Request          │
│           └──────────────────────────┼───────────────────────┼
│                                      │                       │
│                      WiFi: Mesin_Potong_Kertas               │
│                                      │                       │
│                                      ▼                       │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │                     ESP8266 (AP Mode)                    │ │
│  │                     IP: 192.168.4.1                      │ │
│  └─────────────────────────────────────────────────────────┘ │
│                    │                   │                     │
│                    ▼                   ▼                     │
│  ┌─────────────────────┐    ┌─────────────────────┐         │
│  │   Motor Penarik     │    │   Motor Pemotong    │         │
│  │   (Stepper A4988)   │    │   (Stepper A4988)   │         │
│  └─────────────────────┘    └─────────────────────┘         │
└─────────────────────────────────────────────────────────────┘
```

---

## 📦 Requirement

### Hardware
| Komponen | Keterangan |
|----------|------------|
| ESP8266 (NodeMCU) | Mikrokontroler utama |
| Motor Stepper NEMA 17 (x2) | Untuk penarik dan pemotong |
| Driver A4988 (x2) | Driver motor stepper |
| Power Supply 12V 3A | Untuk motor stepper |
| Roller | Diameter 17mm (atau sesuaikan di config) |

### Software
| Software | Versi |
|----------|-------|
| Laragon | 6.x (atau XAMPP) |
| PHP | 8.x |
| MySQL/MariaDB | 10.x |
| Arduino IDE | 2.x |
| ESP8266 Board Manager | 3.x |

### Library Arduino
```
- ESP8266WiFi
- ESP8266WebServer
- ESP8266HTTPClient
- ArduinoJson (v6)
```

---

## 🚀 Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/username/pemotong-kertas.git
cd pemotong-kertas
```

### 2. Setup Database
```bash
# Import schema ke MySQL
mysql -u root -p < database/schema.sql
```

Atau import manual via phpMyAdmin:
- Buka `http://localhost/phpmyadmin`
- Buat database: `hmi_pemotong_kertas`
- Import file: `database/schema.sql`

### 3. Konfigurasi PHP
Edit file `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hmi_pemotong_kertas');
```

### 4. Upload Kode ESP8266
1. Buka `ESP8266/pemotong_kertas.ino` di Arduino IDE
2. Install board ESP8266 via Board Manager
3. Pilih board: **NodeMCU 1.0 (ESP-12E Module)**
4. Upload kode ke ESP8266

### 5. Akses Dashboard
1. Nyalakan ESP8266
2. Hubungkan laptop ke WiFi: **Mesin_Potong_Kertas** (password: `password123`)
3. Buka browser: `http://localhost/pemotongKertas`
4. Login dengan: `admin` / `admin123`

---

## ⚙️ Konfigurasi

### Konfigurasi Mesin (via Dashboard)

| Parameter | Deskripsi | Default |
|-----------|-----------|---------|
| Diameter Roller | Diameter roller penarik (mm) | 17 |
| Jarak Tarik | Jarak pemotong bergerak (cm) | 5 |
| Step Delay | Delay antar step motor (µs) | 1200 |
| Pull Delay | Delay setelah penarik (ms) | 500 |
| Cut Delay | Delay setelah pemotong (ms) | 500 |
| IP Mesin | Alamat IP ESP8266 | 192.168.4.1 |

### Konfigurasi WiFi ESP8266
Edit di `ESP8266/pemotong_kertas.ino`:
```cpp
const char* ap_ssid = "Mesin_Potong_Kertas";
const char* ap_password = "password123";
```

### Pin Wiring ESP8266

| Fungsi | GPIO | NodeMCU Pin |
|--------|------|-------------|
| STEP Penarik | GPIO2 | D4 |
| DIR Penarik | GPIO0 | D3 |
| EN Penarik | GPIO4 | D2 |
| STEP Pemotong | GPIO12 | D6 |
| DIR Pemotong | GPIO14 | D5 |
| EN Pemotong | GPIO13 | D7 |

---

## 📖 Penggunaan

### Mulai Pemotongan
1. Buka Dashboard
2. Pastikan status: **Terhubung** (hijau)
3. Masukkan panjang potong (mm)
4. Masukkan jumlah potong
5. Klik **Mulai Pekerjaan**

### Hentikan Pemotongan
- Klik tombol **Stop** untuk menghentikan pekerjaan

### Ubah Konfigurasi
1. Buka menu **Pengaturan**
2. Ubah parameter sesuai kebutuhan
3. Klik **Simpan Pengaturan**
4. Konfigurasi akan diterapkan pada job selanjutnya

---

## 🔌 API Endpoints

### Status & Monitoring
| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/status.php` | GET | Status mesin dan job aktif |
| `/api/check_connection.php` | GET | Cek koneksi ke ESP8266 |

### Kontrol Mesin
| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/start.php` | POST | Mulai pekerjaan baru |
| `/api/stop.php` | POST | Hentikan pekerjaan |
| `/api/complete_job.php` | POST | Tandai job selesai |

### Konfigurasi
| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/config.php` | GET | Ambil konfigurasi |
| `/api/config.php` | POST | Simpan konfigurasi |
| `/api/get_config.php` | GET | Konfigurasi untuk ESP8266 |

### ESP8266 Endpoints
| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `http://192.168.4.1/` | GET | Status ESP8266 |
| `http://192.168.4.1/start` | GET | Mulai job |
| `http://192.168.4.1/stop` | GET | Stop job |
| `http://192.168.4.1/status` | GET | Status detail |

---

## 📁 Struktur Folder

```
pemotongKertas/
├── api/                    # PHP API endpoints
│   ├── check_connection.php
│   ├── complete_job.php
│   ├── config.php
│   ├── get_config.php
│   ├── progress.php
│   ├── start.php
│   ├── status.php
│   └── stop.php
├── assets/
│   ├── css/
│   │   └── dashboardkit.css
│   ├── js/
│   │   ├── dashboard-react.js
│   │   └── dashboardkit.js
│   └── vendor/             # Library offline
│       ├── bootstrap/
│       ├── bootstrap-icons/
│       └── react/
├── config/
│   ├── auth.php
│   ├── database.php
│   └── functions.php
├── database/
│   └── schema.sql
├── docs/
│   ├── esp8266/
│   │   └── TROUBLESHOOTING.md
│   └── UPDATE_LOG.md
├── ESP8266/
│   └── pemotong_kertas.ino
├── dashboard.php           # Halaman utama
├── login.php
├── logout.php
├── settings.php            # Halaman pengaturan
├── log.php                 # Log pekerjaan
└── README.md
```

---

## 🔧 Troubleshooting

### ESP8266 tidak terdeteksi
1. Pastikan laptop terhubung ke WiFi: `Mesin_Potong_Kertas`
2. Ping ESP: `ping 192.168.4.1`
3. Cek Serial Monitor untuk error

### Dashboard tidak bisa start job
1. Pastikan status: **Terhubung**
2. Cek IP mesin di Pengaturan
3. Buka `test_simple.php` untuk debug

### Motor tidak bergerak
1. Cek wiring pin sesuai tabel
2. Pastikan power supply menyala
3. Cek Serial Monitor untuk log

### Modal shadow tidak hilang
- Refresh halaman (Ctrl+F5)
- Clear browser cache

Untuk troubleshooting lengkap, lihat: [docs/esp8266/TROUBLESHOOTING.md](docs/esp8266/TROUBLESHOOTING.md)

---

## 🤝 Kontribusi

Kontribusi sangat diterima! Silakan:

1. Fork repository
2. Buat branch fitur: `git checkout -b fitur-baru`
3. Commit: `git commit -m 'Menambahkan fitur baru'`
4. Push: `git push origin fitur-baru`
5. Buat Pull Request

---

## 📄 Lisensi

MIT License - Bebas digunakan untuk keperluan pribadi maupun komersial.

---

## 👨‍💻 Author

**Paper Cutting Machine Project**

- 📧 Email: [yanuar.andre28@gmail.com]
- 🌐 GitHub: [github.com/andretantri]

---

## 🙏 Credits

- [ESP8266 Arduino Core](https://github.com/esp8266/Arduino)
- [Bootstrap 5](https://getbootstrap.com/)
- [React](https://reactjs.org/)
- [Bootstrap Icons](https://icons.getbootstrap.com/)
- [ArduinoJson](https://arduinojson.org/)
