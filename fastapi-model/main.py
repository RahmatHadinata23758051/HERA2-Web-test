from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import pickle
import os
import json
import numpy as np
import pandas as pd

app = FastAPI(title="HERA Sensor API", version="2.0")

# Allow CORS for Laravel
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:8000"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Load Models & Calibration
MODEL_DIR = os.path.join(os.path.dirname(__file__), "models")
CHROMIUM_MODEL_PATH = os.path.join(MODEL_DIR, "best_model_chromium_v2.pkl")
NICKEL_MODEL_PATH = os.path.join(MODEL_DIR, "best_model_nickel_v2.pkl")
CALIBRATION_PATH = os.path.join(MODEL_DIR, "calibration_factors.json")

pack_cr = None
pack_ni = None
calibration_factors = None

try:
    if os.path.exists(CHROMIUM_MODEL_PATH):
        with open(CHROMIUM_MODEL_PATH, "rb") as f:
            pack_cr = pickle.load(f)
        print(f"[OK] Loaded fine-tuned Chromium model: {pack_cr.get('best_model', 'XGBoost')}")
    else:
        print(f"[WARN] Chromium model not found at: {CHROMIUM_MODEL_PATH}")

    if os.path.exists(NICKEL_MODEL_PATH):
        with open(NICKEL_MODEL_PATH, "rb") as f:
            pack_ni = pickle.load(f)
        print(f"[OK] Loaded fine-tuned Nickel model: {pack_ni.get('best_model', 'Random Forest')}")
    else:
        print(f"[WARN] Nickel model not found at: {NICKEL_MODEL_PATH}")

    if os.path.exists(CALIBRATION_PATH):
        with open(CALIBRATION_PATH, "r") as f:
            calibration_factors = json.load(f)
        print(f"[OK] Loaded empirical calibration factors successfully.")
    else:
        print(f"[WARN] Calibration factors not found at: {CALIBRATION_PATH}")

except Exception as e:
    print(f"Error loading fine-tuned v2 models or calibration: {e}")

class SensorData(BaseModel):
    ec: float
    tds: float
    ph: float
    suhu_air: float
    suhu_lingkungan: float
    kelembapan: float
    tegangan: float

def apply_calibration(y_raw: float, metal: str) -> float:
    if calibration_factors is None or metal not in calibration_factors:
        return y_raw
    
    factors = calibration_factors[metal]
    method = factors.get("method")
    alpha = factors.get("alpha", 1.0)
    beta = factors.get("beta", 0.0)
    
    if method == "linear":
        pred_cal = alpha * y_raw + beta
        return max(pred_cal, 0.0)
    elif method == "power_law":
        return alpha * (max(y_raw, 1e-3) ** beta)
    else:
        return y_raw

@app.get("/health")
def health_check():
    model_loaded = pack_cr is not None and pack_ni is not None
    cal_loaded = calibration_factors is not None
    return {
        "status": "ok",
        "model_loaded": model_loaded,
        "calibration_loaded": cal_loaded,
        "chromium_model": pack_cr.get("best_model", "None") if pack_cr else "None",
        "nickel_model": pack_ni.get("best_model", "None") if pack_ni else "None",
        "version": "2.0"
    }

@app.post("/predict")
def predict(data: SensorData):
    if pack_cr is None or pack_ni is None:
        raise HTTPException(status_code=500, detail="Models or scalers not loaded")
    
    try:
        # 1. Extract raw features from SensorData payload
        pH = float(data.ph)
        EC_uScm = float(data.ec)
        TDS_mgL = float(data.tds)
        Suhu_Air = float(data.suhu_air)

        # 2. Extract physics-informed derived features
        pH_squared = pH ** 2
        pH_EC_interact = pH * EC_uScm
        log_EC = float(np.log10(np.maximum(EC_uScm, 1e-6)))
        pOH_proxy = 14.0 - pH
        pH_temp_interact = pH * Suhu_Air

        # 3. Assemble into exact column order required by scalers
        feature_names = [
            "pH", "EC_uScm", "TDS_mgL", "Suhu_Air",
            "pH_squared", "pH_EC_interact", "log_EC",
            "pOH_proxy", "pH_temp_interact"
        ]
        input_data = [[
            pH, EC_uScm, TDS_mgL, Suhu_Air,
            pH_squared, pH_EC_interact, log_EC,
            pOH_proxy, pH_temp_interact
        ]]
        df_input = pd.DataFrame(input_data, columns=feature_names)

        # ── Chromium Prediction ─────────────────────────────────────────────
        scaler_cr = pack_cr["scaler"]
        model_cr = pack_cr["model"]
        
        scaled_cr = scaler_cr.transform(df_input.values)
        cr_pred_ug_raw = float(model_cr.predict(scaled_cr)[0])
        
        # Apply empirical calibration
        cr_pred_ug = apply_calibration(cr_pred_ug_raw, "chromium")
        cr_val = cr_pred_ug / 1000.0  # ug/L -> mg/L

        # Classify status based on 50 ug/L (0.05 mg/L) WHO limit
        if cr_val < 0.05:
            cr_status = "normal"
        elif cr_val <= 0.1:
            cr_status = "warning"
        else:
            cr_status = "danger"

        # ── Nickel Prediction ───────────────────────────────────────────────
        scaler_ni = pack_ni["scaler"]
        model_ni = pack_ni["model"]
        
        scaled_ni = scaler_ni.transform(df_input.values)
        ni_pred_ug_raw = float(model_ni.predict(scaled_ni)[0])
        
        # Apply empirical calibration
        ni_pred_ug = apply_calibration(ni_pred_ug_raw, "nickel")
        ni_val = ni_pred_ug / 1000.0  # ug/L -> mg/L

        # Classify status based on 20 ug/L (0.02 mg/L) WHO limit
        if ni_val < 0.02:
            ni_status = "normal"
        elif ni_val <= 0.04:
            ni_status = "warning"
        else:
            ni_status = "danger"

        # Determine overall status based on the maximum severity of both metals
        if cr_status == "danger" or ni_status == "danger":
            overall_status = "danger"
        elif cr_status == "warning" or ni_status == "warning":
            overall_status = "warning"
        else:
            overall_status = "normal"

        # 4. Formulate the response maintaining perfect backward-compatibility
        return {
            "cr_estimated": round(cr_val, 5),
            "ni_estimated": round(ni_val, 5),
            "status": overall_status,
            "unit": "mg/L",
            "chromium": {
                "value": round(cr_val, 5),
                "status": cr_status,
                "unit": "mg/L"
            },
            "nickel": {
                "value": round(ni_val, 5),
                "status": ni_status,
                "unit": "mg/L"
            }
        }

    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Inference error: {str(e)}")

