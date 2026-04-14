FROM python:3.10-slim

WORKDIR /app

# Salin requirements dan instal
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Salin script FastAPI dan AI Model (scaler.pkl, rf_model.pkl)
COPY . .

# Ekspos port FastAPI 
EXPOSE 8001

# Jalankan Uvicorn server mengarah ke seluruh IP kontainer (0.0.0.0)
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8001"]
