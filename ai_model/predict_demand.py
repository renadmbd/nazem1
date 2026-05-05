import warnings
warnings.filterwarnings("ignore", category=UserWarning)

import sys
import json
import os
from datetime import timedelta
import numpy as np
import pandas as pd

def prepare_daily_history(history_df: pd.DataFrame) -> pd.DataFrame:
    """
    يحول سجل الطلبات إلى سلسلة يومية:
    - يجمع المبيعات في نفس اليوم
    - يملأ الأيام الناقصة بصفر
    """
    history_df = history_df.copy()
    history_df["date"] = pd.to_datetime(history_df["date"])

    history_df = (
        history_df.groupby("date", as_index=False)["quantity_sold"]
        .sum()
        .sort_values("date")
    )

    if history_df.empty:
        return history_df

    full_range = pd.date_range(
        start=history_df["date"].min(),
        end=history_df["date"].max(),
        freq="D"
    )

    history_df = (
        history_df.set_index("date")
        .reindex(full_range, fill_value=0)
        .rename_axis("date")
        .reset_index()
    )

    return history_df


def simple_forecast(history_df: pd.DataFrame, horizon: int = 30):
    """
    Forecast مبسط:
    - يعتمد على متوسط آخر 7 أيام
    - ومتوسط آخر 14 يوم
    - واتجاه trend بسيط
    """
    history_df = prepare_daily_history(history_df)

    if history_df.empty:
        return [], []

    series = history_df["quantity_sold"].astype(float).tolist()
    last_date = history_df["date"].iloc[-1]

    future_dates = []
    future_preds = []

    for step in range(1, horizon + 1):
        recent_7 = series[-7:] if len(series) >= 7 else series
        recent_14 = series[-14:] if len(series) >= 14 else series

        avg_7 = float(np.mean(recent_7)) if recent_7 else 0.0
        avg_14 = float(np.mean(recent_14)) if recent_14 else 0.0

        # trend بسيط: الفرق بين متوسط آخر 7 ومتوسط آخر 14
        trend = avg_7 - avg_14

        # forecast weighted
        pred = (avg_7 * 0.65) + (avg_14 * 0.35) + (trend * 0.25)

        # damping خفيف حتى لا يتضخم التوقع بزيادة
        pred *= (1 - min(step * 0.01, 0.15))

        # منع السالب
        pred = max(0.0, pred)

        next_date = last_date + timedelta(days=step)
        future_dates.append(next_date.strftime("%Y-%m-%d"))
        future_preds.append(round(pred, 2))

        # نضيف التوقع للسلسلة حتى نبني عليه التوقعات التالية
        series.append(pred)

    return future_dates, future_preds


def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "no_input"}))
        return

    arg = sys.argv[1]

    if os.path.exists(arg):
        with open(arg, "r", encoding="utf-8") as f:
            payload = json.load(f)
    else:
        payload = json.loads(arg)

    rows = payload.get("rows", [])
    horizon = int(payload.get("horizon", 30))
    future_dates_payload = payload.get("future_dates", [])

    if not rows:
        print(json.dumps({
            "dates": future_dates_payload,
            "predictions": [0.0] * len(future_dates_payload)
        }))
        return

    df = pd.DataFrame(rows)

    required_cols = {"date", "quantity_sold", "product_id"}
    if not required_cols.issubset(df.columns):
        print(json.dumps({"error": "missing_columns"}))
        return

    df["date"] = pd.to_datetime(df["date"])
    df["quantity_sold"] = pd.to_numeric(df["quantity_sold"], errors="coerce").fillna(0)

    agg_dates = None
    agg_preds = None
    used_products = []
    debug = []

    for pid, group in df.groupby("product_id"):
        dates, preds = simple_forecast(group[["date", "quantity_sold"]].copy(), horizon=horizon)

        if not dates or not preds:
            debug.append({
                "product_id": str(pid),
                "status": "skipped_no_history"
            })
            continue

        preds = np.array(preds, dtype=float)

        debug.append({
            "product_id": str(pid),
            "status": "forecasted",
            "sample_predictions": preds[:5].tolist()
        })

        used_products.append(str(pid))

        if agg_dates is None:
            agg_dates = dates
            agg_preds = preds
        else:
            agg_preds += preds

    if agg_dates is None:
        out = {
            "dates": future_dates_payload,
            "predictions": [0.0] * len(future_dates_payload),
            "error": "no_usable_products_for_forecast",
            "debug": debug
        }
    else:
        out = {
            "dates": agg_dates,
            "predictions": agg_preds.round(2).tolist(),
            "used_products": used_products,
            "debug": debug
        }

    print(json.dumps(out, ensure_ascii=False))


if __name__ == "__main__":
    main()