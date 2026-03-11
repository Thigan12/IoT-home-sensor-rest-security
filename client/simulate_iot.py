import requests
import random
import time
from datetime import datetime

BASE_URL = "http://localhost/iot_rest/api/sensors.php"

DEVICES = [
    {
        "device_id":   "SENSOR_01",
        "device_name": "Living Room Sensor",
        "api_key":     "key_abc123plaintext",
        "temp_range":  (18.0, 24.0),
        "hum_range":   (45.0, 65.0)
    },
    {
        "device_id":   "SENSOR_02",
        "device_name": "Bedroom Sensor",
        "api_key":     "key_xyz456plaintext",
        "temp_range":  (16.0, 22.0),
        "hum_range":   (50.0, 70.0)
    },
    {
        "device_id":   "SENSOR_03",
        "device_name": "Kitchen Sensor",
        "api_key":     "key_def789plaintext",
        "temp_range":  (20.0, 30.0),
        "hum_range":   (40.0, 60.0)
    }
]


def generate_reading(device):
    temperature = round(random.uniform(*device["temp_range"]), 2)
    humidity    = round(random.uniform(*device["hum_range"]), 2)
    return temperature, humidity


def send_reading(device, temperature, humidity):
    payload = {
        "device_id":   device["device_id"],
        "api_key":     device["api_key"],
        "temperature": temperature,
        "humidity":    humidity
    }

    try:
        response = requests.post(BASE_URL, json=payload, timeout=5)
        return response.status_code, response.json()
    except requests.exceptions.ConnectionError:
        return None, {"error": "Connection refused – is XAMPP running?"}
    except Exception as e:
        return None, {"error": str(e)}


def main():
    print("=" * 60)
    print("  IoT Sensor Simulator")
    print(f"  Target API: {BASE_URL}")
    print("=" * 60)

    cycle = 1
    while True:
        print(f"\n[Cycle {cycle}] {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        print("-" * 40)

        for device in DEVICES:
            temp, hum = generate_reading(device)
            status_code, response = send_reading(device, temp, hum)

            if status_code == 201:
                print(f"  ✓ {device['device_name']:<22} "
                      f"Temp={temp:>5}°C  Hum={hum:>5}%  "
                      f"[HTTP {status_code}]")
            else:
                print(f"  ✗ {device['device_name']:<22} FAILED – {response}")

        print(f"\n  Next batch in 10 seconds... (Ctrl+C to stop)")
        cycle += 1

        try:
            time.sleep(10)
        except KeyboardInterrupt:
            print("\n\nSimulator stopped.")
            break


if __name__ == "__main__":
    main()
