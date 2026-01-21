# ⚡ QUICK START GUIDE - Pemakaian Kertas Otomatis v2.0

## 🚀 5 Menit Setup

### ✅ Langkah 1: Database (2 menit)
```bash
# Jalankan script database
mysql -u root -p < database/schema.sql

# Verifikasi
mysql -u root -p -e "USE hmi_pemotongKertas; SHOW TABLES;"
```

**Expected Output:**
```
+------------------------+
| Tables_in_hmi_pemotong_kertas |
+------------------------+
| job_potong             |
| log_potong             |
| machine_config         |
| users                  |
+------------------------+
```

---

### ✅ Langkah 2: ESP32 Firmware (2 menit)

1. **Edit `/ESP32/fix.ino` (baris 24-26):**
   ```cpp
   const char* ssid = "YOUR_WIFI_SSID";
   const char* password = "YOUR_WIFI_PASSWORD";
   const char* serverURL = "http://192.168.1.100/pemotongKertas/api";
   // Ganti 192.168.1.100 dengan IP Anda
   ```

2. **Upload ke ESP32:**
   ```
   Arduino IDE:
   - Tools → Board → esp32 → ESP32 Dev Module
   - Tools → Port → COM3 (sesuaikan)
   - Sketch → Upload (Ctrl+U)
   - Tunggu "Hard resetting via RTS pin"
   ```

3. **Buka Serial Monitor:**
   ```
   Tools → Serial Monitor (115200 baud)
   Tunggu muncul: "WiFi connected!" dan "Config loaded successfully!"
   ```

---

### ✅ Langkah 3: Login (1 menit)

1. **Buka browser:** `http://192.168.1.100/pemotongKertas/`

2. **Login dengan:**
   - Username: `admin`
   - Password: `admin123`

3. **Anda akan masuk ke Dashboard**

---

## 📊 Dashboard Overview

```
┌─────────────────────────────────────────────────────┐
│  Dashboard - Pemakaian Kertas Otomatis              │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Status Mesin: READY                               │
│  ┌───────────────────────────────────────────────┐ │
│  │ Pekerjaan Saat Ini: Tidak ada                 │ │
│  │ Progress: 0/0 potong                          │ │
│  └───────────────────────────────────────────────┘ │
│                                                     │
│  ┌─── START JOB ─────────────────────────────────┐ │
│  │ Panjang (mm): [100  ]                         │ │
│  │ Jumlah Potong: [10  ]                         │ │
│  │ [START POTONG] [STOP]                         │ │
│  └───────────────────────────────────────────────┘ │
│                                                     │
│  Statistik:                                         │
│  • Total Pekerjaan: 0                              │
│  • Selesai: 0                                       │
│  • Berjalan: 0                                      │
│  • Total Potong: 0                                  │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

## 🎛️ Settings Page

```
┌─────────────────────────────────────────────────────┐
│  Pengaturan Mesin                                   │
├─────────────────────────────────────────────────────┤
│                                                     │
│  KONFIGURASI ROLLER                                │
│  ┌─────────────────────────────────────────────┐  │
│  │ Diameter Roller (mm): [17  ]                │  │
│  │ Jarak Tarik Kertas (cm): [5  ]              │  │
│  │ Total Steps: 187                            │  │
│  └─────────────────────────────────────────────┘  │
│                                                     │
│  KECEPATAN MOTOR                                    │
│  ┌─────────────────────────────────────────────┐  │
│  │ Step Delay (µs): [1200]                     │  │
│  └─────────────────────────────────────────────┘  │
│                                                     │
│  TIMING (ms)                                        │
│  ┌─────────────────────────────────────────────┐  │
│  │ Delay Penarik (ms): [500  ]                 │  │
│  │ Delay Pemotong (ms): [500  ]                │  │
│  │ Pause Penarik (ms): [1000 ]                 │  │
│  │ Pause Pemotong (ms): [2000 ]                │  │
│  └─────────────────────────────────────────────┘  │
│                                                     │
│  [SIMPAN] [RESET]                                   │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

## 📝 Cara Mulai Potong

### Metode 1: Via Web Interface (Paling Mudah)

1. **Login ke Dashboard**
   ```
   URL: http://192.168.1.100/pemotongKertas/
   Username: admin
   Password: admin123
   ```

2. **Isi form "START JOB"**
   ```
   Panjang (mm): 100      ← sesuaikan ukuran potong
   Jumlah Potong: 10      ← berapa kali potong
   ```

