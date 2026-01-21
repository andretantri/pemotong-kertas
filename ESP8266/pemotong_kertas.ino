// ================= LIBRARY & INCLUDE =================
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
#include <ESP8266WebServer.h>
#include <WiFiClient.h>

// ================= PIN DEFINISI ESP8266 =================
// Motor Penarik Kertas (Pull Motor)
#define STEP_PULL 2   // D4 - GPIO2
#define DIR_PULL  0   // D3 - GPIO0
#define EN_PULL   4   // D2 - GPIO4

// Motor Pemotong Kertas (Cut Motor)
#define STEP_CUT  12  // D6 - GPIO12
#define DIR_CUT   14  // D5 - GPIO14
#define EN_CUT    13  // D7 - GPIO13

// ================= WIFI CONFIGURATION =================
// GANTI DENGAN KONFIGURASI WIFI ANDA!
const char* ap_ssid = "Mesin_Potong_Kertas";
const char* ap_password = "password123";
// Laptop (Server) IP when connected to ESP8266 AP is usually 192.168.4.2
const char* serverURL = "http://192.168.4.2/pemotongKertas/api"; 

// ================= STRUKTUR DATA KONFIGURASI =================
struct MachineConfig {
  int roller_diameter_mm;
  int pull_distance_cm;
  int pull_steps;       // Steps untuk penarik (dari panjang kertas)
  int cut_steps;        // Steps untuk pemotong (dari jarak cm)
  int pull_delay_ms;
  int cut_delay_ms;
  int step_delay_us;
  int pull_pause_ms;
  int cut_pause_ms;
};

struct JobParameters {
  int job_id;
  int panjang_mm;
  int jumlah_potong;
};

// ================= VARIABEL GLOBAL =================
ESP8266WebServer server(80);
MachineConfig machineConfig;
JobParameters currentJob;
int currentCutCount = 0;
bool isRunning = false;
WiFiClient wifiClient;

// ================= SETUP FUNGSI =================
void setup() {
  Serial.begin(115200);
  delay(1000);
  
  Serial.println("\n\n========================================");
  Serial.println("Paper Cutting Machine ESP8266 v1.0");
  Serial.println("========================================\n");
  
  // Inisialisasi PIN
  pinMode(STEP_PULL, OUTPUT);
  pinMode(DIR_PULL, OUTPUT);
  pinMode(EN_PULL, OUTPUT);
  
  pinMode(STEP_CUT, OUTPUT);
  pinMode(DIR_CUT, OUTPUT);
  pinMode(EN_CUT, OUTPUT);
  
  // MATIKAN SEMUA MOTOR (Active LOW untuk ENABLE)
  digitalWrite(EN_PULL, HIGH);
  digitalWrite(EN_CUT, HIGH);
  
  // Set default states
  digitalWrite(STEP_PULL, LOW);
  digitalWrite(STEP_CUT, LOW);
  digitalWrite(DIR_PULL, LOW);
  digitalWrite(DIR_CUT, LOW);
  
  Serial.println("✓ GPIO pins initialized");
  
  // Hubungkan ke WiFi
  connectToWiFi();
  
  // Setup Web Server
  setupWebServer();
  
  // Muat konfigurasi dari server
  loadMachineConfig();
  
  Serial.println("\n========================================");
  Serial.println("Setup Complete! Ready to operate.");
  Serial.println("========================================\n");
}

// ================= MAIN LOOP =================
void loop() {
  // Handle HTTP requests
  server.handleClient();
  
  // Jika ada job yang sedang berjalan
  if (isRunning) {
    executeCuttingCycle();
  }
  
  delay(10); // Small delay untuk stability
}

// ================= WIFI SETUP (ACCESS POINT MODE) =================
void connectToWiFi() {
  Serial.println("\n[WIFI] Setting up Access Point...");
  Serial.println("[WIFI] SSID: " + String(ap_ssid));
  
  WiFi.mode(WIFI_AP);
  
  // Set Static IP for ESP (Gateway)
  IPAddress local_IP(192, 168, 4, 1);
  IPAddress gateway(192, 168, 4, 1);
  IPAddress subnet(255, 255, 255, 0);
  WiFi.softAPConfig(local_IP, gateway, subnet);
  
  bool success = WiFi.softAP(ap_ssid, ap_password);
  
  if (success) {
    Serial.println("✓ Access Point Started Successfully!");
    Serial.println("========================================");
    Serial.print("  IP ADDRESS: ");
    Serial.println(WiFi.softAPIP());
    Serial.println("  Connect your laptop to this WiFi");
    Serial.println("  Then open: http://" + WiFi.softAPIP().toString());
    Serial.println("========================================");
  } else {
    Serial.println("✗ Failed to start Access Point");
  }
}

