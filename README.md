# IoT Home Sensor REST Service

A home IoT monitoring REST API built with PHP and MySQL, demonstrating common security vulnerabilities and their fixes.

---

## Project Overview

This project simulates a home IoT system where three physical sensors (Living Room,
Bedroom, and Kitchen) send temperature and humidity readings to a central REST API.
The system is built in two versions:

- **Vulnerable version** (`api/`) – contains two deliberate security flaws for demonstration
- **Fixed version** (`fixed/api/`) – applies correct security controls to patch both flaws

---

## Folder Structure

```
iot_rest/
│
├── README.md                       ← This file
│
├── db/
│   ├── schema.sql                  ← Vulnerable database (plain-text passwords)
│   └── schema_fixed.sql            ← Fixed database (bcrypt + SHA-256 hashes)
│
├── api/                            ← VULNERABLE REST service
│   ├── config.php                  ← Database connection (hard-coded credentials)
│   ├── sensors.php                 ← GET/POST sensor endpoint (SQL Injection present)
│   ├── login.php                   ← Admin login (plain-text password comparison)
│   └── dashboard.php               ← HTML admin dashboard
│
├── fixed/
│   └── api/                        ← FIXED REST service
│       ├── config_fixed.php        ← Database connection (environment variables)
│       ├── sensors_fixed.php       ← Secure endpoint (prepared statements)
│       ├── login_fixed.php         ← Secure login (bcrypt + HMAC token)
│       └── dashboard_fixed.php     ← SECURE HTML admin dashboard
│
└── client/
    ├── simulate_iot.py             ← Python IoT device simulator (Vulnerable)
    ├── simulate_iot_fixed.py       ← Python IoT device simulator (Secure)
    ├── attack_sqli.py              ← SQL Injection attack (Exploitation)
    └── attack_sqli_fixed.py        ← SQL Injection attack (Defensive test)
```

---

## Technologies Used

| Technology | Purpose |
|---|---|
| PHP 8.x | REST API server-side logic |
| MySQL 8.x | Database for devices, users, readings |
| Apache (XAMPP) | Local web server |
| Python 3.x | IoT device simulator and attack script |
| HTML / JavaScript | Admin dashboard |

---

## Requirements

### Software to Install

1. **XAMPP** (Apache + MySQL + PHP bundled)
   - Download: https://www.apachefriends.org
   - Version: 8.x (PHP 8.x included)

2. **Python 3.8+**
   - Download: https://www.python.org/downloads/
   - **Important:** Tick "Add Python to PATH" during install

3. **Python requests library**
   ```
   pip install requests
   ```

---

## Setup Instructions (Windows)

### Step 1 — Install XAMPP

1. Download XAMPP from https://www.apachefriends.org
2. Run the installer with default settings
3. Install to `C:\xampp\` (default path)

### Step 2 — Start XAMPP Services

1. Open XAMPP Control Panel
2. Click **Start** next to **Apache** → turns green
3. Click **Start** next to **MySQL** → turns green

### Step 3 — Copy Project to Web Root

Copy the entire `iot_rest` folder to:
```
C:\xampp\htdocs\iot_rest\
```

### Step 4 — Create the Vulnerable Database

1. Open browser → go to `http://localhost/phpmyadmin`
2. Click the **SQL** tab
3. Copy and paste the full contents of `db/schema.sql`
4. Click **Go**
5. Confirm the database `iot_sensors` appears in the left panel with tables:
   - `devices`
   - `sensor_readings`
   - `users`

### Step 5 — Verify the API is Working

Open browser and go to:
```
http://localhost/iot_rest/api/sensors.php?device_id=SENSOR_01
```

Expected response:
```json
{
  "status": "success",
  "device": "SENSOR_01",
  "count": 1,
  "readings": [...]
}
```

### Step 6 — Run the IoT Simulator

Open Command Prompt and run:
```
cd "C:\Users\thiga\Desktop\REST service\iot_rest\client"
pip install requests
python simulate_iot.py
```

