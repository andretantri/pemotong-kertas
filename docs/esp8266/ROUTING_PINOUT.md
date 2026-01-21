# 🔌 ESP8266 Pin Routing - Alat Pemotong Kertas

## 📊 Pin Configuration ESP8266

### ESP8266 GPIO Pins (NodeMCU/Wemos D1 Mini)

```
┌─────────────────────────────────────────────────────┐
│              MOTOR PENARIK KERTAS                   │
├─────────────────────────────────────────────────────┤
│ D2 (GPIO4)  → A4988 ENABLE (Pin 4)                 │
│ D3 (GPIO0)  → A4988 DIR    (Pin 3)                 │
│ D4 (GPIO2)  → A4988 STEP   (Pin 2)                 │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│              MOTOR PEMOTONG KERTAS                  │
├─────────────────────────────────────────────────────┤
│ D5 (GPIO14) → A4988 DIR    (Pin 3)                 │
│ D6 (GPIO12) → A4988 STEP   (Pin 2)                 │
│ D7 (GPIO13) → A4988 ENABLE (Pin 4)                 │
└─────────────────────────────────────────────────────┘
```

---

## 🔋 Power Distribution

### Power Supply Requirements
- **Voltage:** 12V DC
- **Current:** Minimum 2A (recommended 3-5A)
- **Type:** Regulated power supply
- **ESP8266 Power:** 5V via USB atau 3.3V via pin (JANGAN langsung dari 12V!)

### Power Connections

```
┌──────────────────┐
│  12V Power Supply │
│                   │
│  GND  12V  COM   │
│   │     │    │    │
│   │     │    └────────────────────────┐
│   │     │                             │
│   │     │                      ┌──────▼─────┐
│   │     │                      │  Relay/    │
│   │     │                      │  MOSFET    │
│   │     │                      │  (Optional)│
│   │     │                      └──────┬─────┘
│   │     │                             │
│   │     └─────────────┬───────────────┤
│   │                   │               │
│   └─────────┬─────────┤               │
│             │         │               │
│        ┌────▼──────┐  │         ┌─────▼──────┐
│        │  A4988 #1 │  │         │  A4988 #2  │
│        │ (Penarik) │  │         │ (Pemotong) │
│        └────┬──────┘  │         └─────┬──────┘
│             │         │               │
│             └────┬────┘               │
│                  │ GND                │
│              ┌───┴─────────┬──────┐   │
│              │             │      │   │
│         ┌────▼─┐      ┌────▼──┐  │   │
│         │Motor1│      │Motor2 │  │   │
│         │Penarik│     │Pemotong│ │   │
│         └──────┘      └───────┘  │   │
│                                  │   │
│  GND Connection (star point):   │   │
│  PSU GND ─ ESP8266 GND ─ A4988 GND─┴──┘
└──────────────────────────────────────┘
```

---

## 🚀 Stepper Motor Connections

### A4988 Stepper Driver Pin Mapping

```
              ┌─────────────┐
         GND  │1      +5V  8│  VCC (Motor Power 12V)
        STEP  │2    SLEEP  7│  GND (atau sambung ke RESET)
         DIR  │3    RESET  6│  GND (atau sambung ke SLEEP) 
         ENA  │4    FAULT  5│  GND (Motor Ground)
              └─────────────┘
         A4988 Stepper Driver
```

### Wiring untuk Motor Penarik - A4988 Driver #1

```
ESP8266 D4 (GPIO2)  ─→ A4988 #1 STEP (Pin 2)
ESP8266 D3 (GPIO0)  ─→ A4988 #1 DIR  (Pin 3)
ESP8266 D2 (GPIO4)  ─→ A4988 #1 ENA  (Pin 4)

Motor Coil A  ──────→ A4988 #1 Motor OUT 1A, 1B
Motor Coil B  ──────→ A4988 #1 Motor OUT 2A, 2B

12V Power ──────────→ A4988 #1 VCC (Pin 8)
GND ────────────────→ A4988 #1 GND (Pin 1, 5, 6, 7)
```

### Wiring untuk Motor Pemotong - A4988 Driver #2

```
ESP8266 D6 (GPIO12) ─→ A4988 #2 STEP (Pin 2)
ESP8266 D5 (GPIO14) ─→ A4988 #2 DIR  (Pin 3)
ESP8266 D7 (GPIO13) ─→ A4988 #2 ENA  (Pin 4)

Motor Coil A  ──────→ A4988 #2 Motor OUT 1A, 1B
Motor Coil B  ──────→ A4988 #2 Motor OUT 2A, 2B

12V Power ──────────→ A4988 #2 VCC (Pin 8)
GND ────────────────→ A4988 #2 GND (Pin 1, 5, 6, 7)
```

---

## 📋 NEMA 17 Stepper Motor Pinout

### 4-Wire Connection (Most Common)

