import sys
import json
import pickle
from pathlib import Path
from datetime import timedelta
import os

import numpy as np
import pandas as pd

# مسار ملف الموديلات (نفس مجلد السكربت)
MODEL_PATH = Path(__file__).with_name("xgb_models.pkl")


def load_models():
    """تحميل قاموس الموديلات {product_id: model} من ملف pickle."""
    with open(MODEL_PATH, "rb") as f:
        models = pickle.load(f)
    return models


# نفس قائمة الـ features المستخدمة في التدريب
SELECTED_FEATURES = [
    "dayofweek",
    "weekofyear",
    "month",
    "day",
    "lag_1",
    "lag_3",
    "lag_7",
    "lag_14",
    "roll_mean_3",
    "roll_mean_7",
    "roll_mean_14",
]


def forecast_for_product(history_df: pd.DataFrame, model, horizon: int = 7):
    """
    history_df: تاريخ المبيعات لمنتج واحد (date, quantity_sold)
    model: موديل XGBoost لهذا المنتج
    horizon: عدد الأيام للأمام
    """
    history_df = history_df.sort_values("date")

    # لازم يكون فيه على الأقل 14 يوم عشان نستخدم نفس منطق التدريب
    if len(history_df) < 14:
        return None

    # نأخذ آخر 14 يوم كنافذة
    window = history_df.tail(14)[["date", "quantity_sold"]].copy()

    future_dates = []
    future_preds = []

    for _ in range(horizon):
        last_date = window["date"].iloc[-1]
        next_date = last_date + timedelta(days=1)

        new_row = {
            "date": next_date,
            "dayofweek": next_date.dayofweek,
            "weekofyear": int(next_date.isocalendar().week),
            "month": next_date.month,
            "day": next_date.day,
        }

        # lags 1, 3, 7, 14
        for lag in [1, 3, 7, 14]:
            new_row[f"lag_{lag}"] = window["quantity_sold"].iloc[-lag]

        # rolling means
        new_row["roll_mean_3"] = window["quantity_sold"].tail(3).mean()
        new_row["roll_mean_7"] = window["quantity_sold"].tail(7).mean()
        new_row["roll_mean_14"] = window["quantity_sold"].tail(14).mean()

        X_future = (
            pd.DataFrame([new_row])[SELECTED_FEATURES]
            .apply(pd.to_numeric, errors="coerce")
            .astype(float)
        )

        # التنبؤ لليوم القادم
        yhat = float(model.predict(X_future)[0])

        future_dates.append(next_date.strftime("%Y-%m-%d"))
        future_preds.append(yhat)

        # نضيف التنبؤ للنافذة عشان نستخدمه في lags والخطوات الجاية
        window = pd.concat(
            [window, pd.DataFrame({"date": [next_date], "quantity_sold": [yhat]})],
            ignore_index=True,
        )

    return future_dates, future_preds


def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "no_input"}))
        return

    arg = sys.argv[1]

    # لو argument عبارة عن مسار ملف JSON نقرأ منه
    if os.path.exists(arg):
        with open(arg, "r", encoding="utf-8") as f:
            payload = json.load(f)
    else:
        # fallback: لو تم إرسال JSON مباشرة (الأسلوب القديم)
        payload = json.loads(arg)

    rows = payload.get("rows", [])
    horizon = int(payload.get("horizon", 7))

    if not rows:
        print(json.dumps({"dates": [], "predictions": []}))
        return

    df = pd.DataFrame(rows)

    # نتأكد من الأعمدة المطلوبة
    if not {"date", "quantity_sold", "product_id"}.issubset(df.columns):
        print(json.dumps({"error": "missing_columns"}))
        return

    # تحويل التاريخ
    df["date"] = pd.to_datetime(df["date"])

    # تحميل الموديلات
    try:
        models = load_models()
    except Exception as e:
        print(json.dumps({"error": f"load_models_failed: {e}"}))
        return

    agg_dates = None
    agg_preds = None

    # Group by product_id ونسوي forecast لكل منتج
    for pid, g in df.groupby("product_id"):
        # نحاول نلاقي الموديل بالمفتاح المناسب (int أو str)
        candidates = [pid]
        pid_str = str(pid)
        if pid_str not in candidates:
            candidates.append(pid_str)
        if isinstance(pid, str) and pid.isdigit():
            candidates.append(int(pid))

        model = None
        for key in candidates:
            if key in models:
                model = models[key]
                break

        if model is None:
            # مافي موديل لهذا المنتج → نتجاهله
            continue

        result = forecast_for_product(
            g[["date", "quantity_sold"]].copy(), model, horizon=horizon
        )
        if result is None:
            continue

        dates, preds = result
        preds = np.array(preds, dtype=float)

        if agg_dates is None:
            agg_dates = dates
            agg_preds = preds
        else:
            # نجمع التنبؤات عبر المنتجات لخط واحد
            agg_preds += preds

    if agg_dates is None:
        # ما قدرنا نتنبأ لأي منتج
        out = {
            "dates": payload.get("future_dates", []),
            "predictions": [0.0] * len(payload.get("future_dates", [])),
        }
    else:
        out = {
            "dates": agg_dates,
            "predictions": agg_preds.tolist(),
        }

    print(json.dumps(out))


if __name__ == "__main__":
    main()
