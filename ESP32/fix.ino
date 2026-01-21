// ================= LIBRARY & INCLUDE =================
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <WebServer.h>

// ================= PIN DEFINISI =================
// Penarik
#define STEP_PULL 25
#define DIR_PULL  26
#define EN_PULL   27

// Pemotong
#define STEP_CUT  32
#define DIR_CUT   33
#define EN_CUT    4

// ================= WIFI CONFIGURATION =================
const char* ssid = "Yanto Fams";
const char* password = "06072618";
const char* serverURL = "http://192.168.18.7/pemotongKertas/api";

// ================= STRUKTUR DATA KONFIGURASI =================
struct MachineConfig {
  int roller_diameter_mm;
  int pull_distance_cm;
  int pull_steps;
  int pull_delay_ms;
  int cut_delay_ms;
  int step_delay_us;
  int pull_pause_ms;
  int cut_pause_ms;
};

struct JobParameters {
  int job_id; // Added job_id
  int panjang_mm;
  int jumlah_potong;
};

// ================= VARIABEL GLOBAL =================
WebServer server(80);
MachineConfig machineConfig;
JobParameters currentJob;
int currentCutCount = 0;
bool isRunning = false;

// ================= SETUP FUNGSI =================
void setup() {
  Serial.begin(115200);
  delay(1000);
  
  Serial.println("\n\nStarting Paper Cutting Machine v2.0...");
  
  // Inisialisasi PIN
  pinMode(STEP_PULL, OUTPUT);
  pinMode(DIR_PULL, OUTPUT);
  pinMode(EN_PULL, OUTPUT);
  
  pinMode(STEP_CUT, OUTPUT);
  pinMode(DIR_CUT, OUTPUT);
  pinMode(EN_CUT, OUTPUT);
  
  // MATIKAN SEMUA MOTOR
  digitalWrite(EN_PULL, HIGH);
  digitalWrite(EN_CUT, HIGH);
  
  // Hubungkan ke WiFi
  connectToWiFi();
  
  // Setup Web Server
  setupWebServer();
  
  // Muat konfigurasi dari server
  loadMachineConfig();
  
  Serial.println("Setup selesai!");
}

// ================= MAIN LOOP =================
void loop() {
  // Handle HTTP requests
  handleWebRequests();
  
  // Jika ada job yang sedang berjalan
  if (isRunning) {
    executeCuttingCycle();
  }
  
  delay(100);
}

// ================= WIFI CONNECTION =================
void connectToWiFi() {
  Serial.print("Connecting to WiFi: ");
  Serial.println(ssid);
  
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 30) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi connected!");
    Serial.println("===============================");
    Serial.print("IP ADDRESS: ");
    Serial.println(WiFi.localIP());
    Serial.println("===============================");
  } else {
    Serial.println("\nFailed to connect to WiFi");
  }
}

// ================= AMBIL KONFIGURASI DARI SERVER =================
bool loadMachineConfig() {
  HTTPClient http;
  String configURL = String(serverURL) + "/get_config.php";
  
  Serial.println("Loading config from: " + configURL);
  
  http.begin(configURL);
  int httpCode = http.GET();
  
  if (httpCode == 200) {
    String payload = http.getString();
    Serial.println("Config Response: " + payload);
    
    // Parse JSON
    StaticJsonDocument<512> doc;
    DeserializationError error = deserializeJson(doc, payload);
    
    if (!error && doc["success"]) {
      JsonObject config = doc["config"];
      machineConfig.roller_diameter_mm = config["roller_diameter_mm"];
      machineConfig.pull_distance_cm = config["pull_distance_cm"];
      machineConfig.pull_steps = config["pull_steps"];
      machineConfig.pull_delay_ms = config["pull_delay_ms"];
      machineConfig.cut_delay_ms = config["cut_delay_ms"];
      machineConfig.step_delay_us = config["step_delay_us"];
      machineConfig.pull_pause_ms = config["pull_pause_ms"];
      machineConfig.cut_pause_ms = config["cut_pause_ms"];
      
      Serial.println("Config loaded successfully!");
      printMachineConfig();
      
      http.end();
      return true;
    }
  }
  
  http.end();
  loadDefaultConfig();
  return false;
}

