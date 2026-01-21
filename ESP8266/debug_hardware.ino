// ===============================================================
// ESP8266 HARDWARE DEBUGGER - Paper Cutting Machine
// ===============================================================
// File ini untuk testing routing kabel dan koneksi hardware
// Upload file ini untuk memastikan semua koneksi sudah benar
// sebelum menggunakan pemotong_kertas.ino
// ===============================================================

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
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
const char* ssid = "Yanto Fams";
const char* password = "06072618";
const char* serverURL = "http://192.168.18.51/pemotongKertas/api";

WiFiClient wifiClient;

// ================= VARIABEL TESTING =================
int testSteps = 200;        // Jumlah step untuk testing motor
int testDelay = 1500;       // Delay antar step (microseconds)

// ================= SETUP =================
void setup() {
  Serial.begin(115200);
  delay(2000);
  
  printHeader();
  
  // Initialize pins
  pinMode(STEP_PULL, OUTPUT);
  pinMode(DIR_PULL, OUTPUT);
  pinMode(EN_PULL, OUTPUT);
  
  pinMode(STEP_CUT, OUTPUT);
  pinMode(DIR_CUT, OUTPUT);
  pinMode(EN_CUT, OUTPUT);
  
  // Set default state - motors OFF
  digitalWrite(EN_PULL, HIGH);
  digitalWrite(EN_CUT, HIGH);
  digitalWrite(STEP_PULL, LOW);
  digitalWrite(STEP_CUT, LOW);
  digitalWrite(DIR_PULL, LOW);
  digitalWrite(DIR_CUT, LOW);
  
  Serial.println("✓ GPIO pins initialized");
  Serial.println("\n========================================");
  Serial.println("READY FOR TESTING");
  Serial.println("========================================\n");
  
  delay(2000);
  
  // Mulai testing sequence
  runAllTests();
}

// ================= MAIN LOOP =================
void loop() {
  // Show menu
  showMenu();
  
  // Wait for user input
  while (!Serial.available()) {
    delay(100);
  }
  
  char choice = Serial.read();
  
  // Clear serial buffer
  while (Serial.available()) {
    Serial.read();
  }
  
  Serial.println("\n");
  handleMenuChoice(choice);
}

// ================= PRINT HEADER =================
void printHeader() {
  Serial.println("\n\n");
  Serial.println("╔════════════════════════════════════════════════╗");
  Serial.println("║   ESP8266 HARDWARE DEBUGGER v1.0              ║");
  Serial.println("║   Paper Cutting Machine Controller            ║");
  Serial.println("╚════════════════════════════════════════════════╝");
  Serial.println();
}

// ================= SHOW MENU =================
void showMenu() {
  Serial.println("\n========================================");
  Serial.println("           TEST MENU");
  Serial.println("========================================");
  Serial.println("1. Test Pin Output (LED Blink)");
  Serial.println("2. Test Motor Penarik (Pull)");
  Serial.println("3. Test Motor Pemotong (Cut)");
  Serial.println("4. Test Both Motors");
  Serial.println("5. Test WiFi Connection");
  Serial.println("6. Test API Connection");
  Serial.println("7. Run All Tests");
  Serial.println("8. Show Pin Configuration");
  Serial.println("9. Manual Step Control");
  Serial.println("0. Reset All Motors");
  Serial.println("========================================");
  Serial.print("Pilih test (0-9): ");
}

// ================= HANDLE MENU CHOICE =================
void handleMenuChoice(char choice) {
  switch(choice) {
    case '1':
      testPinOutput();
      break;
    case '2':
      testPullMotor();
      break;
    case '3':
      testCutMotor();
      break;
    case '4':
      testBothMotors();
      break;
    case '5':
      testWiFiConnection();
      break;
    case '6':
      testAPIConnection();
      break;
    case '7':
      runAllTests();
      break;
    case '8':
      showPinConfiguration();
      break;
    case '9':
      manualStepControl();
      break;
    case '0':
      resetAllMotors();
      break;
    default:
      Serial.println("❌ Pilihan tidak valid!");
      break;
  }
}

// ================= RUN ALL TESTS =================
void runAllTests() {
  Serial.println("\n╔════════════════════════════════════════════════╗");
  Serial.println("║        RUNNING ALL TESTS                       ║");
  Serial.println("╚════════════════════════════════════════════════╝\n");
  
  delay(1000);
  
  testPinOutput();
  delay(2000);
  
  testWiFiConnection();
  delay(2000);
  
  testAPIConnection();
  delay(2000);
  
  testPullMotor();
  delay(2000);
  
  testCutMotor();
  delay(2000);
  
  Serial.println("\n╔════════════════════════════════════════════════╗");
  Serial.println("║        ALL TESTS COMPLETED                     ║");
  Serial.println("╚════════════════════════════════════════════════╝\n");
}