Expected output every 10 seconds:
```
============================================================
  IoT Sensor Simulator
  Target API: http://localhost/iot_rest/api/sensors.php
============================================================

[Cycle 1] 2026-02-26 17:00:00
----------------------------------------
  ✓ Living Room Sensor    Temp= 21.3°C  Hum= 57.2%  [HTTP 201]
  ✓ Bedroom Sensor        Temp= 18.9°C  Hum= 63.1%  [HTTP 201]
  ✓ Kitchen Sensor        Temp= 25.6°C  Hum= 49.8%  [HTTP 201]

  Next batch in 10 seconds... (Ctrl+C to stop)
```

### Step 7 — Open the Dashboard

```
http://localhost/iot_rest/api/dashboard.php
```

Type `SENSOR_01` in the search box and click **Search** to see live readings.

### Step 9 — Setup and Test the FIXED Version

1. **Create the Secure Database**:
   - In phpMyAdmin, go to the **SQL** tab.
   - Run the contents of `db/schema_fixed.sql`.
   - This creates the `iot_sensors_fixed` database with hashed passwords.

2. **Run Secure Simulator**:
   ```powershell
   python client/simulate_iot_fixed.py
   ```

3. **Open Secure Dashboard**:
   - URL: `http://localhost/iot_rest/fixed/api/dashboard_fixed.php`

4. **Run Defensive Attack Test**:
   - This script proves that the SQLi attacks fail on the fixed API:
   ```powershell
   python client/attack_sqli_fixed.py
   ```

---

## API Endpoints

### GET /api/sensors.php

Retrieve sensor readings for a specific device.

| Parameter | Type | Description |
|---|---|---|
| `device_id` | string | The ID of the sensor (e.g. `SENSOR_01`) |

**Example request:**
```
GET http://localhost/iot_rest/api/sensors.php?device_id=SENSOR_01
```

**Example response:**
```json
{
  "status": "success",
  "device": "SENSOR_01",
  "count": 3,
  "readings": [
    {
      "id": "1",
      "device_id": "SENSOR_01",
      "temperature": "21.50",
      "humidity": "55.20",
      "recorded_at": "2026-02-26 17:00:00"
    }
  ]
}
```

---

### POST /api/sensors.php

Submit a new sensor reading.

**Request body (JSON):**
```json
{
  "device_id":   "SENSOR_01",
  "api_key":     "key_abc123plaintext",
  "temperature": 21.5,
  "humidity":    55.2
}
```

**Success response (HTTP 201):**
```json
{
  "status": "success",
  "message": "Reading recorded",
  "reading_id": 4
}
```

**Error response (HTTP 401):**
```json
{
  "status": "error",
  "message": "Unauthorised device"
}
```

---

### POST /api/login.php

Admin login.

**Request body (JSON):**
```json
{
  "username": "admin",
  "password": "admin123"
}
```

**Success response (HTTP 200):**
```json
{
  "status": "success",
  "message": "Login successful",
  "token": "YWRtaW46MTcwMDAwMDAwMA==",
  "username": "admin",
  "role": "admin"
}
```

---

## Database Tables

### `devices`

| Column | Type | Description |
|---|---|---|
| id | INT | Auto-increment primary key |
| device_id | VARCHAR(50) | Unique sensor identifier |
| device_name | VARCHAR(100) | Human-readable name |
| location | VARCHAR(100) | Physical location |
| api_key | VARCHAR(64) | Authentication key (plain-text in vulnerable version) |
| created_at | DATETIME | Registration timestamp |

### `sensor_readings`

| Column | Type | Description |
|---|---|---|
| id | INT | Auto-increment primary key |
| device_id | VARCHAR(50) | References devices.device_id |
| temperature | DECIMAL(5,2) | Temperature in Celsius |
| humidity | DECIMAL(5,2) | Relative humidity percentage |
| recorded_at | DATETIME | Reading timestamp |

