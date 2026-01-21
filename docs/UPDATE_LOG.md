# Update Log - Timer Based Completion

## Tanggal: 2026-01-21

### Masalah yang Diperbaiki:

1. **Setting web tidak diterapkan ke ESP8266**
   - ESP8266 dalam mode AP tidak bisa download config dari server
   - Solusi: Server mengirim config saat start job

2. **Loading tidak selesai**
   - ESP8266 tidak mengirim update progress ke server
   - Solusi: Gunakan timer estimasi berdasarkan konfigurasi mesin

### Perubahan File:

#### 1. ESP8266/pemotong_kertas.ino
- ✅ Endpoint `/start` sekarang menerima parameter config
- ✅ Config dikirim dari server dan langsung diterapkan
- ✅ Parameter: `pull_steps`, `step_delay_us`, `pull_delay_ms`, `cut_delay_ms`, `pull_pause_ms`, `cut_pause_ms`

#### 2. api/start.php
- ✅ Mengambil config dari database
- ✅ Menghitung estimasi waktu selesai
- ✅ Mengirim config ke ESP8266 saat start
- ✅ Return `estimated_seconds` ke frontend

#### 3. api/complete_job.php (BARU)
- ✅ Endpoint untuk mark job as DONE
- ✅ Dipanggil otomatis oleh timer di frontend

#### 4. assets/js/dashboard-react.js
- ✅ Menggunakan `estimated_seconds` dari API
- ✅ Set timer untuk auto-complete job
- ✅ Loading otomatis berhenti setelah estimasi waktu

### Formula Estimasi Waktu:

```
timePerCut = (pull_steps × step_delay_us ÷ 1,000,000) + 
             (pull_delay_ms ÷ 1000) + 
             (pull_pause_ms ÷ 1000) + 
             (cut_delay_ms ÷ 1000) + 
             (cut_pause_ms ÷ 1000)

estimatedSeconds = (timePerCut × jumlah_potong) + 5 detik buffer
```

### Cara Kerja Baru:

1. User klik "Mulai Pekerjaan"
2. Server:
   - Buat job di database
   - Ambil config mesin
   - Hitung estimasi waktu
   - Kirim perintah + config ke ESP8266
   - Return `estimated_seconds` ke frontend
3. Frontend:
   - Tampilkan loading
   - Set timer = `estimated_seconds`
   - Setelah timer selesai:
     - Panggil `complete_job.php`
     - Update status ke DONE
     - Stop loading
     - Tampilkan alert sukses

### Keuntungan:

✅ Tidak perlu ESP8266 kirim update progress
✅ Loading pasti selesai (tidak hang)
✅ Config dari web langsung diterapkan
✅ Estimasi waktu akurat berdasarkan setting

### Catatan:

- Jika ESP8266 tidak merespon, job tetap jalan di database
- Timer tetap berjalan meskipun ESP8266 mati
- User bisa stop manual kapan saja
- Estimasi bisa disesuaikan dengan menambah buffer time
