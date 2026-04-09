from fastapi.testclient import TestClient
from main import app

client = TestClient(app)

print("--- Testing /health ---")
response = client.get("/health")
print(response.status_code)
print(response.json())

print("\n--- Testing /predict ---")
payload = {
    "ec": 500,
    "tds": 250,
    "ph": 7.0,
    "suhu_air": 28.5,
    "suhu_lingkungan": 30.0,
    "kelembapan": 75.0,
    "tegangan": 4.0
}
response = client.post("/predict", json=payload)
print(response.status_code)
print(response.json())