### `users`

| Column | Type | Description |
|---|---|---|
| id | INT | Auto-increment primary key |
| username | VARCHAR(50) | Login username |
| password | VARCHAR(255) | Password (plain-text in vulnerable version) |
| role | VARCHAR(20) | User role: admin or viewer |
| created_at | DATETIME | Account creation timestamp |

---

## Seeded Test Data

### Devices

| device_id | device_name | location | api_key |
|---|---|---|---|
| SENSOR_01 | Living Room Sensor | Living Room | key_abc123plaintext |
| SENSOR_02 | Bedroom Sensor | Bedroom | key_xyz456plaintext |
| SENSOR_03 | Kitchen Sensor | Kitchen | key_def789plaintext |

### Users

| username | password | role |
|---|---|---|
| admin | admin123 | admin |
| monitor | password1 | viewer |

---

## Vulnerabilities Demonstrated

### Vulnerability 1 — SQL Injection (OWASP IoT Top 10 2018 – I7)

**Location:** `api/sensors.php` — `handle_get()` function

**Vulnerable code:**
```php
$device_id = $_GET['device_id'] ?? '';
$sql = "SELECT * FROM sensor_readings
        WHERE device_id = '" . $device_id . "'
        ORDER BY recorded_at DESC LIMIT 50";
```

**Attack payloads (run via attack_sqli.py):**

| Attack | Payload | Effect |
|---|---|---|
| Boolean bypass | `SENSOR_01' OR '1'='1` | Returns all rows from all devices |
| UNION dump users | `nonexistent' UNION SELECT id, username, password, role, created_at FROM users--` | Dumps all usernames and passwords |
| UNION dump API keys | `nonexistent' UNION SELECT id, device_id, device_name, api_key, created_at FROM devices--` | Exposes all device API keys |
| DB version | `nonexistent' UNION SELECT 1, VERSION(), DATABASE(), USER(), NOW()--` | Reveals MySQL version and DB name |

**Fix:** `fixed/api/sensors_fixed.php` — uses MySQLi prepared statements with `bind_param()`

---

### Vulnerability 2 — Insecure Authentication + No HTTPS (OWASP IoT Top 10 2018 – I3)

**Location:** `db/schema.sql` (plain-text passwords), `api/login.php` (plain-text comparison), all files (HTTP only)

**Issues:**
- Passwords stored as plain text in the `users` table
- API keys stored as plain text in the `devices` table
- All traffic sent over HTTP — visible to any network observer
- Session token is just a base64-encoded username — trivially forgeable

**Fix:** `fixed/api/login_fixed.php` and `fixed/api/sensors_fixed.php`
- HTTPS enforced; HTTP requests rejected with HTTP 403
- Passwords stored as bcrypt hashes using `password_hash(PASSWORD_BCRYPT, cost=12)`
- `password_verify()` used for constant-time comparison
- API keys stored as SHA-256 hashes; incoming keys hashed before comparison
- HMAC-signed session token replaces plain base64 token
- `Strict-Transport-Security` header added

---

## Quick Reference — Test Commands

```powershell
# Test normal GET request (PowerShell)
curl http://localhost/iot_rest/api/sensors.php?device_id=SENSOR_01

# Test SQL Injection manually in browser URL bar
http://localhost/iot_rest/api/sensors.php?device_id=SENSOR_01' OR '1'='1

# Test login via curl (PowerShell)
curl -X POST http://localhost/iot_rest/api/login.php `
     -H "Content-Type: application/json" `
     -d '{"username":"admin","password":"admin123"}'

# --- VULNERABLE VERSION ---
# Run simulator
python client/simulate_iot.py
# Run attack demo
python client/attack_sqli.py

# --- FIXED VERSION ---
# Run secure simulator
python client/simulate_iot_fixed.py
# Run defensive test
python client/attack_sqli_fixed.py
```
