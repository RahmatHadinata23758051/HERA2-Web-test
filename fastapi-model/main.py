from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import joblib
import os
import numpy as np
import pandas as pd

app = FastAPI(title="HERA Sensor API", version="1.0")

# Allow CORS for Laravel
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:8000"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Load Models
MODEL_DIR = os.path.join(os.path.dirname(__file__), "models")
MODEL_PATH = os.path.join(MODEL_DIR, "best_model_rf_full.pkl")
SCALER_PATH = os.path.join(MODEL_DIR, "best_model_scaler_full.pkl")

model = None
scaler = None

try:
    if os.path.exists(MODEL_PATH):
        model = joblib.load(MODEL_PATH)
    if os.path.exists(SCALER_PATH):
        scaler = joblib.load(SCALER_PATH)
except Exception as e:
    print(f"Error loading model/scaler: {e}")

class SensorData(BaseModel):
    ec: float
    tds: float
    ph: float
    suhu_air: float
    suhu_lingkungan: float
    kelembapan: float
    tegangan: float

@app.get("/health")
def health_check():
    model_loaded = model is not None and scaler is not None
    return {
        "status": "ok",
        "model_loaded": model_loaded
    }

@app.post("/predict")
def predict(data: SensorData):
    if model is None or scaler is None:
        raise HTTPException(status_code=500, detail="Model or scaler not loaded")
    
    # Format input as a DataFrame or 2D array
    # Adjust columns to match what the scaler/model expects. Usually it expects a 2D array.
    input_data = np.array([[
        data.ec,
        data.tds,
        data.ph,
        data.suhu_air,
        data.suhu_lingkungan,
        data.kelembapan,
        data.tegangan
    ]])
    
    # Note: If the scaler was fitted with specific column names in pandas, 
    # we might need to recreate a DataFrame here:
    feature_names = [
        'EC', 
        'TDS', 
        'pH', 
        'Suhu Air (°C)', 
        'Suhu Lingkungan (°C)', 
        'Kelembapan Lingkungan (%)', 
        'Tegangan (V)'
    ]
    df_input = pd.DataFrame(input_data, columns=feature_names)
    
    try:
        scaled_data = scaler.transform(df_input)
        prediction = model.predict(scaled_data)
        
        cr_val = float(prediction[0])
        
        # Determine status
        if cr_val < 50:
            status = "normal"
        elif cr_val <= 100:
            status = "warning"
        else:
            status = "danger"
            
        return {
            "cr_estimated": round(cr_val, 2),
            "status": status,
            "unit": "µg/L"
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
