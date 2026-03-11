import requests
import json

# TARGETING THE SECURE API
BASE_URL = "http://localhost/iot_rest/fixed/api/sensors_fixed.php"


def send_payload(label, payload):
    print(f"\n{'='*60}")
    print(f"[ATTACK ATTEMPT] {label}")
    print(f"[PAYLOAD] device_id={payload}")
    print("-" * 60)

    try:
        response = requests.get(BASE_URL, params={"device_id": payload}, timeout=5)
        
        print(f"[HTTP {response.status_code}]")
        
        # If response is JSON, print it
        try:
            data = response.json()
            print(f"[RESPONSE DATA]:")
            print(json.dumps(data, indent=2))
        except:
            print(f"[RAW RESPONSE]: {response.text}")

        if response.status_code == 400:
            print("\nRESULT: 🛡️ BLOCKED - Input rejected by Regex validation.")
        elif response.status_code == 200:
            data = response.json()
            if data['count'] == 0:
                print("\nRESULT: 🛡️ SAFE - Prepared Statement treated the attack as a literal string.")
            else:
                print("\nRESULT: ⚠️ VULNERABLE - Unexpected data returned.")
                
    except Exception as e:
        print(f"[ERROR] {e}")


def main():
    print("=" * 60)
    print("  SQL Injection Attack Demo (ON FIXED API)")
    print(f"  Target: {BASE_URL}")
    print("=" * 60)

    # 1. Test Regex Validation (the fixed API rejects special characters immediately)
    send_payload(
        "Attempting SQLi (Blocked by Regex)",
        "SENSOR_01' OR '1'='1"
    )

    # 2. Test UNION Attack
    send_payload(
        "Attempting UNION attack (Blocked by Prepared Statements)",
        "nonexistent' UNION SELECT id, username, password, role, created_at FROM users-- "
    )

    print("\n" + "=" * 60)
    print("[DONE] Security test complete.")
    print("  As you can see, the Fixed API is protected by both Regex and Prepared Statements.")
    print("=" * 60)


if __name__ == "__main__":
    main()
