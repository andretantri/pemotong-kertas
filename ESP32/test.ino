// ================= PIN SWAP TEST (PIN 13) =================
// DIAGNOSA: PIN 25 MUNGKIN RUSAK / APES.
// SOLUSI: KITA PINDAH KABEL 'STEP' KE PIN 13.
// 
// INSTRUKSI WAJIB:
// 1. CABUT KABEL DARI PIN 'D25' (atau G25) DI ESP32.
// 2. PINDAHKAN KABEL ITU KE PIN 'D13' (atau G13).
// 3. JANGAN UBAH YANG LAIN (DIR/EN BIARKAN SAJA).

#define STEP_PIN 13  // PIN BARU (Ganti kabel fisik Anda ke sini)
#define DIR_PIN  26  // Tetap
#define EN_PIN   27  // Tetap

void setup() {
  Serial.begin(115200);
  
  pinMode(STEP_PIN, OUTPUT);
  pinMode(DIR_PIN, OUTPUT);
  pinMode(EN_PIN, OUTPUT);

  // Kunci Motor
  digitalWrite(EN_PIN, LOW);
  delay(500);
}

void loop() {
  // Gerakkan Bolak-Balik supaya kelihatan
  Serial.println("Maju...");
  digitalWrite(DIR_PIN, HIGH);
  gerak(200);
  delay(500);
  
  Serial.println("Mundur...");
  digitalWrite(DIR_PIN, LOW);
  gerak(200);
  delay(500);
}

void gerak(int steps) {
  for(int i=0; i<steps; i++) {
    digitalWrite(STEP_PIN, HIGH);
    delayMicroseconds(2000); // Speed Aman
    digitalWrite(STEP_PIN, LOW);
    delayMicroseconds(2000);
  }
}
