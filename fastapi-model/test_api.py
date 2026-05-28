from fastapi.testclient import TestClient
from main import app

client = TestClient(app)

print("--- Testing /health ---")
response = client.get("/health")
assert response.status_code == 200, f"Health check failed with {response.status_code}"
health_data = response.json()
print("Status Code:", response.status_code)
print("Response:", health_data)
assert health_data["status"] == "ok"
assert health_data["model_loaded"] is True, "Models failed to load"

print("\n--- Testing /predict ---")
payload = {
    "ec": 500.0,
    "tds": 320.0,
    "ph": 7.0,
    "suhu_air": 28.5,
    "suhu_lingkungan": 30.0,
    "kelembapan": 75.0,
    "tegangan": 4.0
}
response = client.post("/predict", json=payload)
assert response.status_code == 200, f"Prediction failed with {response.status_code}"
pred_data = response.json()
print("Status Code:", response.status_code)
print("Response:", pred_data)

# Assertions for backward-compatibility & correctness
assert "cr_estimated" in pred_data, "Missing cr_estimated"
assert "ni_estimated" in pred_data, "Missing ni_estimated"
assert "status" in pred_data, "Missing status"
assert "unit" in pred_data, "Missing unit"

assert "chromium" in pred_data, "Missing chromium block"
assert "nickel" in pred_data, "Missing nickel block"

cr_block = pred_data["chromium"]
ni_block = pred_data["nickel"]

assert "value" in cr_block and "status" in cr_block and "unit" in cr_block, "Malformed chromium block"
assert "value" in ni_block and "status" in ni_block and "unit" in ni_block, "Malformed nickel block"

assert cr_block["unit"] == "mg/L"
assert ni_block["unit"] == "mg/L"

print("\n[SUCCESS] All API tests passed perfectly!")
