# Arsitektur Aplikasi HMI Pemotong Kertas

## 📐 Desain Arsitektur

### 1. Arsitektur Umum

```
┌─────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Browser    │  │  Mobile Web  │  │   Tablet     │      │
│  │  (Desktop)   │  │  (Android)   │  │   Browser    │      │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘      │
│         │                  │                  │              │
└─────────┼──────────────────┼──────────────────┼─────────────┘
          │                  │                  │
          │         HTTP/HTTPS Request          │
          │                  │                  │
┌─────────┼──────────────────┼──────────────────┼─────────────┐
│         ▼                  ▼                  ▼              │
│                    WEB SERVER LAYER                         │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              Apache / Nginx                          │   │
│  │  - Static files (CSS, JS, Images)                   │   │
│  │  - PHP processing                                    │   │
│  │  - URL routing                                       │   │
│  └─────────────────────────────────────────────────────┘   │
│                        │                                     │
└────────────────────────┼─────────────────────────────────────┘
                         │
                         │ PHP Execution
                         │
┌────────────────────────┼─────────────────────────────────────┐
│                        ▼                                     │
│                  APPLICATION LAYER                          │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  ┌──────────────┐  ┌──────────────┐               │   │
│  │  │   Auth       │  │   Dashboard  │               │   │
│  │  │   Module     │  │   Module     │               │   │
│  │  └──────────────┘  └──────────────┘               │   │
│  │                                                    │   │
│  │  ┌──────────────┐  ┌──────────────┐               │   │
│  │  │   API        │  │   Log        │               │   │
│  │  │   Module     │  │   Module     │               │   │
│  │  └──────────────┘  └──────────────┘               │   │
│  └─────────────────────────────────────────────────────┘   │
│                        │                                     │
│                        │ Database Query                      │
│                        │                                     │
└────────────────────────┼─────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                    DATA LAYER                               │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              MySQL / MariaDB                        │   │
│  │  - users                                            │   │
│  │  - job_potong                                       │   │
│  │  - log_potong                                       │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                         │
                         │ HTTP Request
                         │
┌────────────────────────┼─────────────────────────────────────┐
│                        ▼                                     │
│                  ESP32 DEVICE LAYER                        │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              ESP32 Microcontroller                   │   │
│  │  - WiFi Access Point / Client                       │   │
│  │  - HTTP Server                                      │   │
│  │  - Motor Control                                    │   │
│  │  - Sensor Reading                                   │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

## 🔄 Flow Diagram

### Flow: Start Job

```
User → Dashboard → Click START
  │
  ├─→ Validate Input (panjang, jumlah)
  │
  ├─→ API: start.php
  │     │
  │     ├─→ Check Active Job
  │     │
  │     ├─→ Create Job Record (status: READY)
  │     │
  │     ├─→ Send HTTP GET to ESP32
  │     │     GET /start?panjang=XXX&jumlah=YYY
  │     │
  │     ├─→ ESP32 Response
  │     │     │
  │     │     ├─→ Success → Update Job (status: RUNNING)
  │     │     │
  │     │     └─→ Failed → Update Job (status: ERROR)
  │     │
  │     └─→ Return JSON Response
  │
  └─→ Dashboard Refresh → Show Status
```

### Flow: ESP32 Progress Update

```
ESP32 → Complete One Cut
  │
  ├─→ POST /api/progress.php
  │     {
  │       "job_id": 1,
  │       "potong_ke": 5,
  │       "status": "SUCCESS"
  │     }
  │
  ├─→ API: progress.php
  │     │
  │     ├─→ Validate Input
  │     │
  │     ├─→ Insert Log Record
  │     │     log_potong table
  │     │
  │     ├─→ Update Job Progress
  │     │     potong_selesai = max(potong_selesai, potong_ke)
  │     │
  │     ├─→ Check Completion
  │     │     │
  │     │     ├─→ Complete → Update Job (status: DONE)
  │     │     │
  │     │     └─→ Not Complete → Keep Status
  │     │
  │     └─→ Return JSON Response
  │
  └─→ Dashboard Auto-Refresh → Show Updated Progress
```

### Flow: Stop Job

```
User → Dashboard → Click STOP
  │
  ├─→ API: stop.php
  │     │
  │     ├─→ Find Running Job
  │     │
  │     ├─→ Send HTTP GET to ESP32
  │     │     GET /stop
  │     │
  │     ├─→ Update Job (status: STOPPED)
  │     │
  │     └─→ Return JSON Response
  │
  └─→ Dashboard Refresh → Show Status
