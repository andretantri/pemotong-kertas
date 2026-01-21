// ================= DEBUG MOTOR TEST v2 =================
// Upload file ini untuk cek apakah motor bergerak
// Buka Serial Monitor di baudrate 115200

// ================= PIN DEFINISI =================
// PENARIK (Roller)
#define STEP_PULL 25
#define DIR_PULL  26
#define EN_PULL   27

// PEMOTONG (Slider)
#define STEP_CUT  32
#define DIR_CUT   33
#define EN_CUT    4

// ================= PARAMETER KECEPATAN (DIPERLAMBAT) =================
// Semakin BESAR nilai, semakin LAMBAT.
// 500us terlalu cepat untuk start mendadak. Kita pakai 2000us - 3000us untuk tes.
#define STEP_DELAY_TEST 2500    

// Jarak (Steps)
#define STEP_JARAK 200

// ================= FUNGSI STEP =================
void stepMotor(String name, int stepPin, int steps, int delayUs) {
  Serial.print("Gerak " + name + "... ");
  for (int i = 0; i < steps; i++) {
    digitalWrite(stepPin, HIGH);
    delayMicroseconds(delayUs);
    digitalWrite(stepPin, LOW);
    delayMicroseconds(delayUs);
  }
  Serial.println("OK");
}

void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("\n\n=== MULAI TEST MOTOR ===");
  Serial.println("Pastikan POWER SUPLY motor (12V/24V) sudah ON dan terhubung!");

  // PENARIK
  pinMode(STEP_PULL, OUTPUT);
  pinMode(DIR_PULL, OUTPUT);
  pinMode(EN_PULL, OUTPUT);

  // PEMOTONG
  pinMode(STEP_CUT, OUTPUT);
  pinMode(DIR_CUT, OUTPUT);
  pinMode(EN_CUT, OUTPUT);

  // Default: Disable (HIGH biasanya disable untuk A4988/CNC Shield)
  digitalWrite(EN_PULL, HIGH);
  digitalWrite(EN_CUT, HIGH);
  
  delay(2000);
}

void loop() {
  Serial.println("\n--- SIKLUS BARU ---");

  // ================= PENARIK =================
  Serial.println("1. Tes PENARIK (Enable LOW)");
  digitalWrite(EN_PULL, LOW);     // AKTIFKAN DRIVER
  delay(50); // Beri waktu driver bangun
  
  digitalWrite(DIR_PULL, HIGH);   // ARAH 1
  stepMotor("Penarik Arah 1", STEP_PULL, STEP_JARAK, STEP_DELAY_TEST);
  delay(500);

  digitalWrite(DIR_PULL, LOW);    // ARAH 2
  stepMotor("Penarik Arah 2", STEP_PULL, STEP_JARAK, STEP_DELAY_TEST);
  delay(500);

  digitalWrite(EN_PULL, HIGH);    // MATIKAN DRIVER
  
  delay(1000);

  // ================= PEMOTONG =================
  Serial.println("2. Tes PEMOTONG (Enable LOW)");
  digitalWrite(EN_CUT, LOW);      // AKTIFKAN DRIVER
  delay(50);

  digitalWrite(DIR_CUT, HIGH);    // ARAH 1
  stepMotor("Pemotong Arah 1", STEP_CUT, STEP_JARAK, STEP_DELAY_TEST);
  delay(500);

  digitalWrite(DIR_CUT, LOW);     // ARAH 2
  stepMotor("Pemotong Arah 2", STEP_CUT, STEP_JARAK, STEP_DELAY_TEST);
  delay(500);

  digitalWrite(EN_CUT, HIGH);     // MATIKAN DRIVER
  
  delay(2000);
}
