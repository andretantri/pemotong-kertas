# Troubleshooting Koneksi ESP8266

## Masalah: Dashboard tidak bisa terhubung ke ESP8266

### Langkah Pengecekan:

1. **Cek Koneksi WiFi**
   - Pastikan laptop terhubung ke WiFi: `Mesin_Potong_Kertas`
   - Password: `password123`
   - ESP8266 IP seharusnya: `192.168.4.1`

2. **Test Ping**
   ```bash
   ping 192.168.4.1
   ```
   Jika berhasil, berarti koneksi network OK.

3. **Test HTTP dengan Browser**
   Buka browser dan akses:
   ```
   http://192.168.4.1/
   ```
   Seharusnya muncul JSON response:
   ```json
   {"success":true,"message":"ESP8266 Online","status":"READY"}
   ```

4. **Test dengan File PHP**
   Buka di browser:
   ```
   http://localhost/pemotongKertas/test_esp.php
   ```
   File ini akan menampilkan detail koneksi dan error (jika ada).

5. **Cek Log ESP8266**
   Di Serial Monitor Arduino IDE, seharusnya muncul:
   ```
   ✓ Access Point Started Successfully!
   ========================================
     IP ADDRESS: 192.168.4.1
     Connect your laptop to this WiFi
   ========================================
   ✓ Web server started on port 80
   ```

6. **Cek IP di Database**
   - Buka menu **Pengaturan** di dashboard
   - Pastikan **Alamat IP Mesin** = `192.168.4.1`
   - Klik **Simpan Pengaturan**

### Solusi Umum:

#### A. ESP8266 tidak memancarkan WiFi
- Upload ulang kode `pemotong_kertas.ino`
- Reset ESP8266 (tekan tombol reset)
- Cek Serial Monitor untuk pesan error

#### B. Laptop tidak bisa connect ke WiFi ESP
- Lupakan network "Mesin_Potong_Kertas" di Windows
- Connect ulang dengan password: `password123`
- Tunggu sampai mendapat IP (biasanya 192.168.4.2)

#### C. Ping OK tapi HTTP gagal
- Pastikan web server ESP8266 sudah jalan (cek Serial Monitor)
- Coba akses `http://192.168.4.1/` di browser
- Jika browser juga gagal, berarti web server ESP bermasalah

#### D. Browser bisa akses tapi Dashboard tidak
- Buka `test_esp.php` untuk melihat error detail
- Cek PHP error log di: `C:\laragon\data\logs\php_errors.log`
- Pastikan cURL PHP extension aktif

### File Penting:

- **ESP8266 Code**: `ESP8266/pemotong_kertas.ino`
- **PHP Connection**: `config/functions.php` (fungsi `sendESPRequest`)
- **Dashboard Check**: `api/check_connection.php`
- **Test File**: `test_esp.php`

### Catatan Mode Access Point:

ESP8266 berjalan dalam **mode AP (Access Point)**, artinya:
- ✅ ESP8266 memancarkan WiFi sendiri
- ✅ Laptop connect ke WiFi ESP8266
- ✅ Laptop bisa akses ESP8266 di `192.168.4.1`
- ❌ ESP8266 **TIDAK BISA** akses internet
- ❌ ESP8266 **TIDAK BISA** akses server di `192.168.4.2` untuk download config

Konfigurasi mesin akan menggunakan **default values** yang sudah di-hardcode di ESP8266.
