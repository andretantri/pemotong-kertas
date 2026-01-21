# Entity Relationship Diagram (ERD)
## Database Schema - HMI Pemotong Kertas

## 📊 Visual ERD

```
┌─────────────────────────────────────────────────────────────┐
│                         DATABASE                            │
│                  hmi_pemotong_kertas                        │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                         users                                │
├─────────────────────────────────────────────────────────────┤
│ PK │ id              │ INT(11) UNSIGNED AUTO_INCREMENT      │
│    │ username        │ VARCHAR(50) UNIQUE NOT NULL          │
│    │ password        │ VARCHAR(255) NOT NULL (bcrypt)      │
│    │ nama            │ VARCHAR(100) NOT NULL                │
│    │ created_at      │ TIMESTAMP DEFAULT CURRENT_TIMESTAMP  │
│    │ updated_at      │ TIMESTAMP ON UPDATE CURRENT_TIMESTAMP│
└────┼────────────────────────────────────────────────────────┘
     │
     │ 1
     │
     │ (user_id)
     │
     │ N
     ▼
┌─────────────────────────────────────────────────────────────┐
│                      job_potong                              │
├─────────────────────────────────────────────────────────────┤
│ PK │ id              │ INT(11) UNSIGNED AUTO_INCREMENT      │
│ FK │ user_id         │ INT(11) UNSIGNED NOT NULL            │
│    │ panjang_mm      │ INT(11) NOT NULL                     │
│    │ jumlah_potong   │ INT(11) NOT NULL                     │
│    │ status          │ ENUM('READY','RUNNING','STOPPED',   │
│    │                 │       'DONE','ERROR') DEFAULT 'READY'│
│    │ potong_selesai  │ INT(11) DEFAULT 0                    │
│    │ started_at      │ TIMESTAMP NULL                       │
│    │ stopped_at      │ TIMESTAMP NULL                       │
│    │ completed_at    │ TIMESTAMP NULL                       │
│    │ created_at      │ TIMESTAMP DEFAULT CURRENT_TIMESTAMP  │
│    │ updated_at      │ TIMESTAMP ON UPDATE CURRENT_TIMESTAMP│
└────┼────────────────────────────────────────────────────────┘
     │
     │ 1
     │
     │ (job_id)
     │
     │ N
     ▼
┌─────────────────────────────────────────────────────────────┐
│                       log_potong                             │
├─────────────────────────────────────────────────────────────┤
│ PK │ id              │ INT(11) UNSIGNED AUTO_INCREMENT      │
│ FK │ job_id          │ INT(11) UNSIGNED NOT NULL            │
│    │ potong_ke       │ INT(11) NOT NULL                      │
│    │ panjang_mm      │ INT(11) NOT NULL                     │
│    │ status          │ ENUM('SUCCESS','FAILED','SKIPPED')   │
│    │                 │       DEFAULT 'SUCCESS'             │
│    │ waktu_potong    │ TIMESTAMP DEFAULT CURRENT_TIMESTAMP  │
│    │ created_at      │ TIMESTAMP DEFAULT CURRENT_TIMESTAMP  │
└──────────────────────────────────────────────────────────────┘
```

## 📋 Detail Tabel

### 1. Tabel: `users`
**Deskripsi:** Menyimpan data user/admin sistem

| Field | Type | Constraints | Keterangan |
|-------|------|-------------|------------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | ID unik user |
| username | VARCHAR(50) | UNIQUE, NOT NULL | Username untuk login |
| password | VARCHAR(255) | NOT NULL | Password hash (bcrypt) |
| nama | VARCHAR(100) | NOT NULL | Nama lengkap user |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Waktu terakhir update |

**Indexes:**
- PRIMARY KEY (`id`)
- UNIQUE KEY (`username`)

