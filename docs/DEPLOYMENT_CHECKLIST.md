# 🚀 DEPLOYMENT CHECKLIST - Pemakaian Kertas Otomatis v2.0

## Pre-Deployment Verification

### Database Setup
- [ ] Database `hmi_pemotong_kertas` dibuat
- [ ] Tabel `users` ada dengan admin user
- [ ] Tabel `job_potong` ada
- [ ] Tabel `log_potong` ada
- [ ] Tabel `machine_config` ada dengan default values
- [ ] All indexes created correctly
- [ ] Views created (v_job_summary)

**Command to verify:**
```bash
mysql -u root -p hmi_pemotong_kertas -e "SHOW TABLES;"
mysql -u root -p hmi_pemotong_kertas -e "SELECT * FROM machine_config\G"
```

### PHP Configuration
- [ ] PHP version >= 7.4
- [ ] mysqli extension enabled
- [ ] cURL extension enabled
- [ ] JSON support enabled
- [ ] Session support enabled
- [ ] File permissions correct (config/database.php readable)

**Command to verify:**
```bash
php -v
php -m | grep -E "mysqli|curl|json"
```

### Web Server
- [ ] Apache/Nginx running
- [ ] .htaccess configured (if using Apache)
- [ ] CORS headers configured if needed
- [ ] Max upload size >= 1MB
- [ ] Request timeout >= 30 seconds

### File Permissions
- [ ] `config/` folder readable
- [ ] `api/` folder accessible
- [ ] `database/` folder accessible
- [ ] No sensitive files exposed

---

## ESP32 Configuration

### Hardware Requirements
- [ ] ESP32 Dev Module (or compatible)
- [ ] USB cable for upload
- [ ] A4988 Stepper drivers (x2 for pull & cut)
- [ ] NEMA 17 stepper motors (x2)
- [ ] Power supply (12V, 2A minimum)
- [ ] Jumper wires & breadboard

### Pin Configuration Verified
- [ ] STEP_PULL (GPIO 25) connected correctly
- [ ] DIR_PULL (GPIO 26) connected correctly
- [ ] EN_PULL (GPIO 27) connected correctly
- [ ] STEP_CUT (GPIO 32) connected correctly
- [ ] DIR_CUT (GPIO 33) connected correctly
- [ ] EN_CUT (GPIO 14) connected correctly
- [ ] Ground connections verified

### Firmware Configuration
- [ ] WiFi SSID updated in `fix.ino`
- [ ] WiFi password updated in `fix.ino`
- [ ] Server URL updated in `fix.ino`
- [ ] ArduinoJson library installed
- [ ] HTTPClient library available
- [ ] WiFi library available

**Commands to verify libraries:**
```
Arduino IDE → Sketch → Include Library → Manage Libraries
Search: ArduinoJson (v6.x or 7.x) → Install
```

### Upload Process
- [ ] Arduino IDE or PlatformIO installed
- [ ] ESP32 board package installed
- [ ] USB driver installed
- [ ] COM port detected correctly
- [ ] Board selected: ESP32 Dev Module
- [ ] Baud rate set to 115200
- [ ] Firmware compiled successfully
- [ ] Firmware uploaded successfully
- [ ] No errors in upload process

**Compile & Upload:**
```
Arduino IDE:
1. Open ESP32/fix.ino
2. Tools → Board → esp32 → ESP32 Dev Module
3. Tools → Port → COM3 (or your port)
4. Sketch → Upload (Ctrl+U)
5. Wait for "Hard resetting via RTS pin"
```

---

## API Endpoints Verification

### Test Each Endpoint

#### 1. GET /api/get_config.php
```bash
curl http://192.168.x.x/pemotongKertas/api/get_config.php

# Expected Response:
{
  "success": true,
  "message": "Configuration retrieved successfully",
  "config": {
    "roller_diameter_mm": 17,
    "pull_distance_cm": 5,
    "pull_steps": 187,
    ...
  }
}
```
- [ ] Status 200 OK
- [ ] Valid JSON response
- [ ] Config values correct

