/*
 * ============================================
 * TEMPLATE KONFIGURASI ESP8266
 * Paper Cutting Machine Controller
 * ============================================
 * 
 * File ini adalah template untuk konfigurasi ESP8266.
 * Copy konfigurasi ini ke file pemotong_kertas.ino
 * 
 * LANGKAH-LANGKAH:
 * 1. Cari WiFi network Anda dan catat SSID & password
 * 2. Dapatkan IP address komputer/laptop server (ipconfig di CMD)
 * 3. Copy paste bagian yang sudah diisi ke pemotong_kertas.ino
 * 4. Upload ke ESP8266
 */

// ================= WIFI CONFIGURATION =================

// SETTING 1: NAMA WIFI (SSID)
// Ganti dengan nama WiFi/hotspot Anda
const char* ssid = "NAMA_WIFI_ANDA";

// SETTING 2: PASSWORD WIFI
// Ganti dengan password WiFi/hotspot Anda
const char* password = "PASSWORD_WIFI_ANDA";

// SETTING 3: SERVER URL
// Ganti IP_SERVER dengan IP komputer yang menjalankan Laragon
// Cara mendapatkan IP: Buka CMD → ketik ipconfig → lihat IPv4 Address
const char* serverURL = "http://IP_SERVER/pemotongKertas/api";

// ============================================
// CONTOH KONFIGURASI
// ============================================

/*
// ──────────────────────────────────────────
// CONTOH 1: WiFi Rumah/Kantor
// ──────────────────────────────────────────
const char* ssid = "Yanto Fams";
const char* password = "06072618";
const char* serverURL = "http://192.168.18.7/pemotongKertas/api";

// ──────────────────────────────────────────
// CONTOH 2: Hotspot Android
// ──────────────────────────────────────────
const char* ssid = "Hotspot-Android";
const char* password = "12345678";
const char* serverURL = "http://192.168.43.1/pemotongKertas/api";
// Catatan: Hotspot Android biasanya default IP 192.168.43.1

// ──────────────────────────────────────────
// CONTOH 3: Hotspot iPhone
// ──────────────────────────────────────────
const char* ssid = "iPhone-Hotspot";
const char* password = "password123";
const char* serverURL = "http://172.20.10.1/pemotongKertas/api";
// Catatan: Hotspot iPhone biasanya default IP 172.20.10.1

// ──────────────────────────────────────────
// CONTOH 4: WiFi Office dengan DHCP
// ──────────────────────────────────────────
const char* ssid = "OFFICE-NETWORK";
const char* password = "SecurePass2024!";
const char* serverURL = "http://10.0.5.25/pemotongKertas/api";
*/

// ============================================
// CARA MENCARI IP SERVER (Windows)
// ============================================
/*
1. Tekan tombol Windows + R
2. Ketik: cmd
3. Enter
4. Ketik: ipconfig
5. Enter
6. Cari bagian "Wireless LAN adapter Wi-Fi:" atau "Ethernet adapter"
7. Lihat IPv4 Address, contoh:
   
   Wireless LAN adapter Wi-Fi:
      IPv4 Address. . . . . . . . . . . : 192.168.1.7
      
8. IP Anda adalah: 192.168.1.7
9. Maka serverURL = "http://192.168.1.7/pemotongKertas/api"
*/

// ============================================
// VERIFIKASI KONFIGURASI
// ============================================
/*
Setelah upload ke ESP8266:

1. Buka Serial Monitor (115200 baud)
2. Lihat apakah WiFi connected:
   ✓ WiFi connected successfully!
   ========================================
     IP ADDRESS: 192.168.x.x
     Signal Strength: -XX dBm
   ========================================

3. Test akses ESP8266 dari browser:
   http://[IP_ESP8266]/ 
   
   Anda akan lihat halaman status ESP8266

4. Test API connection:
   http://[IP_ESP8266]/status
   
   Harus return JSON:
   {"success":true, "status":"READY", ...}

5. Jika gagal, cek:
   ☐ SSID dan password benar
   ☐ Komputer dan ESP8266 di network yang sama
   ☐ Laragon/Apache sudah running
   ☐ Firewall tidak block koneksi
*/

// ============================================
// TROUBLESHOOTING
// ============================================
/*

MASALAH: WiFi tidak connect
SOLUSI:
- Pastikan SSID dan password benar (case-sensitive!)
- Cek ESP8266 dalam jangkauan WiFi
- Restart ESP8266
- Coba gunakan hotspot HP untuk testing

─────────────────────────────────────────────

MASALAH: Config not loaded from server
SOLUSI:
- Pastikan Laragon/Apache running (lampu hijau)
- Test buka browser: http://[IP_SERVER]/pemotongKertas/
- Pastikan database sudah setup
- Cek firewall tidak memblokir port 80

─────────────────────────────────────────────

MASALAH: ESP8266 restart terus-menerus
SOLUSI:
- Gunakan USB cable yang bagus
- Power supply minimal 500mA
- Jangan sambungkan motor saat testing WiFi

─────────────────────────────────────────────

MASALAH: HTTP timeout / error
SOLUSI:
- Naikkan timeout di code (default 5 detik)
- Cek network tidak terlalu lambat
- Restart router WiFi

*/

// ============================================
// SETELAH KONFIGURASI BERHASIL
// ============================================
/*
1. Catat IP Address ESP8266 dari Serial Monitor
2. Update file: config/database.php
   define('ESP8266_IP', '192.168.x.x'); // ← IP ESP8266 Anda
   
3. Restart Apache/Laragon

4. Buka dashboard web dan test:
   - Settings → Machine Configuration (lihat apakah ESP8266 tersambung)
   - Dashboard → Start cutting job
   
5. Monitor Serial Monitor untuk melihat:
   - Penerimaan perintah dari server
   - Eksekusi motor
   - Update progress
*/

// ============================================
// PIN CONFIGURATION (JANGAN DIUBAH)
// ============================================
/*
Jika Anda menggunakan NodeMCU standar, PIN sudah benar.
Hanya ubah jika Anda menggunakan custom wiring!

Motor Penarik:
  - STEP_PULL = 2  (D4 - GPIO2)
  - DIR_PULL  = 0  (D3 - GPIO0)
  - EN_PULL   = 4  (D2 - GPIO4)

Motor Pemotong:
  - STEP_CUT  = 12 (D6 - GPIO12)
  - DIR_CUT   = 14 (D5 - GPIO14)
  - EN_CUT    = 13 (D7 - GPIO13)

Lihat file: ROUTING_PINOUT.md untuk detail lengkap
*/

// ============================================
// SUPPORT & DOKUMENTASI
// ============================================
/*
📄 README.md          - Panduan lengkap ESP8266
📄 ROUTING_PINOUT.md  - Detail koneksi hardware
📄 WIFI_CONFIG.md     - Panduan WiFi & upload
📄 routing            - Quick reference pinout

Version: 1.0.0
Last Update: 20 Januari 2026
*/
