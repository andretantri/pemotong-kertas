# Konfigurasi WiFi dan Server - ESP8266 Pemotong Kertas

## 📝 Petunjuk Konfigurasi

### 1. Konfigurasi WiFi

Buka file `pemotong_kertas.ino` dan ubah bagian berikut sesuai dengan jaringan WiFi Anda:

```cpp
// ================= WIFI CONFIGURATION =================
const char* ssid = "NAMA_WIFI_ANDA";           // ← GANTI INI
const char* password = "PASSWORD_WIFI_ANDA";    // ← GANTI INI
const char* serverURL = "http://192.168.1.100/pemotongKertas/api"; // ← GANTI INI
```

### 2. Cara Mencari IP Server

#### Windows (Laragon):
1. Buka Command Prompt (CMD)
2. Ketik: `ipconfig`
3. Cari bagian **IPv4 Address** pada adapter WiFi yang aktif
4. Contoh output:
   ```
   Wireless LAN adapter Wi-Fi:
      IPv4 Address. . . . . . . . . . . : 192.168.1.7
   ```
5. IP Server Anda adalah: `192.168.1.7`

#### Verify Server URL:
Setelah mendapat IP, URL server lengkap adalah:
```
http://[IP_ANDA]/pemotongKertas/api
```

Contoh:
```
http://192.168.1.7/pemotongKertas/api
```

### 3. Contoh Konfigurasi Lengkap

```cpp
// Contoh 1: WiFi Rumah
const char* ssid = "Yanto Fams";
const char* password = "06072618";
const char* serverURL = "http://192.168.18.7/pemotongKertas/api";

// Contoh 2: WiFi Kantor
const char* ssid = "OFFICE-WIFI";
const char* password = "SecurePass123";
const char* serverURL = "http://10.0.0.50/pemotongKertas/api";

// Contoh 3: Hotspot HP
const char* ssid = "Hotspot-Android";
const char* password = "12345678";
const char* serverURL = "http://192.168.43.1/pemotongKertas/api";
```

---

## 🔧 Konfigurasi Database (PHP)

File konfigurasi database sudah ada di: `c:\laragon\www\pemotongKertas\config\database.php`

### Update IP ESP8266 di Database Config

Setelah ESP8266 terhubung ke WiFi, catat IP Address yang muncul di Serial Monitor, lalu update di file `config/database.php`:

```php
// ESP32 Configuration
define('ESP32_IP', '192.168.18.41');  // ← GANTI dengan IP ESP8266 Anda
define('ESP32_TIMEOUT', 5); // detik
```

**Cara mendapatkan IP ESP8266:**
1. Upload sketch ke ESP8266
2. Buka Serial Monitor (115200 baud)
3. Tunggu sampai WiFi connected
4. IP Address akan ditampilkan, contoh:
   ```
   ========================================
     IP ADDRESS: 192.168.1.150
     Signal Strength: -45 dBm
   ========================================
   ```
5. Copy IP tersebut ke `database.php`

---

## 🚀 Cara Upload Sketch ke ESP8266

### Persiapan Arduino IDE

1. **Install Board ESP8266:**
   - Buka Arduino IDE
   - File → Preferences
   - Additional Board Manager URLs, tambahkan:
     ```
     http://arduino.esp8266.com/stable/package_esp8266com_index.json
     ```
   - Tools → Board → Board Manager
   - Cari "ESP8266" dan install

2. **Install Library yang Dibutuhkan:**
   - Sketch → Include Library → Manage Libraries
   - Install library berikut:
     - **ArduinoJson** (by Benoit Blanchon) - versi 6.x
     - **ESP8266WiFi** (sudah termasuk di board package)
     - **ESP8266HTTPClient** (sudah termasuk di board package)
     - **ESP8266WebServer** (sudah termasuk di board package)

3. **Pilih Board:**
   - Tools → Board → ESP8266 Boards
   - Pilih board Anda:
     - **NodeMCU 1.0 (ESP-12E Module)** - untuk NodeMCU
     - **LOLIN(WEMOS) D1 R2 & mini** - untuk Wemos D1 Mini

4. **Setting Board:**
   ```
   Board: "NodeMCU 1.0 (ESP-12E Module)"
   Upload Speed: "115200"
   CPU Frequency: "80 MHz"
   Flash Size: "4MB (FS:2MB OTA:~1019KB)"
   Port: [Pilih port COM yang terdeteksi]
   ```

### Upload Sketch

