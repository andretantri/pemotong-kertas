# API Reference - Pemakaian Kertas Otomatis

## Base URL
```
http://192.168.x.x/pemotongKertas/api/
```

---

## 1. Configuration Endpoints

### 1.1 GET - Ambil Konfigurasi Mesin
**Endpoint:** `GET /api/get_config.php`

**Description:** Mengambil konfigurasi mesin dari database untuk digunakan ESP32

**Authentication:** Tidak diperlukan

**Parameters:** Tidak ada

**Response - Success (200):**
```json
{
  "success": true,
  "message": "Configuration retrieved successfully",
  "config": {
    "roller_diameter_mm": 17,
    "pull_distance_cm": 5,
    "pull_steps": 187,
    "pull_delay_ms": 500,
    "cut_delay_ms": 500,
    "step_delay_us": 1200,
    "pull_pause_ms": 1000,
    "cut_pause_ms": 2000
  }
}
```

**Response - Error:**
```json
{
  "success": false,
  "message": "Configuration not found"
}
```

**cURL Example:**
```bash
curl http://192.168.1.100/pemotongKertas/api/get_config.php
```

---

### 1.2 POST - Update Konfigurasi Mesin
**Endpoint:** `POST /api/config.php`

**Description:** Mengubah konfigurasi mesin (hanya admin yang bisa)

**Authentication:** Diperlukan (Login terlebih dahulu)

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

**Parameters Validation:**
- `roller_diameter_mm`: Integer 1-100
- `pull_distance_cm`: Integer 1-50
- `step_delay_us`: Integer 500-5000
- Others: Integer, no range limit

**Response - Success (200):**
```json
{
  "success": true,
  "message": "Konfigurasi mesin berhasil diubah",
  "data": {
    "id": 1,
    "roller_diameter_mm": 17,
    "pull_distance_cm": 5,
    ...
  }
}
```

**Response - Error:**
```json
{
  "success": false,
  "message": "Diameter roller harus antara 1-100 mm"
}
```

**cURL Example:**
```bash
curl -X POST http://192.168.1.100/pemotongKertas/api/config.php \
  -H "Content-Type: application/json" \
  -d '{
    "roller_diameter_mm": 17,
    "pull_distance_cm": 5,
    "step_delay_us": 1200
  }' \
  -b "PHPSESSID=your_session_id"
```

---

## 2. Job Control Endpoints

### 2.1 POST - Mulai Pekerjaan Potong
**Endpoint:** `POST /api/esp32_start.php`

**Description:** Memulai pekerjaan potong baru

**Authentication:** Diperlukan (Login terlebih dahulu)

**Request Body:**
```json
{
  "panjang": 100,
  "jumlah_potong": 10
}
```

**Parameters Validation:**
- `panjang`: Integer 1-10000 (mm)
- `jumlah_potong`: Integer 1-1000

**Response - Success (200):**
```json
{
  "success": true,
  "message": "Pekerjaan dimulai! Job ID: 1",
  "data": {
    "job_id": 1,
    "panjang_mm": 100,
    "jumlah_potong": 10,
    "config": {
      "roller_diameter_mm": 17,
      "pull_distance_cm": 5,
      "step_delay_us": 1200,
      "pull_delay_ms": 500,
      "cut_delay_ms": 500,
      "pull_pause_ms": 1000,
      "cut_pause_ms": 2000
    }
  }
}
```

**Response - Error (Job sudah berjalan):**
```json
{
  "success": false,
  "message": "Masih ada pekerjaan aktif (ID: 1). Hentikan dulu sebelum memulai yang baru."
}
```

**cURL Example:**
```bash
curl -X POST http://192.168.1.100/pemotongKertas/api/esp32_start.php \
  -H "Content-Type: application/json" \
  -d '{
    "panjang": 100,
    "jumlah_potong": 10
  }' \
  -b "PHPSESSID=your_session_id"
```

---

### 2.2 GET - Hentikan Pekerjaan
**Endpoint:** `GET /api/stop.php`

**Description:** Menghentikan pekerjaan yang sedang berjalan

**Authentication:** Diperlukan (Login terlebih dahulu)

**Parameters:** Tidak ada

**Response - Success (200):**
```json
{
  "success": true,
  "message": "Pekerjaan dihentikan! Job ID: 1",
  "data": {
    "job_id": 1,
    "esp32_response": {
      "success": true,
      "http_code": 200
    }
  }
}
```

**Response - Error (Tidak ada job yang berjalan):**
```json
{
  "success": false,
  "message": "Tidak ada pekerjaan yang sedang berjalan"
}
```

**cURL Example:**
```bash
curl http://192.168.1.100/pemotongKertas/api/stop.php \
  -b "PHPSESSID=your_session_id"
```

---

## 3. Monitoring Endpoints