// ================= DEFAULT CONFIGURATION =================
void loadDefaultConfig() {
  Serial.println("Loading default configuration...");
  machineConfig.roller_diameter_mm = 17;
  machineConfig.pull_distance_cm = 5;
  machineConfig.pull_steps = 320;  // Estimasi 17mm roller
  machineConfig.pull_delay_ms = 500;
  machineConfig.cut_delay_ms = 500;
  machineConfig.step_delay_us = 1200;
  machineConfig.pull_pause_ms = 1000;
  machineConfig.cut_pause_ms = 2000;
  printMachineConfig();
}

// ================= PRINT KONFIGURASI =================
void printMachineConfig() {
  Serial.println("=== MACHINE CONFIGURATION ===");
  Serial.println("Roller diameter: " + String(machineConfig.roller_diameter_mm) + " mm");
  Serial.println("Pull distance: " + String(machineConfig.pull_distance_cm) + " cm");
  Serial.println("Pull steps: " + String(machineConfig.pull_steps));
  Serial.println("Step delay: " + String(machineConfig.step_delay_us) + " µs");
  Serial.println("Pull pause: " + String(machineConfig.pull_pause_ms) + " ms");
  Serial.println("Cut pause: " + String(machineConfig.cut_pause_ms) + " ms");
  Serial.println("=============================");
}

// ================= MOTOR STEP FUNGSI =================
void stepMotor(int stepPin, int steps, int delayMicros) {
  for (int i = 0; i < steps; i++) {
    digitalWrite(stepPin, HIGH);
    delayMicroseconds(delayMicros);
    digitalWrite(stepPin, LOW);
    delayMicroseconds(delayMicros);
    
    // Debug setiap 100 langkah supaya tidak flooding serial
    if (i % 100 == 0) {
      Serial.print(".");
      // Handle web requests supaya koneksi tidak dianggap putus oleh client
      // meski sedang bergerak
      server.handleClient();
    }
  }
  Serial.println(" Done");
}

// ================= PENARIK KERTAS - MAJU =================
void pullForward() {
  Serial.println(">>> Pull forward: " + String(machineConfig.pull_steps) + " steps, Delay: " + String(machineConfig.step_delay_us));
  
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
  Serial.println(">>> Cut forward: " + String(machineConfig.pull_steps) + " steps");
  
  digitalWrite(EN_CUT, LOW);     // AKTIFKAN PEMOTONG
  digitalWrite(EN_PULL, HIGH);   // MATIKAN PENARIK
  
  digitalWrite(DIR_CUT, HIGH);   // MAJU
  stepMotor(STEP_CUT, machineConfig.pull_steps, machineConfig.step_delay_us);
}

// ================= PEMOTONG - MUNDUR =================
void cutBackward() {
  Serial.println("<<< Cut backward: " + String(machineConfig.pull_steps) + " steps");
  
  digitalWrite(EN_CUT, LOW);     // AKTIFKAN PEMOTONG
  digitalWrite(EN_PULL, HIGH);   // MATIKAN PENARIK
  
  digitalWrite(DIR_CUT, LOW);    // MUNDUR
  stepMotor(STEP_CUT, machineConfig.pull_steps, machineConfig.step_delay_us);
}

// ================= STOP SEMUA MOTOR =================
void stopAllMotors() {
  digitalWrite(EN_PULL, HIGH);   // MATIKAN PENARIK
  digitalWrite(EN_CUT, HIGH);    // MATIKAN PEMOTONG
  Serial.println("All motors stopped");
}