**Default Data:**
- Username: `admin`
- Password: `admin123` (hash: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`)

---

### 2. Tabel: `job_potong`
**Deskripsi:** Menyimpan data pekerjaan potong

| Field | Type | Constraints | Keterangan |
|-------|------|-------------|------------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | ID unik job |
| user_id | INT(11) UNSIGNED | FOREIGN KEY, NOT NULL | ID user yang membuat job |
| panjang_mm | INT(11) | NOT NULL | Panjang potong dalam mm |
| jumlah_potong | INT(11) | NOT NULL | Jumlah potong yang diminta |
| status | ENUM | DEFAULT 'READY' | Status job: READY, RUNNING, STOPPED, DONE, ERROR |
| potong_selesai | INT(11) | DEFAULT 0 | Jumlah potong yang sudah selesai |
| started_at | TIMESTAMP | NULL | Waktu mulai pekerjaan |
| stopped_at | TIMESTAMP | NULL | Waktu dihentikan |
| completed_at | TIMESTAMP | NULL | Waktu selesai |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Waktu terakhir update |

**Indexes:**
- PRIMARY KEY (`id`)
- FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
- KEY (`status`)
- KEY (`created_at`)

**Status Values:**
- `READY`: Job dibuat, siap dijalankan
- `RUNNING`: Job sedang berjalan
- `STOPPED`: Job dihentikan oleh user
- `DONE`: Job selesai (semua potong complete)
- `ERROR`: Terjadi error saat komunikasi dengan ESP32

---

### 3. Tabel: `log_potong`
**Deskripsi:** Menyimpan log detail setiap potong

| Field | Type | Constraints | Keterangan |
|-------|------|-------------|------------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | ID unik log |
| job_id | INT(11) UNSIGNED | FOREIGN KEY, NOT NULL | ID job terkait |
| potong_ke | INT(11) | NOT NULL | Urutan potong (1, 2, 3, ...) |
| panjang_mm | INT(11) | NOT NULL | Panjang potong dalam mm |
| status | ENUM | DEFAULT 'SUCCESS' | Status potong: SUCCESS, FAILED, SKIPPED |
| waktu_potong | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Waktu potong dilakukan |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Waktu log dibuat |

**Indexes:**
- PRIMARY KEY (`id`)
- FOREIGN KEY (`job_id`) REFERENCES `job_potong`(`id`) ON DELETE CASCADE
- KEY (`waktu_potong`)

**Status Values:**
- `SUCCESS`: Potong berhasil
- `FAILED`: Potong gagal
- `SKIPPED`: Potong dilewati

---

## 🔗 Relasi Antar Tabel

### 1. users → job_potong
- **Relasi:** One-to-Many (1:N)
- **Foreign Key:** `job_potong.user_id` → `users.id`
- **Cascade:** ON DELETE CASCADE
- **Keterangan:** Satu user dapat membuat banyak job

### 2. job_potong → log_potong
- **Relasi:** One-to-Many (1:N)
- **Foreign Key:** `log_potong.job_id` → `job_potong.id`
- **Cascade:** ON DELETE CASCADE
- **Keterangan:** Satu job memiliki banyak log potong

---

## 📈 View: `v_job_summary`
**Deskripsi:** View untuk summary pekerjaan dengan agregasi data

**Query:**
```sql
SELECT 
    j.id,
    j.panjang_mm,
    j.jumlah_potong,
    j.status,
    j.potong_selesai,
    j.started_at,
    j.stopped_at,
    j.completed_at,
    j.created_at,
    u.nama AS user_nama,
    COUNT(l.id) AS total_log
FROM job_potong j
LEFT JOIN users u ON j.user_id = u.id
LEFT JOIN log_potong l ON j.id = l.job_id
GROUP BY j.id;
```

**Kegunaan:**
- Menampilkan summary job dengan nama user
- Menghitung total log per job
- Memudahkan query reporting

---

## 🔍 Query Examples

### 1. Get Active Job
```sql
SELECT * FROM job_potong 
WHERE status IN ('READY', 'RUNNING') 
ORDER BY id DESC 
LIMIT 1;
```

### 2. Get Job with User Info
```sql
SELECT j.*, u.nama as user_nama 
FROM job_potong j 
LEFT JOIN users u ON j.user_id = u.id 
WHERE j.id = ?;
```

### 3. Get Logs by Job
```sql
SELECT * FROM log_potong 
WHERE job_id = ? 
ORDER BY potong_ke ASC;
```

### 4. Get Job Statistics
```sql
SELECT 
    COUNT(*) as total_job,
    SUM(CASE WHEN status = 'DONE' THEN 1 ELSE 0 END) as done_job,
    SUM(CASE WHEN status = 'RUNNING' THEN 1 ELSE 0 END) as running_job
FROM job_potong;
```

### 5. Get Recent Jobs with Log Count
```sql
SELECT 
    j.*,
    COUNT(l.id) as log_count
FROM job_potong j
LEFT JOIN log_potong l ON j.id = l.job_id
GROUP BY j.id
ORDER BY j.created_at DESC
LIMIT 10;
```

---

## 📊 Data Flow

### Job Lifecycle dalam Database

```
1. CREATE JOB
   INSERT INTO job_potong (status = 'READY')
   
2. START JOB
   UPDATE job_potong SET status = 'RUNNING', started_at = NOW()
   
3. PROGRESS UPDATE (setiap potong)
   INSERT INTO log_potong (job_id, potong_ke, status)
   UPDATE job_potong SET potong_selesai = potong_ke
   
4. COMPLETE JOB
   UPDATE job_potong SET status = 'DONE', completed_at = NOW()
   
5. STOP JOB (optional)
   UPDATE job_potong SET status = 'STOPPED', stopped_at = NOW()
```

---

## 🔐 Constraints & Rules

### 1. Foreign Key Constraints
- `job_potong.user_id` → `users.id` (CASCADE DELETE)
- `log_potong.job_id` → `job_potong.id` (CASCADE DELETE)

### 2. Business Rules
- Satu job hanya bisa memiliki status RUNNING pada satu waktu
- `potong_selesai` tidak boleh lebih besar dari `jumlah_potong`
- `potong_ke` dalam log harus unik per job (dapat di-enforce di application layer)

### 3. Data Integrity
- Semua timestamp menggunakan TIMESTAMP dengan timezone server
- Status menggunakan ENUM untuk memastikan nilai valid
- Foreign keys menggunakan CASCADE untuk menjaga referential integrity

---

## 📝 Notes

1. **Engine:** Semua tabel menggunakan InnoDB untuk mendukung foreign key dan transaction
2. **Charset:** UTF8MB4 untuk mendukung karakter unicode lengkap
3. **Indexes:** Index pada kolom yang sering digunakan untuk query (status, created_at, waktu_potong)
4. **Cascade Delete:** Jika user dihapus, semua job-nya ikut terhapus. Jika job dihapus, semua log-nya ikut terhapus.

---

**Versi:** 1.0.0  
**Update Terakhir:** 2024