1. Hubungkan ESP8266 ke komputer via USB
2. Buka file `pemotong_kertas.ino`
3. **PENTING:** Edit konfigurasi WiFi terlebih dahulu!
4. Klik tombol Upload (→)
5. Tunggu sampai selesai
6. Buka Serial Monitor (115200 baud) untuk melihat status

---

## 🧪 Testing Koneksi

### 1. Test ESP8266 WiFi Connection

Setelah upload, buka Serial Monitor dan pastikan muncul:
```
✓ WiFi connected successfully!
========================================
  IP ADDRESS: 192.168.x.x
  Signal Strength: -XX dBm
========================================
✓ HTTP Server started on port 80
```

### 2. Test Web Server ESP8266

Buka browser dan akses:
```
http://[IP_ESP8266]/
```

Contoh: `http://192.168.1.150/`

Anda akan melihat halaman status dengan informasi:
- Status: READY / RUNNING
- IP Address
- Cut Count
- Endpoints tersedia

### 3. Test API Connection

Buka browser dan akses:
```
http://[IP_ESP8266]/status
```

Response yang benar:
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

### 4. Test Start Job

Via browser atau Postman:
```
http://[IP_ESP8266]/start?panjang=100&jumlah=5&job_id=1
```

Response:
```json
{
  "success": true,
  "message": "Job started",
  "data": {
    "panjang": 100,
    "jumlah": 5,
    "job_id": 1
  }
}
```

### 5. Test Integration dengan PHP API

Dari dashboard PHP, klik tombol "Start" dan pastikan:
- ESP8266 menerima perintah
- Motor bergerak sesuai konfigurasi
- Progress terupdate di dashboard

---

## 📊 Monitoring & Debugging

### Serial Monitor Output

Output normal saat berjalan:
```
========== POTONG KE-1 ==========
FASE 1: Penarik maju
>>> Pull forward: 320 steps
.......... Done
FASE 2: Penarik mundur
<<< Pull backward: 320 steps
.......... Done
FASE 3: Pemotong maju
>>> Cut forward: 320 steps
.......... Done
FASE 4: Pemotong mundur
<<< Cut backward: 320 steps
.......... Done
========== SELESAI KE-1 ==========
Progress updated: 1/5
```

### Common Issues

| Problem | Cause | Solution |
|---------|-------|----------|
| WiFi failed to connect | SSID/Password salah | Double-check konfigurasi WiFi |
| Config not loaded | Server URL salah | Verifikasi IP server benar |
| Motor tidak bergerak | Pin tidak terhubung | Cek wiring sesuai ROUTING_PINOUT.md |
| ESP8266 restart terus | Power tidak cukup | Gunakan USB power yang cukup (min 500mA) |
| HTTP error 404 | API endpoint salah | Pastikan file API ada di folder yang benar |

---

## 🔄 Update Configuration Tanpa Re-upload

ESP8266 mengambil konfigurasi dari server setiap kali:
- Boot up / restart
- Manual reload (tambahkan endpoint jika perlu)

Untuk update konfigurasi tanpa upload ulang:
1. Login ke dashboard PHP
2. Settings → Machine Configuration
3. Ubah nilai yang diinginkan
4. Restart ESP8266 (tekan tombol RESET)
5. ESP8266 akan download konfigurasi terbaru

---

## 📡 Network Topology

```
┌─────────────────┐
│   WiFi Router   │
│  192.168.1.1    │
└────────┬────────┘
         │
    ┌────┴─────────────────────┐
    │                          │
┌───▼──────────┐      ┌────────▼──────┐
│  PC/Laptop   │      │   ESP8266     │
│  (Server PHP)│      │  (Controller) │
│ 192.168.1.7  │      │ 192.168.1.150 │
│              │      │               │
│  Laragon     │◄─────┤  HTTP Client  │
│  Apache+MySQL│      │  Web Server   │
└──────────────┘      └───────┬───────┘
                              │
                     ┌────────▼────────┐
                     │  A4988 Drivers  │
                     │  Stepper Motors │
                     └─────────────────┘
```

---

## 📞 Support Information

**Version:** 1.0.0  
**Last Updated:** 20 Januari 2026  
**Compatible:** ESP8266 (NodeMCU, Wemos D1 Mini)  
**Firmware:** Arduino Core for ESP8266

**Dokumentasi Tambahan:**
- [ROUTING_PINOUT.md](ROUTING_PINOUT.md) - Detail koneksi pin
- [../HARDWARE_CONNECTIONS.md](../HARDWARE_CONNECTIONS.md) - Panduan hardware (ESP32, reference)
- [../API_REFERENCE.md](../API_REFERENCE.md) - Dokumentasi API endpoint
