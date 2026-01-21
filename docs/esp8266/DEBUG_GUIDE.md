# 🔧 Panduan Penggunaan Hardware Debugger ESP8266

## 📋 Tentang File Ini

**File:** `debug_hardware.ino`  
**Fungsi:** Testing dan verifikasi routing kabel ESP8266 sebelum menggunakan program utama

File debugger ini akan membantu Anda:
- ✅ Memastikan semua pin GPIO terhubung dengan benar
- ✅ Test motor penarik dan pemotong satu per satu
- ✅ Verifikasi koneksi WiFi
- ✅ Test komunikasi dengan API server
- ✅ Troubleshooting masalah hardware

---

## 🚀 Cara Menggunakan

### Langkah 1: Upload Debugger

1. **Buka file** `debug_hardware.ino` di Arduino IDE
2. **Pastikan konfigurasi WiFi** sudah benar (baris 26-28):
   ```cpp
   const char* ssid = "Yanto Fams";
   const char* password = "06072618";
   const char* serverURL = "http://192.168.18.51/pemotongKertas/api";
   ```
3. **Upload** ke ESP8266 (sama seperti upload program biasa)
4. **Buka Serial Monitor** (115200 baud)

### Langkah 2: Running Tests

Setelah upload, Anda akan melihat header:

```
╔════════════════════════════════════════════════╗
║   ESP8266 HARDWARE DEBUGGER v1.0              ║
║   Paper Cutting Machine Controller            ║
╚════════════════════════════════════════════════╝
```

Program akan otomatis menjalankan **semua test** terlebih dahulu.

### Langkah 3: Menu Interaktif

Setelah test awal selesai, akan muncul menu:

```
========================================
           TEST MENU
========================================
1. Test Pin Output (LED Blink)
2. Test Motor Penarik (Pull)
3. Test Motor Pemotong (Cut)
4. Test Both Motors
5. Test WiFi Connection
6. Test API Connection
7. Run All Tests
8. Show Pin Configuration
9. Manual Step Control
0. Reset All Motors
========================================
Pilih test (0-9):
```

Ketik nomor pilihan di Serial Monitor dan tekan Enter.

---

## 📊 Penjelasan Setiap Test

### Test 1: Pin Output (LED Blink)

**Fungsi:** Mengecek apakah semua pin GPIO berfungsi dengan baik

**Yang Terjadi:**
- Setiap pin akan di-toggle HIGH/LOW beberapa kali
- Anda bisa mengukur dengan multimeter atau LED

**Output:**
```
Testing STEP_PULL (D4/GPIO2) ... ..... ✓ OK
Testing DIR_PULL (D3/GPIO0) ... ..... ✓ OK
Testing EN_PULL (D2/GPIO4) ... ..... ✓ OK
Testing STEP_CUT (D6/GPIO12) ... ..... ✓ OK
Testing DIR_CUT (D5/GPIO14) ... ..... ✓ OK
Testing EN_CUT (D7/GPIO13) ... ..... ✓ OK

✅ Pin Output Test COMPLETED
```

**Apa yang Harus Dicek:**
- [ ] Tidak ada error di output
- [ ] Jika menggunakan LED, semua pin bisa menyalakan LED
- [ ] Jika menggunakan multimeter, voltage berubah HIGH (3.3V) / LOW (0V)

---

### Test 2: Motor Penarik (Pull)

**Fungsi:** Test motor penarik kertas forward dan backward

**Yang Terjadi:**
- Motor penarik akan berputar maju 200 steps
- Motor penarik akan berputar mundur 200 steps

**Output:**
```
⚙️  Enabling Pull Motor...
▶️  Testing FORWARD rotation (200 steps)...
   Stepping PULL_FWD: ........ ✓ (200 steps)
◀️  Testing BACKWARD rotation (200 steps)...
   Stepping PULL_BWD: ........ ✓ (200 steps)

✅ Pull Motor Test COMPLETED
```

**Apa yang Harus Dicek:**
- [ ] Motor berputar (terdengar suara atau terlihat gerakan)
- [ ] Motor berputar searah jarum jam saat forward
- [ ] Motor berputar berlawanan saat backward
- [ ] Tidak ada suara buzzing (jika buzzing, cek VREF A4988)
- [ ] Motor tidak terlalu panas

**Troubleshooting:**
- ❌ **Motor tidak bergerak:** Cek koneksi kabel, power 12V, ENABLE pin
- ❌ **Motor buzzing:** VREF terlalu rendah, putar potentiometer A4988
- ❌ **Motor lemah:** Naikkan VREF atau cek power supply

---

### Test 3: Motor Pemotong (Cut)

**Fungsi:** Test motor pemotong kertas forward dan backward

**Yang Terjadi:**
- Motor pemotong akan berputar maju 200 steps
- Motor pemotong akan berputar mundur 200 steps

**Output:**
```
⚙️  Enabling Cut Motor...
▶️  Testing FORWARD rotation (200 steps)...
   Stepping CUT_FWD: ........ ✓ (200 steps)
◀️  Testing BACKWARD rotation (200 steps)...
   Stepping CUT_BWD: ........ ✓ (200 steps)

✅ Cut Motor Test COMPLETED
```

