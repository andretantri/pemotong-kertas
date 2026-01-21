# 🔌 Hardware Connection Guide - ESP32 Motor Control

## 📊 Pin Configuration

### ESP32 GPIO Pins (fix.ino)

```
┌─────────────────────────────────────────────────────┐
│              PENARIK (Pull Motor)                   │
├─────────────────────────────────────────────────────┤
│ STEP_PULL (GPIO 25) → A4988 STEP (Pin 1)          │
│ DIR_PULL  (GPIO 26) → A4988 DIR  (Pin 2)          │
│ EN_PULL   (GPIO 27) → A4988 ENA  (Pin 3)          │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│             PEMOTONG (Cut Motor)                    │
├─────────────────────────────────────────────────────┤
│ STEP_CUT  (GPIO 32) → A4988 STEP (Pin 1)          │
│ DIR_CUT   (GPIO 33) → A4988 DIR  (Pin 2)          │
│ EN_CUT    (GPIO 14) → A4988 ENA  (Pin 3)          │
└─────────────────────────────────────────────────────┘
```

---

## 🔋 Power Distribution

### Power Supply Requirements
- **Voltage:** 12V DC
- **Current:** Minimum 2A (recommended 3-5A)
- **Type:** Regulated power supply

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
│        │ (Pull)    │  │         │ (Cut)      │
│        └────┬──────┘  │         └─────┬──────┘
│             │         │               │
│             └────┬────┘               │
│                  │ GND                │
│              ┌───┴─────────┬──────┐   │
│              │             │      │   │
│         ┌────▼─┐      ┌────▼──┐  │   │
│         │Motor1│      │Motor2 │  │   │
│         │(Pull)│      │(Cut)  │  │   │
│         └──────┘      └───────┘  │   │
│                                  │   │
│  GND Connection (star point):   │   │
│  PSU GND ─ ESP32 GND ─ A4988 GND─┴──┘
└──────────────────────────────────────┘
```

---

## 🚀 Stepper Motor Connections

### A4988 Stepper Driver Pin Mapping

```
              ┌─────────────┐
         GND  │1      +5V  8│  VCC (Motor Power)
        STEP  │2    SLEEP  7│  GND
         DIR  │3    RESET  6│  GND  
         ENA  │4    FAULT  5│  GND (Motor Ground)
              └─────────────┘
         A4988 Stepper Driver
```

### Wiring for Each Motor

#### Motor 1 (Pull Motor) - A4988 Driver #1
```
ESP32 GPIO 25 ──→ A4988 #1 STEP (Pin 2)
ESP32 GPIO 26 ──→ A4988 #1 DIR  (Pin 3)
ESP32 GPIO 27 ──→ A4988 #1 ENA  (Pin 4)