#### 2. POST /api/config.php (UPDATE)
```bash
curl -X POST http://192.168.x.x/pemotongKertas/api/config.php \
  -H "Content-Type: application/json" \
  -d '{
    "roller_diameter_mm": 17,
    "pull_distance_cm": 5,
    "step_delay_us": 1200
  }' \
  -b "PHPSESSID=your_session_id"

# Expected: Configuration updated
```
- [ ] Status 200 OK
- [ ] Changes saved to database
- [ ] Response shows updated values

#### 3. POST /api/esp32_start.php
```bash
curl -X POST http://192.168.x.x/pemotongKertas/api/esp32_start.php \
  -H "Content-Type: application/json" \
  -d '{
    "panjang": 100,
    "jumlah_potong": 5
  }' \
  -b "PHPSESSID=your_session_id"

# Expected: Job created with ID
```
- [ ] Status 200 OK
- [ ] Job ID returned
- [ ] Database record created
- [ ] Status set to RUNNING

#### 4. POST /api/progress.php
```bash
curl -X POST http://192.168.x.x/pemotongKertas/api/progress.php \
  -H "Content-Type: application/json" \
  -d '{
    "job_id": 1,
    "potong_ke": 1,
    "status": "SUCCESS",
    "panjang_mm": 100
  }'

# Expected: Progress updated
```
- [ ] Status 200 OK
- [ ] Progress recorded in database
- [ ] potong_selesai incremented

#### 5. GET /api/stop.php
```bash
curl http://192.168.x.x/pemotongKertas/api/stop.php \
  -b "PHPSESSID=your_session_id"

# Expected: Job stopped
```
- [ ] Status 200 OK
- [ ] Job status updated to STOPPED

#### 6. GET /api/status.php
```bash
curl http://192.168.x.x/pemotongKertas/api/status.php \
  -b "PHPSESSID=your_session_id"

# Expected: Current status
```
- [ ] Status 200 OK
- [ ] Current job data returned
- [ ] Stats accurate

---

## Web Interface Verification

### Login Page
- [ ] Accessible at `/pemotongKertas/`
- [ ] Default credentials work (admin/admin123)
- [ ] Login redirects to dashboard
- [ ] Session created

### Dashboard
- [ ] All cards load correctly
- [ ] Current job display working
- [ ] Start job button functional
- [ ] Stop job button functional
- [ ] Progress updates in real-time

### Settings Page
- [ ] Accessible from sidebar
- [ ] All input fields loaded
- [ ] Current values displayed
- [ ] Calculations work (circumference, steps)
- [ ] Save button updates database
- [ ] Changes reflected immediately

### Log Page
- [ ] Job history displayed
- [ ] Recent jobs listed
- [ ] Statistics calculated
- [ ] Filter working (optional)

---

## System Integration Test

### Full Workflow Test (Priority: HIGH)

1. **Start Clean State**
   - [ ] No active jobs in database
   - [ ] machine_config has default values

2. **Load Configuration**
   ```bash
   ESP32 loads config via GET /api/get_config.php
   ```
   - [ ] Serial shows config loaded
   - [ ] Values match database

3. **Start Job from Web**
   - [ ] Login to dashboard
   - [ ] Click "Start Job"
   - [ ] Enter panjang: 100 mm
   - [ ] Enter jumlah: 3 potong
   - [ ] Click Submit

4. **Monitor Progress**
   - [ ] Job created in database
   - [ ] Status shows RUNNING
   - [ ] ESP32 starts cutting cycle
   - [ ] Serial shows progress messages

5. **Verify Motor Movement**
   - [ ] Pull motor moves forward
   - [ ] Pull motor moves backward
   - [ ] Cut motor moves forward
   - [ ] Cut motor moves backward
   - [ ] Sequence repeats 3 times

6. **Check Database Updates**
   - [ ] job_potong: potong_selesai incrementing
   - [ ] log_potong: records created for each cut
   - [ ] Progress API responses accurate

