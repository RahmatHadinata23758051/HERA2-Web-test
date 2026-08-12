import pickle
import os
import requests
import numpy as np
import pandas as pd
from sklearn.preprocessing import StandardScaler
from sklearn.ensemble import RandomForestRegressor

BASE_URL = "http://localhost:8001"
SECRET_KEY = "hera-internal-secret-key-2026"

def create_valid_dummy_model(filename="valid_dummy_model.pkl"):
    """Membuat file model ML valid dengan 9 fitur."""
    feature_names = [
        "pH", "EC_uScm", "TDS_mgL", "Suhu_Air",
        "pH_squared", "pH_EC_interact", "log_EC",
        "pOH_proxy", "pH_temp_interact"
    ]
    X_train = np.random.rand(10, 9) * 100
    y_train = np.random.rand(10) * 50

    scaler = StandardScaler()
    X_scaled = scaler.fit_transform(X_train)

    model = RandomForestRegressor(n_estimators=5, random_state=42)
    model.fit(X_scaled, y_train)

    pack = {
        "scaler": scaler,
        "model": model,
        "best_model": "Random Forest Test Dummy v2.0"
    }

    with open(filename, "wb") as f:
        pickle.dump(pack, f)

    return filename

def create_invalid_dummy_model(filename="invalid_dummy_model.pkl"):
    """Membuat file model invalid (tidak punya 'scaler'/'model')."""
    pack = {
        "wrong_key": "invalid_data"
    }
    with open(filename, "wb") as f:
        pickle.dump(pack, f)
    return filename

def run_qc_tests():
    print("==================================================")
    print("[QC] MEMULAI AUTOMATED QC & INTEGRATION TESTING")
    print("==================================================")

    # 1. Test GET /health
    print("\n[TEST 1] Testing GET /health...")
    r = requests.get(f"{BASE_URL}/health")
    assert r.status_code == 200, f"Expected 200, got {r.status_code}"
    print(f"[PASSED] Output: {r.json()}")

    # 2. Test GET /api/v2/models/info
    print("\n[TEST 2] Testing GET /api/v2/models/info...")
    r = requests.get(f"{BASE_URL}/api/v2/models/info")
    assert r.status_code == 200, f"Expected 200, got {r.status_code}"
    print(f"[PASSED] Models Info: {r.json()}")

    # 3. Test Unauthorized Upload (Tanpa / Wrong Secret Key)
    print("\n[TEST 3] Testing Unauthorized Access (Wrong Secret Key)...")
    valid_file = create_valid_dummy_model()
    with open(valid_file, "rb") as f:
        r = requests.post(
            f"{BASE_URL}/api/v2/reload-model",
            headers={"X-Internal-Key": "wrong-key"},
            data={"target": "chromium"},
            files={"file": f}
        )
    assert r.status_code == 401, f"Expected 401, got {r.status_code}"
    print("[PASSED] Akses tanpa kuncinya berhasil ditolak (401 Unauthorized).")

    # 4. Test Upload File Invalid (Dry-Run Rejection)
    print("\n[TEST 4] Testing Invalid Model Upload (Dry-Run Failure)...")
    invalid_file = create_invalid_dummy_model()
    with open(invalid_file, "rb") as f:
        r = requests.post(
            f"{BASE_URL}/api/v2/reload-model",
            headers={"X-Internal-Key": SECRET_KEY},
            data={"target": "nickel"},
            files={"file": f}
        )
    assert r.status_code == 422, f"Expected 422, got {r.status_code}"
    print(f"[PASSED] File invalid berhasil ditolak (422 Unprocessable Entity). Response: {r.json()['detail']}")

    # 5. Test Live Hot-Reload Model Valid (Chromium)
    print("\n[TEST 5] Testing Live Hot-Reload Model Valid (Chromium)...")
    with open(valid_file, "rb") as f:
        r = requests.post(
            f"{BASE_URL}/api/v2/reload-model",
            headers={"X-Internal-Key": SECRET_KEY},
            data={"target": "chromium"},
            files={"file": f}
        )
    assert r.status_code == 200, f"Expected 200, got {r.status_code}"
    res_data = r.json()
    assert res_data["status"] == "success", "Expected status == success"
    print(f"[PASSED] Live Swap Chromium Berhasil! Message: {res_data['message']}")

    # 6. Test Prediction Inference Setelah Live Reload
    print("\n[TEST 6] Testing Post-Reload Prediction (/predict)...")
    dummy_payload = {
        "ec": 850.0,
        "tds": 420.0,
        "ph": 7.2,
        "suhu_air": 27.5,
        "suhu_lingkungan": 30.1,
        "kelembapan": 78.0,
        "tegangan": 4.1
    }
    r = requests.post(f"{BASE_URL}/predict", json=dummy_payload)
    assert r.status_code == 200, f"Expected 200, got {r.status_code}"
    pred_res = r.json()
    print(f"[PASSED] Estimasi AI Sukses Pasca Reload! Response: {pred_res}")

    # Clean up temp test files
    if os.path.exists(valid_file): os.remove(valid_file)
    if os.path.exists(invalid_file): os.remove(invalid_file)

    print("\n==================================================")
    print("[QC PASSED] SEMUA PENGUJIAN QC SERTA HOT-RELOAD 100% SUKSES")
    print("==================================================")

if __name__ == "__main__":
    run_qc_tests()