```
  ┌──────────────┐
  │  NEMA 17     │
  │  Stepper     │
  │  Motor       │
  └──────────────┘
  │  │  │  │
  ▼  ▼  ▼  ▼
 A1 A2 B1 B2

Color Code (Standard):
- Red:    A1 (Coil A Phase 1)
- Green:  A2 (Coil A Phase 2)
- Blue:   B1 (Coil B Phase 1)
- Black:  B2 (Coil B Phase 2)

⚠️ Catatan: Warna bisa berbeda tergantung manufacturer!
   Gunakan multimeter untuk test continuity
```

---

## ⚙️ A4988 DIP Switch Settings

### Microstepping Configuration

```
┌──────────────────────────────────┐
│  DIP Switch Position             │
│  (View from top with MS3 to left)│
├──────────────────────────────────┤
│  MS1  MS2  MS3  │  Microsteps    │
├──────────────────┼────────────────┤
│  OFF  OFF  OFF  │  Full Step     │
│  ON   OFF  OFF  │  Half Step     │
│  OFF  ON   OFF  │  Quarter Step  │
│  ON   ON   OFF  │  Eighth Step   │
│  ON   ON   ON   │  Sixteenth     │
└──────────────────┴────────────────┘

Recommended: Half Step (MS1=ON, MS2=OFF, MS3=OFF)
- Good balance between speed and precision
- Smoother movement than full step
```

---

## 🔌 ESP8266 NodeMCU Pin Mapping

```
 ┌─────────────────────────────┐
 │      NodeMCU ESP8266        │
 ├─────────────────────────────┤
 │ Pin Label │ GPIO  │ Function│
 ├───────────┼───────┼─────────┤
 │ D0        │ GPIO16│ -       │
 │ D1        │ GPIO5 │ -       │
 │ D2        │ GPIO4 │ EN_PULL │
 │ D3        │ GPIO0 │ DIR_PULL│
 │ D4        │ GPIO2 │STEP_PULL│
 │ D5        │ GPIO14│ DIR_CUT │
 │ D6        │ GPIO12│STEP_CUT │
 │ D7        │ GPIO13│ EN_CUT  │
 │ D8        │ GPIO15│ -       │
 │ GND       │ GND   │ Ground  │
 │ 3V3       │ 3.3V  │ Power   │
 │ 5V        │ 5V    │ -       │
 └───────────┴───────┴─────────┘

⚠️ PENTING:
- GPIO0 (D3) digunakan untuk boot mode, hindari pull-down saat boot!
- GPIO2 (D4) harus HIGH saat boot
- GPIO15 (D8) harus LOW saat boot
```

---

## 🔍 Troubleshooting Connections

### ESP8266 Won't Boot
1. **Boot Mode Issues:**
   - [ ] Pastikan D3 (GPIO0) tidak di-pull LOW saat boot
   - [ ] Pastikan D4 (GPIO2) HIGH saat boot
   - [ ] Lepas koneksi ke A4988 sementara saat flash

2. **Power Issues:**
   - [ ] ESP8266 butuh minimum 300mA stabil
   - [ ] Gunakan USB power atau 3.3V regulator yang cukup
   - [ ] JANGAN langsung dari 12V PSU!

### Motor Not Moving
1. **Check Power:**
   - [ ] 12V connected to A4988 VCC
   - [ ] GND connected properly (ESP8266 GND = A4988 GND)
   - [ ] PSU turned on and delivering 12V

2. **Check GPIO Connections:**
   - [ ] STEP, DIR, ENA pins connected to correct GPIO
   - [ ] No loose wires
   - [ ] Logic level 3.3V from ESP8266 should work with A4988

3. **Check Motor Wiring:**
   - [ ] Motor coils connected to OUT pins
   - [ ] No reversed connections
   - [ ] All 4 wires connected

---

## 📞 Quick Reference Table

| Component | Pin ESP8266 | GPIO | Pin A4988 | Motor |
|-----------|-------------|------|-----------|-------|
| Penarik   | D2          | 4    | ENABLE    | 1     |
| Penarik   | D3          | 0    | DIR       | 1     |
| Penarik   | D4          | 2    | STEP      | 1     |
| Pemotong  | D5          | 14   | DIR       | 2     |
| Pemotong  | D6          | 12   | STEP      | 2     |
| Pemotong  | D7          | 13   | ENABLE    | 2     |

---

## 🛡️ Safety Considerations

### Electrical Safety
- ⚠️ Always disconnect power before touching circuit
- ⚠️ Check polarity before connecting power
- ⚠️ ESP8266 works at 3.3V logic - DO NOT connect to 5V directly!
- ⚠️ Use proper fuses/circuit breakers

### ESP8266 Specific
- ⚠️ Max GPIO current: 12mA (use drivers like A4988, never drive motors directly!)
- ⚠️ Total max current all GPIO: 50mA
- ⚠️ Input voltage: 3.3V max (exceeding will damage the chip!)

---

**Last Updated:** 20 Januari 2026  
**Version:** 1.0.0  
**Compatible with:** ESP8266 NodeMCU, Wemos D1 Mini