// ================= SETUP WEB SERVER =================
void setupWebServer() {
  // Root endpoint - for connection check
  server.on("/", HTTP_GET, []() {
    String json = "{\"success\":true,\"message\":\"ESP8266 Online\",\"status\":\"";
    json += isRunning ? "RUNNING" : "READY";
    json += "\"}";
    server.send(200, "application/json", json);
    Serial.println("[WEB] Root request handled");
  });
  
  // Status endpoint
  server.on("/status", HTTP_GET, []() {
    String json = "{\"success\":true,\"isRunning\":";
    json += isRunning ? "true" : "false";
    json += ",\"currentCut\":";
    json += String(currentCutCount);
    json += ",\"totalCuts\":";
    json += String(currentJob.jumlah_potong);
    json += ",\"jobId\":";
    json += String(currentJob.job_id);
    json += "}";
    server.send(200, "application/json", json);
  });
  
  // Start endpoint
  server.on("/start", HTTP_GET, []() {
    if (isRunning) {
      server.send(400, "application/json", "{\"success\":false,\"message\":\"Already running\"}");
      return;
    }
    
    currentJob.panjang_mm = server.arg("panjang").toInt();
    currentJob.jumlah_potong = server.arg("jumlah").toInt();
    currentJob.job_id = server.arg("job_id").toInt();
    
    // Update machine config if provided
    if (server.hasArg("pull_steps")) {
      machineConfig.pull_steps = server.arg("pull_steps").toInt();
    }
    if (server.hasArg("cut_steps")) {
      machineConfig.cut_steps = server.arg("cut_steps").toInt();
    }
    if (server.hasArg("step_delay_us")) {
      machineConfig.step_delay_us = server.arg("step_delay_us").toInt();
    }
    if (server.hasArg("pull_delay_ms")) {
      machineConfig.pull_delay_ms = server.arg("pull_delay_ms").toInt();
    }
    if (server.hasArg("cut_delay_ms")) {
      machineConfig.cut_delay_ms = server.arg("cut_delay_ms").toInt();
    }
    if (server.hasArg("pull_pause_ms")) {
      machineConfig.pull_pause_ms = server.arg("pull_pause_ms").toInt();
    }
    if (server.hasArg("cut_pause_ms")) {
      machineConfig.cut_pause_ms = server.arg("cut_pause_ms").toInt();
    }
    
    if (currentJob.panjang_mm <= 0 || currentJob.jumlah_potong <= 0) {
      server.send(400, "application/json", "{\"success\":false,\"message\":\"Invalid parameters\"}");
      return;
    }
    
    isRunning = true;
    currentCutCount = 0;
    
    server.send(200, "application/json", "{\"success\":true,\"message\":\"Job started\"}");
    
    Serial.println("\n========== JOB STARTED ==========");
    Serial.println("[WEB] Job ID: " + String(currentJob.job_id));
    Serial.println("[WEB] Panjang: " + String(currentJob.panjang_mm) + " mm");
    Serial.println("[WEB] Jumlah: " + String(currentJob.jumlah_potong) + " potong");
    Serial.println("[CONFIG] Pull steps: " + String(machineConfig.pull_steps) + " (penarik)");
    Serial.println("[CONFIG] Cut steps: " + String(machineConfig.cut_steps) + " (pemotong)");
    Serial.println("[CONFIG] Step delay: " + String(machineConfig.step_delay_us) + " us");
    Serial.println("==================================\n");
  });
  
  // Stop endpoint
  server.on("/stop", HTTP_GET, []() {
    isRunning = false;
    server.send(200, "application/json", "{\"success\":true,\"message\":\"Job stopped\"}");
    Serial.println("[WEB] Stop command received");
  });
  
  server.begin();
  Serial.println("✓ Web server started on port 80");
}

// ================= AMBIL KONFIGURASI DARI SERVER =================
bool loadMachineConfig() {
  // Dalam mode AP, kita tidak bisa mengakses server eksternal
  // Gunakan default config
  Serial.println("[CONFIG] Running in AP mode, using default configuration");
  loadDefaultConfig();
  return false;
}