3. **Klik "START POTONG"**
   - Sistem akan membuat job baru di database
   - Status berubah menjadi RUNNING
   - Motor akan mulai bergerak

4. **Monitor Progress**
   - Lihat progress bar di dashboard
   - Serial monitor ESP32 menunjukkan urutan potong
   - Saat selesai, status berubah menjadi DONE

5. **View Log**
   - Klik menu "Log Pekerjaan"
   - Lihat detail setiap potong yang dilakukan

---

## 🔧 Konfigurasi (Opsional)

### Mengubah Roller Diameter
*Jika menggunakan roller bukan 17mm:*

1. **Buka Pengaturan**
   - Dashboard → Pengaturan

2. **Ubah "Diameter Roller (mm)"**
   - Contoh: untuk roller 20mm, isi 20
   - Steps akan recalculated otomatis

3. **Klik "SIMPAN"**
   - Perubahan langsung berlaku untuk job berikutnya

### Mengubah Kecepatan Potong
*Jika ingin motor lebih cepat/lambat:*

1. **Buka Pengaturan**

2. **Ubah "Step Delay Motor (µs)"**
   - Lebih besar = lebih lambat (aman)
   - Lebih kecil = lebih cepat (tapi kurang stabil)
   - Range aman: 800-2000 µs

3. **Klik "SIMPAN"**

---

## ⚠️ Emergency Stop

### Jika Ada Masalah Saat Berjalan:

1. **Via Web (Recommended)**
   ```
   Dashboard → Klik tombol "STOP"
   Motor akan langsung berhenti
   ```

2. **Via Power**
   ```
   Lepas kabel power ESP32 dari USB
   Atau matikan power supply
   ⚠️ Gunakan ini jika tombol STOP tidak merespons
   ```

3. **Mengecek Status**
   ```
   Serial Monitor → Lihat pesan terakhir
   Dashboard → Refresh page (F5)
   Lihat job status
   ```

---

## 📊 Monitoring Progress

### Real-time Monitoring
```bash
# Terminal 1: Monitor serial ESP32
Arduino IDE → Tools → Serial Monitor

# Terminal 2: Monitor database
mysql -u root -p hmi_pemotongKertas
mysql> SELECT * FROM job_potong WHERE status='RUNNING'\G
mysql> SELECT COUNT(*) FROM log_potong WHERE job_id=1;
```

### Expected Output Saat Berjalan
```
[Serial Monitor - ESP32]
Pull forward: 187 steps
<<< Pull backward: 187 steps
>>> Cut forward: 187 steps
<<< Cut backward: 187 steps
========== SELESAI KE-1 ==========
========== POTONG KE-2 ==========
...
```

---

## 🐛 Troubleshooting Cepat

| Masalah | Penyebab | Solusi |
|---------|----------|--------|
| **ESP32 tidak terhubung WiFi** | SSID/Password salah | Edit fix.ino, ulang upload |
| **Motor tidak bergerak** | Pin tidak terhubung | Cek koneksi GPIO & A4988 |
| **Potong tidak sesuai ukuran** | Roller diameter salah | Ubah di Pengaturan |
| **Motor bergerak lambat** | Step delay terlalu besar | Ubah step delay di Pengaturan |
| **Login gagal** | Password salah | Reset password via reset_password.php |
| **Tidak bisa stop** | Job sudah complete | Refresh page untuk update status |

---

## 📚 File-File Penting

```
pemotongKertas/
├── dashboard.php ..................... Main interface
├── settings.php ...................... Pengaturan mesin
├── login.php ......................... Login page
├── log.php ........................... Riwayat pekerjaan
│
├── api/
│   ├── get_config.php ............... Ambil config (ESP32)
│   ├── config.php ................... Ubah config (Web)
│   ├── esp32_start.php .............. Mulai job
│   ├── stop.php ..................... Stop job
│   ├── progress.php ................. Update progress
│   └── status.php ................... Cek status
│
├── config/
│   ├── database.php ................. Database config
│   ├── auth.php ..................... Authentication
│   └── functions.php ................ Helper functions
│
├── database/
│   └── schema.sql ................... Database schema
│
├── ESP32/
│   └── fix.ino ...................... ESP32 firmware
│
└── DOCUMENTATION/
    ├── SETUP_GUIDE.md ............... Panduan lengkap
    ├── API_REFERENCE.md ............. Referensi API
    ├── HARDWARE_CONNECTIONS.md ...... Koneksi hardware
    ├── DEPLOYMENT_CHECKLIST.md ...... Pre-deployment
    └── QUICK_START.md ............... File ini
```