// ================= TEST 1: PIN OUTPUT =================
void testPinOutput() {
  Serial.println("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  Serial.println("TEST 1: PIN OUTPUT (LED BLINK TEST)");
  Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  Serial.println("Testing each pin by toggling HIGH/LOW");
  Serial.println("You can measure with multimeter or LED");
  Serial.println();
  
  int pins[] = {STEP_PULL, DIR_PULL, EN_PULL, STEP_CUT, DIR_CUT, EN_CUT};
  String pinNames[] = {"STEP_PULL (D4/GPIO2)", "DIR_PULL (D3/GPIO0)", "EN_PULL (D2/GPIO4)", 
                       "STEP_CUT (D6/GPIO12)", "DIR_CUT (D5/GPIO14)", "EN_CUT (D7/GPIO13)"};
  
  for (int i = 0; i < 6; i++) {
    Serial.print("Testing " + pinNames[i] + " ... ");
    
    for (int j = 0; j < 5; j++) {
      digitalWrite(pins[i], HIGH);
      delay(200);
      digitalWrite(pins[i], LOW);
      delay(200);
      Serial.print(".");
    }
    
    Serial.println(" ✓ OK");
    delay(500);
  }
  
  Serial.println("\n✅ Pin Output Test COMPLETED");
  Serial.println("   All pins toggled successfully");
}

// ================= TEST 2: PULL MOTOR =================
void testPullMotor() {
  Serial.println("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  Serial.println("TEST 2: MOTOR PENARIK (PULL MOTOR)");
  Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  Serial.println("Motor should rotate in both directions");
  Serial.println();
  
  // Enable motor
  Serial.println("⚙️  Enabling Pull Motor...");
  digitalWrite(EN_PULL, LOW);
  digitalWrite(EN_CUT, HIGH);
  delay(500);
  
  // Test Forward
  Serial.println("▶️  Testing FORWARD rotation (" + String(testSteps) + " steps)...");
  digitalWrite(DIR_PULL, HIGH);
  stepMotor(STEP_PULL, testSteps, testDelay, "PULL_FWD");
  delay(1000);
  
  // Test Backward
  Serial.println("◀️  Testing BACKWARD rotation (" + String(testSteps) + " steps)...");
  digitalWrite(DIR_PULL, LOW);
  stepMotor(STEP_PULL, testSteps, testDelay, "PULL_BWD");
  delay(1000);
  
  // Disable motor
  digitalWrite(EN_PULL, HIGH);
  
  Serial.println("\n✅ Pull Motor Test COMPLETED");
  Serial.println("   Check if motor rotated properly");
}

// ================= TEST 3: CUT MOTOR =================
void testCutMotor() {
  Serial.println("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  Serial.println("TEST 3: MOTOR PEMOTONG (CUT MOTOR)");
  Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  Serial.println("Motor should rotate in both directions");
  Serial.println();
  
  // Enable motor
  Serial.println("⚙️  Enabling Cut Motor...");
  digitalWrite(EN_CUT, LOW);
  digitalWrite(EN_PULL, HIGH);
  delay(500);
  
  // Test Forward
  Serial.println("▶️  Testing FORWARD rotation (" + String(testSteps) + " steps)...");
  digitalWrite(DIR_CUT, HIGH);
  stepMotor(STEP_CUT, testSteps, testDelay, "CUT_FWD");
  delay(1000);
  
  // Test Backward
  Serial.println("◀️  Testing BACKWARD rotation (" + String(testSteps) + " steps)...");
  digitalWrite(DIR_CUT, LOW);
  stepMotor(STEP_CUT, testSteps, testDelay, "CUT_BWD");
  delay(1000);
  
  // Disable motor
  digitalWrite(EN_CUT, HIGH);
  
  Serial.println("\n✅ Cut Motor Test COMPLETED");
  Serial.println("   Check if motor rotated properly");
}

// ================= TEST 4: BOTH MOTORS =================
void testBothMotors() {
  Serial.println("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  Serial.println("TEST 4: BOTH MOTORS SEQUENTIAL");
  Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  Serial.println("Testing complete cutting sequence");
  Serial.println();
  
  Serial.println("Sequence: Pull Forward → Pull Back → Cut Forward → Cut Back");
  delay(2000);
  
  // Pull Forward
  Serial.println("\n1️⃣  Pull Forward");
  digitalWrite(EN_PULL, LOW);
  digitalWrite(EN_CUT, HIGH);
  digitalWrite(DIR_PULL, HIGH);
  stepMotor(STEP_PULL, testSteps, testDelay, "PULL_FWD");
  delay(500);
  
  // Pull Backward
  Serial.println("\n2️⃣  Pull Backward");
  digitalWrite(DIR_PULL, LOW);
  stepMotor(STEP_PULL, testSteps, testDelay, "PULL_BWD");
  digitalWrite(EN_PULL, HIGH);
  delay(1000);
  
  // Cut Forward
  Serial.println("\n3️⃣  Cut Forward");
  digitalWrite(EN_CUT, LOW);
  digitalWrite(DIR_CUT, HIGH);
  stepMotor(STEP_CUT, testSteps, testDelay, "CUT_FWD");
  delay(500);
  
  // Cut Backward
  Serial.println("\n4️⃣  Cut Backward");
  digitalWrite(DIR_CUT, LOW);
  stepMotor(STEP_CUT, testSteps, testDelay, "CUT_BWD");
  digitalWrite(EN_CUT, HIGH);
  delay(1000);
  
  Serial.println("\n✅ Both Motors Test COMPLETED");
  Serial.println("   Full sequence executed successfully");
}

// ================= TEST 5: WIFI CONNECTION =================
void testWiFiConnection() {
  Serial.println("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  Serial.println("TEST 5: WIFI CONNECTION");
  Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  Serial.println("SSID: " + String(ssid));
  Serial.println();
  
  Serial.print("Connecting to WiFi");
  
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  Serial.println();
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✅ WiFi Connection SUCCESS");
    Serial.println("   IP Address: " + WiFi.localIP().toString());
    Serial.println("   Signal: " + String(WiFi.RSSI()) + " dBm");
    Serial.println("   MAC: " + WiFi.macAddress());
  } else {
    Serial.println("\n❌ WiFi Connection FAILED");
    Serial.println("   Check SSID and password!");
    Serial.println("   Current SSID: " + String(ssid));
  }
}

// ================= TEST 6: API CONNECTION =================
void testAPIConnection() {
  Serial.println("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  Serial.println("TEST 6: API CONNECTION");
  Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("❌ WiFi not connected!");
    Serial.println("   Run WiFi test first (option 5)");
    return;
  }
  
  String configURL = String(serverURL) + "/get_config.php";
  Serial.println("Testing URL: " + configURL);
  Serial.println();
  
  HTTPClient http;
  http.begin(wifiClient, configURL);
  http.setTimeout(5000);
  
  Serial.print("Sending GET request... ");
  int httpCode = http.GET();
  
  if (httpCode == 200) {
    Serial.println("✓");
    
    String payload = http.getString();
    Serial.println("\n📥 Response received:");
    Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
    Serial.println(payload);
    Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
    
    // Parse JSON
    DynamicJsonDocument doc(1024);
    DeserializationError error = deserializeJson(doc, payload);
    
    if (!error && doc["success"]) {
      Serial.println("\n✅ API Connection SUCCESS");
      Serial.println("   Config loaded successfully");
      
      JsonObject config = doc["config"];
      Serial.println("\n📊 Configuration received:");
      Serial.println("   Roller Diameter: " + String((int)config["roller_diameter_mm"]) + " mm");
      Serial.println("   Pull Distance: " + String((int)config["pull_distance_cm"]) + " cm");
      Serial.println("   Pull Steps: " + String((int)config["pull_steps"]));
      Serial.println("   Step Delay: " + String((int)config["step_delay_us"]) + " µs");
    } else {
      Serial.println("\n⚠️  Response OK but JSON invalid");
      Serial.println("   Error: " + String(error.c_str()));
    }
  } else if (httpCode == 404) {
    Serial.println("❌");
    Serial.println("\n❌ API Connection FAILED - 404 Not Found");
    Serial.println("   Check if file exists: /api/get_config.php");
    Serial.println("   Current URL: " + configURL);
  } else {
    Serial.println("❌");
    Serial.println("\n❌ API Connection FAILED");
    Serial.println("   HTTP Code: " + String(httpCode));
    Serial.println("   Error: " + http.errorToString(httpCode));
    Serial.println("\n💡 Troubleshooting:");
    Serial.println("   1. Check if Laragon/Apache is running");
    Serial.println("   2. Test URL in browser");
    Serial.println("   3. Check firewall settings");
  }
  
  http.end();
}

// ================= SHOW PIN CONFIGURATION =================
void showPinConfiguration() {
  Serial.println("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  Serial.println("PIN CONFIGURATION");
  Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  Serial.println();
  
  Serial.println("🔧 Motor Penarik (Pull Motor):");
  Serial.println("   NodeMCU Pin | GPIO | A4988   | Function");
  Serial.println("   ─────────────────────────────────────");
  Serial.println("   D2          | 4    | ENABLE  | Enable Motor");
  Serial.println("   D3          | 0    | DIR     | Direction");
  Serial.println("   D4          | 2    | STEP    | Step Signal");
  Serial.println();
  
  Serial.println("🔧 Motor Pemotong (Cut Motor):");
  Serial.println("   NodeMCU Pin | GPIO | A4988   | Function");
  Serial.println("   ─────────────────────────────────────");
  Serial.println("   D5          | 14   | DIR     | Direction");
  Serial.println("   D6          | 12   | STEP    | Step Signal");
  Serial.println("   D7          | 13   | ENABLE  | Enable Motor");
  Serial.println();
  
  Serial.println("⚡ Power Connections:");
  Serial.println("   12V PSU → A4988 VCC (both drivers)");
  Serial.println("   GND PSU → A4988 GND → ESP8266 GND");
  Serial.println("   5V USB  → ESP8266 VIN/5V");
  Serial.println();
  
  Serial.println("⚠️  WARNINGS:");
  Serial.println("   • ESP8266 works at 3.3V logic");
  Serial.println("   • DO NOT connect ESP8266 to 12V!");
  Serial.println("   • Common GND is MANDATORY");
  Serial.println("   • Max GPIO current: 12mA");
}

// ================= MANUAL STEP CONTROL =================
void manualStepControl() {
  Serial.println("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  Serial.println("MANUAL STEP CONTROL");
  Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  Serial.println();
  Serial.println("Commands:");
  Serial.println("  pf = Pull Forward");
  Serial.println("  pb = Pull Backward");
  Serial.println("  cf = Cut Forward");
  Serial.println("  cb = Cut Backward");
  Serial.println("  s  = Stop all motors");
  Serial.println("  x  = Exit");
  Serial.println();
  
  while (true) {
    Serial.print("Enter command: ");
    
    while (!Serial.available()) {
      delay(10);
    }
    
    String cmd = Serial.readStringUntil('\n');
    cmd.trim();
    cmd.toLowerCase();
    
    Serial.println(cmd);
    
    if (cmd == "x") {
      resetAllMotors();
      break;
    } else if (cmd == "s") {
      resetAllMotors();
    } else if (cmd == "pf") {
      digitalWrite(EN_PULL, LOW);
      digitalWrite(EN_CUT, HIGH);
      digitalWrite(DIR_PULL, HIGH);
      stepMotor(STEP_PULL, 100, 1500, "PULL_FWD");
      digitalWrite(EN_PULL, HIGH);
    } else if (cmd == "pb") {
      digitalWrite(EN_PULL, LOW);
      digitalWrite(EN_CUT, HIGH);
      digitalWrite(DIR_PULL, LOW);
      stepMotor(STEP_PULL, 100, 1500, "PULL_BWD");
      digitalWrite(EN_PULL, HIGH);
    } else if (cmd == "cf") {
      digitalWrite(EN_CUT, LOW);
      digitalWrite(EN_PULL, HIGH);
      digitalWrite(DIR_CUT, HIGH);
      stepMotor(STEP_CUT, 100, 1500, "CUT_FWD");
      digitalWrite(EN_CUT, HIGH);
    } else if (cmd == "cb") {
      digitalWrite(EN_CUT, LOW);
      digitalWrite(EN_PULL, HIGH);
      digitalWrite(DIR_CUT, LOW);
      stepMotor(STEP_CUT, 100, 1500, "CUT_BWD");
      digitalWrite(EN_CUT, HIGH);
    } else {
      Serial.println("❌ Unknown command: " + cmd);
    }
  }
}

// ================= RESET ALL MOTORS =================
void resetAllMotors() {
  Serial.println("\n🔄 Resetting all motors...");
  digitalWrite(EN_PULL, HIGH);
  digitalWrite(EN_CUT, HIGH);
  digitalWrite(STEP_PULL, LOW);
  digitalWrite(STEP_CUT, LOW);
  digitalWrite(DIR_PULL, LOW);
  digitalWrite(DIR_CUT, LOW);
  Serial.println("✓ All motors disabled");
}

// ================= MOTOR STEP FUNCTION =================
void stepMotor(int stepPin, int steps, int delayMicros, String motorName) {
  Serial.print("   Stepping " + motorName + ": ");
  
  for (int i = 0; i < steps; i++) {
    digitalWrite(stepPin, HIGH);
    delayMicroseconds(delayMicros);
    digitalWrite(stepPin, LOW);
    delayMicroseconds(delayMicros);
    
    if (i % 50 == 0 && i > 0) {
      Serial.print(".");
      yield();
    }
  }
  
  Serial.println(" ✓ (" + String(steps) + " steps)");
}