// ================= DEFAULT CONFIGURATION =================
void loadDefaultConfig() {
  Serial.println("Loading default configuration...");
  machineConfig.roller_diameter_mm = 17;
  machineConfig.pull_distance_cm = 5;
  machineConfig.pull_steps = 320;  // Estimasi untuk 17mm roller (penarik)
  machineConfig.cut_steps = 200;   // Default steps untuk pemotong
  machineConfig.pull_delay_ms = 500;
  machineConfig.cut_delay_ms = 500;
  machineConfig.step_delay_us = 1200;
  machineConfig.pull_pause_ms = 1000;
  machineConfig.cut_pause_ms = 2000;
  printMachineConfig();
}

// ================= PRINT KONFIGURASI =================
void printMachineConfig() {
  Serial.println("\n========== MACHINE CONFIGURATION ==========");
  Serial.println("Roller diameter : " + String(machineConfig.roller_diameter_mm) + " mm");
  Serial.println("Pull distance   : " + String(machineConfig.pull_distance_cm) + " cm");
  Serial.println("Pull steps      : " + String(machineConfig.pull_steps));
  Serial.println("Step delay      : " + String(machineConfig.step_delay_us) + " µs");
  Serial.println("Pull delay      : " + String(machineConfig.pull_delay_ms) + " ms");
  Serial.println("Cut delay       : " + String(machineConfig.cut_delay_ms) + " ms");
  Serial.println("Pull pause      : " + String(machineConfig.pull_pause_ms) + " ms");
  Serial.println("Cut pause       : " + String(machineConfig.cut_pause_ms) + " ms");
  Serial.println("===========================================\n");
}

// ================= MOTOR STEP FUNGSI =================
void stepMotor(int stepPin, int steps, int delayMicros) {
  for (int i = 0; i < steps; i++) {
    digitalWrite(stepPin, HIGH);
    delayMicroseconds(delayMicros);
    digitalWrite(stepPin, LOW);
    delayMicroseconds(delayMicros);
    
    // Handle web requests setiap 100 langkah
    if (i % 100 == 0) {
      Serial.print(".");
      server.handleClient();
      yield(); // ESP8266 specific - prevent watchdog timeout
    }
  }
  Serial.println(" Done");
}

// ================= PENARIK KERTAS - MAJU =================
void pullForward() {
  Serial.println(">>> Pull forward: " + String(machineConfig.pull_steps) + " steps");
  
  digitalWrite(EN_PULL, LOW);    // AKTIFKAN PENARIK
  digitalWrite(EN_CUT, HIGH);    // MATIKAN PEMOTONG
  
  digitalWrite(DIR_PULL, HIGH);  // MAJU
  stepMotor(STEP_PULL, machineConfig.pull_steps, machineConfig.step_delay_us);
}

// ================= PENARIK KERTAS - MUNDUR =================
void pullBackward() {
  Serial.println("<<< Pull backward: " + String(machineConfig.pull_steps) + " steps");
  
  digitalWrite(EN_PULL, LOW);    // AKTIFKAN PENARIK
  digitalWrite(EN_CUT, HIGH);    // MATIKAN PEMOTONG
  
  digitalWrite(DIR_PULL, LOW);   // MUNDUR
  stepMotor(STEP_PULL, machineConfig.pull_steps, machineConfig.step_delay_us);
}

// ================= PEMOTONG - MAJU =================
void cutForward() {
  Serial.println(">>> Cut forward: " + String(machineConfig.cut_steps) + " steps");
  
  digitalWrite(EN_CUT, LOW);     // AKTIFKAN PEMOTONG
  digitalWrite(EN_PULL, HIGH);   // MATIKAN PENARIK
  
  digitalWrite(DIR_CUT, HIGH);   // MAJU
  stepMotor(STEP_CUT, machineConfig.cut_steps, machineConfig.step_delay_us);
}

// ================= PEMOTONG - MUNDUR =================
void cutBackward() {
  Serial.println("<<< Cut backward: " + String(machineConfig.cut_steps) + " steps");
  
  digitalWrite(EN_CUT, LOW);     // AKTIFKAN PEMOTONG
  digitalWrite(EN_PULL, HIGH);   // MATIKAN PENARIK
  
  digitalWrite(DIR_CUT, LOW);    // MUNDUR
  stepMotor(STEP_CUT, machineConfig.cut_steps, machineConfig.step_delay_us);
}

