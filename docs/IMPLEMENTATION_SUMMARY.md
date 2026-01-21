# 📋 Ringkasan Perbaikan Sistem Penarik Kertas Otomatis

**Tanggal:** 2 Januari 2026  
**Version:** 2.0.0

---

## 🎯 Objective Tercapai

✅ Sistem penarik kertas dapat dikontrol via web interface  
✅ Konfigurasi roller (17mm) dan parameter mesin dapat diubah  
✅ Jumlah loop/potong dapat disetting dari aplikasi web  
✅ API terintegrasi dengan ESP32 untuk komunikasi real-time  
✅ Progress tracking dan logging lengkap  

---

## 📝 File-File Baru & Perubahan

### 1. **Database Schema** (`database/schema.sql`)
**Perubahan:** Menambah tabel `machine_config`

```sql
CREATE TABLE IF NOT EXISTS `machine_config` (
  id INT(11) PRIMARY KEY AUTO_INCREMENT,
  roller_diameter_mm INT(11) DEFAULT 17,
  pull_distance_cm INT(11) DEFAULT 5,
  pull_delay_ms INT(11) DEFAULT 500,
  cut_delay_ms INT(11) DEFAULT 500,
  step_delay_us INT(11) DEFAULT 1200,
  pull_pause_ms INT(11) DEFAULT 1000,
  cut_pause_ms INT(11) DEFAULT 2000
)
```

**Fungsi:** Menyimpan konfigurasi mesin yang dapat diubah via web interface

---

### 2. **ESP32 Firmware** (`ESP32/fix.ino`)
**Perubahan Komprehensif:**

#### Fitur Baru:
- ✅ WiFi connectivity
- ✅ HTTP client untuk komunikasi dengan server
- ✅ JSON parsing (ArduinoJson library)
- ✅ Automatic config loading dari server
- ✅ Loop control dengan parameter dari web
- ✅ Step calculation berdasarkan roller diameter
- ✅ Progress reporting ke server

#### Struktur Kode:
```cpp
struct MachineConfig {
  int roller_diameter_mm;
  int pull_distance_cm;
  int pull_steps;
  int pull_delay_ms;
  int cut_delay_ms;
  int step_delay_us;
  int pull_pause_ms;
  int cut_pause_ms;
};

struct JobParameters {
  int panjang_mm;
  int jumlah_potong;
};
```

#### Fungsi Utama:
- `loadMachineConfig()` - Load config dari server
- `executeCuttingCycle()` - Jalankan 1 siklus potong lengkap
- `pullForward()`, `pullBackward()` - Kontrol penarik
- `cutForward()`, `cutBackward()` - Kontrol pemotong
- `updateJobStatus()` - Kirim progress ke server

#### Loop/Cycle Execution:
```
Potong ke-N:
  1. Penarik maju (pull_steps) → delay (pull_delay_ms)
  2. Penarik mundur (pull_steps) → pause (pull_pause_ms)
  3. Pemotong maju (pull_steps) → delay (cut_delay_ms)
  4. Pemotong mundur (pull_steps) → pause (cut_pause_ms)
  5. Report progress ke server
  6. Loop hingga jumlah potong tercapai
```

---

### 3. **API Endpoints**

#### 📥 `api/get_config.php` (BARU)
```
GET /api/get_config.php
- Purpose: Ambil config dari database untuk ESP32
- Auth: Tidak diperlukan
- Response: JSON dengan semua parameter mesin + calculated pull_steps
```

#### 📤 `api/config.php` (BARU)
```
GET  /api/config.php  → Ambil config (untuk web)
POST /api/config.php  → Update config (admin only)
```

#### 📤 `api/esp32_start.php` (BARU/UPDATE)
```
POST /api/esp32_start.php
Parameters:
  - panjang (mm)
  - jumlah_potong (loop count)
Returns:
  - job_id
  - config untuk digunakan ESP32
```

#### ✅ `api/progress.php` (EXISTING/UPDATE)
```
POST /api/progress.php
- Menerima update dari ESP32 setiap potong selesai
- Update job progress di database
- No auth required (untuk POST dari ESP32)
```

#### 🛑 `api/stop.php` (EXISTING)
```
GET /api/stop.php
- Hentikan pekerjaan yang sedang berjalan
- Update status ke STOPPED
```

#### 📊 `api/status.php` (EXISTING)
```
GET /api/status.php
- Ambil status mesin dan current job
- Return stats, progress, recent jobs
```

---

### 4. **Web Interface** (`settings.php`) (BARU)

**Lokasi:** Dashboard → Settings  
**Fitur:**
- ✅ Form untuk edit semua parameter mesin
- ✅ Real-time calculation steps berdasarkan diameter roller
- ✅ Validation input dari user
- ✅ Help panel dengan penjelasan setiap parameter
- ✅ Info panel showing circumference calculation
- ✅ Save/Reset buttons
- ✅ Visual feedback (alerts)