**Checklist sama seperti Test 2.**

---

### Test 4: Both Motors Sequential

**Fungsi:** Test kedua motor secara berurutan (simulasi cutting cycle)

**Yang Terjadi:**
1. Pull Forward
2. Pull Backward
3. Cut Forward
4. Cut Backward

**Output:**
```
Sequence: Pull Forward → Pull Back → Cut Forward → Cut Back

1️⃣  Pull Forward
   Stepping PULL_FWD: .... ✓ (200 steps)

2️⃣  Pull Backward
   Stepping PULL_BWD: .... ✓ (200 steps)

3️⃣  Cut Forward
   Stepping CUT_FWD: .... ✓ (200 steps)

4️⃣  Cut Backward
   Stepping CUT_BWD: .... ✓ (200 steps)

✅ Both Motors Test COMPLETED
```

**Apa yang Harus Dicek:**
- [ ] Hanya 1 motor aktif di satu waktu
- [ ] Tidak ada interference antar motor
- [ ] Sequence berjalan smooth tanpa error

---

### Test 5: WiFi Connection

**Fungsi:** Test koneksi ke WiFi

**Output (Sukses):**
```
SSID: Yanto Fams

Connecting to WiFi..........

✅ WiFi Connection SUCCESS
   IP Address: 192.168.18.150
   Signal: -45 dBm
   MAC: XX:XX:XX:XX:XX:XX
```

**Output (Gagal):**
```
❌ WiFi Connection FAILED
   Check SSID and password!
   Current SSID: Yanto Fams
```

**Troubleshooting:**
- ❌ **Failed:** Cek SSID dan password benar
- ❌ **Timeout:** WiFi terlalu jauh, dekatkan ESP8266 ke router
- ❌ **Wrong password:** Double-check password (case-sensitive!)

---

### Test 6: API Connection

**Fungsi:** Test koneksi ke API server PHP

**Output (Sukses):**
```
Testing URL: http://192.168.18.51/pemotongKertas/api/get_config.php

Sending GET request... ✓

📥 Response received:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
{"success":true,"message":"Configuration retrieved",...}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✅ API Connection SUCCESS
   Config loaded successfully

📊 Configuration received:
   Roller Diameter: 17 mm
   Pull Distance: 5 cm
   Pull Steps: 320
   Step Delay: 1200 µs
```

**Output (Gagal):**
```
❌ API Connection FAILED - 404 Not Found
   Check if file exists: /api/get_config.php

💡 Troubleshooting:
   1. Check if Laragon/Apache is running
   2. Test URL in browser
   3. Check firewall settings
```

**Troubleshooting:**
- ❌ **404:** File API tidak ada, cek folder `/api/get_config.php`
- ❌ **Timeout:** Server lambat atau tidak running
- ❌ **Connection refused:** Firewall block atau Apache mati

---

### Test 7: Run All Tests

**Fungsi:** Menjalankan semua test dari 1-6 secara berurutan

Pilih ini jika ingin test semua sekaligus. Berguna untuk:
- Initial testing setelah assembly
- Regression testing setelah perbaikan
- Documentation (record serial output)

---

### Test 8: Show Pin Configuration

**Fungsi:** Menampilkan routing pin lengkap

**Output:**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
PIN CONFIGURATION
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

🔧 Motor Penarik (Pull Motor):
   NodeMCU Pin | GPIO | A4988   | Function
   ─────────────────────────────────────
   D2          | 4    | ENABLE  | Enable Motor
   D3          | 0    | DIR     | Direction
   D4          | 2    | STEP    | Step Signal

🔧 Motor Pemotong (Cut Motor):
   NodeMCU Pin | GPIO | A4988   | Function
   ─────────────────────────────────────
   D5          | 14   | DIR     | Direction
   D6          | 12   | STEP    | Step Signal
   D7          | 13   | ENABLE  | Enable Motor

⚡ Power Connections:
   12V PSU → A4988 VCC (both drivers)
   GND PSU → A4988 GND → ESP8266 GND
   5V USB  → ESP8266 VIN/5V

⚠️  WARNINGS:
   • ESP8266 works at 3.3V logic
   • DO NOT connect ESP8266 to 12V!
   • Common GND is MANDATORY
   • Max GPIO current: 12mA
```

**Gunakan ini sebagai referensi cepat saat wiring!**

---

### Test 9: Manual Step Control

**Fungsi:** Kontrol motor secara manual dengan command

**Commands:**
- `pf` = Pull Forward
- `pb` = Pull Backward
- `cf` = Cut Forward
- `cb` = Cut Backward
- `s` = Stop all motors
- `x` = Exit

**Contoh Penggunaan:**
```
Enter command: pf
   Stepping PULL_FWD: .. ✓ (100 steps)

Enter command: cf
   Stepping CUT_FWD: .. ✓ (100 steps)

Enter command: s
🔄 Resetting all motors...
✓ All motors disabled

