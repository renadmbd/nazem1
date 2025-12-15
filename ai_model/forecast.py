import sys
import json
import pickle
from pathlib import Path
from datetime import datetime
import numpy as np

# مكاننا الحالي = مجلد ai_model
BASE_DIR = Path(__file__).resolve().parent

# تحميل المودل
MODEL_PATH = BASE_DIR / "xgb_models.pkl"
with open(MODEL_PATH, "rb") as f:
    model = pickle.load(f)

# قراءة الـ JSON اللي جاي من Laravel من الـ stdin
data = json.load(sys.stdin)
dates = data.get("dates", [])


X = []
for i, d in enumerate(dates):
    dt = datetime.strptime(d, "%Y-%m-%d")
    day_index = i + 1
    day_of_week = dt.weekday()  # 0 = Monday ... 6 = Sunday

    features = [day_index, day_of_week]

    X.append(features)

X = np.array(X)

# تشغيل التنبؤ
y_pred = model.predict(X)

# تجهيز الإخراج
out = {
    "labels": dates,
    "values": [float(v) for v in y_pred],
}

json.dump(out, sys.stdout)