```

## 🗄️ Entity Relationship Diagram (ERD)

```
┌─────────────────┐
│     users       │
├─────────────────┤
│ id (PK)         │
│ username (UK)   │
│ password        │
│ nama            │
│ created_at      │
│ updated_at      │
└────────┬────────┘
         │
         │ 1:N
         │
         ▼
┌─────────────────┐
│   job_potong    │
├─────────────────┤
│ id (PK)         │
│ panjang_mm      │
│ jumlah_potong   │
│ status          │
│ potong_selesai  │
│ user_id (FK)    │──┐
│ started_at      │  │
│ stopped_at      │  │
│ completed_at    │  │
│ created_at      │  │
│ updated_at      │  │
└────────┬────────┘  │
         │           │
         │ 1:N       │
         │           │
         ▼           │
┌─────────────────┐ │
│   log_potong    │ │
├─────────────────┤ │
│ id (PK)         │ │
│ job_id (FK) ────┘ │
│ potong_ke       │ │
│ panjang_mm      │ │
│ status          │ │
│ waktu_potong    │ │
│ created_at      │ │
└─────────────────┘ │
                    │
                    │
                    └─── Referensi ke users.id
```

## 📦 Komponen Aplikasi

### 1. Frontend Components

- **Login Page** (`login.php`)
  - Form autentikasi
  - Session management
  - Responsive design

- **Dashboard** (`dashboard.php`)
  - Control panel (panjang, jumlah)
  - START/STOP buttons
  - Status display
  - Progress bar
  - Recent jobs table
  - Auto-refresh (2 detik)

- **Log Page** (`log.php`)
  - Tabel log detail
  - Filter by job_id
  - Pagination

### 2. Backend Components

- **Config Module** (`config/`)
  - `database.php`: Database connection
  - `auth.php`: Authentication & session
  - `functions.php`: Helper functions

- **API Module** (`api/`)
  - `start.php`: Start job endpoint
  - `stop.php`: Stop job endpoint
  - `progress.php`: Receive progress from ESP32

### 3. Database Schema

- **users**: User management
- **job_potong**: Job records
- **log_potong**: Detailed cut logs

## 🔐 Security Architecture

### Authentication Flow

```
1. User → Login Page
2. Submit Credentials
3. Verify Password (bcrypt)
4. Create Session
5. Set Session Variables:
   - user_id
   - username
   - login_time
6. Redirect to Dashboard
```

### Authorization Check

```
Every Protected Page:
1. Check Session
2. If Not Logged In → Redirect to Login
3. If Logged In → Allow Access
```

### Data Protection

- Password: bcrypt hash
- SQL Injection: Prepared statements
- XSS: Output escaping
- CSRF: (Bisa ditambahkan token jika diperlukan)

## 🌐 Network Architecture

### Local Network Setup

```
┌─────────────┐
│   Router    │
│ 192.168.1.1│
└──────┬──────┘
       │
       ├──────────────┐
       │              │
       ▼              ▼
┌─────────────┐  ┌─────────────┐
│   Server    │  │    ESP32    │
│192.168.1.100│  │ 192.168.4.1 │
│  (Laragon)  │  │  (AP Mode)  │
└─────────────┘  └─────────────┘
       │
       │ HTTP Request
       │
       ▼
┌─────────────┐
│   Browser   │
│  (Client)   │
└─────────────┘
```

### Communication Protocol

1. **Web → ESP32**: HTTP GET
   - Timeout: 5 detik
   - Retry: Tidak (handled di frontend)

2. **ESP32 → Web**: HTTP POST
   - Content-Type: application/json
   - CORS enabled
   - No authentication (local network)

## 📊 Data Flow

### Job Lifecycle

```
CREATED (READY)
    │
    ├─→ START Command
    │
    ▼
RUNNING
    │
    ├─→ Progress Updates (potong_ke++)
    │
    ├─→ STOP Command
    │   │
    │   └─→ STOPPED
    │
    └─→ Complete (potong_selesai == jumlah_potong)
        │
        └─→ DONE
```

## 🎯 Design Principles

1. **Simplicity**: PHP native, no framework overhead
2. **Reliability**: Error handling, validation
3. **Security**: Prepared statements, password hashing
4. **Usability**: Mobile-friendly, clear UI
5. **Maintainability**: Clean code, documentation

## 📝 Notes

- Single user system (admin only)
- No real-time WebSocket (using polling)
- Local network only (no internet required)
- ESP32 acts as WiFi Access Point
- Database on same server as web app