### 3.1 POST - Update Progress
**Endpoint:** `POST /api/progress.php`

**Description:** Menerima update progress dari ESP32 saat potong sedang berjalan

**Authentication:** Tidak diperlukan (untuk POST dari ESP32)

**Request Body:**
```json
{
  "job_id": 1,
  "potong_ke": 1,
  "status": "SUCCESS",
  "panjang_mm": 100
}
```

**Parameters Validation:**
- `job_id`: Integer > 0 (wajib)
- `potong_ke`: Integer > 0 (wajib)
- `status`: String "SUCCESS", "FAILED", atau "SKIPPED"
- `panjang_mm`: Integer > 0 (opsional, jika tidak ada akan pakai dari job)

**Response - Success (200):**
```json
{
  "success": true,
  "message": "Progress updated",
  "data": {
    "job_id": 1,
    "potong_ke": 1,
    "potong_selesai": 1,
    "jumlah_potong": 10,
    "is_complete": false,
    "status": "RUNNING"
  }
}
```

**Response - Error:**
```json
{
  "success": false,
  "message": "job_id tidak valid"
}
```

**cURL Example (dari ESP32):**
```cpp
HTTPClient http;
String url = "http://192.168.1.100/pemotongKertas/api/progress.php";

StaticJsonDocument<256> doc;
doc["job_id"] = 1;
doc["potong_ke"] = 1;
doc["status"] = "SUCCESS";
doc["panjang_mm"] = 100;

String payload;
serializeJson(doc, payload);

http.begin(url);
http.addHeader("Content-Type", "application/json");
int httpCode = http.POST(payload);

String response = http.getString();
http.end();
```

---

### 3.2 GET - Status Mesin
**Endpoint:** `GET /api/status.php`

**Description:** Mendapatkan status mesin dan progress job saat ini

**Authentication:** Diperlukan (Login terlebih dahulu)

**Parameters:** Tidak ada

**Response - Success (200):**
```json
{
  "success": true,
  "message": "Status retrieved",
  "data": {
    "current_job": {
      "id": 1,
      "panjang_mm": 100,
      "jumlah_potong": 10,
      "status": "RUNNING",
      "potong_selesai": 3,
      "started_at": "2026-01-02 10:30:00",
      "created_at": "2026-01-02 10:30:00"
    },
    "stats": {
      "total_job": 5,
      "done_job": 2,
      "running_job": 1,
      "total_potong": 45
    },
    "recent_jobs": [ ... ],
    "status": "RUNNING",
    "status_text": "Berjalan",
    "status_badge": "bg-primary"
  }
}
```

**cURL Example:**
```bash
curl http://192.168.1.100/pemotongKertas/api/status.php \
  -b "PHPSESSID=your_session_id"
```

---

## Error Responses

### Standard Error Response:
```json
{
  "success": false,
  "message": "Error message here",
  "data": {}
}
```

### HTTP Status Codes:
- `200 OK`: Request berhasil
- `400 Bad Request`: Parameter tidak valid
- `401 Unauthorized`: Autentikasi diperlukan
- `403 Forbidden`: Akses ditolak
- `405 Method Not Allowed`: HTTP method tidak sesuai
- `500 Internal Server Error`: Error server

---

## Rate Limiting & Best Practices

1. **ESP32 Config Loading**
   - Load config saat startup
   - Reload config sebelum mulai job (optional)
   - Jangan load config setiap siklus

2. **Progress Updates**
   - Send progress setelah setiap potong selesai
   - Gunakan HTTP Keep-Alive untuk performa lebih baik

3. **Job Management**
   - Cek status sebelum start job baru
   - Implement timeout handling untuk network error

---

## Example: Complete Cutting Workflow

### 1. Load Configuration
```bash
GET /api/get_config.php
→ Dapatkan settings dari database
```

### 2. Start Job
```bash
POST /api/esp32_start.php
body: {"panjang": 100, "jumlah_potong": 10}
→ Database create job (ID: 1)
→ Return config untuk ESP32
```

### 3. Execute Cutting Loop (ESP32)
```
For i = 1 to 10:
  - Pull forward
  - Pull backward
  - Cut forward
  - Cut backward
  - POST /api/progress.php (potong_ke: i, status: SUCCESS)
  - If last iteration: status menjadi DONE
```

### 4. Monitor Progress (Web UI)
```bash
GET /api/status.php
→ Dapatkan current_job dan stats
→ Update UI setiap 1-2 detik
```

### 5. Stop (jika diperlukan)
```bash
GET /api/stop.php
→ Update job status ke STOPPED
```

---

## Testing dengan Postman

1. Import collection dari workspace
2. Set environment variables:
   - `{{base_url}}` = http://192.168.1.100/pemotongKertas
   - `{{job_id}}` = 1
3. Setup authentication dengan PHPSESSID dari browser
4. Test endpoints satu per satu