// ================= STOP SEMUA MOTOR =================
void stopAllMotors() {
  digitalWrite(EN_PULL, HIGH);   // MATIKAN PENARIK
  digitalWrite(EN_CUT, HIGH);    // MATIKAN PEMOTONG
  Serial.println("✓ All motors stopped");
}

// ================= SIKLUS PEMOTONGAN LENGKAP =================
void executeCuttingCycle() {
  if (currentCutCount >= currentJob.jumlah_potong) {
    Serial.println("\n========== PEMOTONGAN SELESAI ==========");
    Serial.println("Total: " + String(currentCutCount) + " potong");
    Serial.println("========================================\n");
    
    stopAllMotors();
    isRunning = false;
    
    // Update status ke database
    updateJobStatus("DONE");
    
    currentCutCount = 0;
    return;
  }
  
  currentCutCount++;
  Serial.println("\n========== POTONG KE-" + String(currentCutCount) + " ==========");
  
  // === TAHAP 1: PENARIK MAJU ===
  Serial.println("FASE 1: Penarik maju");
//  pullForward();
  pullBackward();
  delay(machineConfig.pull_delay_ms);
  
  // === TAHAP 2: PENARIK MUNDUR ===
//  Serial.println("FASE 2: Penarik mundur");
//  pullBackward();
//  delay(machineConfig.pull_pause_ms);
  
  // === TAHAP 3: PEMOTONG MAJU ===
  Serial.println("FASE 3: Pemotong maju");
  cutForward();
  delay(machineConfig.cut_delay_ms);
  
  // === TAHAP 4: PEMOTONG MUNDUR ===
  Serial.println("FASE 4: Pemotong mundur");
  cutBackward();
  server.handleClient(); // Keep connection alive
  delay(machineConfig.cut_pause_ms);
  
  Serial.println("========== SELESAI KE-" + String(currentCutCount) + " ==========\n");
  
  // Update progress setiap potong
  updateJobProgress();
}



// ================= UPDATE JOB PROGRESS =================
void updateJobProgress() {
  if (WiFi.status() != WL_CONNECTED) {
    return;
  }
  
  HTTPClient http;
  String progressURL = String(serverURL) + "/progress.php";
  
  Serial.print("[HTTP] Updating Progress: #" + String(currentCutCount) + "... ");
  
  http.begin(wifiClient, progressURL);
  http.addHeader("Content-Type", "application/json");
  http.setTimeout(3000);
  
  // JSON Payload
  String payload = "{";
  payload += "\"job_id\":" + String(currentJob.job_id) + ",";
  payload += "\"potong_ke\":" + String(currentCutCount) + ",";
  payload += "\"status\":\"RUNNING\",";
  payload += "\"panjang_mm\":" + String(currentJob.panjang_mm);
  payload += "}";
  
  int httpCode = http.POST(payload);
  
  if (httpCode > 0) {
    if (httpCode == 200) {
      Serial.println("OK (200)");
    } else {
      Serial.println("HTTP " + String(httpCode));
      Serial.println("[DEBUG] Response: " + http.getString());
    }
  } else {
    Serial.println("FAILED: " + http.errorToString(httpCode));
  }
  
  http.end();
}

// ================= UPDATE JOB STATUS KE DATABASE =================
void updateJobStatus(String status) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("✗ Cannot update status - WiFi not connected");
    return;
  }
  
  HTTPClient http;
  String statusURL = String(serverURL) + "/progress.php";
  
  http.begin(wifiClient, statusURL);
  http.addHeader("Content-Type", "application/json");
  http.setTimeout(5000);
  
  // JSON Payload
  String payload = "{";
  payload += "\"job_id\":" + String(currentJob.job_id) + ",";
  payload += "\"potong_ke\":" + String(currentCutCount) + ",";
  payload += "\"status\":\"" + status + "\",";
  payload += "\"panjang_mm\":" + String(currentJob.panjang_mm);
  payload += "}";
  
  Serial.println("Updating job status...");
  Serial.println("URL: " + statusURL);
  Serial.println("Payload: " + payload);
  
  int httpCode = http.POST(payload);
  
  if (httpCode > 0) {
    Serial.println("✓ Job status updated: " + status + " (Code: " + String(httpCode) + ")");
    String response = http.getString();
    Serial.println("Response: " + response);
  } else {
    Serial.println("✗ Failed to update job status");
    Serial.println("Error: " + http.errorToString(httpCode));
  }
  
  http.end();
}