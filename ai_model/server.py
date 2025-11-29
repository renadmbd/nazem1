from flask import Flask, request, jsonify
from datetime import datetime, timedelta
import pandas as pd
import joblib

# حمّل المودل مرة وحدة عند تشغيل السيرفر
MODEL_PATH = "xgb_models.pkl"
model = joblib.load(MODEL_PATH)

app = Flask(__name__)

def build_feature_frame(payload):
    """
    هنا نبني DataFrame بنفس الأعمدة اللي تدرب عليها المودل:
    expiration_date, category, price, quantity_sold (label), date, product_id

    إحنا في التنبؤ **ما عندنا quantity_sold** لأنه هو اللي نتنبأ له,
    فبنحط أي رقم placeholder والمودل راح يتجاهله لو التدريب كان صح
    (أو يكون العمود أصلاً هو الـ target داخل الـ pipeline).
    لو في سكربت التدريب عندك, يفضّل تحفظي الـ pipeline بحيث يتوقع
    من DataFrame بدون عمود quantity_sold.
    """

    product_id      = payload["product_id"]
    category        = payload["category"]
    price           = payload["price"]
    expiration_date = payload["expiration_date"]  # نص, نفس فورمات التدريب
    start_date      = datetime.fromisoformat(payload["start_date"])
    days            = int(payload.get("days", 7))

    dates = [start_date + timedelta(days=i) for i in range(days)]

    # NOTE: غيّري فورمات التاريخ هنا لو ملف التدريب كان مثلاً 10/29/2026
    # مثلاً: d.strftime('%m/%d/%Y')
    df = pd.DataFrame({
        "expiration_date": [expiration_date] * days,
        "category": [category] * days,
        "price": [price] * days,
        "quantity_sold": [0] * days,         # placeholder فقط
        "date": [d.strftime('%Y-%m-%d') for d in dates],
        "product_id": [product_id] * days,
    })

    return df, dates

@app.post("/forecast")
def forecast():
    """
    JSON متوقع من Laravel:
    {
        "product_id": 1001,
        "category": "food",
        "price": 67.5,
        "expiration_date": "2026-10-29",
        "start_date": "2025-11-30",   # ISO
        "days": 7
    }
    """
    try:
        payload = request.get_json(force=True)
        df, dates = build_feature_frame(payload)

        # لو المودل عبارة عن pipeline (preprocess + model) هذا يكفي:
        preds = model.predict(df)

        # نحول لـ list + تواريخ كنصوص
        results = {
            "dates": [d.strftime('%Y-%m-%d') for d in dates],
            "predictions": [float(p) for p in preds]
        }
        return jsonify(results)

    except Exception as e:
        # عشان لو صار خطأ تشوفيه في الـ log
        return jsonify({"error": str(e)}), 500


if __name__ == "__main__":
    # نشغّل على بورت 5001
    app.run(host="127.0.0.1", port=5001, debug=True)