**Parameter yang Bisa Diubah:**
1. **Roller Diameter (mm)** - 1-100mm
2. **Pull Distance (cm)** - 1-50cm  
3. **Step Delay (µs)** - 500-5000µs
4. **Pull Delay (ms)** - 100-5000ms
5. **Cut Delay (ms)** - 100-5000ms
6. **Pull Pause (ms)** - 100-5000ms
7. **Cut Pause (ms)** - 100-5000ms

---

### 5. **Dashboard Update** (`dashboard.php`)
**Perubahan:** Menambah link ke Settings page di sidebar

---

### 6. **Documentation** (BARU)

#### 📖 `SETUP_GUIDE.md`
Complete guide untuk:
- Database preparation
- ESP32 configuration & upload
- API endpoints overview
- Cara kerja sistem
- Pengaturan mesin
- Troubleshooting

#### 📖 `API_REFERENCE.md`
Dokumentasi lengkap:
- Semua API endpoints
- Request/response format
- Parameter validation
- cURL examples
- Complete workflow example
- Testing dengan Postman

---

## 🔧 Cara Kerja Sistem - Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                         WEB INTERFACE                        │
│                       (Dashboard)                            │
│                                                              │
│  1. User set parameters:                                    │
│     - Panjang potong (mm)                                   │
│     - Jumlah potong (loop count)                            │
│     - Via Settings page: roller diameter, delays, etc.      │
└─────────────────────────────────────────────────────────────┘
                           │
                           ↓
┌─────────────────────────────────────────────────────────────┐
│                    DATABASE (MySQL)                         │
│                                                              │
│  - machine_config: Konfigurasi mesin                       │
│  - job_potong: Pekerjaan yang dibuat                       │
│  - log_potong: Log detail setiap potong                    │
└─────────────────────────────────────────────────────────────┘
                           │
                           ↓
┌─────────────────────────────────────────────────────────────┐
│                         API SERVER                          │
│                     (PHP - Laravel like)                    │
│                                                              │
│  Endpoints:                                                 │
│  - GET  /api/get_config.php     → Get config               │
│  - POST /api/config.php         → Update config             │
│  - POST /api/esp32_start.php    → Start job                │
│  - GET  /api/stop.php           → Stop job                 │
│  - GET  /api/status.php         → Get status               │
│  - POST /api/progress.php       → Update progress          │
└─────────────────────────────────────────────────────────────┘
                           │
            ┌──────────────┴──────────────┐
            ↓                             ↓
    ┌──────────────┐        ┌────────────────────┐
    │   ESP32      │←─────→ │  External Server   │
    │              │ HTTP   │  (MySQL + PHP API) │
    │ Firmware:    │        │                    │
    │ fix.ino      │        │  • Ambil config    │
    │              │        │  • Report progress │
    └──────────────┘        │  • Update status   │
            │               └────────────────────┘
            │
    ┌───────┴───────┬───────────┐
    ↓               ↓           ↓
  PULL            CUT        LOGIC
  Motor         Motor       Control
   (PWM)        (PWM)       Circuit
    │             │
    └─────┬───────┘
          ↓
    ┌──────────────┐
    │ Stepper      │
    │ Motors A4988 │
    │ Drivers      │
    └──────────────┘
          │
    ┌─────┴─────┐
    ↓           ↓
  PULL        CUT
  Roller    Blade
```

---

## 📊 Perhitungan Steps Motor

**Formula:**
```
Circumference (cm) = π × diameter_mm / 10
Steps per cm = 200 / circumference
Total Steps = pull_distance_cm × steps_per_cm
```

**Contoh (Roller 17mm, Pull 5cm):**
```
Circumference = 3.14159 × 17 / 10 = 5.34 cm
Steps per cm = 200 / 5.34 = 37.45 steps/cm
Total Steps = 5 × 37.45 = 187.25 ≈ 187 steps
```

**Variasi Sesuai Kebutuhan:**
| Diameter (mm) | Circumference (cm) | Steps/cm | Steps (5cm) |
|---|---|---|---|
| 10 | 3.14 | 63.66 | 318 |
| 15 | 4.71 | 42.44 | 212 |
| **17** | **5.34** | **37.45** | **187** |
| 20 | 6.28 | 31.83 | 159 |
| 25 | 7.85 | 25.46 | 127 |

---

## ⚙️ Parameter Default

```
Roller Diameter: 17 mm
Pull Distance: 5 cm
Pull Steps: ~187 (calculated)

