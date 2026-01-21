# 📦 Ringkasan Lengkap - Integrasi ESP8266 dengan Sistem Pemotong Kertas

**Tanggal:** 20 Januari 2026  
**Versi:** 1.0.0  
**Status:** ✅ Selesai dan Siap Digunakan

---

## 🎯 Apa yang Sudah Dibuat?

Saya telah berhasil mengadaptasi sistem pemotong kertas dari ESP32 ke **ESP8266** dengan penyesuaian routing kabel dan integrasi penuh dengan API yang sudah ada. Berikut file-file yang telah dibuat:

### ✅ File yang Dibuat

| No | File | Keterangan |
|----|------|------------|
| 1 | **routing** | Quick reference pin mapping (updated) |
| 2 | **ROUTING_PINOUT.md** | Dokumentasi lengkap koneksi hardware ESP8266 |
| 3 | **pemotong_kertas.ino** | Arduino sketch utama untuk ESP8266 |
| 4 | **WIFI_CONFIG.md** | Panduan konfigurasi WiFi dan upload |
| 5 | **README.md** | Dokumentasi lengkap folder ESP8266 |
| 6 | **config_template.ino** | Template konfigurasi WiFi dengan contoh |

### ✅ File yang Diupdate

| No | File | Perubahan |
|----|------|-----------|
| 1 | **config/database.php** | Ditambahkan konfigurasi ESP8266_IP |

---

## 🔌 Routing Kabel ESP8266

### Pin NodeMCU ke A4988 Driver

#### Motor Pemotong Kertas
```
┌─────────────┬──────┬────────────┬────────────────┐
│ NodeMCU Pin │ GPIO │ A4988 Pin  │ Fungsi         │
├─────────────┼──────┼────────────┼────────────────┤
│ D5          │ 14   │ DIR        │ Arah Putaran   │
│ D6          │ 12   │ STEP       │ Langkah Motor  │
│ D7          │ 13   │ ENABLE     │ Aktif/Nonaktif │
└─────────────┴──────┴────────────┴────────────────┘
```

#### Motor Penarik Kertas
```
┌─────────────┬──────┬────────────┬────────────────┐
│ NodeMCU Pin │ GPIO │ A4988 Pin  │ Fungsi         │
├─────────────┼──────┼────────────┼────────────────┤
│ D2          │ 4    │ ENABLE     │ Aktif/Nonaktif │
│ D3          │ 0    │ DIR        │ Arah Putaran   │
│ D4          │ 2    │ STEP       │ Langkah Motor  │
└─────────────┴──────┴────────────┴────────────────┘
```

### Power Connections
```
ESP8266 Power: 5V USB atau 3.3V regulated
A4988 Power:   12V DC (min 2A)
Ground:        Common GND (ESP8266 + A4988 + PSU)

⚠️ JANGAN sambungkan ESP8266 ke 12V langsung!
```

---

## 🔧 Cara Setup dan Menggunakan

### Langkah 1: Persiapan Hardware

1. **Sambungkan ESP8266 ke A4988** sesuai tabel routing di atas
2. **Hubungkan motor stepper** ke output A4988
3. **Sambungkan power:**
   - 12V DC ke VCC A4988
   - 5V USB ke ESP8266
   - Common ground semua komponen

📖 **Detail:** Lihat file `ESP8266/ROUTING_PINOUT.md`

---

### Langkah 2: Install Arduino IDE & Library

1. **Install Arduino IDE** (versi 1.8.x atau 2.x)

2. **Install ESP8266 Board:**
   - File → Preferences
   - Additional Board Manager URLs:
     ```
     http://arduino.esp8266.com/stable/package_esp8266com_index.json
     ```
   - Tools → Board Manager → Install "ESP8266"

3. **Install Library:**
   - **ArduinoJson** (v6.x) - via Library Manager
   - ESP8266WiFi ✅ (included)
   - ESP8266HTTPClient ✅ (included)
   - ESP8266WebServer ✅ (included)

📖 **Detail:** Lihat file `ESP8266/WIFI_CONFIG.md`

---

### Langkah 3: Konfigurasi WiFi

1. **Buka file:** `ESP8266/pemotong_kertas.ino`

2. **Edit baris 20-22:**
   ```cpp
   const char* ssid = "NAMA_WIFI_ANDA";        // ← Ganti
   const char* password = "PASSWORD_WIFI_ANDA"; // ← Ganti
   const char* serverURL = "http://192.168.1.7/pemotongKertas/api"; // ← Ganti IP
   ```