// ================= SIKLUS PEMOTONGAN LENGKAP =================
void executeCuttingCycle() {
  if (currentCutCount >= currentJob.jumlah_potong) {
    Serial.println("\n=== PEMOTONGAN SELESAI ===");
    Serial.println("Total: " + String(currentCutCount) + " potong");
    
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
  pullForward();
  delay(machineConfig.pull_delay_ms);
  
  // === TAHAP 2: PENARIK MUNDUR ===
  Serial.println("FASE 2: Penarik mundur");
  pullBackward();
  delay(machineConfig.pull_pause_ms);
  
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
}

// ================= WEB SERVER HANDLERS =================
void handleRoot() {
  server.send(200, "text/plain", "Paper Cutter ESP32 Ready");
}

void handleStart() {
  if (isRunning) {
    server.send(400, "application/json", "{\"success\":false, \"message\":\"Job already running\"}");
    return;
  }
  
  if (!server.hasArg("panjang") || !server.hasArg("jumlah")) {
    server.send(400, "application/json", "{\"success\":false, \"message\":\"Missing parameters\"}");
    return;
  }
  
  // Set Parameter Job
  if (server.hasArg("job_id")) {
    currentJob.job_id = server.arg("job_id").toInt();
  } else {
    currentJob.job_id = 0;
  }
  currentJob.panjang_mm = server.arg("panjang").toInt();
  currentJob.jumlah_potong = server.arg("jumlah").toInt();
  
  // Reset Counter
  currentCutCount = 0;
  isRunning = true;
  
  // Hitung langkah berdasarkan panjang (Logic Sederhana Perlu Kalibrasi)
  // Untuk sementara kita gunakan nilai dari parameter (butuh konversi mm ke step nanti)
  // machineConfig.pull_steps = ... (logic konversi di sini idealnya)
  
  Serial.println("START JOB ID: " + String(currentJob.job_id) + ", P=" + String(currentJob.panjang_mm) + "mm, N=" + String(currentJob.jumlah_potong));
  
  String response = "{\"success\":true, \"message\":\"Job started\", \"data\":{\"panjang\":" + String(currentJob.panjang_mm) + ", \"jumlah\":" + String(currentJob.jumlah_potong) + "}}";
  server.send(200, "application/json", response);
}

void handleStop() {
  isRunning = false;
  stopAllMotors();
  Serial.println("STOP JOB RECEIVED");
  server.send(200, "application/json", "{\"success\":true, \"message\":\"Job stopped\"}");
}

void handleStatus() {
  String status = isRunning ? "RUNNING" : "READY";
  String json = "{\"success\":true, \"status\":\"" + status + "\", \"cut_count\":" + String(currentCutCount) + ", \"total\":" + String(currentJob.jumlah_potong) + "}";
  server.send(200, "application/json", json);
}

// ================= WEB SERVER SETUP =================
void setupWebServer() {
  Serial.println("Starting Web Server...");
  
  server.on("/", handleRoot);
  server.on("/start", handleStart);
  server.on("/stop", handleStop);
  server.on("/status", handleStatus);
  
  server.begin();
  Serial.println("HTTP Server started on port 80");
}

// ================= HANDLE WEB REQUESTS =================
void handleWebRequests() {
  server.handleClient();
}

// ================= UPDATE JOB STATUS KE DATABASE =================
void updateJobStatus(String status) {
  HTTPClient http;
  String statusURL = String(serverURL) + "/progress.php";
  
  http.begin(statusURL);
  http.addHeader("Content-Type", "application/json");
  
  // JSON Payload
  // Note: job_id harusnya dikirim dari start, tapi kalau belum ada kita kirim 0 
  // atau perlu modif handleStart untuk simpan job_id
  String payload = "{";
  payload += "\"job_id\":" + String(currentJob.job_id) + ",";
  payload += "\"potong_ke\":" + String(currentCutCount) + ",";
  payload += "\"status\":\"" + status + "\",";
  payload += "\"panjang_mm\":" + String(currentJob.panjang_mm);
  payload += "}";
  
  Serial.println("Update Status URL: " + statusURL);
  Serial.println("Payload: " + payload);
  
  int httpCode = http.POST(payload);
  
  if (httpCode > 0) {
    Serial.println("Job status updated: " + status + ", Code: " + String(httpCode));
    String response = http.getString();
    Serial.println("Response: " + response);
  } else {
    Serial.println("Failed to update job status. Error: " + http.errorToString(httpCode));
  }
  
  http.end();
}
