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
# شكل البيانات المتوقّع: { "dates": ["2025-12-01", "2025-12-02", ...] }
data = json.load(sys.stdin)
dates = data.get("dates", [])

# =============================
#   تجهيز الـ features للمودل
# =============================
# ⚠️ هنا لازم يكون نفس تجهيز البيانات اللي استخدمتيه وقت التدريب.
# أنا حاط مثال بسيط (day index + day_of_week) عشان يشتغل مبدئياً،
# لكن إذا المودل تدرب على أعمدة أكثر، عدّلي الكود هنا.

X = []
for i, d in enumerate(dates):
    dt = datetime.strptime(d, "%Y-%m-%d")
    day_index = i + 1
    day_of_week = dt.weekday()  # 0 = Monday ... 6 = Sunday

    # TODO: عدّلي هذه القائمة عشان تطابق أعمدة التدريب بالضبط
    # مثال لو تدربتي على [day_index, day_of_week]:
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