3. **Cara mendapatkan IP Server:**
   - Buka CMD (Command Prompt)
   - Ketik: `ipconfig`
   - Cari **IPv4 Address** pada adapter WiFi aktif
   - Contoh: `192.168.18.7`

4. **Lihat contoh konfigurasi** di file `ESP8266/config_template.ino`

---

### Langkah 4: Upload ke ESP8266

1. **Hubungkan ESP8266** via USB ke komputer
2. **Pilih Board:** Tools → NodeMCU 1.0 (ESP-12E Module)
3. **Pilih Port:** Port COM yang terdeteksi
4. **Upload Speed:** 115200
5. **Klik Upload** ▶️

---

### Langkah 5: Verifikasi

1. **Buka Serial Monitor** (115200 baud)
2. **Pastikan muncul:**
   ```
   ✓ WiFi connected successfully!
   ========================================
     IP ADDRESS: 192.168.1.150
     Signal Strength: -45 dBm
   ========================================
   ✓ Configuration loaded successfully!
   ```

3. **Catat IP Address ESP8266**

4. **Test via browser:** `http://[IP_ESP8266]/`
   - Anda akan lihat halaman status

5. **Test API:** `http://[IP_ESP8266]/status`
   - Harus return JSON dengan status "READY"

---

### Langkah 6: Update Konfigurasi PHP

1. **Buka file:** `config/database.php`

2. **Update IP ESP8266:**
   ```php
   define('ESP8266_IP', '192.168.1.150'); // ← IP ESP8266 dari step 5
   ```

3. **Restart Laragon/Apache**

---

### Langkah 7: Testing Integrasi

1. **Buka Dashboard Web** di browser:
   ```
   http://localhost/pemotongKertas/
   ```

2. **Login** dengan akun yang ada

3. **Test Start Job:**
   - Klik tombol "Start" di dashboard
   - Atau gunakan form cutting job
   - Motor harus bergerak sesuai konfigurasi

4. **Monitor:**
   - **Serial Monitor** → lihat log eksekusi
   - **Dashboard** → lihat progress update

---

## 🌐 API Endpoints ESP8266

ESP8266 menyediakan web server dengan endpoints:

### GET `/`
Halaman status web interface

### GET `/status`
Mendapatkan status mesin
```json
{
  "success": true,
  "status": "READY",
  "cut_count": 0,
  "total": 0,
  "job_id": 0,
  "ip": "192.168.1.150"
}
```

### GET `/start`
Memulai job pemotongan
```
http://[IP_ESP8266]/start?panjang=100&jumlah=10&job_id=5
```

### GET `/stop`
Menghentikan job yang sedang berjalan

---

## 🔄 Integrasi dengan API Server (PHP)

ESP8266 berkomunikasi dengan server PHP untuk:

### 1. Get Configuration
- **URL:** `GET /api/get_config.php`
- **Kapan:** Saat boot/restart
- **Dapat:** Konfigurasi mesin (diameter roller, jarak tarik, steps, delays)

### 2. Update Progress
- **URL:** `POST /api/progress.php`
- **Kapan:** Setiap selesai 1 potongan
- **Kirim:** Job ID, potong ke-berapa, status, panjang

### 3. Update Status
- **URL:** `POST /api/progress.php`
- **Kapan:** Job selesai atau dihentikan
- **Kirim:** Status final (DONE/STOPPED)

---

## 📊 Alur Kerja Sistem

```
┌─────────────┐          ┌──────────────┐          ┌─────────────┐
│   Browser   │          │  PHP Server  │          │   ESP8266   │
│  (User)     │          │  (Laragon)   │          │ (Controller)│
└──────┬──────┘          └──────┬───────┘          └──────┬──────┘
       │                        │                         │
       │ 1. Open Dashboard      │                         │
       ├───────────────────────>│                         │
       │                        │                         │
       │ 2. Start Job           │                         │
       ├───────────────────────>│                         │
       │                        │                         │
       │                        │ 3. Send Start Command   │
       │                        ├────────────────────────>│
       │                        │                         │
       │                        │ 4. Execute Cutting      │
       │                        │                         ├─── Motor
       │                        │                         │
       │                        │ 5. Update Progress      │
       │                        │<────────────────────────┤
       │                        │                         │
       │ 6. Show Progress       │                         │
       │<───────────────────────┤                         │
       │                        │                         │
       │                        │ 7. Job Complete         │
       │                        │<────────────────────────┤
       │                        │                         │
       │ 8. Show Complete       │                         │
       │<───────────────────────┤                         │
       │                        │                         │
```