Timing:
- Step Delay: 1200 µs
- Pull Delay: 500 ms
- Cut Delay: 500 ms
- Pull Pause: 1000 ms (1 second)
- Cut Pause: 2000 ms (2 seconds)
```

**Penjelasan Timing:**
- **Step Delay**: Jeda antar step motor (lebih besar = lebih lambat)
- **Pull/Cut Delay**: Waktu yang diambil untuk maju/mundur
- **Pauses**: Waktu tunggu antar fase untuk motor cooling dan transisi

---

## 🔄 Job Execution Sequence

```
1. User start job via web
   POST /api/esp32_start.php
   └─ Create job in database (READY status)

2. Database create job record
   job_potong:
   - id: 1
   - panjang_mm: 100
   - jumlah_potong: 10
   - status: RUNNING
   - user_id: 1

3. ESP32 polling /api/get_config.php
   └─ Load config from database

4. ESP32 execute cutting loop (10x):
   
   Loop iteration 1:
   - Pull forward (187 steps)
   - Delay 500ms
   - Pull backward (187 steps)
   - Pause 1000ms
   - Cut forward (187 steps)
   - Delay 500ms
   - Cut backward (187 steps)
   - Pause 2000ms
   - POST /api/progress.php (potong_ke=1, status=SUCCESS)
   └─ Database update: potong_selesai=1
   
   Loop iteration 2-9:
   └─ Repeat same process
   
   Loop iteration 10:
   - Same process
   - POST /api/progress.php (is_complete=true)
   └─ Database update: status=DONE, completed_at=NOW()

5. Web UI polling /api/status.php
   └─ Update progress bar: 10/10 selesai ✓
```

---

## 🚀 Implementation Checklist

- [x] Database schema updated dengan machine_config table
- [x] API endpoint untuk ambil config dari database
- [x] API endpoint untuk update config dari web
- [x] ESP32 firmware rewrite dengan fitur lengkap
- [x] WiFi & HTTP client implementation
- [x] JSON config parsing
- [x] Auto step calculation based on roller diameter
- [x] Loop control dengan parameter dari web
- [x] Progress reporting mechanism
- [x] Web interface untuk settings
- [x] Form validation
- [x] Real-time calculation display
- [x] Error handling & alerts
- [x] Complete documentation

---

## 📚 Dokumentasi Lengkap

1. **SETUP_GUIDE.md** - Panduan setup sistem
2. **API_REFERENCE.md** - Referensi semua API endpoints
3. **ARCHITECTURE.md** - (Existing) Arsitektur sistem
4. **README.md** - (Existing) Overview project

---

## 🔐 Security Considerations

1. **API Authentication:**
   - ✅ `/api/get_config.php` - Public (untuk ESP32)
   - ✅ `/api/progress.php` - Public (POST dari ESP32)
   - ✅ `/api/config.php` - Protected (login required)
   - ✅ `/api/esp32_start.php` - Protected (login required)

2. **Default Credentials:**
   - Username: `admin`
   - Password: `admin123`
   - ⚠️ **CHANGE BEFORE PRODUCTION!**

3. **Network Security:**
   - HTTPS recommended untuk production
   - Implement CORS jika dibutuhkan
   - Rate limiting recommended

---

## 🧪 Testing Checklist

- [ ] Database schema installed successfully
- [ ] API /api/get_config.php returns config
- [ ] Web interface Settings page loaded correctly
- [ ] Can update config via Settings page
- [ ] ESP32 connects to WiFi
- [ ] ESP32 loads config from server
- [ ] Start job creates record in database
- [ ] Motors execute cutting cycle correctly
- [ ] Progress updates received from ESP32
- [ ] Job completes and status updates to DONE
- [ ] Log entries created for each cut
- [ ] Stop job works correctly
- [ ] Settings changes reflected in next job

---

## 📱 Quick Start

### 1. Setup Database
```bash
mysql -u root -p < database/schema.sql
```

### 2. Configure ESP32
Edit `ESP32/fix.ino`:
```cpp
const char* ssid = "YOUR_SSID";
const char* password = "YOUR_PASSWORD";
const char* serverURL = "http://192.168.x.x/pemotongKertas/api";
```

### 3. Upload to ESP32
- Gunakan Arduino IDE atau PlatformIO
- Board: ESP32 Dev Module
- Baud Rate: 115200

### 4. Access Web Interface
- URL: `http://192.168.x.x/pemotongKertas/`
- Login: admin / admin123

### 5. Configure Machine (Optional)
- Dashboard → Pengaturan
- Adjust roller diameter, delays, etc.
- Save changes

### 6. Start Cutting
- Dashboard → Start Job
- Enter panjang (mm) dan jumlah potong
- Monitor progress in real-time

---

## 📞 Support & Maintenance

Untuk pertanyaan atau masalah:
1. Cek SETUP_GUIDE.md → Troubleshooting section
2. Cek API_REFERENCE.md → Error codes
3. Check ESP32 serial monitor untuk debug logs
4. Check database records untuk job history

---

**Status:** ✅ READY FOR PRODUCTION (dengan testing)  
**Last Updated:** 2 Januari 2026