Enter command: x
🔄 Resetting all motors...
✓ All motors disabled
```

**Berguna untuk:**
- Fine-tuning posisi motor
- Testing manual movement
- Debugging specific direction

---

### Test 0: Reset All Motors

**Fungsi:** Matikan semua motor (EMERGENCY STOP)

Gunakan ini jika:
- Motor stuck
- Perlu stop cepat
- Sebelum disconnect power

---

## 🐛 Troubleshooting Guide

### Problem: "WiFi not connected"

**Penyebab:**
- SSID/password salah
- WiFi 5GHz (ESP8266 hanya support 2.4GHz)
- Jarak terlalu jauh

**Solusi:**
1. Double-check SSID dan password
2. Pastikan WiFi 2.4GHz
3. Dekatkan ESP8266 ke router
4. Test dengan hotspot HP

---

### Problem: "Motor tidak bergerak"

**Penyebab:**
- Kabel tidak terhubung
- A4988 tidak dapat power 12V
- Motor connector salah
- VREF terlalu rendah

**Solusi:**
1. Cek koneksi sesuai pin configuration
2. Ukur voltage 12V di VCC A4988
3. Test continuity motor coil dengan multimeter
4. Adjust VREF A4988 (putar potentiometer searah jarum jam)

---

### Problem: "Motor buzzing tanpa putaran"

**Penyebab:**
- VREF terlalu rendah
- Motor coil terbalik
- Microstepping terlalu tinggi

**Solusi:**
1. Naikkan VREF perlahan (max 1V untuk motor 2A)
2. Swap motor coil A dengan B
3. Ubah DIP switch A4988 ke full-step

---

### Problem: "API timeout"

**Penyebab:**
- Laragon/Apache tidak running
- Firewall block koneksi
- URL salah

**Solusi:**
1. Pastikan Laragon running (lampu hijau)
2. Test URL di browser
3. Disable firewall sementara
4. Ping IP server dari ESP8266

---

## 📝 Checklist Testing

Gunakan checklist ini untuk memastikan semua sudah OK:

### Hardware
- [ ] ESP8266 terhubung via USB dan mendapat power
- [ ] A4988 driver #1 dan #2 mendapat power 12V
- [ ] Semua pin GPIO terhubung sesuai routing
- [ ] Motor penarik terhubung ke A4988 #1
- [ ] Motor pemotong terhubung ke A4988 #2
- [ ] Common ground (PSU-ESP8266-A4988) tersambung

### Software
- [ ] File debug_hardware.ino ter-upload tanpa error
- [ ] Serial Monitor baud rate 115200
- [ ] WiFi SSID dan password sudah benar
- [ ] Server URL sudah benar

### Testing Results
- [ ] Test 1 (Pin Output) - PASS
- [ ] Test 2 (Pull Motor) - PASS
- [ ] Test 3 (Cut Motor) - PASS
- [ ] Test 4 (Both Motors) - PASS
- [ ] Test 5 (WiFi) - PASS
- [ ] Test 6 (API) - PASS

### Observations
- [ ] Motor berputar smooth tanpa skip
- [ ] Tidak ada suara aneh
- [ ] Motor tidak overheat
- [ ] WiFi signal strength baik (>-70 dBm)
- [ ] API response time cepat (<1 detik)

---

## 🎓 Tips & Best Practices

### 1. Test Bertahap
Jangan langsung test semua. Mulai dari:
1. Pin output (tanpa motor)
2. WiFi connection
3. Satu motor
4. Kedua motor

### 2. Safety First
- Disconnect power saat wiring
- Jangan sentuh motor yang berputar
- Matikan motor jika terdengar aneh

### 3. Document Results
Screenshot atau copy-paste output Serial Monitor untuk dokumentasi.

### 4. Iterative Testing
Jika ada yang gagal:
1. Fix masalahnya
2. Re-run test yang gagal
3. Pastikan PASS sebelum lanjut

---

## 🔄 Setelah Debugging Selesai

Jika semua test **PASS**, Anda siap menggunakan program utama:

1. **Upload** file `pemotong_kertas.ino`
2. **Konfigurasi WiFi** sama seperti debugger
3. **Test** dari dashboard web
4. **Monitor** Serial Monitor untuk memastikan

---

## 📞 Quick Reference

| Test | Fungsi | Critical? |
|------|--------|-----------|
| 1 | Pin Output | ✅ Yes |
| 2 | Pull Motor | ✅ Yes |
| 3 | Cut Motor | ✅ Yes |
| 4 | Both Motors | ⭐ Recommended |
| 5 | WiFi | ✅ Yes |
| 6 | API | ✅ Yes |
| 7 | All Tests | ⭐ Initial test |
| 8 | Pin Config | ℹ️ Reference |
| 9 | Manual | 🔧 Troubleshooting |
| 0 | Reset | 🚨 Emergency |

---

**Version:** 1.0.0  
**Date:** 21 Januari 2026  
**File:** debug_hardware.ino  

**Selamat Testing! 🚀**