7. **Job Completion**
   - [ ] After 3 cycles, motors stop
   - [ ] Job status changes to DONE
   - [ ] completed_at timestamp set
   - [ ] Dashboard shows ✓ completed

8. **Data Integrity**
   - [ ] All records in database correct
   - [ ] No orphaned records
   - [ ] Statistics calculated correctly

---

## Performance Testing

### Load Testing (Optional but Recommended)
- [ ] Multiple simultaneous requests handled
- [ ] Database queries optimized
- [ ] API response time < 500ms
- [ ] No memory leaks observed

### Reliability Testing
- [ ] 10 consecutive jobs completed successfully
- [ ] No data corruption observed
- [ ] No session timeout issues
- [ ] Clean job history maintained

---

## Security Verification

### Access Control
- [ ] Unauthenticated users cannot access:
  - [ ] /api/config.php (POST)
  - [ ] /api/esp32_start.php
  - [ ] /api/stop.php
  - [ ] /api/status.php
  - [ ] settings.php
  - [ ] dashboard.php
  - [ ] log.php

- [ ] Public access allowed to:
  - [ ] /api/get_config.php (GET)
  - [ ] /api/progress.php (POST)
  - [ ] /login.php
  - [ ] index.php (redirects to login)

### Input Validation
- [ ] panjang: 1-10000 mm enforced
- [ ] jumlah_potong: 1-1000 enforced
- [ ] roller_diameter: 1-100 mm enforced
- [ ] step_delay: 500-5000 µs enforced
- [ ] SQL injection prevention verified
- [ ] XSS protection verified

### Credentials
- [ ] Default admin password changed
- [ ] Password hashing verified (bcrypt)
- [ ] Session timeout set (1 hour)
- [ ] CSRF tokens if implemented

---

## Emergency Procedures

### If System Crashes
- [ ] Reset database to initial state
- [ ] Re-upload ESP32 firmware
- [ ] Clear browser cache & restart
- [ ] Restart PHP-FPM/Apache

### If Motors Don't Stop
- [ ] Cut power immediately (safety first!)
- [ ] Check ESP32 GPIO pins
- [ ] Verify motor driver connections
- [ ] Re-upload firmware

### If Communication Lost
- [ ] Check ESP32 WiFi connection
- [ ] Verify server URL in firmware
- [ ] Check firewall/network
- [ ] Review API logs for errors

---

## Documentation Verification

- [ ] SETUP_GUIDE.md complete and accurate
- [ ] API_REFERENCE.md comprehensive
- [ ] IMPLEMENTATION_SUMMARY.md up-to-date
- [ ] Code comments clear
- [ ] README.md updated
- [ ] ARCHITECTURE.md reflects new changes

---

## Go-Live Checklist

### Before Going Live
- [ ] All tests passed
- [ ] Backups created
- [ ] Database optimized
- [ ] Logs cleared (optional)
- [ ] Monitoring setup (optional)
- [ ] Support plan in place

### Production Deployment
- [ ] Database migrated to production server
- [ ] PHP code deployed to production
- [ ] ESP32 firmware uploaded with production URL
- [ ] SSL/HTTPS configured (recommended)
- [ ] Admin password changed
- [ ] Documentation accessible to operators

### Post-Deployment
- [ ] Monitor system for 24 hours
- [ ] Check logs for errors
- [ ] Verify all operations working
- [ ] Confirm backup strategy
- [ ] Train operators on usage
- [ ] Document any issues found

---

## Rollback Plan

### If Critical Issues Found
1. Stop all active jobs
2. Revert database changes (from backup)
3. Restore previous firmware version to ESP32
4. Restart all services
5. Verify system functionality
6. Investigate root cause

---

## Sign-Off

- [ ] Technical Lead: _________________ Date: _______
- [ ] Project Manager: ________________ Date: _______
- [ ] QA Tester: ____________________ Date: _______

---

**Last Updated:** 2 Januari 2026  
**Version:** 2.0.0  
**Status:** ⏳ READY FOR DEPLOYMENT (pending verification)

