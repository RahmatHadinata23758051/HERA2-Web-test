from fastapi import FastAPI, HTTPException, File, UploadFile, Header, Form
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import pickle
import os
import json
import shutil
import time
import threading
import numpy as np
import pandas as pd

app = FastAPI(title="HERA Sensor API", version="2.0")

# Allow CORS for Laravel
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Secret Key for Internal API Gateway Communication
INTERNAL_API_SECRET = os.getenv("INTERNAL_AI_SECRET", "hera-internal-secret-key-2026")

# Load Models & Calibration
MODEL_DIR = os.path.join(os.path.dirname(__file__), "models")
os.makedirs(MODEL_DIR, exist_ok=True)
CHROMIUM_MODEL_PATH = os.path.join(MODEL_DIR, "best_model_chromium_v2.pkl")
NICKEL_MODEL_PATH = os.path.join(MODEL_DIR, "best_model_nickel_v2.pkl")
CALIBRATION_PATH = os.path.join(MODEL_DIR, "calibration_factors.json")
METADATA_PATH = os.path.join(MODEL_DIR, "models_metadata.json")

# Thread lock for atomic model swapping
model_lock = threading.Lock()

pack_cr = None
pack_ni = None
calibration_factors = None
models_metadata = {
    "chromium_filename": "best_model_chromium_v2.pkl",
    "nickel_filename": "best_model_nickel_v2.pkl"
}

def load_models_from_disk():
    global pack_cr, pack_ni, calibration_factors, models_metadata
    with model_lock:
        try:
            if os.path.exists(METADATA_PATH):
                try:
                    with open(METADATA_PATH, "r") as f:
                        models_metadata.update(json.load(f))
                except Exception:
                    pass

            if os.path.exists(CHROMIUM_MODEL_PATH):
                with open(CHROMIUM_MODEL_PATH, "rb") as f:
                    pack_cr = pickle.load(f)
                if isinstance(pack_cr, dict) and "original_filename" not in pack_cr:
                    pack_cr["original_filename"] = models_metadata.get("chromium_filename", "best_model_chromium_v2.pkl")
                print(f"[OK] Loaded Chromium model: {pack_cr.get('best_model', 'XGBoost')} ({models_metadata.get('chromium_filename')})")

            if os.path.exists(NICKEL_MODEL_PATH):
                with open(NICKEL_MODEL_PATH, "rb") as f:
                    pack_ni = pickle.load(f)
                if isinstance(pack_ni, dict) and "original_filename" not in pack_ni:
                    pack_ni["original_filename"] = models_metadata.get("nickel_filename", "best_model_nickel_v2.pkl")
                print(f"[OK] Loaded Nickel model: {pack_ni.get('best_model', 'Random Forest')} ({models_metadata.get('nickel_filename')})")

            if os.path.exists(CALIBRATION_PATH):
                with open(CALIBRATION_PATH, "r") as f:
                    calibration_factors = json.load(f)
                print(f"[OK] Loaded empirical calibration factors.")
        except Exception as e:
            print(f"Error loading models or calibration: {e}")

load_models_from_disk()

class SensorData(BaseModel):
    ec: float
    tds: float
    ph: float
    suhu_air: float
    suhu_lingkungan: float
    kelembapan: float
    tegangan: float

def validate_model_pack(pack_obj) -> tuple[bool, str]:
    """Uji coba dry-run pada model pack baru sebelum diaktifkan."""
    if not isinstance(pack_obj, dict):
        return False, "Format file model harus berupa dictionary yang ter-pickle."
    
    if "scaler" not in pack_obj or "model" not in pack_obj:
        return False, "File model tidak memiliki kunci 'scaler' atau 'model' yang valid."

    try:
        scaler = pack_obj["scaler"]
        model = pack_obj["model"]

        # Dummy feature matrix (9 features)
        feature_names = [
            "pH", "EC_uScm", "TDS_mgL", "Suhu_Air",
            "pH_squared", "pH_EC_interact", "log_EC",
            "pOH_proxy", "pH_temp_interact"
        ]
        df_dummy = pd.DataFrame([[
            7.0, 1000.0, 500.0, 28.0,
            49.0, 7000.0, 3.0,
            7.0, 196.0
        ]], columns=feature_names)

        scaled = scaler.transform(df_dummy.values)
        pred = float(model.predict(scaled)[0])

        if np.isnan(pred) or np.isinf(pred):
            return False, "Hasil simulasi prediksi bernilai NaN atau Infinity."

        return True, f"Model valid. Hasil simulasi prediksi dummy: {pred:.5f} ug/L"
    except Exception as e:
        return False, f"Gagal uji dry-run prediksi: {str(e)}"

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
    with model_lock:
        local_cr = pack_cr
        local_ni = pack_ni
    model_loaded = local_cr is not None and local_ni is not None
    cal_loaded = calibration_factors is not None
    return {
        "status": "ok",
        "model_loaded": model_loaded,
        "calibration_loaded": cal_loaded,
        "chromium_model": local_cr.get("best_model", "None") if local_cr else "None",
        "chromium_filename": local_cr.get("original_filename", models_metadata.get("chromium_filename", "best_model_chromium_v2.pkl")) if local_cr else "None",
        "nickel_model": local_ni.get("best_model", "None") if local_ni else "None",
        "nickel_filename": local_ni.get("original_filename", models_metadata.get("nickel_filename", "best_model_nickel_v2.pkl")) if local_ni else "None",
        "version": "2.0"
    }