---

## ⚙️ Perbedaan ESP32 vs ESP8266

| Aspek | ESP32 | ESP8266 |
|-------|-------|---------|
| **Pin GPIO** | GPIO 25,26,27,32,33,14 | GPIO 0,2,4,12,13,14 |
| **Pin Label** | Langsung GPIO number | D0-D8 (NodeMCU) |
| **Voltage** | 3.3V | 3.3V |
| **WiFi** | Dual-core, lebih stabil | Single-core |
| **Library** | ESP32... | ESP8266... |
| **Memori** | Lebih besar | Lebih kecil |
| **Harga** | Lebih mahal | Lebih murah |
| **Kecocokan** | Proyek kompleks | Proyek sederhana-medium |

Untuk proyek pemotong kertas ini, **ESP8266 sudah cukup** karena tidak perlu processing yang berat.

---

## 🐛 Troubleshooting

### ❌ WiFi tidak connect
**Penyebab:**
- SSID/password salah
- Jarak terlalu jauh dari router
- WiFi 5GHz (ESP8266 hanya support 2.4GHz)

**Solusi:**
- Double-check SSID dan password (case-sensitive!)
- Dekatkan ESP8266 ke router
- Pastikan WiFi 2.4GHz, bukan 5GHz
- Coba gunakan hotspot HP untuk testing

---

### ❌ Config tidak ter-load dari server
**Penyebab:**
- Server URL salah
- Laragon/Apache tidak running
- Firewall memblokir koneksi

**Solusi:**
- Verifikasi IP server benar (ipconfig)
- Pastikan Laragon running (lampu hijau)
- Test buka: `http://[IP]/pemotongKertas/api/get_config.php`
- Disable firewall sementara untuk testing

---

### ❌ Motor tidak bergerak
**Penyebab:**
- Kabel tidak terhubung dengan benar
- A4988 tidak mendapat power 12V
- VREF A4988 terlalu rendah
- Motor connector terbalik

**Solusi:**
- Cek semua koneksi sesuai routing table
- Ukur voltage 12V di VCC A4988
- Adjust VREF dengan obeng (putar perlahan)
- Swap motor coil jika perlu

---

### ❌ ESP8266 restart terus-menerus
**Penyebab:**
- Power supply tidak cukup
- USB cable rusak
- Ada short circuit
- Code error (watchdog timeout)

**Solusi:**
- Gunakan USB power min 500mA
- Ganti USB cable
- Cek tidak ada short di wiring
- Tambahkan `yield()` di loop yang panjang

---

### ❌ HTTP timeout
**Penyebab:**
- Server PHP terlalu lambat
- Network congestion
- Database query lambat

**Solusi:**
- Naikkan timeout di code (default 5s)
- Optimize database
- Restart router

---

## 📁 Struktur File

```
pemotongKertas/
├── ESP8266/                        ← FOLDER BARU
│   ├── README.md                   ← Dokumentasi utama
│   ├── routing                     ← Quick reference (updated)
│   ├── ROUTING_PINOUT.md          ← Detail koneksi hardware
│   ├── WIFI_CONFIG.md             ← Panduan WiFi & upload
│   ├── pemotong_kertas.ino        ← Arduino sketch utama
│   └── config_template.ino        ← Template konfigurasi
│
├── ESP32/                          ← Referensi ESP32
│   ├── fix.ino
│   ├── debug.ino
│   └── test.ino
│
├── api/                            ← API endpoints (existing)
│   ├── config.php
│   ├── get_config.php             ← Digunakan oleh ESP8266
│   ├── progress.php               ← Digunakan oleh ESP8266
│   ├── start.php
│   ├── stop.php
│   └── status.php
│
├── config/                         ← Konfigurasi (updated)
│   ├── database.php               ← Ditambahkan ESP8266_IP
│   ├── auth.php
│   └── functions.php
│
├── HARDWARE_CONNECTIONS.md        ← Referensi ESP32
├── API_REFERENCE.md               ← Dokumentasi API
└── ...
```

---

## ✅ Checklist Setup

Gunakan checklist ini untuk memastikan semuanya sudah benar:

### Hardware
- [ ] ESP8266 terhubung ke A4988 sesuai routing
- [ ] Motor stepper terhubung ke A4988
- [ ] Power 12V ke A4988
- [ ] Power 5V USB ke ESP8266
- [ ] Common ground semua komponen
- [ ] Tidak ada short circuit

### Software
- [ ] Arduino IDE terinstall
- [ ] ESP8266 board terinstall
- [ ] Library ArduinoJson terinstall
- [ ] WiFi SSID dan password sudah diisi
- [ ] Server URL sudah diisi dengan IP yang benar
- [ ] Sketch berhasil di-upload tanpa error

### Network
- [ ] ESP8266 dan PC di network yang sama
- [ ] WiFi connected (cek Serial Monitor)
- [ ] IP ESP8266 bisa diakses via browser
- [ ] Endpoint `/status` return JSON yang benar
- [ ] Laragon/Apache running
- [ ] API dapat diakses dari browser

### Database
- [ ] File `config/database.php` sudah update ESP8266_IP
- [ ] Database `hmi_pemotong_kertas` ada
- [ ] Table `machine_config` ada dan berisi data
- [ ] Apache sudah direstart

### Testing
- [ ] Dashboard web bisa dibuka
- [ ] Bisa login ke dashboard
- [ ] Start job dari dashboard berhasil
- [ ] Motor bergerak sesuai konfigurasi
- [ ] Progress update di dashboard
- [ ] Serial Monitor menunjukkan log yang benar

---

## 📚 Dokumentasi Lengkap

| File | Lokasi | Keterangan |
|------|--------|------------|
| **README.md** | ESP8266/ | Panduan lengkap ESP8266 |
| **ROUTING_PINOUT.md** | ESP8266/ | Detail koneksi hardware |
| **WIFI_CONFIG.md** | ESP8266/ | Panduan WiFi & upload |
| **config_template.ino** | ESP8266/ | Template konfigurasi |
| **routing** | ESP8266/ | Quick reference |
| **API_REFERENCE.md** | root | Dokumentasi API PHP |
| **HARDWARE_CONNECTIONS.md** | root | Referensi ESP32 |

---

## 🎓 Tips & Best Practices

### 1. Testing Bertahap
Jangan langsung test semua. Lakukan step-by-step:
1. Test WiFi connection dulu
2. Test API connection
3. Test hardware tanpa beban (motor tidak tersambung)
4. Test dengan motor
5. Test integrasi penuh

### 2. Monitoring
Selalu buka Serial Monitor saat testing untuk:
- Melihat log koneksi WiFi
- Melihat request/response API
- Debugging error

### 3. Safety
- Pastikan emergency stop tersedia
- Jangan tinggalkan mesin running tanpa pengawasan
- Disconnect power saat melakukan wiring

### 4. Backup Configuration
Simpan konfigurasi WiFi yang sudah benar di file terpisah untuk backup.

---

## 🔮 Pengembangan Selanjutnya

Fitur yang bisa ditambahkan di masa depan:

### 1. Security
- [ ] API key authentication
- [ ] Basic auth untuk web server
- [ ] HTTPS (jika perlu)

### 2. Features
- [ ] Manual control via web interface
- [ ] Resume job setelah power loss
- [ ] Multiple job queue
- [ ] Emergency stop via web

### 3. Monitoring
- [ ] Real-time sensor monitoring (jika ada sensor)
- [ ] Statistics dan analytics
- [ ] Email/notification saat job selesai

### 4. Optimization
- [ ] PID control untuk motor
- [ ] Adaptive speed berdasarkan beban
- [ ] Automatic calibration

---

## 📞 Support & Resources

### Dokumentasi Resmi
- **ESP8266 Arduino Core:** https://arduino-esp8266.readthedocs.io/
- **ArduinoJson:** https://arduinojson.org/
- **A4988 Datasheet:** https://www.pololu.com/product/1182

### Community
- **ESP8266 Forum:** https://www.esp8266.com/
- **Arduino Forum:** https://forum.arduino.cc/

---

## 📄 License & Credits

**Version:** 1.0.0  
**Date:** 20 Januari 2026  
**Platform:** ESP8266 (NodeMCU / Wemos D1 Mini)  
**License:** MIT

---

## 🎉 Selamat!

Sistem ESP8266 untuk pemotong kertas sudah siap digunakan! 

Jika ada pertanyaan atau masalah, silakan refer ke dokumentasi di folder ESP8266 atau lihat troubleshooting section di atas.

**Happy Making! 🚀**
