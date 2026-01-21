# Setup Guide - Pemakaian Sistem Penarik Kertas Otomatis

## 📋 Daftar Isi
1. [Persiapan Database](#persiapan-database)
2. [Konfigurasi ESP32](#konfigurasi-esp32)
3. [API Endpoints](#api-endpoints)
4. [Cara Kerja Sistem](#cara-kerja-sistem)
5. [Pengaturan Mesin](#pengaturan-mesin)

---

## 🔧 Persiapan Database

### 1. Buat Database
```sql
mysql -u root -p < database/schema.sql
```

### 2. Tabel yang Dibuat:

#### `users`
- Menyimpan data admin/user

#### `machine_config`
- **roller_diameter_mm**: Diameter roller (default 17mm)
- **pull_distance_cm**: Jarak penarik dalam cm setiap siklus
- **pull_delay_ms**: Delay saat penarik bergerak
- **cut_delay_ms**: Delay saat pemotong bergerak
- **step_delay_us**: Delay antar step motor (default 1200 µs)
- **pull_pause_ms**: Pause setelah penarik selesai (default 1000 ms)
- **cut_pause_ms**: Pause setelah pemotong selesai (default 2000 ms)

#### `job_potong`
- Menyimpan data pekerjaan potong yang dibuat

#### `log_potong`
- Menyimpan log detail setiap potong

---

## 🎛️ Konfigurasi ESP32

### 1. Edit WiFi & Server Settings di `fix.ino`
```cpp
// Line 24-26
const char* ssid = "YOUR_SSID";
const char* password = "YOUR_PASSWORD";
const char* serverURL = "http://192.168.x.x/pemotongKertas/api";
```

### 2. Upload ke ESP32
- Gunakan Arduino IDE atau VS Code + PlatformIO
- Board: ESP32 Dev Module
- Baud Rate: 115200

### 3. Instalasi Library
```
- WiFi.h (built-in)
- HTTPClient.h (built-in)
- ArduinoJson.h (version 6.x atau 7.x)
```

---

## 📡 API Endpoints

### 1. **GET `/api/get_config.php`**
Mengambil konfigurasi mesin dari database

**Response:**
```json
{
  "success": true,
  "config": {
    "roller_diameter_mm": 17,
    "pull_distance_cm": 5,
    "pull_steps": 320,
    "step_delay_us": 1200,
    "pull_delay_ms": 500,
    "cut_delay_ms": 500,
    "pull_pause_ms": 1000,
    "cut_pause_ms": 2000
  }
}
```

### 2. **POST `/api/config.php`**
Mengubah konfigurasi mesin (memerlukan login)

**Request Body:**
```json
{
  "roller_diameter_mm": 17,
  "pull_distance_cm": 5,
  "pull_delay_ms": 500,
  "cut_delay_ms": 500,
  "step_delay_us": 1200,
  "pull_pause_ms": 1000,
  "cut_pause_ms": 2000
}
```

### 3. **POST `/api/esp32_start.php`**
Memulai pekerjaan potong (memerlukan login)

**Request Body:**
```json
{
  "panjang": 100,
  "jumlah_potong": 10
}
```

**Response:**
```json
{
  "success": true,
  "message": "Pekerjaan dimulai! Job ID: 1",
  "data": {
    "job_id": 1,
    "panjang_mm": 100,
    "jumlah_potong": 10,
    "config": { ... }
  }
}
```

### 4. **POST `/api/progress.php`**
Update progress dari ESP32 (tanpa autentikasi)

**Request Body:**
```json
{
  "job_id": 1,
  "potong_ke": 1,
  "status": "SUCCESS",
  "panjang_mm": 100
}
```

### 5. **GET `/api/stop.php`**
Menghentikan pekerjaan yang sedang berjalan (memerlukan login)

### 6. **GET `/api/status.php`**
Mendapatkan status mesin dan job (memerlukan login)

---

## 🔄 Cara Kerja Sistem

### Alur Saat Memulai Pekerjaan:

```
1. User Input di Dashboard
   ↓
2. Kirim POST ke /api/esp32_start.php
   ↓
3. Server buat record job di database
   ↓
4. Server update status ke RUNNING
   ↓
5. ESP32 Polling /api/get_config.php
   ↓
6. ESP32 Jalankan Siklus:
   a) Penarik Maju → Pause → Penarik Mundur
   b) Delay (pull_pause_ms)
   c) Pemotong Maju → Pause → Pemotong Mundur
   d) Delay (cut_pause_ms)
   ↓
7. ESP32 Kirim POST ke /api/progress.php
   ↓
8. Server update job progress
   ↓
9. Loop hingga jumlah potong tercapai
```

### Perhitungan Steps Motor:

```
Circumference (cm) = π × diameter_mm / 10
Steps per cm = 200 / circumference
Total Steps = pull_distance_cm × steps_per_cm

Contoh (roller 17mm):
Circumference = 3.14159 × 17 / 10 = 5.34 cm
Steps per cm = 200 / 5.34 = 37.45 steps/cm
Total Steps (5cm) = 5 × 37.45 = 187.25 ≈ 187 steps
```

---

## ⚙️ Pengaturan Mesin

### Akses Settings:
1. Login ke dashboard
2. Klik menu "Pengaturan"
3. Ubah parameter sesuai kebutuhan
4. Klik "Simpan Pengaturan"

### Parameter yang Dapat Diubah:

#### Konfigurasi Roller
- **Diameter Roller (mm)**: Range 1-100 mm
  - Digunakan untuk perhitungan steps motor
  - Default: 17mm (sesuai spesifikasi roller saat ini)

- **Jarak Tarik Kertas (cm)**: Range 1-50 cm
  - Berapa cm kertas ditarik setiap kali penarik bergerak
  - Disesuaikan dengan ukuran potong yang diinginkan

#### Kecepatan Motor
- **Step Delay Motor (µs)**: Range 500-5000 µs
  - Jeda waktu antar step motor
  - Nilai lebih besar = motor lebih lambat tetapi lebih stabil
  - Minimal 500µs untuk keamanan

#### Timing (ms)
- **Delay Penarik (ms)**: Delay saat penarik bergerak
- **Delay Pemotong (ms)**: Delay saat pemotong bergerak
- **Pause Setelah Penarik (ms)**: Waktu tunggu setelah penarik selesai sebelum pemotong mulai
- **Pause Setelah Pemotong (ms)**: Waktu tunggu setelah pemotong selesai sebelum loop berikutnya

---

## 📊 Monitoring & Logging

### Dashboard
- Menampilkan pekerjaan aktif
- Progress potong (jumlah selesai vs total)
- Status mesin (READY, RUNNING, DONE, ERROR)
- Statistik total pekerjaan dan potong

### Log Pekerjaan
- Menyimpan detail setiap potong
- Status setiap potong (SUCCESS, FAILED)
- Waktu eksekusi

---

## 🚨 Troubleshooting

### ESP32 Tidak Terhubung WiFi
1. Cek SSID dan password di `fix.ino`
2. Cek IP address router
3. Reset ESP32 dengan menekan tombol reset

### Motor Tidak Bergerak
1. Cek koneksi PIN ke stepper driver
2. Verifikasi step delay tidak terlalu besar
3. Lihat serial monitor untuk error log

### Potong Tidak Sesuai
1. Ubah `pull_distance_cm` di settings
2. Cek diameter roller yang digunakan
3. Atur `step_delay_us` untuk kecepatan optimal

---

## 📝 Catatan Penting

1. **Roller 17mm** adalah default yang sudah dikonfigurasi
2. Setiap perubahan diameter roller akan mempengaruhi perhitungan steps otomatis
3. Step delay minimal 500µs untuk mencegah overload motor
4. Pause times disesuaikan untuk memberikan waktu motor cooling dan transisi antar fase
5. Semua parameter bisa diubah via web interface tanpa perlu re-upload ESP32

---

## 🔐 Security Notes

- API `/api/progress.php` tidak memerlukan autentikasi (untuk POST dari ESP32)
- API `/api/get_config.php` tidak memerlukan autentikasi (untuk GET dari ESP32)
- API lainnya memerlukan login terlebih dahulu
- Ubah default admin password sebelum production