Motor A ────────→ A4988 #1 Motor A (Pin A)
Motor B ────────→ A4988 #1 Motor B (Pin B)
Motor A' ───────→ A4988 #1 Motor A' (Pin A')
Motor B' ───────→ A4988 #1 Motor B' (Pin B')

12V Power ──────→ A4988 #1 VCC (Pin 8)
GND ────────────→ A4988 #1 GND (Pin 1, 5, 6, 7)
```

#### Motor 2 (Cut Motor) - A4988 Driver #2
```
ESP32 GPIO 32 ──→ A4988 #2 STEP (Pin 2)
ESP32 GPIO 33 ──→ A4988 #2 DIR  (Pin 3)
ESP32 GPIO 14 ──→ A4988 #2 ENA  (Pin 4)

Motor A ────────→ A4988 #2 Motor A (Pin A)
Motor B ────────→ A4988 #2 Motor B (Pin B)
Motor A' ───────→ A4988 #2 Motor A' (Pin A')
Motor B' ───────→ A4988 #2 Motor B' (Pin B')

12V Power ──────→ A4988 #2 VCC (Pin 8)
GND ────────────→ A4988 #2 GND (Pin 1, 5, 6, 7)
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
```

### A4988 Motor Coil Connections

```
         ┌─────────────────┐
         │     A4988       │
         │                 │
  ESP32 ─┤ STEP            │ Motor
  GPIO ─┤ DIR        ┌─────┤ A1/A2 (Red/Green)
  GPIO ─┤ ENA    ────┤ OUT │ B1/B2 (Blue/Black)
  12V ──┤ VCC        └─────┤
  GND ──┤ GND             │
         └─────────────────┘
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

### Current Limiting (VREF Adjustment)

```
Formula: Motor Current = VREF × 2.5

Example:
VREF = 0.5V → Motor Current = 1.25A
VREF = 1.0V → Motor Current = 2.5A
VREF = 1.5V → Motor Current = 3.75A

⚠️ Don't exceed motor rating!
  Measure VREF with multimeter
  Adjust potentiometer on A4988
```

---

## 🔌 Complete Wiring Diagram

```
                    ┌──────────────────┐
                    │    Power Supply  │
                    │    12V / 3A      │
                    └──────┬───┬───────┘
                           │   │
                      +12V │   │ GND
                           │   │
                ┌──────────┘   └─────────┐
                │                        │
         ┌──────▼──────┐          ┌─────▼──────┐
         │  A4988 #1   │          │  A4988 #2  │
         │  (Pull)     │          │  (Cut)     │
         └──┬───┬───┬──┘          └──┬───┬───┬─┘
            │   │   │                │   │   │
      ┌─────┘   │   └─┐         ┌────┘   │   └──┐
      │         │     │         │        │      │
   Motor1  ┌────▼─┐   │  GND-Link┌──┐   ┌─┴──┐
   (Pull)  │ STEP │   │     │    │MS│   │ EN │
      │    │ Dir  │   │     │    │1 │   └────┘
      │    └───┬──┘   │     │    │  │
      │        │      │     │    └──┘
      │        │      │     │
   Motor2      ├──────┤     │
   (Cut)       │      │     │
      │        │      │     │
      └────────┼──────┴─────┘
               │
         ┌─────▼──────────┐
         │     ESP32      │
         │                │
         │ GPIO25 (STEP1) │
         │ GPIO26 (DIR1)  │
         │ GPIO27 (ENA1)  │
         │ GPIO32 (STEP2) │
         │ GPIO33 (DIR2)  │
         │ GPIO14 (ENA2)  │
         │ GND            │
         │ VCC (3.3V)     │
         └────────────────┘
```

---

## 🔍 Troubleshooting Connections

### Motor Not Moving
1. **Check Power:**
   - [ ] 12V connected to A4988 VCC
   - [ ] GND connected properly
   - [ ] PSU turned on and delivering 12V

2. **Check GPIO Connections:**
   - [ ] STEP, DIR, ENA pins connected
   - [ ] Correct GPIO numbers used
   - [ ] No loose wires

3. **Check Motor Wiring:**
   - [ ] Motor coils connected to OUT pins
   - [ ] No reversed connections
   - [ ] All 4 wires connected

4. **Check Firmware:**
   - [ ] ESP32 running and responsive
   - [ ] Serial monitor shows output
   - [ ] Config loaded successfully

### Motor Stalled/Weak
1. **Current Settings:**
   - [ ] VREF adjusted correctly
   - [ ] DIP switches set to Half Step
   - [ ] Motor not overheating

2. **Power Supply:**
   - [ ] Sufficient current (min 2A)
   - [ ] Stable voltage output
   - [ ] No voltage drops in wiring

### Motor Buzzing Without Rotation
1. **Microstepping:**
   - [ ] Reduce microsteps
   - [ ] Try Full Step mode
   - [ ] Check DIP switch settings

2. **Current Limiting:**
   - [ ] VREF might be too low
   - [ ] Increase motor current
   - [ ] Verify VREF measurement

### Inconsistent Movement
1. **Connections:**
   - [ ] Check for loose wires
   - [ ] Verify all connections tight
   - [ ] No corroded contacts

2. **Power Quality:**
   - [ ] Use regulated PSU
   - [ ] Add capacitor near A4988 (100µF)
   - [ ] Good ground connections

---

## 🛡️ Safety Considerations

### Electrical Safety
- ⚠️ Always disconnect power before touching circuit
- ⚠️ Check polarity before connecting power
- ⚠️ Use proper fuses/circuit breakers
- ⚠️ Don't exceed motor rating

### Mechanical Safety
- ⚠️ Motors can pinch fingers - keep hands away
- ⚠️ Emergency stop mechanism recommended
- ⚠️ Proper guards around moving parts
- ⚠️ Regular maintenance checks

### Thermal Management
- ⚠️ A4988 can get hot - add heatsink if needed
- ⚠️ Ensure adequate ventilation
- ⚠️ Motor should not overheat
- ⚠️ Consider thermal cutoff

---

## 📚 Component Specifications

### A4988 Stepper Driver
- Voltage: 8-35V DC
- Max Current: 2A per coil (with heatsink)
- Microstepping: 16 levels
- Size: ~20mm × 15mm

### NEMA 17 Stepper Motor
- Voltage: 12V (typical)
- Current: 1.5-2A per coil
- Torque: ~0.4 N·m
- Holding Torque: ~0.25 N·m
- Resistance: ~8-10Ω per coil

### ESP32 Dev Module
- GPIO Voltage: 3.3V
- Max GPIO Current: 40mA (absolute max)
- Operating Voltage: 3.3V
- Number of GPIO: 30+

---

## ✅ Pre-Assembly Checklist

Before connecting anything:
- [ ] All components identified and counted
- [ ] Power supply tested and working
- [ ] Multimeter available for testing
- [ ] Documentation reviewed
- [ ] Work area clean and organized
- [ ] Proper grounding strap worn (optional but recommended)
- [ ] No power connected yet

After assembly:
- [ ] Visual inspection complete
- [ ] No visible damage or loose parts
- [ ] All connections double-checked
- [ ] Power supply test with multimeter
- [ ] First power-up with close monitoring

---

## 📞 Quick Reference

**If motor doesn't respond immediately:**
1. Kill power
2. Check ESP32 serial output
3. Verify GPIO pins in code match wiring
4. Check A4988 power and GND
5. Measure VREF with multimeter
6. Re-check motor coil connections

**Expected current draw at runtime:**
- Idle: ~50-100mA
- One motor running: ~500-800mA
- Both motors running: ~1000-1500mA
- Peak (both running full power): ~2000-2500mA

**Total system power budget:**
- ESP32: 100mA
- Pull Motor: 2A max
- Cut Motor: 2A max
- Recommended PSU: 5A / 12V

---

**Last Updated:** 2 Januari 2026  
**Version:** 1.0.0