---

## 🔐 Default Credentials

```
Username: admin
Password: admin123

⚠️ GANTI PASSWORD SEBELUM PRODUCTION!
```

**Cara ganti password:**
1. Jalankan: `php reset_password.php`
2. Ikuti instruksi di layar
3. Login dengan password baru

---

## 🌐 Network Configuration

### Sebelum Mulai

**Cari IP Address Komputer:**
```bash
Windows (Command Prompt):
ipconfig

Linux/Mac (Terminal):
ifconfig
```

**Cari IP ESP32:**
- Buka Serial Monitor di Arduino IDE
- Tunggu muncul: "IP address: 192.168.x.x"

### Contoh Setup

```
Komputer (Server):     192.168.1.100
ESP32 (Client):        192.168.1.101 (auto-assigned)
WiFi Router:           192.168.1.1
```

---

## ✅ Verification Checklist

Sebelum production, pastikan:

- [ ] Database created dengan semua tabel
- [ ] PHP API endpoints bisa diakses
- [ ] ESP32 firmware uploaded tanpa error
- [ ] WiFi connection successful di ESP32
- [ ] Config loaded dari server di startup
- [ ] Motor bergerak forward-backward
- [ ] Job bisa dibuat dari web interface
- [ ] Progress tracking berfungsi
- [ ] Database records terupdate dengan benar
- [ ] Stop button menghentikan motor

---

## 📞 Support Resources

1. **Untuk Setup Issues:**
   → Baca SETUP_GUIDE.md

2. **Untuk API Questions:**
   → Baca API_REFERENCE.md

3. **Untuk Hardware Problems:**
   → Baca HARDWARE_CONNECTIONS.md

4. **Untuk Pre-Deployment:**
   → Ikuti DEPLOYMENT_CHECKLIST.md

5. **Untuk Error Messages:**
   → Check database logs & serial monitor

---

## 🎯 Typical Usage Workflow

```
1. POWER ON
   └─ ESP32 boots
   └─ Connects to WiFi
   └─ Loads config from server
   └─ Waits for commands

2. LOGIN
   └─ Open browser → http://192.168.x.x/pemotongKertas
   └─ admin / admin123

3. CONFIGURE (First time only)
   └─ Dashboard → Pengaturan
   └─ Adjust roller diameter, delays, etc.
   └─ SIMPAN

4. START JOB
   └─ Enter panjang: 100 mm
   └─ Enter jumlah: 10 potong
   └─ Click START POTONG

5. MONITOR
   └─ Watch progress bar
   └─ Check serial monitor for details
   └─ System auto-updates status

6. COMPLETE
   └─ After 10 potong, status = DONE
   └─ View log for details
   └─ Ready for next job

7. REPEAT or SHUTDOWN
   └─ Start another job OR
   └─ Power off safely
```

---

## ⚡ Performance Tips

1. **Untuk Potong Cepat:**
   - Kurangi step_delay_us ke 800µs
   - Kurangi pause_ms values
   - ⚠️ Monitor untuk overheating

2. **Untuk Potong Presisi:**
   - Naikkan step_delay_us ke 2000µs
   - Naikkan pause_ms values
   - Motor akan lebih stabil

3. **Untuk Multiple Jobs:**
   - Jangan mulai job baru sampai yang lama DONE
   - Check status dahulu sebelum START
   - Lihat recent jobs history

---

## 🎓 Next Steps

1. **Baca SETUP_GUIDE.md** untuk pemahaman menyeluruh
2. **Ikuti DEPLOYMENT_CHECKLIST.md** sebelum production
3. **Simpan HARDWARE_CONNECTIONS.md** untuk referensi hardware
4. **Gunakan API_REFERENCE.md** jika ingin integrate dengan sistem lain

---

**Status:** ✅ READY TO USE  
**Last Updated:** 2 Januari 2026  
**Version:** 2.0.0

---

## 💬 Quick Help

**"Motor tidak bergerak"**
→ Cek HARDWARE_CONNECTIONS.md bagian Troubleshooting

**"Potong tidak sesuai ukuran"**
→ Ubah diameter roller di Pengaturan

**"Sistem down"**
→ Restart ESP32 & baca SETUP_GUIDE.md troubleshooting

**"Mau integrate dengan sistem lain"**
→ Baca API_REFERENCE.md complete endpoint documentation