@app.get("/api/v2/models/info")
def get_models_info():
    """Mengembalikan informasi status dan metadata file model yang sedang aktif."""
    def get_file_info(path, pack_obj, metal_key):
        if not os.path.exists(path):
            return {"exists": False, "name": "File Not Found", "filename": "N/A", "size_kb": 0, "last_modified": "N/A"}
        stat = os.stat(path)
        mod_time = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(stat.st_mtime))
        model_name = pack_obj.get("best_model", "Custom Model") if isinstance(pack_obj, dict) else "Custom Model"
        filename = pack_obj.get("original_filename", models_metadata.get(f"{metal_key}_filename", os.path.basename(path))) if isinstance(pack_obj, dict) else os.path.basename(path)
        return {
            "exists": True,
            "name": model_name,
            "filename": filename,
            "size_kb": round(stat.st_size / 1024, 2),
            "last_modified": mod_time
        }

    with model_lock:
        cr_info = get_file_info(CHROMIUM_MODEL_PATH, pack_cr, "chromium")
        ni_info = get_file_info(NICKEL_MODEL_PATH, pack_ni, "nickel")

    return {
        "chromium": cr_info,
        "nickel": ni_info
    }

@app.post("/api/v2/reload-model")
async def reload_model(
    target: str = Form(...),
    model_name: str = Form(None),
    file: UploadFile = File(...),
    x_internal_key: str = Header(None, alias="X-Internal-Key")
):
    """Endpoint internal untuk mengunggah, memvalidasi, dan me-reload model ML secara live (zero downtime)."""
    # 1. Autentikasi Secret Key
    if x_internal_key != INTERNAL_API_SECRET:
        raise HTTPException(status_code=401, detail="Unauthorized internal key")

    target = target.lower().strip()
    if target not in ["chromium", "nickel"]:
        raise HTTPException(status_code=400, detail="Target model tidak valid. Pilih 'chromium' atau 'nickel'.")

    # 2. Baca file & unpickle
    try:
        file_bytes = await file.read()
        pack_obj = pickle.loads(file_bytes)
    except Exception as e:
        raise HTTPException(status_code=422, detail=f"File gagal di-unpickle: {str(e)}")

    # 3. Jalankan Dry-Run Test
    is_valid, val_msg = validate_model_pack(pack_obj)
    if not is_valid:
        raise HTTPException(status_code=422, detail=f"Validasi dry-run gagal: {val_msg}")

    # Simpan original filename & custom model_name (jika diinputkan user)
    pack_obj["original_filename"] = file.filename or f"model_{target}.pkl"
    if model_name and model_name.strip():
        pack_obj["best_model"] = model_name.strip()

    # 4. Backup model lama & simpan file baru ke disk
    target_path = CHROMIUM_MODEL_PATH if target == "chromium" else NICKEL_MODEL_PATH
    backup_path = target_path + ".bak"

    try:
        if os.path.exists(target_path):
            shutil.copy2(target_path, backup_path)
            
        with open(target_path, "wb") as f:
            f.write(file_bytes)

        # 5. Atomic In-Memory Swap & Save Metadata
        global pack_cr, pack_ni, models_metadata
        with model_lock:
            if target == "chromium":
                pack_cr = pack_obj
                models_metadata["chromium_filename"] = pack_obj["original_filename"]
            else:
                pack_ni = pack_obj
                models_metadata["nickel_filename"] = pack_obj["original_filename"]

            try:
                with open(METADATA_PATH, "w") as f:
                    json.dump(models_metadata, f, indent=2)
            except Exception as ex:
                print(f"[WARN] Failed to write metadata json: {ex}")

        return {
            "status": "success",
            "message": f"Model {target.capitalize()} berhasil divalidasi dan di-reload secara live!",
            "detail": val_msg,
            "model_info": {
                "best_model": pack_obj.get("best_model", "Custom ML Model"),
                "filename": pack_obj["original_filename"],
                "size_kb": round(len(file_bytes) / 1024, 2)
            }
        }
    except Exception as e:
        # Emergency Rollback jika ada kegagalan penulisan disk
        if os.path.exists(backup_path):
            shutil.copy2(backup_path, target_path)
        raise HTTPException(status_code=500, detail=f"Gagal memperbarui file model di disk: {str(e)}")

@app.post("/predict")
def predict(data: SensorData):
    with model_lock:
        local_cr = pack_cr
        local_ni = pack_ni

    if local_cr is None or local_ni is None:
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
        scaler_cr = local_cr["scaler"]
        model_cr = local_cr["model"]
        
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
        scaler_ni = local_ni["scaler"]
        model_ni = local_ni["model"]
        
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


